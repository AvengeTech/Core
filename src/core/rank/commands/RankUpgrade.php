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

class RankUpgrade extends CoreCommand {

	public function __construct(public Core $plugin, string $name, string $description) {
		parent::__construct($name, $description);
		$this->setHierarchy(Rank::HIERARCHY_HEAD_MOD);
	}

	public function handle(CommandSender $sender, string $commandLabel, array $args): void {
		if (count($args) < 1) {
			$sender->sendMessage(TextFormat::RED . TextFormat::BOLD . "(i) " . TextFormat::RESET . TextFormat::GRAY . "Usage: /setrank <player>");
			return;
		}

		$name = array_shift($args);

		$sub = false;
		$player = $this->plugin->getServer()->getPlayerExact($name);
		if ($player instanceof Player) {
			switch ($player->getRank()) {
				case "default":
				case "":
					$rank = "endermite";
					break;
				case "endermite":
					$rank = "blaze";
					break;
				case "blaze":
					$rank = "ghast";
					break;
				case "ghast":
					$rank = "enderman";
					break;
				case "enderman":
					$rank = "wither";
					break;
				case "wither":
					$rank = "enderdragon";
					break;
				default:
					$sub = true;
					break;
			}
			if (!$sub) {
				$player->setRank($rank);
				$player->sendMessage(TextFormat::GI . "Your rank has been upgraded to " . TextFormat::YELLOW . $rank . TextFormat::GRAY . "!");
				$sender->sendMessage(TextFormat::GI . "Successfully set " . TextFormat::YELLOW . $player->getName() . "'s" . TextFormat::GRAY . " rank to " . TextFormat::YELLOW . $rank . TextFormat::GRAY . "!");
			} else {
				$player->getSession()->getRank()->addSub(31);
				$player->sendMessage(TextFormat::GI . "You were given " . TextFormat::YELLOW . "31 days" . TextFormat::GRAY . " of our Warden subscription!");
				$sender->sendMessage(TextFormat::GI . "Gave " . TextFormat::YELLOW . $player->getName() . " 31 days" . TextFormat::GRAY . " of our Warden subscription!");
			}
		} else {
			Core::getInstance()->getUserPool()->useUser($name, function (User $user) use ($sender, $player): void {
				if (!$user->valid()) {
					$sender->sendMessage(TextFormat::RI . "Player never seen!");
					return;
				}
				Core::getInstance()->getSessionManager()->useSession($user, function (CoreSession $session) use ($sender, $player): void {
					switch ($session->getRank()->getRank()) {
						case "default":
						case "":
							$rank = "endermite";
							break;
						case "endermite":
							$rank = "blaze";
							break;
						case "blaze":
							$rank = "ghast";
							break;
						case "ghast":
							$rank = "enderman";
							break;
						case "enderman":
							$rank = "wither";
							break;
						case "wither":
							$rank = "enderdragon";
							break;
						default:
							$sub = true;
							break;
					}
					if (!$sub) {
						$session->getRank()->setRank($rank);
						$sender->sendMessage(TextFormat::GI . "Successfully set " . TextFormat::YELLOW . $player->getName() . "'s" . TextFormat::GRAY . " rank to " . TextFormat::YELLOW . $rank . TextFormat::GRAY . "!");
					} else {
						$session->getRank()->addSub(31);
						$sender->sendMessage(TextFormat::GI . "Gave " . TextFormat::YELLOW . $player->getName() . " 31 days" . TextFormat::GRAY . " of our Warden subscription!");
					}
					$session->getRank()->saveAsync();
				});
			});
		}
	}

	public function getPlugin(): Plugin {
		return $this->plugin;
	}
}
