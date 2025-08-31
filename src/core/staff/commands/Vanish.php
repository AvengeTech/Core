<?php

namespace core\staff\commands;

use core\command\type\CoreCommand;
use core\rank\Rank;
use pocketmine\command\CommandSender;
use pocketmine\plugin\Plugin;

use core\AtPlayer as Player;
use core\Core;
use core\utils\TextFormat;

use prison\PrisonPlayer;

class Vanish extends CoreCommand {

	public function __construct(public Core $plugin, string $name, string $description) {
		parent::__construct($name, $description);
		$this->setHierarchy(Rank::HIERARCHY_STAFF);
		$this->setInGameOnly();
	}

	public function handlePlayer(Player $sender, string $commandLabel, array $args)
	{
		if ($sender->toggleVanish()) {
			$sender->sendMessage(TextFormat::GN . "You are now invisible.");
		} else {
			$sender->sendMessage(TextFormat::GN . "You are now visible.");
		}
	}

	public function getPlugin(): Plugin {
		return $this->plugin;
	}
}
