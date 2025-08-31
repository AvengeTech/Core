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
use core\utils\TextFormat;

class Ping extends CoreCommand {

	public $plugin;

	public function __construct(Core $plugin, $name, $description) {
		$this->plugin = $plugin;
		parent::__construct($name, $description);
	}

	public function handle(CommandSender $sender, string $commandLabel, array $args) {
		if (($sender instanceof Player && !$sender->isStaff()) || empty($args)) {
			$ping = ($sender instanceof Player) ?
				($sender->isFromProxy() ?
					$sender->getSession()->getStaff()->getPing() :
					$sender->getNetworkSession()->getPing()
				) : 0;
		} else {
			$player = Server::getInstance()->getPlayerByPrefix(array_shift($args));
			if (!$player instanceof Player) {
				$sender->sendMessage(TextFormat::RI . "Player not found!");
				return;
			}
			if ($player->isFromProxy()) {
				$ping = $player->isLoaded() ? $player->getSession()->getStaff()->getPing() : 10;
			} else {
				$ping = $player->getNetworkSession()->getPing();
			}
		}
		if ($ping <= 30) {
			$ping = TextFormat::GREEN . $ping;
		} elseif ($ping > 30 && $ping <= 90) {
			$ping = TextFormat::YELLOW . $ping;
		} else {
			$ping = TextFormat::RED . $ping;
		}
		$sender->sendMessage(TextFormat::YN . "Pong! " . $ping . "ms");
	}

	public function getPlugin(): Plugin {
		return $this->plugin;
	}
}
