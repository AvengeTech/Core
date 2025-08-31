<?php

namespace core\staff\commands;

use core\command\type\CoreCommand;
use core\rank\Rank;
use pocketmine\command\CommandSender;
use pocketmine\plugin\Plugin;

use core\{
	Core,
	AtPlayer as Player
};
use core\utils\{
	TextFormat,
	CapeData
};

class Cape extends CoreCommand {

	public function __construct(public Core $plugin, string $name, string $description) {
		parent::__construct($name, $description);
		$this->setHierarchy(Rank::HIERARCHY_HEAD_MOD);
		$this->setInGameOnly();
	}

	public function handlePlayer(Player $sender, string $commandLabel, array $args)
	{
		if (count($args) == 0) {
			$sender->sendMessage(TextFormat::RI . "Usage: /cape <capeName>");
			return;
		}
		$capeName = strtolower(array_shift($args));

		$capedata = new CapeData();
		if (!$capedata->capeExists($capeName)) {
			$sender->sendMessage(TextFormat::RI . "This cape does not exist!");
			return;
		}

		$skin = $capedata->getSkinWithCape($sender, $capeName);
		$sender->setSkin($skin);
		$sender->sendSkin();
		$sender->sendMessage(TextFormat::GI . "You now have the " . TextFormat::YELLOW . $capeName . " cape" . TextFormat::GRAY . " applied!");
	}

	public function getPlugin(): Plugin {
		return $this->plugin;
	}
}
