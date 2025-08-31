<?php

namespace core\discord\command;

use core\command\type\CoreCommand;
use core\rank\Rank;
use pocketmine\command\CommandSender;
use core\{
	Core,
	AtPlayer as Player
};
use core\session\CoreSession;
use core\user\User;
use core\utils\TextFormat;

class Unlink extends CoreCommand {

	public function __construct(public Core $plugin, string $name, string $description) {
		parent::__construct($name, $description);
		$this->setHierarchy(Rank::HIERARCHY_HEAD_MOD);
		$this->setAliases(["du"]);
	}

	public function handle(CommandSender $sender, string $commandLabel, array $args): void {
		if(count($args) === 0){
			$sender->sendMessage(TextFormat::RI . "Usage: /unlink <gamertag>");
			return;
		}
		$name = array_shift($args);

		$player = $this->plugin->getServer()->getPlayerExact($name);
		if($player instanceof Player){
			$player->getSession()->getDiscord()->unlink();
			$sender->sendMessage(TextFormat::GI . $player->getName() . "'s discord account has been unlinked!");
		}else{
			Core::getInstance()->getUserPool()->useUser($name, function (User $user) use ($sender): void {
				if (!$user->valid()) {
					$sender->sendMessage(TextFormat::RI . "Player never seen!");
					return;
				}
				Core::getInstance()->getSessionManager()->useSession($user, function (CoreSession $session) use ($user, $sender): void {
					$session->getDiscord()->unlink();
					$sender->sendMessage(TextFormat::GI . $user->getGamertag() . "'s discord account has been unlinked!");
				});
			});
		}
	}

	public function getPlugin(): \pocketmine\plugin\Plugin {
		return $this->plugin;
	}
}
