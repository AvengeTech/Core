<?php

namespace core\staff\commands;

use core\command\type\CoreCommand;
use core\rank\Rank;
use pocketmine\command\CommandSender;
use pocketmine\plugin\Plugin;

use core\AtPlayer as Player;
use core\Core;
use core\staff\anticheat\AntiCheat;
use core\staff\entry\BanEntry;
use core\staff\entry\BanManager;
use core\user\User;
use core\utils\TextFormat;

class Unban extends CoreCommand {

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

		Core::getInstance()->getStaff()->loadBans($username, function (BanManager $bm) use ($sender): void {
			if (!$bm->isBanned()) {
				$sender->sendMessage(TextFormat::RN . "This player isn't banned!");
				return;
			}
			$ban = $bm->getRecentBan();
			$su = ($sender instanceof Player ? $sender->getUser() : null) ?? new User(-100, AntiCheat::USER_NAME);
			$ban->revoke($su);
			$sender->sendMessage(TextFormat::GI . $ban->getUser()->getGamertag() . " has been unbanned!");
		});
	}

	public function getPlugin(): Plugin {
		return $this->plugin;
	}
}
