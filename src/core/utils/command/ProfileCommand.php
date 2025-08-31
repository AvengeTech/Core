<?php

namespace core\utils\command;

use core\AtPlayer as Player;
use core\Core;
use core\command\type\CoreCommand;
use core\session\CoreSession;
use core\user\User;
use core\utils\TextFormat;
use core\utils\profile\ProfileUi;
use core\rank\Rank;
use pocketmine\command\CommandSender;
use pocketmine\plugin\Plugin;

class ProfileCommand extends CoreCommand {

	public function __construct(public Core $plugin, string $name, string $description) {
		parent::__construct($name, $description);
		$this->setInGameOnly();
	}

	public function handlePlayer(Player $sender, string $commandLabel, array $args)
	{
		if (count($args) === 0) {
			$sender->showModal(new ProfileUi($sender->getSession()));
			return;
		}
		$player = array_shift($args);
		Core::getInstance()->getUserPool()->useUser($player, function (User $user) use ($sender): void {
			if (!$sender->isConnected()) return;
			if (!$user->valid()) {
				$sender->sendMessage(TextFormat::RI . "Player never seen!");
				return;
			}
			Core::getInstance()->getSessionManager()->useSession($user, function (CoreSession $session) use ($sender): void {
				if (!$sender->isConnected()) return;
				$sender->showModal(new ProfileUi($session));
			});
		});
	}

	public function getPlugin(): Plugin {
		return $this->plugin;
	}
}
