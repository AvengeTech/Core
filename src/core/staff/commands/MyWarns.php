<?php

namespace core\staff\commands;

use core\command\type\CoreCommand;
use core\rank\Rank;
use pocketmine\command\CommandSender;
use pocketmine\plugin\Plugin;

use core\AtPlayer as Player;
use core\Core;
use core\staff\uis\player\MyWarnsUi;
use core\utils\TextFormat;

class MyWarns extends CoreCommand {

	public function __construct(public Core $plugin, string $name, string $description) {
		parent::__construct($name, $description);
		$this->setInGameOnly();
	}

	public function handlePlayer(Player $sender, string $commandLabel, array $args)
	{
		$session = $sender->getSession()->getStaff();
		if (count(($wm = $session->getWarnManager())->getWarns()) == 0) {
			$sender->sendMessage(TextFormat::RI . "You have never been warned in-game! Let's keep it that way...");
			return;
		}
		$sender->showModal(new MyWarnsUi($wm->getWarns(), $sender->getUser()));
	}

	public function getPlugin(): Plugin {
		return $this->plugin;
	}
}
