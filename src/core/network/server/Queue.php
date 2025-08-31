<?php

namespace core\network\server;

use core\AtPlayer as Player;

class Queue {

	public array $players = [];

	public function __construct(public ServerInstance $server) {
	}

	public function tick(): void {
		if (count($this->getPlayers()) === 0 || !$this->getServer()->isOnline()) return;

		if (
			($server = $this->getServer())->isOnline() &&
			!$this->getServer()->isFull()
		) {
			$difference = $this->getServer()->getMaxPlayers() - count($this->getServer()->getCluster()->getPlayers());
			while ($difference > 0 || count($this->getPlayers()) !== 0) {
				$player = array_shift($this->players);
				if ($player === null) break;
				if ($player->isConnected()) {
					$this->getServer()->transfer($player);
					$difference--;
				}
			}
		}
	}

	public function getServer(): ServerInstance {
		return $this->server;
	}

	public function getMaxPlayers(): int {
		return $this->getServer()->getMaxPlayers();
	}

	public function getPlayers(): array {
		return $this->players;
	}

	public function hasPlayer(Player $player): bool {
		return isset($this->getPlayers()[$player->getName()]);
	}

	public function addPlayer(Player $player): void {
		if (!isset($this->players[$player->getName()]))
			$this->players[$player->getName()] = $player;
	}

	public function removePlayer(Player|string $player): void {
		unset($this->players[$player instanceof Player ? $player->getName() : $player]);
	}
}
