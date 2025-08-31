<?php

namespace core\network\server;

use pocketmine\Server;

use core\{
	Core,
	AtPlayer as Player
};
use core\utils\TextFormat;

class Reconnection {

	const DEFAULT_TIME = 5; //will lower

	public $player;
	public $identifier;

	public $ticks;
	public $restart;

	public function __construct($player, string $identifier, int $ticks = self::DEFAULT_TIME, bool $restart = false) {
		$this->player = ($player instanceof Player ? $player->getName() : $player);
		$this->identifier = $identifier;

		$this->ticks = $ticks;
		$this->restart = $restart;
	}

	public function getPlayer(): ?Player {
		return Server::getInstance()->getPlayerExact($this->player);
	}

	public function getIdentifier(): string {
		return $this->identifier;
	}

	public function isRestart(): bool {
		return $this->restart;
	}

	public function getServer(): ?ServerInstance {
		return Core::getInstance()->getNetwork()->getServerManager()->getServerById($this->getIdentifier());
	}

	public function tick(): bool {
		$this->ticks--;
		if ($this->ticks <= 0) {
			$player = $this->getPlayer();
			if ($player instanceof Player) {
				$server = $this->getServer();
				if ($server instanceof ServerInstance && $server->isOnline()) {
					$server->delayedTransfer($player, TextFormat::GI . ($this->isRestart() ? "Server has restarted.. But you were reconnected!" : "Successfully reconnected to " . TextFormat::YELLOW . $server->getIdentifier()));
				}
			}
			return false;
		}
		return true;
	}
}
