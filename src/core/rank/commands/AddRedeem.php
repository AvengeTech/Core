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
use core\rank\uis\CreateRedeemUi;
use core\utils\TextFormat;

class AddRedeem extends CoreCommand {

	public Core $plugin;

	public function __construct(Core $plugin, $name, $description) {
		$this->plugin = $plugin;
		parent::__construct($name, $description);
		$this->setHierarchy(Rank::HIERARCHY_HEAD_MOD);
		$this->setInGameOnly();
	}

	public function handlePlayer(Player $sender, string $commandLabel, array $args)
	{
		$sender->showModal(new CreateRedeemUi($sender));
	}

	public function getPlugin(): Plugin {
		return $this->plugin;
	}
}
