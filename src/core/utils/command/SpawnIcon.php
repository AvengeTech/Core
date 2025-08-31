<?php

namespace core\utils\command;

use core\AtPlayer as Player;
use core\Core;
use core\command\type\CoreCommand;
use core\rank\Rank;
use core\utils\TextFormat;
use core\utils\entity\AtIcon;
use core\utils\entity\ScavTest;
use pocketmine\command\CommandSender;
use pocketmine\entity\Location;
use pocketmine\entity\Skin;
use pocketmine\plugin\Plugin;

class SpawnIcon extends CoreCommand {

	public function __construct(public Core $plugin, string $name, string $description) {
		parent::__construct($name, $description);
		$this->setInGameOnly();
		$this->setHierarchy(Rank::HIERARCHY_HEAD_MOD);
	}

	public function handlePlayer(Player $sender, string $commandLabel, array $args)
	{
		switch ($type = (array_shift($args) ?? "")) {
			case "cheese":
			case "cheeseburger":
			case "hotdog":
			case "shoes":
			case "skull":
				$icon = new ScavTest(new Location($sender->getLocation()->getX(), $sender->getLocation()->getY(), $sender->getLocation()->getZ(), $sender->getWorld(), 0, 0), new Skin("Standard_Custom", file_get_contents("/home/data/skins/techie.dat"), "", "geometry.humanoid.custom"), $type);
				break;
			default:
				$icon = new AtIcon(new Location($sender->getLocation()->getX(), $sender->getLocation()->getY(), $sender->getLocation()->getZ(), $sender->getWorld(), 0, 0), new Skin("Standard_Custom", file_get_contents("/home/data/skins/techie.dat"), "", "geometry.humanoid.custom"));
				break;
		}
		$icon->spawnToAll();

		$sender->sendMessage(TextFormat::GI . "Spawned thing!");
	}

	public function getPlugin(): Plugin {
		return $this->plugin;
	}
}
