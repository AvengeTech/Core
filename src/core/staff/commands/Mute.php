<?php

namespace core\staff\commands;

use core\command\type\CoreCommand;
use core\rank\Rank;
use pocketmine\Server;
use pocketmine\command\CommandSender;
use pocketmine\plugin\Plugin;

use core\{
	Core,
	AtPlayer as Player
};
use core\discord\command\DiscordSender;
use core\staff\entry\MuteEntry;
use core\staff\entry\MuteManager;
use core\user\User;
use core\utils\TextFormat;

class Mute extends CoreCommand {

	public function __construct(public \core\Core $plugin, string $name, string $description) {
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
			$sender->sendMessage("Must provide a time in days! Ex: 7, 31, -1 for ETERNITY...");
			return;
		}
		$until = (int) array_shift($args);
		if ($until == 0) {
			$sender->sendMessage("Time must be at least 1 day!");
			return;
		}
		if ($until > 0) {
			$until = $until * (60 * 60 * 24);
		}
		if (count($args) == 0) {
			$sender->sendMessage("Must provide a reason!");
			return;
		}
		$reason = implode(" ", $args);

		$check = function (User $senderUser, User $user) use ($sender, $until, $reason): void {
			Core::getInstance()->getStaff()->loadMutes($user, function (MuteManager $mute) use ($user, $sender, $senderUser, $until, $reason): void {
				if (!$user->valid()) {
					$sender->sendMessage(TextFormat::RI . "Player never seen!");
					return;
				}
				if ($mute->isMuted()) {
					$sender->sendMessage(TextFormat::RI . "This player is already muted!");
					return;
				}
				Core::getInstance()->getStaff()->mute($user, $senderUser, $reason, $until);
				$sender->sendMessage(TextFormat::GI . $user->getGamertag() . " has been muted!");
			});
		};

		$closure = function (User $senderUser) use ($sender, $check, $username, $until, $reason): void {
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
