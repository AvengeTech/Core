<?php

namespace core\staff\commands;

use core\command\type\CoreCommand;
use core\rank\Rank;
use pocketmine\command\CommandSender;
use pocketmine\plugin\Plugin;

use core\AtPlayer as Player;
use core\Core;
use core\staff\entry\WarnEntry;
use core\user\User;
use core\utils\TextFormat;

class WarnAs extends CoreCommand {

	public function __construct(public Core $plugin, string $name, string $description) {
		parent::__construct($name, $description);
		$this->setHierarchy(Rank::HIERARCHY_HEAD_MOD);
	}

	public function handle(CommandSender $sender, string $commandLabel, array $args)
	{
		if (count($args) == 0) {
			$sender->sendMessage(TextFormat::RI . "Usage: /warnas <player> [identifier] <type [c:m]> <warned> <reason>");
			return;
		}
		$pl = array_shift($args);
		if (count($args) == 0) $identifier = Core::thisServer()->getIdentifier();
		else $identifier = strtolower(array_shift($args));
		if (count($args) == 0) {
			$sender->sendMessage(TextFormat::RN . "No player provided.");
			return;
		}
		$type = strtolower(array_shift($args));
		switch ($type) {
			case "c":
			case "chat":
				$type = WarnEntry::TYPE_CHAT;
				break;
			case "m":
			case "misc":
				$type = WarnEntry::TYPE_MISC;
				break;
			default:
				$sender->sendMessage(TextFormat::RN . "Invalid type provided! (chat or misc)");
				return;
		}

		$warning = str_replace("_", " ", array_shift($args));
		if (count($args) == 0) {
			$sender->sendMessage(TextFormat::RI . "Must provide a reason!");
			return;
		}
		$reason = implode(" ", $args);
		$severe = str_ends_with("!s", $reason);
		$reason = trim(str_replace("!s", "", $reason));

		Core::getInstance()->getUserPool()->useUser($pl, function (User $user) use ($sender, $warning, $pl, $identifier, $reason, $type, $severe): void {
			if (!$user->valid()) {
				$sender->sendMessage(TextFormat::RI . "Player trying to warn as never seen!");
				return;
			}
			Core::getInstance()->getUserPool()->useUser($warning, function (User $newUser) use ($sender, $user, $identifier, $reason, $type, $severe): void {
				if (!$newUser->valid()) {
					$sender->sendMessage(TextFormat::RI . "Player trying to warn never seen!");
					return;
				}
				Core::getInstance()->getStaff()->warn($newUser, $user, $reason, $identifier, $type, $severe);
				$sender->sendMessage(TextFormat::GN . $newUser->getGamertag() . " has been warned as " . $user->getGamertag() . "! Reason: " . $reason);
			});
		});
	}

	public function getPlugin(): Plugin {
		return $this->plugin;
	}
}
