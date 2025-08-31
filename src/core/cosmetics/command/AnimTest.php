<?php

namespace core\cosmetics\command;

use pocketmine\Server;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\network\mcpe\protocol\AnimateEntityPacket;
use pocketmine\plugin\Plugin;

use core\{
	Core,
	AtPlayer as Player,
};
use core\command\type\CoreCommand;
use core\rank\Rank;
use core\utils\TextFormat;

class AnimTest extends CoreCommand {

	public function __construct(public Core $plugin, string $name, string $description) {
		parent::__construct($name, $description);
		$this->setHierarchy(Rank::HIERARCHY_HEAD_MOD);
  		$this->setInGameOnly();
	}

	public function handlePlayer(Player $sender, string $commandLabel, array $args) {
		if (count($args) < 1) {
			$sender->sendMessage(TextFormat::RI . "Usage: /animtest <anim name>");
			return;
		}
		$name = array_shift($args);
		if (count($args) > 1) {
			$guy = array_shift($args);
			$guy = Server::getInstance()->getPlayerExact($guy);
			if ($guy instanceof Player) {
				$id = $guy->getId();
			} else {
				$id = $sender->getId();
			}
		} else {
			$id = $sender->getId();
		}

		$packet = AnimateEntityPacket::create($name, "", "", 0, "", 0, [$id]);
		$sender->getNetworkSession()->sendDataPacket($packet);

		$sender->sendMessage(TextFormat::GI . "Animation sent!");
	}

	public function getPlugin(): Plugin {
		return $this->plugin;
	}
}
