<?php

namespace core\vote\commands;

use core\AtPlayer;
use core\command\type\CoreCommand;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\plugin\Plugin;

use core\Core;
use core\vote\uis\TopVotersPage;

class TopVoters extends CoreCommand{

	public $plugin;

	public function __construct(Core $plugin, $name, $description) {
		$this->plugin = $plugin;
		parent::__construct($name, $description);
		$this->setInGameOnly();
	}

	public function handlePlayer(AtPlayer $sender, string $commandLabel, array $args)
	{
		$sender->showModal(new TopVotersPage(1));
	}

	public function getPlugin(): Plugin {
		return $this->plugin;
	}
}
