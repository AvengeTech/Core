<?php

namespace core\network\server;

use core\{
	Core,
	AtPlayer as Player
};
use core\utils\TextFormat;

class FullQueue extends Queue {

	public array $subServers = [];
	public array $customClosure = [];

	public function __construct(
		public ServerInstance $server,
		public int $maxPlayers
	) {
		parent::__construct($server);
	}

	public function tick(): void {
		if (count($this->getPlayers()) === 0 || !$this->getServer()->isOnline()) return;

		$players = $this->getServer()->getPlayerCount();
		foreach ($this->getServer()->getSubServers(false) as $server) {
			$players += count($server->getCluster()->getPlayers());
		}
		if ($players < $this->getMaxPlayers()) {
			$difference = $this->getMaxPlayers() - $players;
			while ($difference > 0 || count($this->getPlayers()) !== 0) {
				$player = array_shift($this->players);
				if ($player === null) break;
				if ($player->isConnected()) {
					if ($this->hasCustomSubServer($player)) {
						$server = Core::getInstance()->getNetwork()->getServerManager()->getServerById($this->shiftCustomSubServer($player));
					} else {
						$server = $this->getServer();
					}
					$closure = null;
					if ($this->hasCustomClosure($player)) {
						$closure = $this->shiftCustomClosure($player);
					}
					if ($server !== null && $server->isOnline()) {
						if ($closure !== null) $closure($player, $server);
						$server->transfer($player);
					} else {
						$player->sendMessage(TextFormat::RI . "Server you were queued for is no longer online. Please try again or connect to a different server!");
					}
					$difference--;
				}
			}
		}
	}

	public function getMaxPlayers(): int {
		return $this->maxPlayers;
	}

	public function hasCustomSubServer(Player $player): bool {
		return isset($this->subServers[$player->getName()]);
	}

	public function shiftCustomSubServer(Player $player): string {
		$subServer = $this->subServers[$player->getName()] ?? "";
		unset($this->subServers[$player->getName()]);
		return $subServer;
	}

	public function hasCustomClosure(Player $player): bool {
		return isset($this->customClosure[$player->getName()]);
	}

	public function shiftCustomClosure(Player $player): ?\Closure {
		$closure = $this->customClosure[$player->getName()] ?? null;
		unset($this->customClosure[$player->getName()]);
		return $closure;
	}

	public function addPlayerWithCustomActions(
		Player $player,
		string $identifier = "",
		?\Closure $closure = null
	): void {
		if ($identifier !== "") $this->subServers[$player->getName()] = $identifier;
		if ($closure !== null) $this->customClosure[$player->getName()] = $closure;
		$this->addPlayer($player);
	}

	public function removePlayer(Player|string $player): void {
		parent::removePlayer($player);
		$player = $player instanceof Player ? $player->getName() : $player;
		if (isset($this->subServers[$player]))
			unset($this->subServers[$player]);
		if (isset($this->customClosure[$player]))
			unset($this->customClosure[$player]);
	}
}
