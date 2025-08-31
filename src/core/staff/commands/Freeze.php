<?php

namespace core\staff\commands;

use core\command\type\CoreCommand;
use core\rank\Rank;
use pocketmine\Server;
use pocketmine\command\CommandSender;
use pocketmine\plugin\Plugin;

use core\AtPlayer as Player;
use core\Core;
use core\utils\TextFormat;

class Freeze extends CoreCommand {

	public function __construct(public Core $plugin, string $name, string $description) {
		parent::__construct($name, $description);
		$this->setHierarchy(Rank::HIERARCHY_STAFF);
	}

	public function handle(CommandSender $sender, string $commandLabel, array $args): void {
		if (count($args) < 1) {
			$sender->sendMessage(TextFormat::RN . "Usage: /freeze <player>");
			return;
		}

		$player = Server::getInstance()->getPlayerByPrefix(array_shift($args));
		if (!$player instanceof Player) {
			$sender->sendMessage(TextFormat::RN . "Player not found!");
			return;
		}
		if($player === $sender){
			$sender->sendMessage(TextFormat::RN . "You cannot freeze yourself silly goose");
			return;
		}

		$frozen = $player->toggleFrozen();
		if($frozen){
			$player->sendMessage(TextFormat::RI . "You were frozen by a staff member! Leaving before being unfrozen may result in punishment");
			$sender->sendMessage(TextFormat::GI . $player->getName() . " has been frozen");
		}else{
			$player->sendMessage(TextFormat::RI . "You are no longer frozen");
			$sender->sendMessage(TextFormat::GI . $player->getName() . " has been unfrozen");
		}
	}

	public function getPlugin(): Plugin {
		return $this->plugin;
	}
}
