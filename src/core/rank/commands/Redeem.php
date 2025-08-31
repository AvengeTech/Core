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
use core\rank\Structure;
use core\utils\TextFormat;

class Redeem extends CoreCommand {

	public Core $plugin;

	public function __construct(Core $plugin, $name, $description) {
		$this->plugin = $plugin;
		parent::__construct($name, $description);
		$this->setHierarchy(Rank::HIERARCHY_STAFF);
		$this->setInGameOnly();
	}

	public function handlePlayer(Player $sender, string $commandLabel, array $args)
	{
		if (count($args) < 1) {
			$sender->sendMessage(TextFormat::RN . "Usage: /redeem <code>");
			return;
		}

		$redeemer = Core::getInstance()->getRank()->getRedeemer();

		$code = strtolower(array_shift($args));
		if (!$redeemer->exists($code)) {
			$sender->sendMessage(TextFormat::RI . "An unknown code was provided!");
			return;
		}

		if ($redeemer->redeemed($code)) {
			$sender->sendMessage(TextFormat::RI . "This code has already been redeemed by " . TextFormat::YELLOW . $redeemer->getRedeemedBy($code));
			return;
		}

		$prize = explode(":", $redeemer->getPrize($code));
		if ($prize[0] == "rank") {
			$rank = $sender->getRank();
			$pr = $prize[1];
			if ($rank == $pr) {
				$sender->sendMessage(TextFormat::RI . "This redeem code is for the rank you already have!");
				return;
			}
			if (Structure::RANK_HIERARCHY[$rank] > Structure::RANK_HIERARCHY[$pr]) {
				$sender->sendMessage(TextFormat::RI . "You have a rank better than this redeem code offers!");
				return;
			}
		}

		$redeemer->redeemCode($code, $sender);

		return;
	}

	public function getPlugin(): Plugin {
		return $this->plugin;
	}
}
