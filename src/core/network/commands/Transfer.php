<?php

namespace core\network\commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\plugin\Plugin;

use core\{
	Core,
	AtPlayer as Player
};
use core\command\type\CoreCommand;
use core\network\server\ui\SelectQueueUi;
use core\rank\Structure as RS;
use core\utils\TextFormat;

class Transfer extends CoreCommand {

	public function __construct(public Core $plugin, string $name, string $description) {
		parent::__construct($name, $description);
		$this->setInGameOnly();
		$this->setAliases(["goto", "move"]);
	}

	public function handlePlayer(Player $sender, string $commandLabel, array $args)
	{
		$network = Core::getInstance()->getNetwork();
		$manager = $network->getServerManager();
		if (count($args) < 1) {
			//$sender->showModal(new SelectQueueUi($sender));
			return;
		}

		$identifier = strtolower(array_shift($args));
		if (($server = $manager->getServerById($identifier)) === null) {
			$sender->sendMessage(TextFormat::RI . "Invalid server ID provided.");
			return;
		}

		if (Core::thisServer()->getIdentifier() == $server->getIdentifier()) {
			$sender->sendMessage(TextFormat::RI . "You are already on this server!");
			return;
		}

		if ($server->isSubServer() && !$sender->isTier3()) {
			$sender->sendMessage(TextFormat::RI . "You cannot directly transfer to this server.");
			return;
		}
		if ($server->isRestricted() && $server->getRestricted() > RS::RANK_HIERARCHY[$sender->getRank()] && !$server->onWhitelist($sender)) {
			$sender->sendMessage(TextFormat::RI . "This server is restricted! You cannot access it without " . $server->restricted . " rank or higher!");
			return;
		}
		if (!$server->canTransfer($sender)) {
			$sender->sendMessage(TextFormat::RI . "You do not have access to this server. It is either offline or private. Please try again later!");
			return;
		}
		if ($sender->isTransferring()) {
			$sender->sendMessage(TextFormat::RI . "You are already connecting to a server!");
			return;
		}

		if ($sender->isStaff()) {
			$server->transfer($sender, TextFormat::GI . "Connected to " . TextFormat::AQUA . $identifier . TextFormat::GRAY . "!");
		} else {
			($queue = $server->getFullQueue() ?? $server->getQueue());
			if ($queue->hasPlayer($sender)) {
				$queue->removePlayer($sender);
				$sender->sendMessage(TextFormat::RI . "You left the " . TextFormat::YELLOW . $server->getIdentifier() . TextFormat::GRAY . " queue.");
			} else {
				$queue->addPlayer($sender);
				$sender->sendMessage(TextFormat::RI . "You are now queued to join " . TextFormat::YELLOW . $server->getIdentifier());
			}
		}
	}

	public function getPlugin(): Plugin {
		return $this->plugin;
	}
}
