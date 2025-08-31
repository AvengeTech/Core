<?php

namespace core\staff\commands;

use core\command\type\CoreCommand;
use core\rank\Rank;
use pocketmine\Server;
use pocketmine\command\CommandSender;
use pocketmine\plugin\Plugin;

use core\AtPlayer as Player;
use core\Core;
use core\discord\command\DiscordSender;
use core\staff\entry\WarnEntry;
use core\user\User;
use core\utils\TextFormat;

class Warn extends CoreCommand {

	public function __construct(public Core $plugin, string $name, string $description) {
		parent::__construct($name, $description);
		$this->setHierarchy(Rank::HIERARCHY_STAFF);
	}

	public function handle(CommandSender $sender, string $commandLabel, array $args): void {
		if (count($args) == 0) {
			$sender->sendMessage(TextFormat::RN . "No player provided.");
			return;
		}
		$username = array_shift($args);

		if (count($args) == 0) {
			$sender->sendMessage("Must provide a type (chat or misc)");
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

		if (count($args) == 0) {
			$sender->sendMessage("Must provide a reason!");
			return;
		}
		$reason = implode(" ", $args);
		$severe = str_ends_with($reason, "!s");
		$reason = trim(str_replace("!s", "", $reason));

		$check = function (User $senderUser, User $user) use ($sender, $reason, $type, $severe): void {
			Core::getInstance()->getStaff()->warn($user, $senderUser, $reason, null, $type, $severe);
			$sender->sendMessage(TextFormat::GI . $user->getGamertag() . " has been warned!");
		};

		$closure = function (User $senderUser) use ($sender, $check, $username): void {
			if (substr($username, 0, 1) == "*") {
				$nick = substr($username, 1);
				Core::getInstance()->getRank()->userByNick($nick, function (?User $user) use ($sender, $senderUser, $check): void {
					if ($user === null) {
						$sender->sendMessage(TextFormat::RI . "Player with this nickname doesn't exist!");
						return;
					}
					$check($senderUser, $user);
				});
			} else {
				$player = Server::getInstance()->getPlayerByPrefix($username);
				if ($player instanceof Player) {
					$check($senderUser, $player->getUser());
				} else {
					Core::getInstance()->getUserPool()->useUser($username, function (User $user) use ($sender, $senderUser, $check): void {
						if (!$user->valid()) {
							$sender->sendMessage(TextFormat::RI . "Player never seen!");
							return;
						}
						$check($senderUser, $user);
					});
				}
			}
		};

		if ($sender instanceof DiscordSender) {
			Core::getInstance()->getDiscord()->s2u($sender->getSnowflake(), function (User $user) use ($sender, $closure): void {
				if (!$user->valid()) {
					$sender->sendMessage("Must verify your Discord ingame before using this command!");
					return;
				}
				$closure($user);
			});
		} elseif ($sender instanceof Player) {
			if (!$sender->isStaff() || $sender->isTrainee()) {
				$sender->sendMessage(TextFormat::RI . "This is a MOD only command!");
				return;
			}
			$user = $sender->getUser();
			$closure($user);
		} else {
			$user = Core::getInstance()->getSn3ak();
			$closure($user);
		}
	}

	public function getPlugin(): Plugin {
		return $this->plugin;
	}
}
