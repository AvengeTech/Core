<?php

namespace core\vote\commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\entity\Location;
use pocketmine\entity\Skin;
use pocketmine\plugin\Plugin;

use core\Core;
use core\AtPlayer as Player;
use core\command\type\CoreCommand;
use core\rank\Rank;
use core\utils\TextFormat;
use core\vote\entity\VoteBox as VoteBoxEntity;

class VoteBox extends CoreCommand{

	public function __construct(public Core $plugin, string $name, string $description) {
		parent::__construct($name, $description);
		$this->setHierarchy(Rank::HIERARCHY_HEAD_MOD);
		$this->setInGameOnly();
	}

	public function handlePlayer(Player $sender, string $commandLabel, array $args)
	{
		$box = new VoteBoxEntity(new Location($sender->getLocation()->getX(), $sender->getLocation()->getY(), $sender->getLocation()->getZ(), $sender->getWorld(), 0, 0), new Skin("Standard_Custom", file_get_contents("/home/data/skins/techie.dat"), "", "geometry.humanoid.custom"));
		$box->spawnToAll();

		$sender->sendMessage(TextFormat::GI . "Spawned vote box!");
	}

	public function getPlugin(): Plugin {
		return $this->plugin;
	}
}
