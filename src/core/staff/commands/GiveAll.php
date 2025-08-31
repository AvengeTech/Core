<?php

namespace core\staff\commands;

use core\AtPlayer;
use core\Core;
use core\network\protocol\ServerSubUpdatePacket;
use core\utils\TextFormat;
use core\command\type\CoreCommand;
use core\rank\Rank;
use pocketmine\command\CommandSender;
use pocketmine\item\StringToItemParser;
use pocketmine\Server;

class GiveAll extends CoreCommand {

	public function __construct(public Core $plugin, string $name, string $description) {
		parent::__construct($name, $description);
		$this->setHierarchy(Rank::HIERARCHY_HEAD_MOD);
	}

	public function handle(CommandSender $sender, string $commandLabel, array $args): void {
		if (!isset($args[0])) {
			$sender->sendMessage(TextFormat::RN . "Usage: /giveall <hand|item>");
			return;
		}

		switch (strtolower($args[0])) {
			case "hand":
				if (!$sender instanceof AtPlayer) return;
				$item = $sender->getInventory()->getItemInHand();
				break;

			default:
			case "item":
				if (!isset($args[1])) {
					$sender->sendMessage(TextFormat::RN . "Usage: /giveall item <string: item> [int: count]");
					return;
				}

				$item = StringToItemParser::getInstance()->parse($args[1]);

				if (is_null($item)) {
					$sender->sendMessage(TextFormat::RN . TextFormat::YELLOW . $args[1] . TextFormat::RED . " does not exist");
					return;
				}

				$item->setCount((isset($args[2]) ? $args[2] : 1));
				break;
		}

		foreach (Server::getInstance()->getOnlinePlayers() as $online) {
			if (!$online instanceof AtPlayer) continue;
			if (!$online->isLoaded()) continue;

			if ($online->getInventory()->canAddItem($item)) {
				$online->sendMessage(TextFormat::GRAY . "Everyone online has received " . TextFormat::GREEN . TextFormat::BOLD . "+" . $item->getCount() . " " . TextFormat::RESET . TextFormat::AQUA . $item->getName() . TextFormat::GRAY . "!");
				$online->getInventory()->addItem($item);
			}
		}

		(new ServerSubUpdatePacket([
			"server" => array_map(function ($ss) {
				return $ss->getIdentifier();
			}, Core::thisServer()->getSubServers(false, true)),
			"type" => "giveall",
			"data" => [
				"item" => json_encode($item->nbtSerialize()),
			]
		]))->queue();
	}
}
