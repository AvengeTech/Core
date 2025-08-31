<?php

namespace core\network\server\player;

use core\{
	Core,
	AtPlayer as Player
};
use core\network\server\{
	ServerInstance,
	SubServer,
	ServerManager
};
use core\user\User;

class ConnectionCluster {

	/** @var ConnectionData[] $players */
	public array $players = [];

	public function __construct(public string $identifier) {
	}

	public function getServerManager(): ServerManager {
		return Core::getInstance()->getNetwork()->getServerManager();
	}

	public function getServerInstance(): ServerInstance {
		return $this->getServerManager()->getServerById($this->getIdentifier());
	}

	public function reset(): void {
		$this->players = [];
	}

	public function getIdentifier(): string {
		return $this->identifier;
	}

	/** @return ConnectionData[] */
	public function getPlayers(): array {
		return $this->players;
	}

	public function setPlayers(array $players): void {
		$joined = [];
		$left = [];
		foreach ($players as $gt => $data) {
			if (!isset($this->players[$gt])) {
				$joined[] = $data;
			}
		}
		foreach ($this->players as $gt => $data) {
			if (!isset($players[$gt])) {
				$left[] = $data;
			}
		}

		$this->reset();
		$this->players = $players;

		if (
			($server = $this->getServerInstance())->isThis() ||
			($server instanceof SubServer &&
				$server->getParentServer()->isThis()
			)
		) {
			//todo
			//$this->getServerManager()->updatePlayerList($joined, $left);
		}
	}

	public function addPlayer(ConnectionData $data): void {
		$this->players[strtolower($data->getGamertag())] = $data;
	}

	public function removePlayer(mixed $player): void {
		if ($player instanceof Player) {
			$player = $player->getName();
		} elseif ($player instanceof User) {
			$player = $player->getGamertag();
		}
		$player = strtolower($player);
		$connection = $this->players[$player] ?? null;
		unset($this->players[$player]);
	}

	public function getUser(string $name) : ?User{
		$connection = $this->players[strtolower($name)] ?? null;

		return $connection?->getUser();
	}

	public function hasPlayer($player): bool {
		if ($player instanceof Player) {
			$player = $player->getName();
		} elseif ($player instanceof User) {
			$player = $player->getGamertag();
		}
		$player = strtolower($player);
		return isset($this->players[$player]);
	}

	public function getPlayerCount(): int {
		return count($this->players);
	}
}
