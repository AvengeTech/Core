<?php

namespace core\staff\commands;

use core\command\type\CoreCommand;
use core\rank\Rank;
use pocketmine\Server;
use pocketmine\command\CommandSender;
use pocketmine\plugin\Plugin;

use core\AtPlayer as Player;
use core\Core;
use core\utils\TextFormat;

class SeeCommand extends CoreCommand {

	public function __construct(public Core $plugin, string $name, string $description) {
		parent::__construct($name, $description);
		$this->setHierarchy(Rank::HIERARCHY_STAFF);
		$this->setInGameOnly();
	}

	public function handlePlayer(Player $sender, string $commandLabel, array $args)
	{
		$staff = Core::getInstance()->getStaff();
		if (count($args) == 0) {
			$sender->sendMessage(TextFormat::RI . "Usage: /seecommand <off:false:stop> OR /seecommand <all:true:on> OR /seecommand <player1> [player2] etc");
			return;
		}
		$session = $sender->getSession()->getStaff();
		if (in_array($args[0], ["off", "false", "stop"])) {
			$c = 0;
			foreach (Server::getInstance()->getOnlinePlayers() as $pl) {
				/** @var Player $pl */
				$session = $pl->getSession();
				if ($session !== null && $session->getStaff()->getWatchlist()->removeViewer($sender->getName()))
					$c++;
			}
			if ($staff->canSeeAll($sender->getName())) {
				$staff->toggleSeeAll($sender->getName());
			}
			$sender->sendMessage(TextFormat::GI . "Toggled off all command seeing (" . TextFormat::AQUA . $c . TextFormat::GRAY . " total)");
			return;
		} elseif (in_array($args[0], ["all", "on", "true"])) {
			foreach (Server::getInstance()->getOnlinePlayers() as $pl) {
				/** @var Player $pl */
				$pl->getSession()?->getStaff()->getWatchlist()->removeViewer($sender->getName());
			}


			$toggle = $staff->toggleSeeAll($sender->getName());

			$sender->sendMessage(TextFormat::GI . "Command seeing of all players will " . ($toggle ? "now " : "no longer ") . "be shown!");
			return;
		}

		$changed = [];
		$failed = 0;
		$message = "";
		foreach ($args as $name) {
			$player = Core::getInstance()->getServer()->getPlayerExact($name);
			if ($player instanceof Player) {
				$session = $player->getSession();
				if ($session !== null) {
					$wl = $session->getStaff()->getWatchlist();
					$toggle = true;
					if (!$wl->addViewer($sender->getName())) {
						$wl->removeViewer($sender->getName());
						$toggle = false;
					}
					$changed[$player->getName()] = $toggle;
					$message .= ($toggle ? TextFormat::GREEN : TextFormat::RED) . $player->getName() . TextFormat::GRAY . ", ";
				}
				continue;
			}
			$failed++;
		}
		$sender->sendMessage(TextFormat::GI . "Successfully toggled command seeing for the following players: " . $message . "[" . TextFormat::RED . $failed . " failed" . TextFormat::GRAY . "]");
	}

	public function getPlugin(): Plugin {
		return $this->plugin;
	}
}
