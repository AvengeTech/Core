<?php

namespace core\staff\commands;

use core\command\type\CoreCommand;
use core\rank\Rank;
use pocketmine\Server;
use pocketmine\command\CommandSender;
use pocketmine\plugin\Plugin;

use core\AtPlayer as Player;
use core\Core;
use core\network\server\ServerInstance;
use core\network\Structure as NetworkStructure;
use core\session\CoreSession;
use core\session\PlayerSession;
use core\staff\inventory\SeeinvInventory;
use core\user\User;
use core\utils\TextFormat;
use prison\Prison;
use prison\PrisonSession;
use skyblock\SkyBlock;
use skyblock\SkyBlockSession;

class Seeinv extends CoreCommand {

	public static array $runner = [];

	public function __construct(public Core $plugin, string $name, string $description) {
		parent::__construct($name, $description);
		$this->setHierarchy(Rank::HIERARCHY_STAFF);
		$this->setInGameOnly();
	}

	public function handlePlayer(Player $sender, string $commandLabel, array $args)
	{
		if (!in_array(Core::thisServer()->getType(), ["skyblock", "prison"])) {
			$sender->sendMessage(TextFormat::RN . "This feature does not exist on " . NetworkStructure::TYPE_TO_CASE[Core::thisServer()->getType()] . "!");
			return;
		}

		if (count($args) != 1) {
			$sender->sendMessage(TextFormat::RN . "Usage: /seeinv <player>");
			return;
		}

		$player = Server::getInstance()->getPlayerByPrefix($name = array_shift($args));
		self::$runner[$id = time()] = true;
		if (!$player instanceof Player) {
			$sm = Core::getInstance()->getNetwork()->getServerManager();
			$server = $sm->getServerByPlayer($name);
			if (!$server instanceof ServerInstance) {
				Core::getInstance()->getUserPool()->useUser(strtolower($name), function (User $user) use ($sender, $id): void {
					if (!$user->valid()) {
						if ($sender instanceof Player && $sender->isConnected()) $sender->sendMessage(TextFormat::RN . "Player never seen!");
						return;
					}
					$sender->sendMessage(TextFormat::RN . "Attempting to load user inventory...");
					switch (Core::thisServer()->getType()) {
						case 'skyblock': {
								SkyBlock::getInstance()->getSessionManager()->useSession($user, function (SkyBlockSession $session) use ($sender, $user, $id): void {
									if (!isset(self::$runner[$id])) return;
									unset(self::$runner[$id]);
									if ($sender instanceof Player && $sender->isConnected()) {
										if (count($session->getSeeInv()?->getViewers() ?? []) < 1) $session->getSeeInv()?->pullFromPlayer();
										if ($session->getSeeInv()?->doOpen($sender)) $sender->sendMessage(TextFormat::GN . "Opened " . $user->getName() . "'s inventory");
										else $sender->sendMessage(TextFormat::RN . "Unknown error opening " . $user->getName() . "'s inventory");
									}
									return;
								});
								break;
							}
						case 'prison': {
								Prison::getInstance()->getSessionManager()->useSession($user, function (PrisonSession $session) use ($sender, $user, $id): void {
									if (!isset(self::$runner[$id])) return;
									unset(self::$runner[$id]);
									if ($sender instanceof Player && $sender->isConnected()) {
										if (count($session->getSeeInv()?->getViewers() ?? []) < 1) $session->getSeeInv()?->pullFromPlayer();
										if ($session->getSeeInv()?->doOpen($sender)) $sender->sendMessage(TextFormat::GN . "Opened " . $user->getName() . "'s inventory");
										else $sender->sendMessage(TextFormat::RN . "Unknown error opening " . $user->getName() . "'s inventory");
									}
									return;
								});
								break;
							}
						default: {
								Core::getInstance()->getSessionManager()->useSession($user, function (CoreSession $session) use ($sender, $user, $id): void {
									if (!isset(self::$runner[$id])) return;
									unset(self::$runner[$id]);
									if ($sender instanceof Player && $sender->isConnected()) {
										if (count($session->getSeeInv()?->getViewers() ?? []) < 1) $session->getSeeInv()?->pullFromPlayer();
										if ($session->getSeeInv()?->doOpen($sender)) $sender->sendMessage(TextFormat::GN . "Opened " . $user->getName() . "'s inventory");
										else $sender->sendMessage(TextFormat::RN . "Unknown error opening " . $user->getName() . "'s inventory");
									}
									return;
								});
								break;
							}
					}
				});
			} else {
				$sender->sendMessage(TextFormat::RN . "Cannot open inventories across servers! Please /stp");
			}
			return;
		}

		if (count($player->getSeeInv()?->getViewers() ?? []) < 1) $player->getSeeInv()?->pullFromPlayer();
		$player->getSeeInv()?->doOpen($sender);
		$sender->sendMessage(TextFormat::GN . "Opened " . $player->getName() . "'s inventory");
	}

	public function getPlugin(): Plugin {
		return $this->plugin;
	}
}
