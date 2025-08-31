<?php

namespace core\network\commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\plugin\Plugin;

use core\{
	Core,
	AtPlayer as Player
};
use core\command\type\CoreCommand;
use core\rank\Rank;
use core\session\CoreSession;
use core\user\User;
use core\utils\TextFormat;

class LoadTest extends CoreCommand {

	public function __construct(public Core $plugin, string $name, string $description) {
		parent::__construct($name, $description);
		$this->setHierarchy(Rank::HIERARCHY_HEAD_MOD);
		$this->setAliases(["lt"]);
	}

	public function handle(CommandSender $sender, string $commandLabel, array $args): void {
		if (count($args) === 0) {
			$sender->sendMessage(TextFormat::RI . "Usage: /lt <name or xuid>");
			return;
		}

		$name = array_shift($args);
		Core::getInstance()->getUserPool()->useUser($name, function (User $user) use ($sender): void {
			if ($sender instanceof Player && !$sender->isConnected()) return;
			if (!$user->valid()) {
				$sender->sendMessage(TextFormat::RI . "Invalid gamertag or xuid provided!");
				return;
			}
			Core::getInstance()->getSessionManager()->useSession($user, function (CoreSession $session) use ($sender): void {
				if ($sender instanceof Player && !$sender->isConnected()) return;
				$sender->sendMessage(
					"User data loaded for " . $session->getGamertag() . PHP_EOL .
						"XUID: " . $session->getNetwork()->getStoredXuid() . PHP_EOL .
						"Rank: " . $session->getRank()->getRank() . PHP_EOL .
						"Last seen: " . date("F j, Y, g:ia", $session->getNetwork()->getLastLogin()) . " (" . $session->getNetwork()->getLastServer() . ")"
				);
			});
		});
	}

	public function getPlugin(): Plugin {
		return $this->plugin;
	}
}
