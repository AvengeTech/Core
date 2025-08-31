<?php

namespace core\network\server\ui;

use core\{
	Core,
	AtPlayer as Player
};
use core\chat\Chat;
use core\network\Structure;
use core\ui\elements\simpleForm\Button;
use core\ui\windows\SimpleForm;
use core\utils\TextFormat;

class JoinQueueUi extends SimpleForm {

	public array $servers = [];

	public function __construct(Player $player, string $serverType, public bool $back = false) {
		parent::__construct(Structure::TYPE_TO_CASE[$serverType], Chat::convertWithEmojis(Structure::GAME_DESCRIPTIONS[$serverType] ?? "no game description"));

		$servers = Core::getInstance()->getNetwork()->getServerManager()->getServersByType($serverType);
		foreach ($servers as $key => $server) {
			if (
				$server->isSubServer() || ($server->isPrivate() && (!$server->isRestricted() ||
						!$server->onWhitelist($player) ||
						$server->getRestricted() > $player->getRankHierarchy()
					) && !$player->isStaff()
				)
			) {
				unset($servers[$key]);
			}
		}
		$this->servers = array_values($servers);
		foreach ($this->servers as $server) {
			$players = $server->getPlayerCount();
			foreach ($server->getSubServers(false) as $sub) {
				$players += count($sub->getCluster()->getPlayers());
			}
			$queue = $serverType == "lobby" ? $server->getQueue() : $server->getFullQueue();
			$this->addButton(new Button($server->getId() . " " . ($server->isOnline() ? TextFormat::DARK_GREEN . "[" . $players . " online" : TextFormat::DARK_RED . "[Offline") . "]" . PHP_EOL . (Core::thisServer()->getIdentifier() == $server->getIdentifier() ? TextFormat::AQUA . "You are here" : ($queue->hasPlayer($player) ? TextFormat::RED . "Leave queue" : TextFormat::GREEN . "Join queue"))));
		}

		if ($back) {
			$this->addButton(new Button("Go back"));
		}
	}

	public function handle($response, Player $player) {
		$server = $this->servers[$response] ?? null;
		if ($server === null) {
			if ($this->back) {
				$player->showModal(new SelectQueueUi($player));
			}
			return;
		}
		if ($server->getIdentifier() == Core::thisServer()->getIdentifier()) {
			$player->sendMessage(TextFormat::RI . "You are already on " . TextFormat::YELLOW . $server->getIdentifier());
			return;
		}

		if ($server->isRestricted() && $player->getRankHierarchy() < $server->getRestricted() && !$server->onWhitelist($player)) {
			$player->sendMessage(TextFormat::RI . "This server is restricted to " . $server->getRestrictedRank() . " rank!");
			return;
		}
		if ($player->getSession()->getRank()->hasSub()) {
			if ($server->isFull()) {
				$player->sendMessage(TextFormat::YI . "Wow this server is popular. Try to join again later!");
				return;
			}
			$server->transfer($player, TextFormat::GI . "Successfully connected to " . TextFormat::YELLOW . $server->getIdentifier());
		}
		$queue = $server->getType() == "lobby" ? $server->getQueue() : $server->getFullQueue();
		if ($queue->hasPlayer($player)) {
			$queue->removePlayer($player);
			$player->sendMessage(TextFormat::RI . "You left the " . TextFormat::YELLOW . $server->getIdentifier() . TextFormat::GRAY . " queue");
		} else {
			$queue->addPlayer($player);
			$player->sendMessage(TextFormat::GI . "You are now queued to join " . TextFormat::YELLOW . $server->getIdentifier());
		}
	}
}
