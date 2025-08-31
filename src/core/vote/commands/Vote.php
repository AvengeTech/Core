<?php

namespace core\vote\commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\plugin\Plugin;

use core\AtPlayer as Player;
use core\command\type\CoreCommand;
use core\Core;
use core\vote\uis\VoteRewardsUi;
use core\utils\TextFormat;

class Vote extends CoreCommand{

	public function __construct(public Core $plugin, string $name, string $description) {
		parent::__construct($name, $description);
		$this->setInGameOnly();
	}

	public function handlePlayer(Player $sender, string $commandLabel, array $args)
	{
		if (in_array(($network = Core::getInstance()->getNetwork())->getServerType(), ["lobby", "idle"])) {
			$sender->sendMessage(TextFormat::RI . "This command can only be ran on gamemode servers! Claim prizes you want on your favorite gamemode!");
			return;
		}
		if ($network->getThisServer()->getTypeId() == "event") {
			$sender->sendMessage(TextFormat::RI . "You are not allowed to claim vote prizes on an event server");
			return;
		}
		if (stristr("archive", $network->getThisServer()->getIdentifier())) {
			$sender->sendMessage(TextFormat::RI . "You are not allowed to claim vote prizes on an archive server");
			return;
		}

		$sender->showModal(new VoteRewardsUi($sender));
	}

	public function getPlugin(): Plugin {
		return $this->plugin;
	}
}
