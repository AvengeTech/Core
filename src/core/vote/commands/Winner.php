<?php

namespace core\vote\commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\plugin\Plugin;

use core\AtPlayer as Player;
use core\command\type\CoreCommand;
use core\Core;
use core\utils\TextFormat;
use core\vote\uis\PrizeWinnerPage;

class Winner extends CoreCommand{

	public function __construct(public Core $plugin, string $name, string $description) {
		parent::__construct($name, $description);
		$this->setInGameOnly();
	}

	public function handlePlayer(Player $sender, string $commandLabel, array $args)
	{
		$vote = Core::getInstance()->getVote();
		$entry = $vote->getWinnerEntry($sender);
		if ($entry == null) {
			$sender->sendMessage(TextFormat::RI . "You did not win a vote prize last month! Learn how you can win one THIS month by typing " . TextFormat::YELLOW . "/topvoters");
			return;
		}
		if ($entry->hasClaimed()) {
			$sender->sendMessage(TextFormat::RI . "You have already claimed your vote prize for last month!");
			return;
		}
		$sender->showModal(new PrizeWinnerPage($sender, $entry));
	}

	public function getPlugin(): Plugin {
		return $this->plugin;
	}
}
