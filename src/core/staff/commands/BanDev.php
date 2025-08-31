<?php

namespace core\staff\commands;

use core\command\type\CoreCommand;
use core\rank\Rank;
use pocketmine\command\CommandSender;
use pocketmine\plugin\Plugin;

use pocketmine\{
	Server
};

use core\{
	Core,
	AtPlayer as Player
};
use core\discord\command\DiscordSender;
use core\session\mysqli\data\MySqlQuery;
use core\session\stray\StrayRequest;
use core\user\User;
use core\utils\TextFormat;

class BanDev extends CoreCommand {

	public function __construct(public \core\Core $plugin, $name, $description) {
		parent::__construct($name, $description);
		$this->setHierarchy(Rank::HIERARCHY_MOD);
	}

	public function handle(CommandSender $sender, string $commandLabel, array $args): void {
		$username = array_shift($args);
		if (count($args) === 0) {
			$sender->sendMessage(TextFormat::RN . "No player name provided.");
			return;
		}
		$until = (int) array_shift($args);
		if ($until == 0) {
			$sender->sendMessage(TextFormat::RN . "Time must be at least 1 day! Set to -1 for ETERNITY");
			return;
		}
		if ($until > 0) $until = $until * (60 * 60 * 24);
		if (count($args) === 0) {
			$sender->sendMessage(TextFormat::RN . "Must provide a reason!");
			return;
		}
		$reason = implode(" ", $args);

		$check = function (User $senderUser, User $user) use ($sender, $username, $until, $reason): void {
			$senderXuid = $senderUser->getXuid();
			Core::getInstance()->getSessionManager()->sendStrayRequest(new StrayRequest("bandev_check_did_" . $username, new MySqlQuery(
				"main",
				"SELECT deviceid FROM network_playerdata WHERE xuid=?",
				[$user->getXuid()]
			)), function (StrayRequest $request) use ($sender, $senderXuid, $username, $until, $reason): void {
				$deviceid = ($request->getQuery()->getResult()->getRows()[0] ?? [])["deviceid"] ?? null;
				if ($deviceid !== null) {
					Core::getInstance()->getSessionManager()->sendStrayRequest(new StrayRequest("bandev_check_isbanned_" . $deviceid, new MySqlQuery(
						"main",
						"SELECT * FROM bans WHERE id=?",
						[$deviceid]
					)), function (StrayRequest $request) use ($sender, $senderXuid, $deviceid, $username, $until, $reason): void {
						if (count($request->getQuery()->getResult()->getRows()) == 0) {
							Core::getInstance()->getStaff()->ban($deviceid, $senderXuid, $reason, $until);
							$sender->sendMessage(TextFormat::GI . TextFormat::YELLOW . $username . TextFormat::GRAY . "'s device ID has been banned! (DID: " . TextFormat::AQUA . $deviceid . TextFormat::GRAY . ")");
						} else {
							$sender->sendMessage(TextFormat::RI . "This player's device ID is already banned!");
						}
					});
				} else {
					$sender->sendMessage(TextFormat::RI . "Player has no device ID stored? (impossible!)");
				}
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
