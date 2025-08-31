<?php

namespace core\tutorial\command;

use core\command\type\CoreCommand;
use core\rank\Rank;
use pocketmine\command\CommandSender;
use core\AtPlayer as Player;
use core\Core;
use core\utils\TextFormat;

class TutorialCommand extends CoreCommand {

	public function __construct(public Core $plugin, string $name, string $description) {
		parent::__construct($name, $description);
		$this->setInGameOnly();
	}

	public function handlePlayer(Player $sender, string $commandLabel, array $args) {
		if (Core::thisServer()->isSubServer()) {
			$sender->sendMessage(TextFormat::RI . "Tutorial can only be ran at spawn!");
			return;
		}
		$tut = Core::getInstance()->getTutorials();
		if ($tut->getTutorial() === null) {
			$sender->sendMessage(TextFormat::RI . "No tutorial available for this server!");
			return;
		}
		if ($tut->inTutorial($sender)) {
			$tut->endTutorial($sender);
			$sender->sendMessage(TextFormat::GI . "Ended tutorial");
		} else {
			$sender->sendMessage(TextFormat::GI . "Starting tutorial!");
			$tut->startTutorial($sender);
		}
	}

	public function getPlugin(): \pocketmine\plugin\Plugin {
		return $this->plugin;
	}
}
