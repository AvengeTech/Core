<?php

namespace core\vote\commands;

use pocketmine\Server;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\plugin\Plugin;

use core\AtPlayer as Player;
use core\command\type\CoreCommand;
use core\Core;
use core\rank\Rank;
use core\session\CoreSession;
use core\user\User;
use core\utils\TextFormat;

class SetVotes extends CoreCommand{

	public function __construct(public Core $plugin, string $name, string $description) {
		parent::__construct($name, $description);
		$this->setHierarchy(Rank::HIERARCHY_HEAD_MOD);
	}

	public function handle(CommandSender $sender, string $commandLabel, array $args): void {
		if (count($args) == 0) {
			$sender->sendMessage(TextFormat::RI . "Usage: /setvotes <player> <day> <total> <highest>");
			return;
		}

		$username = array_shift($args);
		$day = (int) array_shift($args);
		if ($day < 0) {
			$sender->sendMessage(TextFormat::RI . "Day must be at least 0!");
			return;
		}
		$total = (int) (array_shift($args) ?? 0);
		if ($total < 0) {
			$sender->sendMessage(TextFormat::RI . "Total must be at least 0!");
			return;
		}
		$highest = (int) (array_shift($args) ?? 0);
		if ($highest < 0) {
			$sender->sendMessage(TextFormat::RI . "Highest must be at least 0!");
			return;
		}

		$player = Server::getInstance()->getPlayerByPrefix($username);
		if ($player instanceof Player) {
			$username = $player->getName();
		}

		Core::getInstance()->getUserPool()->useUser($username, function (User $user) use ($sender, $day, $total, $highest): void {
			if (!$user->valid()) {
				$sender->sendMessage(TextFormat::RI . "Player never seen!");
				return;
			}
			Core::getInstance()->getSessionManager()->useSession($user, function (CoreSession $session) use ($sender, $day, $total, $highest): void {
				$session->getVote()->setVote($day, $total, $highest);
				$session->getVote()->saveAsync();

				if (($player = $session->getPlayer()) instanceof Player) $player->sendMessage(TextFormat::GI . "Your vote streak has been force set to " . TextFormat::YELLOW . $day);
				$sender->sendMessage(TextFormat::GI . "Force set this player's vote streak to " . TextFormat::YELLOW . $day);
			});
		});
	}

	public function getPlugin(): Plugin {
		return $this->plugin;
	}
}
