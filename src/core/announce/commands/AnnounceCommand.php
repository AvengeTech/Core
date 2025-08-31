<?php

namespace core\announce\commands;

use pocketmine\command\CommandSender;

use core\{
	Core,
	AtPlayer as Player
};
use core\announce\Announce;
use core\command\type\CoreCommand;
use core\utils\TextFormat;

class AnnounceCommand extends CoreCommand {

	public $plugin;

	public function __construct(Core $plugin, $name, $description) {
		$this->plugin = $plugin;
		parent::__construct($name, $description);
		$this->setInGameOnly();
		$this->setAliases(["news"]);
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args) {
		if (!$this->hasPermission($sender)) {
			$sender->sendMessage(TextFormat::RED . "This command can only be used in-game.");
			return;
		}

		$a = Announce::getInstance()->getAnnouncement();
		if (!is_null($a)) {
			$a->send($sender);
		}

		return;
	}
}
