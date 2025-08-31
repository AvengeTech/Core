<?php

namespace core\rank\commands;

use core\command\type\CoreCommand;
use core\rank\Rank;
use pocketmine\command\CommandSender;
use pocketmine\plugin\Plugin;

use core\{
	Core,
	AtPlayer as Player
};
use core\settings\GlobalSettings;
use core\utils\TextFormat;

class JoinMessage extends CoreCommand {

	public function __construct(public Core $plugin, string $name, string $description) {
		parent::__construct($name, $description);
		$this->setHierarchy(Rank::HIERARCHY_STAFF);
		$this->setInGameOnly();
		$this->setAliases(["jm"]);
	}

	public function handlePlayer(Player $sender, string $commandLabel, array $args)
	{
		if (!$sender->hasRank()) {
			$sender->sendMessage(TextFormat::RI . "Only ranked players can use this command!");
			return;
		}

		$settings = $sender->getSession()->getSettings();
		$settings->setSetting(GlobalSettings::JOIN_MESSAGE, ($t = !$settings->getSetting(GlobalSettings::JOIN_MESSAGE)));

		$sender->sendMessage(TextFormat::GI . "Successfully toggled your join message " . ($t ? TextFormat::GREEN . "ON" : TextFormat::GREEN . "OFF") . TextFormat::GRAY . "!");
	}

	public function getPlugin(): Plugin {
		return $this->plugin;
	}
}
