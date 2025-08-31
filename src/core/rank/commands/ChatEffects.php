<?php

namespace core\rank\commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\plugin\Plugin;

use core\{
	Core,
	AtPlayer as Player
};
use core\command\type\CoreCommand;
use core\rank\uis\ChatEffectsUi;
use core\utils\TextFormat;

class ChatEffects extends CoreCommand {

	public function __construct(public Core $plugin, string $name, string $description) {
		parent::__construct($name, $description);
		$this->setInGameOnly();
		$this->setAliases(["che"]);
	}

	public function handlePlayer(Player $sender, string $commandLabel, array $args)
	{
		if (!$sender->getSession()->getRank()->hasSub()) {
			$sender->sendMessage(TextFormat::RI . "You must have the " . TextFormat::DARK_AQUA . "Warden " . TextFormat::ICON_WARDEN . TextFormat::GRAY . " subscription to use this command! Purchase a subscription at " . TextFormat::YELLOW . "store.avengetech.net");
			return;
		}

		$sender->showModal(new ChatEffectsUi($sender));
	}

	public function getPlugin(): Plugin {
		return $this->plugin;
	}
}
