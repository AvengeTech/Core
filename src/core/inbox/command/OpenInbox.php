<?php

namespace core\inbox\command;

use core\command\type\CoreCommand;
use core\rank\Rank;
use pocketmine\command\CommandSender;
use core\{
	Core,
	AtPlayer as Player
};
use core\inbox\ui\InboxUi;

class OpenInbox extends CoreCommand {

	public function __construct(public Core $plugin, string $name, string $description) {
		parent::__construct($name, $description);
		$this->setAliases(["oi", "inbox", "mail"]);
		$this->setInGameOnly();
	}

	public function handlePlayer(Player $sender, string $commandLabel, array $args)
	{
		$sender->showModal(new InboxUi($sender));
	}

	public function getPlugin(): \pocketmine\plugin\Plugin {
		return $this->plugin;
	}
}
