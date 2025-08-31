<?php

namespace core\network\commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\plugin\Plugin;

use core\{
	Core,
	AtPlayer as Player
};
use core\command\type\CoreCommand;
use core\rank\Rank;
use core\utils\TextFormat;

class VarDumpEntity extends CoreCommand {

	public function __construct(public Core $plugin, string $name, string $description) {
		parent::__construct($name, $description);
		$this->setHierarchy(Rank::HIERARCHY_HEAD_MOD);
		$this->setInGameOnly();
		$this->setAliases(["vde"]);
	}

	public function handlePlayer(Player $sender, string $commandLabel, array $args)
	{
		if (count($args) < 1) {
			$sender->sendMessage(TextFormat::RI . "Usage: /vde <entity id>");
			return;
		}

		$entity = $sender->getWorld()->getEntity((int) array_shift($args));
		if ($entity !== null) {
			var_dump(get_class($entity));
			$sender->sendMessage(TextFormat::GI . "Entity class dumped to console");
		} else {
			$sender->sendMessage(TextFormat::RI . "Entity with that ID not found!");
		}
	}

	public function getPlugin(): Plugin {
		return $this->plugin;
	}
}
