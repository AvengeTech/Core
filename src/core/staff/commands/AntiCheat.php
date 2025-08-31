<?php

namespace core\staff\commands;

use core\command\type\CoreCommand;
use core\rank\Rank;
use pocketmine\command\CommandSender;
use pocketmine\plugin\Plugin;

use core\{
	Core,
	AtPlayer as Player
};
use core\utils\TextFormat;

class AntiCheat extends CoreCommand {

	public function __construct(public Core $plugin, string $name, string $description) {
		parent::__construct($name, $description);
		$this->setHierarchy(Rank::HIERARCHY_STAFF);
		$this->setAliases(["ac"]);
		$this->setInGameOnly();
	}

	public function handlePlayer(Player $sender, string $commandLabel, array $args)
	{
		if ($sender->getSession()->getStaff()->toggleAnticheat()) {
			$sender->sendMessage(TextFormat::GN . "Activated anticheat messages");
		} else {
			$sender->sendMessage(TextFormat::GN . "You will no longer receive anticheat messages");
		}
	}

	public function getPlugin(): Plugin {
		return $this->plugin;
	}
}
