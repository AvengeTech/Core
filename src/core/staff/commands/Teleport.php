<?php

namespace core\staff\commands;

use core\command\type\CoreCommand;
use core\rank\Rank;
use pocketmine\Server;
use pocketmine\command\CommandSender;
use pocketmine\plugin\Plugin;

use core\Core;
use core\AtPlayer as Player;
use core\network\protocol\PlayerLoadActionPacket;
use core\staff\anticheat\session\SessionManager;
use core\staff\uis\actions\TeleportUi;
use core\utils\TextFormat;
use skyblock\SkyBlock;
use skyblock\SkyBlockPlayer;
use skyblock\SkyBlockSession;

class Teleport extends CoreCommand {

	public function __construct(public Core $plugin, string $name, string $description) {
		parent::__construct($name, $description);
		$this->setHierarchy(Rank::HIERARCHY_STAFF);
		$this->setInGameOnly();
	}

	public function handlePlayer(Player $sender, string $commandLabel, array $args) {
		if (count($args) < 1) {
			$sender->showModal(new TeleportUi($sender));
			return;
		}

		$random = false;
		$name = array_shift($args);
		if ($name == "@r") {
			$players = Server::getInstance()->getOnlinePlayers();
			$player = $players[array_rand($players)];
			$random = true;
		} else {
			$player = Server::getInstance()->getPlayerByPrefix($name);
		}
		if (!$player instanceof Player) {
			$players = [];
			foreach (Core::thisServer()->getSubServers(false, true) as $sub) {
				foreach ($sub->getCluster()->getPlayers() as $pl) {
					$players[$pl->getGamertag()] = $sub->getId();
				}
			}

			$found = null;
			$server = null;
			$name = strtolower($name);
			$delta = PHP_INT_MAX;
			foreach ($players as $pl => $serv) {
				if (stripos($pl, $name) === 0) {
					$curDelta = strlen($pl) - strlen($name);
					if ($curDelta < $delta) {
						$found = $pl;
						$server = $serv;
						$delta = $curDelta;
					}
					if ($curDelta === 0) {
						break;
					}
				}
			}

			if ($found !== null) {
				(new PlayerLoadActionPacket([
					"player" => $sender->getName(),
					"server" => $server,
					"action" => "teleport",
					"actionData" => ["player" => $found]
				]))->queue();

				$server = Core::getInstance()->getNetwork()->getServerManager()->getServerById($server);

				if ($sender instanceof SkyBlockPlayer) {
					$sender->getGameSession()?->getCombat()->getCombatMode()?->reset(false);
					SkyBlock::getInstance()->onQuit($sender, true);
					if ($sender->isLoaded()) {
						$sender->getGameSession()->save(true, function (SkyBlockSession $session) use ($sender, $found, $server): void {
							$session->getCombat()->getCombatMode()?->reset(false);
							if ($sender->isConnected()) {
								$server->transfer($sender, TextFormat::GN . "Teleported to " . TextFormat::YELLOW . $found);
								$server->sendSessionSavedPacket($sender, 1);
							}
							$sender->getGameSession()->getSessionManager()->removeSession($sender);
						});
					}
					$sender->sendMessage(TextFormat::YELLOW . "Saving game session data...");
					return;
				} else {
					$server->transfer($sender, TextFormat::GN . "Teleported to " . TextFormat::YELLOW . $found);
				}


				//todo: process save + transfer properly

				$sender->sendMessage(TextFormat::RN . "Player $found found on subserver! Teleporting to...");

				//Core::getInstance()->getNetwork()->getServerManager()->getServerById($serv)->transfer($sender);
				return;
			}


			$sender->sendMessage(TextFormat::RN . "Player not found!");
			return;
		}

		$sender->teleport($player->getLocation());
		$player->teleportProcess($sender);
		$sender->sendMessage(TextFormat::GN . "Teleported to " . TextFormat::YELLOW . $player->getName() . TextFormat::GRAY . ($random ? " (random)" : ""));
	}

	public function getPlugin(): Plugin {
		return $this->plugin;
	}
}
