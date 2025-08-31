<?php

namespace core\user;

use core\Core;
use core\AtPlayer as Player;
use core\session\mysqli\data\{
	MySqlRequest,
	MySqlQuery
};
use core\staff\anticheat\AntiCheat;

class UserPool {

	public static $newBatchId = 0;

	public array $loadingBatches = [];
	public array $loadingBatchClosures = [];

	public array $loading = [];

	public array $loadedUsersByXuid = [];
	public array $loadedUsersByGamertag = [];

	public static function newBatchId(): int {
		return self::$newBatchId++;
	}

	public function tick(): void {
		foreach ($this->getLoadingBatches() as $batchId => $loading) {
			$allLoaded = true;
			foreach ($loading as $loadBy => $user) {
				if ($user === null) {
					$allLoaded = false;
					break;
				}
			}
			if ($allLoaded) {
				$closure = $this->getLoadingBatchClosure($batchId);
				if ($closure instanceof \Closure) {
					$closure($loading);
				}
				unset($this->loadingBatches[$batchId]);
				unset($this->loadingBatchClosures[$batchId]);
			}
		}
	}

	public function getLoadingUsers(): array {
		return $this->loading;
	}

	public function getLoadingUser(string|int $key): ?\Closure {
		return $this->loading[$key] ?? null;
	}

	public function getLoadingBatches(): array {
		return $this->loadingBatches;
	}

	public function getLoadingBatch(string $batchId): ?array {
		return $this->getLoadingBatches()[$batchId] ?? null;
	}

	public function getLoadingBatchClosures(): array {
		return $this->loadingBatchClosures;
	}

	public function getLoadingBatchClosure(string $batchId): ?\Closure {
		return $this->getLoadingBatchClosures()[$batchId] ?? null;
	}

	public function getLoadedUsersByXuid(): array {
		return $this->loadedUsersByXuid;
	}

	public function getLoadedUsersByGamertag(): array {
		return $this->loadedUsersByGamertag;
	}

	public function addByPlayer(Player $player): User {
		$user = new User((int) $player->getXuid(), $player->getName());
		$this->loadedUsersByXuid[$user->getXuid()] = $user;
		$this->loadedUsersByGamertag[strtolower($user->getGamertag())] = $user;
		return $user;
	}

	public function useUsers(array $players, \Closure $return, bool $withRank = false): void {
		$batchId = self::newBatchId();
		$loading = [];
		foreach ($players as $loadBy) {
			$loading[$loadBy] = null;
		}
		$this->loadingBatches[$batchId] = $loading;
		$this->loadingBatchClosures[$batchId] = $return;
		foreach ($loading as $loadBy => $user) {
			$this->useUser($loadBy, function (User $user) use ($batchId, $loadBy): void {
				$this->loadingBatches[$batchId][$loadBy] = $user;
			}, $withRank);
		}
	}

	public function useUser(mixed $player, \Closure $return, bool $withRank = false): void {
		if ($player instanceof User) {
			$user = $player;
		} elseif ($player instanceof Player) {
			$user = $this->loadedUsersByGamertag[strtolower($player->getName())] ?? null;
		} elseif (is_string($player)) {
			$convertTest = $player;
			$convertTest = (int) $convertTest;
			if ($convertTest == 0) {
				$user = $this->loadedUsersByGamertag[strtolower($player)] ?? null;
			} else {
				$user = $this->loadedUsersByXuid[$player] ?? null;
			}
		} elseif (is_int($player)) {
			$user = $this->loadedUsersByXuid[$player] ?? null;
		} else {
			$user = null;
		}
		if ($player === -100) {
			$return(new User(-100, AntiCheat::USER_NAME));
			return;
		}

		if ($user === null || ($withRank && !$user->rankLoaded())) {
			$this->loadUser($player, $return, $withRank);
		} else {
			$return($user);
		}
	}

	public function loadUser(mixed $player, \Closure $return, bool $withRank = false): void {
		if ($player instanceof Player) {
			$player = (int) $player->getXuid();
		} elseif (is_string($player)) {
			$convertTest = $player;
			$convertTest = (int) $convertTest;
			if ($convertTest != 0) {
				$player = (int) $player;
			} else {
				$player = strtolower($player);
			}
		}

		if (!isset($this->loading[$player])) {
			$this->loading[$player] = [$return];

			Core::getInstance()->getSessionManager()?->sendStrayRequest(new MySqlRequest(
				$player . "_user",
				new MySqlQuery(
					"main",
					is_int($player) ?
						"SELECT xuid, gamertag FROM network_playerdata WHERE xuid=?" :
						"SELECT xuid, gamertag FROM network_playerdata WHERE gamertag=?",
					[$player]
				)
			), function (MySqlRequest $request) use ($player, $withRank): void {
				$rows = $request->getQuery()->getResult()?->getRows();
				$result = array_shift($rows);
				if ($result === null) $result = [];
				$user = new User($result["xuid"] ?? (is_int($player) ? $player : 0), $result["gamertag"] ?? (is_int($player) ? "Server" : $player));
				if ($withRank) {
					Core::getInstance()->getSessionManager()?->sendStrayRequest(new MySqlRequest(
						$player . "user_rank",
						new MySqlQuery(
							"main",
							"SELECT `rank` FROM rank_data WHERE xuid=?",
							[$user->getXuid()]
						)
					), function (MySqlRequest $request) use ($user): void {
						$rank = ($request->getQuery()->getResult()->getRows()[0] ?? [])["rank"] ?? "default";
						$user->setRank($rank);
						Core::getInstance()->getUserPool()->returnUser($user);
					});
				} else {
					Core::getInstance()->getUserPool()->returnUser($user);
				}
			});
		} else {
			$this->loading[$player][] = $return;
		}
	}

	public function returnUser(User $user): void {
		if ($user->getXuid() != 0 && $user->getGamertag() != "Server") {
			$this->loadedUsersByXuid[$user->getXuid()] = $user;
			$this->loadedUsersByGamertag[strtolower($user->getGamertag())] = $user;
		}

		if (isset($this->loading[$user->getXuid()])) {
			foreach ($this->loading[$user->getXuid()] as $closure) $closure($user);
			unset($this->loading[$user->getXuid()]);
		} elseif (isset($this->loading[strtolower($user->getGamertag())])) {
			foreach ($this->loading[strtolower($user->getGamertag())] as $closure) $closure($user);
			unset($this->loading[strtolower($user->getGamertag())]);
		}
	}

	public function onlineElsewhere(User $user): bool {
		foreach (Core::getInstance()->getNetwork()->getServerManager()->getServers() as $ss) {
			foreach ($ss->getCluster()->getPlayers() as $p) {
				if ($p->getXuid() === $user->getXuid()) return true;
			}
		}
		return false;
	}
}
