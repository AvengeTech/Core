<?php

namespace core\rank\commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\plugin\Plugin;

use core\{
	Core,
	AtPlayer as Player
};
use core\command\type\CoreCommand;
use core\rank\Rank;
use core\rank\Structure;
use core\session\CoreSession;
use core\user\User;
use core\utils\TextFormat;

class SetRank extends CoreCommand {

	public function __construct(public Core $plugin, string $name, string $description) {
		parent::__construct($name, $description);
		$this->setHierarchy(Rank::HIERARCHY_HEAD_MOD);
	}

	public function handle(CommandSender $sender, string $commandLabel, array $args): void {
		if (count($args) < 2) {
			$sender->sendMessage(TextFormat::RN . "Usage: /setrank <player> <rank>");
			return;
		}

		$name = array_shift($args);
		$rank = strtolower(array_shift($args));

		$player = $this->plugin->getServer()->getPlayerExact($name);
		$ranks = array_keys(Structure::RANK_HIERARCHY);
		if (!in_array($rank, $ranks)) {
			$sender->sendMessage(TextFormat::RI . "Invalid rank! (" . implode(", ", $ranks) . ")");
			return;
		}

		if ($player instanceof Player) {
			$player->setRank($rank);
			$player->sendMessage(TextFormat::GI . "Your rank has been set to " . TextFormat::YELLOW . $rank . TextFormat::GRAY . "!");
			$sender->sendMessage(TextFormat::GI . "Successfully set " . TextFormat::YELLOW . $player->getName() . "'s" . TextFormat::GRAY . " rank to " . TextFormat::YELLOW . $rank . TextFormat::GRAY . "!");
		} else {
			Core::getInstance()->getUserPool()->useUser($name, function (User $user) use ($sender, $rank): void {
				if (!$user->valid()) {
					if(!$sender instanceof Player || $sender->isConnected()) $sender->sendMessage(TextFormat::RI . "Player never seen!");
					return;
				}
				Core::getInstance()->getSessionManager()->useSession($user, function (CoreSession $session) use ($sender, $rank): void {
					$session->getRank()->setRank($rank);
					$session->getRank()->saveAsync();
					if(!$sender instanceof Player || $sender->isConnected()) $sender->sendMessage(TextFormat::GI . "Successfully set " . TextFormat::YELLOW . $session->getUser()->getGamertag() . "'s" . TextFormat::GRAY . " rank to " . TextFormat::YELLOW . $rank . TextFormat::GRAY . "!");
				});
			});
		}
	}

	public function getPlugin(): Plugin {
		return $this->plugin;
	}
}
