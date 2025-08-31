<?php

namespace core\staff\commands;

use core\command\type\CoreCommand;
use core\rank\Rank;
use pocketmine\command\CommandSender;
use pocketmine\plugin\Plugin;

use core\AtPlayer as Player;
use core\Core;
use core\staff\entry\MuteEntry;
use core\staff\entry\MuteManager;
use core\user\User;
use core\utils\TextFormat;

class Unmute extends CoreCommand {

	public function __construct(public Core $plugin, string $name, string $description) {
		parent::__construct($name, $description);
		$this->setHierarchy(Rank::HIERARCHY_MOD);
	}

	public function handle(CommandSender $sender, string $commandLabel, array $args): void {
		if (count($args) == 0) {
			$sender->sendMessage(TextFormat::RN . "No player provided.");
			return;
		}

		$username = array_shift($args);

		Core::getInstance()->getStaff()->loadMutes($username, function (MuteManager $mute) use ($sender): void {
			if (!$mute->isMuted()) {
				$sender->sendMessage(TextFormat::RN . "This player isn't muted!");
				return;
			}
			$mute->removeMute($mute->getRecentMute(), $sender instanceof Player ? $sender->getUser() : new User(0, "CONSOLE"));
			$sender->sendMessage(TextFormat::GN . $mute->getUser()->getGamertag() . " has been unmuted!");
		});
	}

	public function getPlugin(): Plugin {
		return $this->plugin;
	}
}
