<?php

namespace core\rank\commands;

use core\command\type\CoreCommand;
use core\rank\Rank;
use pocketmine\command\CommandSender;
use pocketmine\plugin\Plugin;

use core\{
	Core,
	AtPlayer as Player
};
use core\session\CoreSession;
use core\user\User;
use core\utils\TextFormat;

class AddSub extends CoreCommand {

	public function __construct(public Core $plugin, string $name, string $description) {
		parent::__construct($name, $description);
		$this->setHierarchy(Rank::HIERARCHY_HEAD_MOD);
	}

	public function handle(CommandSender $sender, string $commandLabel, array $args): void {
		if (count($args) < 2) {
			$sender->sendMessage(TextFormat::RN . "Usage: /addsub <player> <days>");
			return;
		}

		$name = array_shift($args);
		$opt = array_shift($args);

		if ($opt == "reset") {
			$player = $this->plugin->getServer()->getPlayerExact($name);

			if ($player instanceof Player && $player->isLoaded()) {
				$player->getSession()->getRank()->clearSub();
				$player->sendMessage(TextFormat::GI . "Your Warden subscription has been removed!");
				$sender->sendMessage(TextFormat::GI . "Successfully cleared " . TextFormat::YELLOW . $player->getName() . "'s Warden subscription!");
			} else {
				Core::getInstance()->getUserPool()->useUser($name, function (User $user) use ($sender): void {
					if (!$user->valid()) {
						$sender->sendMessage(TextFormat::RI . "Player never seen!");
						return;
					}
					Core::getInstance()->getSessionManager()->useSession($user, function (CoreSession $session) use ($sender): void {
						$session->getRank()->clearSub();
						$session->getRank()->saveAsync();
						$sender->sendMessage(TextFormat::GI . "Successfully cleared " . TextFormat::YELLOW . $session->getUser()->getName() . "'s Warden subscription!");
					});
				});
			}
			return;
		}

		$days = (int) $opt;

		if ($days <= 0) {
			$sender->sendMessage(TextFormat::RI . "Days must be more than 0");
			return;
		}

		$player = $this->plugin->getServer()->getPlayerExact($name);

		if ($player instanceof Player && $player->isLoaded()) {
			$player->getSession()->getRank()->addSub($days);
			$player->sendMessage(TextFormat::GI . "You have been rewarded " . TextFormat::YELLOW . number_format($days) . TextFormat::GRAY . " days of Warden");
			$sender->sendMessage(TextFormat::GI . "Successfully gave " . TextFormat::YELLOW . $player->getName() . " " . TextFormat::AQUA . $days . TextFormat::GRAY . " days of Warden!");
		} else {
			Core::getInstance()->getUserPool()->useUser($name, function (User $user) use ($sender, $days): void {
				if (!$user->valid()) {
					$sender->sendMessage(TextFormat::RI . "Player never seen!");
					return;
				}
				Core::getInstance()->getSessionManager()->useSession($user, function (CoreSession $session) use ($sender, $days): void {
					$session->getRank()->addSub($days);
					$session->getRank()->saveAsync();
					$sender->sendMessage(TextFormat::GI . "Successfully gave " . TextFormat::YELLOW . $session->getUser()->getName() . " " . TextFormat::AQUA . $days . TextFormat::GRAY . " days of Warden!");
				});
			});
		}
	}

	public function getPlugin(): Plugin {
		return $this->plugin;
	}
}
