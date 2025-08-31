<?php

namespace core\utils\command;

use core\AtPlayer as Player;
use core\Core;
use core\command\type\CoreCommand;
use core\utils\TextFormat;
use pocketmine\command\CommandSender;
use pocketmine\plugin\Plugin;

class FlyCommand extends CoreCommand {

	public function __construct(public Core $plugin, string $name, string $description) {
		parent::__construct($name, $description);
		$this->setAliases(["flight", "skyboi", "upupandaway"]);
		$this->setInGameOnly();
	}

	public function handlePlayer(Player $sender, string $commandLabel, array $args)
	{
		$canFly = $sender->canFly();
		if (is_string($canFly)) {
			$sender->sendMessage(TextFormat::RI . $canFly);
			return;
		}
		$sender->setFlightMode(!$sender->inFlightMode());
		$sender->sendMessage(TextFormat::GI . "You are " . ($sender->inFlightMode() ? "now" : "no longer") . " in flight mode");
	}

	public function getPlugin(): Plugin {
		return $this->plugin;
	}
}
