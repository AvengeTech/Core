<?php

namespace core\network;

use pocketmine\event\Listener;
use pocketmine\event\player\{
	PlayerKickEvent,
	PlayerLoginEvent,
	PlayerQuitEvent,
};
use pocketmine\event\server\QueryRegenerateEvent;

use core\{
	Core,
	AtPlayer as Player,
	AtPlayer
};
use core\utils\{
	TextFormat,
    Utils,
};
use core\rank\Structure as RS;

class EventListener implements Listener {

	public function __construct(
		public Core $plugin,
		public Network $network
	) {
	}

	public function getNetwork(): Network {
		return $this->network;
	}

	public function onKick(PlayerKickEvent $e) {
		if ($e->getDisconnectReason() == "disconnectionScreen.serverFull") {
			$e->cancel();
		}
	}

	public function onJoin(Player $player) {
		$network = $this->getNetwork();
		$lobby = ($sm = $network->getServerManager())->getServerById("lobby-1");

		//if ($network->getServerType() == "lobby") {
			$player->sendTip(TextFormat::GREEN . "Loading...");
			if (($rc = $sm->getReconnect($player)) !== null) {
				$server = $sm->getServerById($rc->getIdentifier());
				if ($server->isOnline() && !$rc->isRestart()) {
					$server->delayedTransfer($player, TextFormat::GI . ($rc->isRestart() ? "The server you were on restarted... Luckily, you were transferred back!" : "Successfully reconnected to " . TextFormat::YELLOW . $server->getIdentifier()));
					$sm->removeReconnect($player);
				}
				return;
			}

			/**if (!$player->isFromProxy()) {
				$cf = $player->getConnectedFrom();
				$identifier = strtolower(explode(".", $cf)[0]);
				if ($identifier === "play") {
					return;
				}
				$arr = explode("-", $identifier);
				if (count($arr) === 1) {
					if (($server = $sm->getServerById($identifier . "-1")) === null) {
						$lobby->delayedTransfer($player, TextFormat::RI . "Invalid custom connection.");
						return;
					}
					if (!$server->canTransfer($player)) {
						$lobby->delayedTransfer($player, TextFormat::RI . "Unable to connect with this custom connection. Server is offline or private.");
						return;
					}
					$server->delayedTransfer($player, TextFormat::GI . "Custom connection successful! (" . TextFormat::AQUA . $server->getIdentifier() . TextFormat::GRAY . ")");
					return;
				}
				if (count($arr) === 2) {
					if (($server = $sm->getServerById($identifier)) === null) {
						$lobby->delayedTransfer($player, TextFormat::RI . "Invalid custom connection.");
						return;
					}
					if (!$server->canTransfer($player)) {
						$lobby->delayedTransfer($player, TextFormat::RI . "Unable to connect with this custom connection. Server is offline or private.");
						return;
					}
					$server->delayedTransfer($player, TextFormat::GI . "Custom connection successful! (" . TextFormat::AQUA . $server->getIdentifier() . TextFormat::GRAY . ")");
					return;
				}
			}*/
		//}

		$server = $sm->getThisServer();
		if (
			($server->isFull())
			&& !$server->isBeingSummoned($player)
		) {
			$lobby->delayedTransfer($player, TextFormat::RI . "The server you tried connecting to was full, so you were sent back to the lobby.");
			return;
		}

		if (
			!$server->onWhitelist($player) &&
			!$server->canTransfer($player, false) && !$server->isBeingSummoned($player)
		) {
			$lobby->delayedTransfer($player, TextFormat::RI . "The server you tried connecting to is private, so you were sent back to the lobby.");
			return;
		}

		if ($server->isBeingSummoned($player)) {
			$server->onSummon($player);
		}
	}

	/**
	 * @priority HIGHEST
	 */
	public function onQuit(PlayerQuitEvent $e) {
		$player = $e->getPlayer();
		$this->getNetwork()->getServerManager()->getThisServer()->onDisconnect($player);
	}

	public function onRegen(QueryRegenerateEvent $e) {
		$network = $this->getNetwork();
		$thisServer = Core::thisServer();
		if (in_array($network->getServerType(), ["lobby"])) {
			$players = [];
			foreach ($network->getServerManager()->getServers() as $ss) {
				if ($ss->isTestServer()) continue;
				foreach ($ss->getCluster()->getPlayers() as $spD) $players[] = $spD->getGamertag();
			}
			$e->getQueryInfo()->setPlayerCount(count($players));
			$e->getQueryInfo()->setMaxPlayerCount(count($players) + 1);
			$e->getQueryInfo()->setPlayerList($players);
		}
		if (in_array($network->getServerType(), ["skyblock", "prison", "pvp"]) && !$thisServer->isSubServer()) {
			$players = [];
			if (!$thisServer->isTestServer()) foreach ($thisServer->getCluster()->getPlayers() as $pD) $players[] = $pD->getGamertag();
			foreach ($thisServer->getSubServers(false) as $ss) {
				if ($ss->isTestServer()) continue;
				foreach ($ss->getCluster()->getPlayers() as $spD) $players[] = $spD->getGamertag();
			}
			$e->getQueryInfo()->setPlayerCount(count($players));
			$e->getQueryInfo()->setMaxPlayerCount(count($players) + 1);
			$e->getQueryInfo()->setPlayerList($players);
		}
		$e->getQueryInfo()->setListPlugins(false);
	}
}
