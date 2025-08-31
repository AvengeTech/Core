<?php

namespace core\cosmetics\command;

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

class AddCosmetic extends CoreCommand {

	public function __construct(public Core $plugin, string $name, string $description) {
		parent::__construct($name, $description);
		$this->setHierarchy(Rank::HIERARCHY_HEAD_MOD);
	}

	public function handle(CommandSender $sender, string $commandLabel, array $args) {
		if (count($args) !== 3) {
			$sender->sendMessage(TextFormat::RI . "Usage: /addcosmetic <name> <cape:trail:idle:dj:hat:back:shoes:suit> <id>"); // this usage message gives me an aneurism
			return;
		}

		$name = array_shift($args);
		$type = strtolower(array_shift($args));
		$id = (int) array_shift($args);

		if (!in_array($type, ["cape", "trail", "idle", "dj", "hat", "back", "shoes", "suit"])) {
			$sender->sendMessage(TextFormat::RI . "Invalid cosmetic type! <cape:trail:idle:dj:hat:back:shoes:suit>");
			return;
		}

		$addCosmetic = function (CoreSession $session) use ($sender, $type, $id) {
			if ($sender instanceof Player && !$sender->isConnected()) return;
			switch ($type) {
				case "cape":
					if (($c = Core::getInstance()->getCosmetics()->getCape($id)) === null) {
						$sender->sendMessage(TextFormat::RI . "Cape not found!");
						return;
					}
					if (!$session->getCosmetics()->hasCape($id)) {
						$session->getCosmetics()->addCape($id);
						if (!($pl = $session->getPlayer()) instanceof Player) {
							$session->getCosmetics()->saveAsync();
						} else {
							$pl->sendMessage(TextFormat::GI . "You just received the " . $c->getName() . " cape!");
						}
					}
					$sender->sendMessage(TextFormat::GI . "Gave " . $session->getUser()->getGamertag() . " the " . $c->getName() . " cape!");
					break;
				case "trail":
					if (($c = Core::getInstance()->getCosmetics()->getTrail($id)) === null) {
						$sender->sendMessage(TextFormat::RI . "Trail effect not found!");
						return;
					}
					if (!$session->getCosmetics()->hasTrail($id)) {
						$session->getCosmetics()->addTrail($id);
						if (!($pl = $session->getPlayer()) instanceof Player) {
							$session->getCosmetics()->saveAsync();
						} else {
							$pl->sendMessage(TextFormat::GI . "You just received the " . $c->getName() . " trail effect!");
						}
					}
					$sender->sendMessage(TextFormat::GI . "Gave " . $session->getUser()->getGamertag() . " the " . $c->getName() . " trail effect!");
					break;
				case "idle":
					if (($c = Core::getInstance()->getCosmetics()->getIdle($id)) === null) {
						$sender->sendMessage(TextFormat::RI . "Idle effect not found!");
						return;
					}
					if (!$session->getCosmetics()->hasIdle($id)) {
						$session->getCosmetics()->addIdle($id);
						if (!($pl = $session->getPlayer()) instanceof Player) {
							$session->getCosmetics()->saveAsync();
						} else {
							$pl->sendMessage(TextFormat::GI . "You just received the " . $c->getName() . " idle effect!");
						}
					}
					$sender->sendMessage(TextFormat::GI . "Gave " . $session->getUser()->getGamertag() . " the " . $c->getName() . " idle effect!");
					break;
				case "dj":
					if (($c = Core::getInstance()->getCosmetics()->getDoubleJump($id)) === null) {
						$sender->sendMessage(TextFormat::RI . "Double jump effect not found!");
						return;
					}
					if (!$session->getCosmetics()->hasDoubleJump($id)) {
						$session->getCosmetics()->addDoubleJump($id);
						if (!($pl = $session->getPlayer()) instanceof Player) {
							$session->getCosmetics()->saveAsync();
						} else {
							$pl->sendMessage(TextFormat::GI . "You just received the " . $c->getName() . " double jump effect!");
						}
					}
					$sender->sendMessage(TextFormat::GI . "Gave " . $session->getUser()->getGamertag() . " the " . $c->getName() . " double jump effect!");
					break;
				case "hat":
					if (($c = Core::getInstance()->getCosmetics()->getHat($id)) === null) {
						$sender->sendMessage(TextFormat::RI . "Hat not found!");
						return;
					}
					if (!$session->getCosmetics()->hasHat($id)) {
						$session->getCosmetics()->addHat($id);
						if (!($pl = $session->getPlayer()) instanceof Player) {
							$session->getCosmetics()->saveAsync();
						} else {
							$pl->sendMessage(TextFormat::GI . "You just received the " . $c->getName() . " hat!");
						}
					}
					$sender->sendMessage(TextFormat::GI . "Gave " . $session->getUser()->getGamertag() . " the " . $c->getName() . " hat!");
					break;
				case "back":
					if (($c = Core::getInstance()->getCosmetics()->getBack($id)) === null) {
						$sender->sendMessage(TextFormat::RI . "Back not found!");
						return;
					}
					if (!$session->getCosmetics()->hasBack($id)) {
						$session->getCosmetics()->addBack($id);
						if (!($pl = $session->getPlayer()) instanceof Player) {
							$session->getCosmetics()->saveAsync();
						} else {
							$pl->sendMessage(TextFormat::GI . "You just received the " . $c->getName() . " back!");
						}
					}
					$sender->sendMessage(TextFormat::GI . "Gave " . $session->getUser()->getGamertag() . " the " . $c->getName() . " back!");
					break;
				case "shoes":
					if (($c = Core::getInstance()->getCosmetics()->getShoes($id)) === null) {
						$sender->sendMessage(TextFormat::RI . "Shoes not found!");
						return;
					}
					if (!$session->getCosmetics()->hasShoes($id)) {
						$session->getCosmetics()->addShoes($id);
						if (!($pl = $session->getPlayer()) instanceof Player) {
							$session->getCosmetics()->saveAsync();
						} else {
							$pl->sendMessage(TextFormat::GI . "You just received the " . $c->getName() . " shoes!");
						}
					}
					$sender->sendMessage(TextFormat::GI . "Gave " . $session->getUser()->getGamertag() . " the " . $c->getName() . " shoes!");
					break;
				case "suit":
					if (($c = Core::getInstance()->getCosmetics()->getSuit($id)) === null) {
						$sender->sendMessage(TextFormat::RI . "Suit not found!");
						return;
					}
					if (!$session->getCosmetics()->hasSuit($id)) {
						$session->getCosmetics()->addSuit($id);
						if (!($pl = $session->getPlayer()) instanceof Player) {
							$session->getCosmetics()->saveAsync();
						} else {
							$pl->sendMessage(TextFormat::GI . "You just received the " . $c->getName() . " suit!");
						}
					}
					$sender->sendMessage(TextFormat::GI . "Gave " . $session->getUser()->getGamertag() . " the " . $c->getName() . " suit!");
					break;
			}
		};

		Core::getInstance()->getUserPool()->useUser($name, function (User $user) use ($sender, $addCosmetic): void {
			if ($sender instanceof Player && !$sender->isConnected()) return;
			if (!$user->valid()) {
				$sender->sendMessage(TextFormat::RI . "Player never seen!");
				return;
			}
			Core::getInstance()->getSessionManager()->useSession($user, function (CoreSession $session) use ($addCosmetic): void {
				$addCosmetic($session);
			});
		});
	}

	public function getPlugin(): Plugin {
		return $this->plugin;
	}
}
