<?php

namespace core\utils;

use pocketmine\Server;

use core\Core;
use core\AtPlayer as Player;
use core\staff\anticheat\session\SessionManager;
use core\utils\TextFormat;

class LoadAction {

	const TIMEOUT = 30;

	public int $created;

	public function __construct(
		public string $playerName,
		public string $action,
		public array $actionData = []
	) {
		$this->created = time();
	}

	public function getName(): string {
		return $this->playerName;
	}

	public function getPlayer(): ?Player {
		return Server::getInstance()->getPlayerExact($this->getName());
	}

	public function getAction(): string {
		return $this->action;
	}

	public function getActionData(): array {
		return $this->actionData;
	}

	public function getCreated(): int {
		return $this->created;
	}

	public function canTimeout(): bool {
		return $this->created + self::TIMEOUT < time();
	}

	public function process(bool $preLoad = false): void {
		$player = $this->getPlayer();
		$adata = $this->getActionData();
		if (!$player instanceof Player) return;
		switch ($this->getAction()) {
			case "test":
				$player->sendMessage("ACCOUNT LOADED. LOAD ACTION CALLED. " . TextFormat::EMOJI_BURGER);
				break;

			case "voteparty":
				$player->teleport(Core::getInstance()->getVote()->getPartySpawn());
				break;

			case "teleport":
				$pl = Server::getInstance()->getPlayerExact($adata["player"]);
				if($pl instanceof Player){
					if(!$preLoad){
						$pl->teleportProcess($player);
						$player->teleport($pl->getLocation());
					}else{
						$player->teleport($pl->getLocation());
					}
				}
				break;
		}
		if ($preLoad) $player->removePreLoadAction($this);
		else $player->removeLoadAction($this);
	}
}
