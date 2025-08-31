<?php

namespace core\network\commands;

use pocketmine\Server;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\plugin\Plugin;

use core\{
	Core,
	AtPlayer as Player
};
use core\command\type\CoreCommand;
use core\network\protocol\PlayerSummonPacket;
use core\rank\Rank;
use core\utils\TextFormat;

class Summon extends CoreCommand {

	public $plugin;

	public function __construct(Core $plugin, $name, $description) {
		$this->plugin = $plugin;
		parent::__construct($name, $description);
		$this->setHierarchy(Rank::HIERARCHY_HEAD_MOD);
	}

	public function handle(CommandSender $sender, string $commandLabel, array $args) {
		if (count($args) < 1) {
			$sender->sendMessage(TextFormat::RI . "Usage: /summon <player>");
			return;
		}
		if (Server::getInstance()->getPlayerExact($name = array_shift($args)) instanceof Player) {
			$sender->sendMessage(TextFormat::RI . "This player is already connected to this server!");
			return;
		}
		$sm = Core::getInstance()->getNetwork()->getServerManager();
		if (!$sm->isPlayerOnline($name)) {
			$sender->sendMessage(TextFormat::RI . "Player not online!");
			return;
		}
		$sm->getThisServer()->addSummoning($name, $sender->getName());
		$pk = new PlayerSummonPacket([
			"player" => $name,
			"sentby" => $sender->getName()
		]);
		$pk->queue();
		$sender->sendMessage(TextFormat::YI . "Attempting to summon player to this server...");
	}

	public function getPlugin(): Plugin {
		return $this->plugin;
	}
}
