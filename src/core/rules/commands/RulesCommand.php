<?php

namespace core\rules\commands;

use core\command\type\CoreCommand;
use core\rank\Rank;
use pocketmine\command\CommandSender;

use core\{
	Core,
	AtPlayer as Player
};
use core\rules\uis\RulesUi;

class RulesCommand extends CoreCommand {

	public function __construct(public \core\Core $plugin, $name, $description) {
		parent::__construct($name, $description);
		$this->setInGameOnly();
		$this->setAliases(["rule"]);
	}

	public function handlePlayer(Player $sender, string $commandLabel, array $args)
	{
		$sender->showModal(new RulesUi($this->plugin, $this->plugin->getRules()->getRuleManager()));
	}
}
