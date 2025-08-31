<?php

namespace core\staff\commands;

use core\command\type\CoreCommand;
use core\rank\Rank;
use pocketmine\command\CommandSender;
use pocketmine\plugin\Plugin;

use core\AtPlayer as Player;
use core\Core;
use core\session\mysqli\data\MySqlQuery;
use core\session\stray\StrayRequest;
use core\staff\anticheat\AntiCheat;
use core\staff\entry\BanManager;
use core\user\User;
use core\utils\TextFormat;

class UnbanIP extends CoreCommand {

	public function __construct(public Core $plugin, $name, $description) {
		parent::__construct($name, $description);
		$this->setHierarchy(Rank::HIERARCHY_SR_MOD);
	}

	public function handle(CommandSender $sender, string $commandLabel, array $args): void {
		if (empty($args)) {
			$sender->sendMessage(TextFormat::RN . "No player name provided.");
			return;
		}

		$name = array_shift($args);

		Core::getInstance()->getUserPool()->useUser($name, function (User $user) use ($sender): void {
			if (!$user->valid()) {
				$sender->sendMessage(TextFormat::RI . "Player never seen!");
				return;
			}
			Core::getInstance()->getSessionManager()->sendStrayRequest(new StrayRequest("did_check_ubip", new MySqlQuery(
				"main",
				"SELECT lastaddress FROM network_playerdata WHERE xuid=?",
				[$user->getXuid()]
			)), function (StrayRequest $request) use ($sender): void {
				$address = ($request->getQuery()->getResult()->getRows()[0] ?? [])["lastaddress"] ?? null;
				if ($address !== null) {
					Core::getInstance()->getStaff()->loadBans($address, function (BanManager $bm) use ($sender): void {
						if (!$bm->isBanned()) {
							$sender->sendMessage(TextFormat::RN . "This player isn't IP banned!");
							return;
						}
						$ban = $bm->getRecentBan();
						$su = ($sender instanceof Player ? $sender->getUser() : null) ?? new User(-100, AntiCheat::USER_NAME);
						$ban->revoke($su);
						$sender->sendMessage(TextFormat::GI . "IP Address: " . $ban->getId() . " has been unbanned!");
					});
				} else {
					$sender->sendMessage(TextFormat::RI . "Player has no IP address stored? (impossible!)");
				}
			});
		});
	}

	public function getPlugin(): Plugin {
		return $this->plugin;
	}
}
