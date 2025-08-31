<?php

namespace core\gadgets\command;

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

class AddGadgets extends CoreCommand {

	public function __construct(public Core $plugin, string $name, string $description) {
		parent::__construct($name, $description);
		$this->setHierarchy(Rank::HIERARCHY_HEAD_MOD);
	}

	public function handle(CommandSender $sender, string $commandLabel, array $args): void {
		if (count($args) < 2) {
			$sender->sendMessage(TextFormat::RN . "Usage: /addgadgets <player> <id> <amount>");
			return;
		}

		$name = array_shift($args);
		$id = (int) array_shift($args);
		$gadget = Core::getInstance()->getGadgets()->getGadget($id);
		if ($gadget === null) {
			$sender->sendMessage(TextFormat::RI . "Invalid gadget ID!");
			return;
		}

		$total = (int) array_shift($args);
		if ($total <= 0) {
			$sender->sendMessage(TextFormat::RI . "Total must be more than 0");
			return;
		}

		$player = $this->plugin->getServer()->getPlayerExact($name);

		if ($player instanceof Player && $player->isLoaded()) {
			$player->getSession()->getGadgets()->addTotal($gadget, $total);
			$player->sendMessage(TextFormat::GI . "You have been rewarded " . TextFormat::YELLOW . number_format($total) . TextFormat::GRAY . " " . $gadget->getName() . ($total > 1 ? "s" : "") . "!");
			$sender->sendMessage(TextFormat::GI . "Successfully gave " . TextFormat::YELLOW . $player->getName() . " " . TextFormat::AQUA . number_format($total) . TextFormat::GRAY . " " . $gadget->getName() . " gadgets!");
		} else {
			Core::getInstance()->getUserPool()->useUser($name, function (User $user) use ($sender, $gadget, $total): void {
				if (!$user->valid()) {
					$sender->sendMessage(TextFormat::RI . "Player never seen!");
					return;
				}
				Core::getInstance()->getSessionManager()->useSession($user, function (CoreSession $session) use ($gadget, $total, $sender, $user): void {
					$session->getGadgets()->addTotal($gadget, $total);
					$session->getGadgets()->saveAsync();
					$sender->sendMessage(TextFormat::GI . "Successfully gave " . TextFormat::YELLOW . $user->getGamertag() . " " . TextFormat::AQUA . number_format($total) . TextFormat::GRAY . " " . $gadget->getName() . " gadgets!");
				});
			});
		}
	}
}
