<?php

namespace core\network\server;

use pocketmine\Server;
use pocketmine\entity\Skin;
use pocketmine\network\mcpe\protocol\{
	PlayerListPacket,
	types\PlayerListEntry
};
use pocketmine\network\mcpe\convert\LegacySkinAdapter;

use core\AtPlayer as Player;
use core\network\Structure;
use core\network\protocol\ConnectPacketHandler;
use core\network\server\player\ConnectionData;
use core\utils\TextFormat;

class ServerManager {

	public array $servers = [];
	public array $reconnects = [];
	public int $ticks = 0;

	public ConnectPacketHandler $connectPacketHandler;
	public array $subUpdateHandlers = [];

	public function __construct() {
		foreach (Structure::PORT_TO_IDENTIFIER as $port => $id) {
			$idSplit = explode("-", $id);

			$type = $idSplit[0];
			$which = $idSplit[1];
			$identifier = $type . "-" . $which;
			$private = $which == "test";

			if (count($idSplit) > 2) {
				$this->registerServer(new SubServer($this->getServerById($identifier), $id, $port, Structure::MAX_PLAYERS[$id] ?? Structure::MAX_PLAYERS[$type] ?? 20, $private, Structure::RESTRICTED[$type] ?? Structure::RESTRICTED[$id] ?? ""));
			} else {
				$this->registerServer(new ServerInstance($id, $port, Structure::MAX_PLAYERS[$id] ?? Structure::MAX_PLAYERS[$type] ?? 20, $private, Structure::RESTRICTED[$type] ?? Structure::RESTRICTED[$id] ?? ""));
			}
		}
		$this->getThisServer()->online = true;

		$this->connectPacketHandler = new ConnectPacketHandler($this);
	}

	public function getPacketHandler(): ConnectPacketHandler {
		return $this->connectPacketHandler;
	}

	public function getSubUpdateHandlers(): array {
		return $this->subUpdateHandlers;
	}

	public function addSubUpdateHandler(?\Closure $closure): void {
		$this->subUpdateHandlers[] = $closure;
	}

	public function getSyncedPlayerList(Player $pl): PlayerListPacket {
		$players = [];
		foreach ($this->getThisServer()->getSubServers(true, true) as $server) {
			foreach ($server->getCluster()->getPlayers() as $player) {
				if ($player->getXuid() != $pl->getXuid()) $players[] = $player;
			}
		}
		$pk = PlayerListPacket::add(array_map(function (ConnectionData $data): PlayerListEntry {
			return PlayerListEntry::createAdditionEntry(
				$data->getUniqueId(),
				$data->getXuid(),
				$data->getGamertag(),
				(new LegacySkinAdapter)->toSkinData(
					($pl = $data->getUser()->getPlayer()) instanceof Player ?
						$pl->getSkin() :
						new Skin(
							"Standard_Custom",
							file_get_contents("/[REDACTED]/skins/techie.dat"),
							"",
							"geometry.humanoid.custom"
						)
				),
				$data->getXuid()
			);
		}, $players));
		return $pk;
	}

	/**
	 * Sends global player list packet update with add/removes
	 */
	public function updatePlayerList(array $joined, array $left): void {
		foreach ($joined as $join) {
			$pk = PlayerListPacket::add([PlayerListEntry::createAdditionEntry(
				$join->getUniqueId(),
				$join->getXuid(),
				$join->getGamertag(),
				(new LegacySkinAdapter)->toSkinData(
					($pl = $join->getUser()->getPlayer()) instanceof Player ?
						$pl->getSkin() :
						new Skin(
							"Standard_Custom",
							file_get_contents("/[REDACTED]/skins/techie.dat"),
							"",
							"geometry.humanoid.custom"
						)
				),
				$join->getXuid()
			)]);
			foreach (Server::getInstance()->getOnlinePlayers() as $player) {
				if ($join->getXuid() != $player->getXuid()) $player->getNetworkSession()->sendDataPacket($pk);
			}
		}
		foreach ($left as $le) {
			$pk = PlayerListPacket::remove([PlayerListEntry::createRemovalEntry(
				$le->getUniqueId()
			)]);
			foreach (Server::getInstance()->getOnlinePlayers() as $player) {
				if ($le->getXuid() != $player->getXuid()) $player->getNetworkSession()->sendDataPacket($pk);
			}
		}
	}

	public function sendPlayerList(?Player $player = null): void {
		if ($player === null) {
			foreach (Server::getInstance()->getOnlinePlayers() as $player) {
				$player->getNetworkSession()->sendDataPacket($this->getSyncedPlayerList($player));
			}
		} else {
			$player->getNetworkSession()->sendDataPacket($this->getSyncedPlayerList($player));
		}
	}

	public function tick(): void {
		$this->ticks++;
		if ($this->ticks % 5 == 0) {
			$this->getPacketHandler()->tick();
		}

		if ($this->ticks % 20 == 0) {
			foreach ($this->getReconnects() as $key => $rc) {
				if (!$rc->tick())
					unset($this->reconnects[$key]);
			}
			foreach ($this->getThisServer()->summoning as $gamertag => $summoning) {
				if ($summoning->created + 30 < time()) {
					$sent = Server::getInstance()->getPlayerExact($summoning->sentBy);
					if ($sent instanceof Player) {
						$sent->sendMessage(TextFormat::RI . "Failed to summon " . TextFormat::YELLOW . $gamertag);
					}
					unset($this->getThisServer()->summoning[$gamertag]);
				}
			}
		}
		if ($this->ticks % 100 == 0) {
			foreach ($this->getServers() as $server) {
				$server->getQueue()->tick();
				$server->getFullQueue()?->tick();
			}
		}
	}

	public function close(): void {
		$this->getPacketHandler()->close();
		foreach ($this->getServers() as $server) {
			$server->close();
		}
	}

	public function registerServer(ServerInstance $server): void {
		$this->servers[$server->getIdentifier()] = $server;
	}

	public function deleteServer(string $identifier): void {
		unset($this->servers[$identifier]);
	}

	/** @return ServerInstance[] */
	public function getServers(): array {
		return $this->servers;
	}

	public function getServer(string $identifier): ?ServerInstance {
		return $this->servers[$identifier] ?? null;
	}

	/** @return ServerInstance[] */
	public function getServersWithNumberKeys(): array {
		$servers = $this->getServers();
		$key = 0;
		$s = [];
		foreach ($servers as $server) {
			$s[$key] = $server;
			$key++;
		}
		return $s;
	}

	protected static ServerInstance $thisServer;

	public function getThisServer(): ?ServerInstance {
		if (isset(self::$thisServer) && !is_null(self::$thisServer)) return self::$thisServer;
		foreach ($this->getServers() as $server) {
			if ($server->isThis())
				return (self::$thisServer ??= $server);
		}
		return null;
	}

	public function getServerById(string $identifier): ?ServerInstance {
		foreach ($this->servers as $id => $server) {
			if ($server->getIdentifier() == $identifier) {
				return $server;
			}
		}
		return null;
	}

	/** @return ServerInstance[] */
	public function getServersByType(string $serverType): array {
		$servers = [];
		foreach ($this->getServers() as $id => $server) {
			if (stristr($id, $serverType)) {
				$servers[$id] = $server;
			}
		}
		return $servers;
	}

	/** @return ServerInstance[] */
	public function getAvailableByType(string $serverType, bool $checkFull = true): array {
		$servers = [];
		foreach ($this->getServersByType($serverType) as $server) {
			if ($server->isOnline() && !$server->isPrivate() && (!$checkFull || !$server->isFull())) $servers[] = $server;
		}
		return $servers;
	}

	/** @return ServerInstance[] */
	public function getRandomAvailable(string $serverType): ?ServerInstance {
		$servers = $this->getAvailableByType($serverType);
		if (count($servers) === 0) return null;
		return $servers[array_rand($servers)] ?? null;
	}

	public function getLeastPopulated(string $serverType, bool $includeTest = false): ?ServerInstance {
		$servers = $this->getServersByType($serverType);
		$transfer = null;
		$count = PHP_INT_MAX;
		foreach ($servers as $id => $server) {
			if (
				$server->getPlayerCount() < $count &&
				($server->getTypeId() !== "test" || $includeTest) &&
				$server->isOnline()
			) {
				$transfer = $server;
				$count = $server->getPlayerCount();
			}
		}
		return $transfer;
	}

	public function getPlayerCountByType(string $serverType): int {
		$count = 0;
		foreach ($this->getServersByType($serverType) as $server) {
			$count += $server->getPlayerCount();
		}
		return $count;
	}

	public function getTotalPlayers(): int {
		$count = 0;
		foreach ($this->getServers() as $server) {
			$count += $server->getPlayerCount();
		}
		return $count;
	}

	public function isPlayerOnline($player): bool {
		foreach ($this->getServers() as $server) {
			if ($server->getCluster()->hasPlayer($player)) return true;
		}
		return false;
	}

	public function getServerByPlayer($player): ?ServerInstance {
		foreach ($this->getServers() as $server) {
			if ($server->getCluster()->hasPlayer($player)) return $server;
		}
		return null;
	}

	public function validId(string $id): bool {
		return in_array($id, $this->getIds());
	}

	public function getIds(): array {
		return array_keys($this->getServers());
	}

	/** @return Reconnection[] */
	public function getReconnects(): array {
		return $this->reconnects;
	}

	public function addReconnect($player, string $identifier, int $time = Reconnection::DEFAULT_TIME, bool $restart = false): Reconnection {
		$this->reconnects[] = $reconnection = new Reconnection($player, $identifier, $time, $restart);
		return $reconnection;
	}

	public function getReconnect(Player $player): ?Reconnection {
		foreach ($this->getReconnects() as $rc) {
			if ($rc->getPlayer() == $player) return $rc;
		}
		return null;
	}

	public function hasReconnect(Player $player): bool {
		return $this->getReconnect($player) !== null;
	}

	public function removeReconnect(Player $player): void {
		foreach ($this->getReconnects() as $key => $rc) {
			if ($rc->getPlayer() === $player) unset($this->reconnects[$key]);
		}
	}
}
