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
use core\user\User;
use core\utils\TextFormat;

class Xuid extends CoreCommand {

	public function __construct(public Core $plugin, string $name, string $description) {
		parent::__construct($name, $description);
		$this->setHierarchy(Rank::HIERARCHY_STAFF);
	}

	public function handle(CommandSender $sender, string $commandLabel, array $args): void {
		if (count($args) < 1) {
			$sender->sendMessage(TextFormat::RI . "Usage: /xuid <gamertag>");
			return;
		}

		Core::getInstance()->getUserPool()->useUser(array_shift($args), function (User $user) use ($sender): void {
			if ($sender instanceof Player && !$sender->isConnected()) return;
			if (!$user->valid()) {
				$sender->sendMessage(TextFormat::RI . "Player never seen!");
				return;
			}
			$sender->sendMessage(TextFormat::GI . $user->getGamertag() . "'s XUID: " . TextFormat::YELLOW . $user->getXuid());
		});
	}

	public function getPlugin(): Plugin {
		return $this->plugin;
	}
}
