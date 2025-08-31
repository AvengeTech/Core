<?php

namespace core\staff\commands;

use core\command\type\CoreCommand;
use core\rank\Rank;
use pocketmine\command\CommandSender;
use pocketmine\plugin\Plugin;

use core\AtPlayer as Player;
use core\Core;
use core\utils\TextFormat;

class StaffChat extends CoreCommand {

	public function __construct(public Core $plugin, string $name, string $description) {
		parent::__construct($name, $description);
		$this->setHierarchy(Rank::HIERARCHY_STAFF);
		$this->setInGameOnly();
	}

	public function handlePlayer(Player $sender, string $commandLabel, array $args)
	{
		$sender->sendMessage(TextFormat::GI . "You are " . ($sender->getSession()->getStaff()->toggleStaffChat() ? "now " : "no longer ") . "in staff chat mode!");
	}

	public function getPlugin(): Plugin {
		return $this->plugin;
	}
}
