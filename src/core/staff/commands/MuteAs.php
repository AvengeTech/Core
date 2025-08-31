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
use core\staff\entry\MuteEntry;
use core\staff\entry\MuteManager;
use core\utils\TextFormat;

class MuteAs extends CoreCommand {

	public function __construct(public Core $plugin, string $name, string $description) {
		parent::__construct($name, $description);
		$this->setHierarchy(Rank::HIERARCHY_HEAD_MOD);
	}

	public function handle(CommandSender $sender, string $commandLabel, array $args): void {
		if (count($args) == 0) {
			$sender->sendMessage(TextFormat::RN . "Usage: /muteas <player> <muting> <days> <reason>");
			return;
		}

		$mutingAs = array_shift($args);

		if (empty($args)) {
			$sender->sendMessage(TextFormat::RI . "Must provide player!");
			return;
		}

		$username = str_replace("_", " ", array_shift($args));

		if (count($args) == 0) {
			$sender->sendMessage(TextFormat::RI . "Must specify number of days!");
			return;
		}

		$until = (int) array_shift($args);
		if ($until == 0) {
			$sender->sendMessage(TextFormat::RI . "Time must be at least 1 day!");
			return;
		}
		if ($until > 0) {
			$until = $until * (60 * 60 * 24);
		}

		if (count($args) == 0) {
			$sender->sendMessage(TextFormat::RI . "Must provide a reason!");
			return;
		}
		$reason = implode(" ", $args);

		Core::getInstance()->getStaff()->loadMutes($username, function (MuteManager $mute) use ($sender, $username, $mutingAs, $until, $reason): void {
			if (!$mute->getUser()->valid()) {
				$sender->sendMessage(TextFormat::RI . "Player never seen!");
				return;
			}
			if ($mute->isMuted()) {
				$sender->sendMessage(TextFormat::RI . "This player is already muted!");
				return;
			}
			Core::getInstance()->getStaff()->mute($username, $mutingAs, $reason, $until);
			$sender->sendMessage(TextFormat::GI . "Player has been muted!");
		});
	}

	public function getPlugin(): Plugin {
		return $this->plugin;
	}
}
