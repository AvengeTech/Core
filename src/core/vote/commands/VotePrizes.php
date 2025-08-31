<?php

namespace core\vote\commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\plugin\Plugin;

use core\Core;
use core\AtPlayer as Player;
use core\command\type\CoreCommand;
use core\rank\Rank;
use core\vote\uis\VoteRewardsUi;
use core\utils\TextFormat;

class VotePrizes extends CoreCommand{

	public $plugin;

	public function __construct(Core $plugin, $name, $description) {
		$this->plugin = $plugin;
		parent::__construct($name, $description);
		$this->setHierarchy(Rank::HIERARCHY_HEAD_MOD);
		$this->setInGameOnly();
	}

	public function handlePlayer(Player $sender, string $commandLabel, array $args)
	{
		if (empty($args)) {
			$sender->showModal(new VoteRewardsUi($sender));
			//$sender->sendMessage(TextFormat::RI . "Usage: /voteprizes <day>");
			return false;
		}
		$day = array_shift($args);

		$vendor = Core::getInstance()->getVote()->getPrizeVendor();
		$prize = $vendor->getPrizeFor($day);

		if ($prize === null) {
			$sender->sendMessage(TextFormat::RI . "Invalid day! (1-30, daily, end)");
			return false;
		}

		$prize->reward($sender);
		$sender->sendMessage(TextFormat::GI . "Received the following day " . $day . " vote prizes:");
		$sender->sendMessage($prize->getItemList());
		return true;
	}

	public function getPlugin(): Plugin {
		return $this->plugin;
	}
}
