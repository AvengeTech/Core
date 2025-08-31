<?php

namespace core\staff\commands;

use core\command\type\CoreCommand;
use core\rank\Rank;
use pocketmine\command\CommandSender;
use pocketmine\command\ConsoleCommandSender;

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

class BanIP extends CoreCommand {

	public Core $plugin;

	public function __construct(Core $plugin, $name, $description) {
		$this->plugin = $plugin;
		parent::__construct($name, $description);
		$this->setHierarchy(Rank::HIERARCHY_SR_MOD);
	}

	public function handle(CommandSender $sender, string $commandLabel, array $args): void {
		$username = array_shift($args);
		if (empty($args)) {
			$sender->sendMessage(TextFormat::RN . "No player name provided.");
			return;
		}
		if (empty($args)) {
			$sender->sendMessage(TextFormat::RN . "Must provide a time in days! Ex: 7, 31, -1 for ETERNITY...");
			return;
		}
		$until = (int) array_shift($args);
		if ($until == 0) {
			$sender->sendMessage(TextFormat::RN . "Time must be at least 1 day! Set to -1 for ETERNITY");
			return;
		}
		if ($until > 0) $until = $until * (60 * 60 * 24);
		if (empty($args)) {
			$sender->sendMessage(TextFormat::RN . "Must provide a reason!");
			return;
		}
		$reason = implode(" ", $args);

		$check = function (User $senderUser, User $user) use ($sender, $username, $until, $reason): void {
			$senderXuid = $senderUser->getXuid();
			Core::getInstance()->getSessionManager()->sendStrayRequest(new StrayRequest("banip_check_ip_" . $username, new MySqlQuery(
				"main",
				"SELECT lastaddress FROM network_playerdata WHERE xuid=?",
				[$user->getXuid()]
			)), function (StrayRequest $request) use ($sender, $senderXuid, $username, $until, $reason): void {
				$address = ($request->getQuery()->getResult()->getRows()[0] ?? [])["lastaddress"] ?? null;
				if ($address !== null) {
					Core::getInstance()->getSessionManager()->sendStrayRequest(new StrayRequest("banip_check_isbanned_" . $address, new MySqlQuery(
						"main",
						"SELECT * FROM bans WHERE id=?",
						[$address]
					)), function (StrayRequest $request) use ($sender, $senderXuid, $address, $username, $until, $reason): void {
						if (count($request->getQuery()->getResult()->getRows()) == 0) {
							Core::getInstance()->getStaff()->ban($address, $senderXuid, $reason, $until);
							$sender->sendMessage(TextFormat::GI . TextFormat::YELLOW . $username . TextFormat::GRAY . "'s IP address has been banned!");
						} else {
							$sender->sendMessage(TextFormat::RI . "This player's IP address is already banned!");
						}
					});
				} else {
					$sender->sendMessage(TextFormat::RI . "Player has no IP address stored? (impossible!)");
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
