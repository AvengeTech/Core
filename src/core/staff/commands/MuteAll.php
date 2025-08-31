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
use core\utils\TextFormat;

class MuteAll extends CoreCommand {

	public Core $plugin;

	public function __construct(Core $plugin, $name, $description) {
		$this->plugin = $plugin;
		parent::__construct($name, $description);
		$this->setHierarchy(Rank::HIERARCHY_SR_MOD);
	}

	public function handle(CommandSender $sender, string $commandLabel, array $args): void {
		$staff = Core::getInstance()->getStaff();
		$sender->sendMessage(TextFormat::GI . "Everyone is " . ($staff->toggleAllMuted() ? "now" : "no longer") . " muted!");
	}

	public function getPlugin(): Plugin {
		return $this->plugin;
	}
}
