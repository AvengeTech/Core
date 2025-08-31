<?php

namespace core\staff\uis\actions;

use pocketmine\Server;

use core\Core;
use core\AtPlayer as Player;
use core\network\protocol\PlayerLoadActionPacket;
use core\staff\anticheat\session\SessionManager;
use core\ui\windows\CustomForm;
use core\ui\elements\customForm\{
	Label,
	Dropdown
};
use core\utils\TextFormat;

use skyblock\SkyBlock;
use skyblock\SkyBlockPlayer;

class TeleportUi extends CustomForm {

	public $players = [];
	public $p2s = [];

	public function __construct(Player $player, string $error = "") {
		parent::__construct("Teleport to Player");

		$this->addElement(new Label($error == "" ? "" : TextFormat::RED . "Error: " . $error));
		$players = [];
		foreach(Core::thisServer()->getSubServers(true, true) as $serv){
			foreach($serv->getCluster()->getPlayers() as $pl){
				if(!in_array($pl->getGamertag(), $players)){
					$players[] = $pl->getGamertag();
					$this->p2s[$pl->getGamertag()] = $serv;
				}
			}
		}
		$this->players = $players;
		$this->addElement(new Dropdown("Players online", $players));
	}

	public function handle($response, Player $player) {
		$pl = $this->players[$response[1]] ?? "";

		$teleport = Server::getInstance()->getPlayerByPrefix($pl);
		if (!$teleport instanceof Player) {
			$serv = $this->p2s[$pl];
			if($serv !== Core::thisServer()){
				(new PlayerLoadActionPacket([
					"player" => $player->getName(),
					"server" => $serv->getId(),
					"action" => "teleport",
					"actionData" => ["player" => $pl]
				]))->queue();

				if($player instanceof SkyBlockPlayer){
					SkyBlock::getInstance()->onQuit($player, true);
					if($player->isLoaded()){
						$player->getGameSession()->save(true, function($session) use ($serv, $pl, $player) : void{
							if($player->isConnected()){
								$serv->transfer($player, TextFormat::GN . "Teleported to " . TextFormat::YELLOW . $pl);
								$serv->sendSessionSavedPacket($player, 1);
							}
							$player->getGameSession()->getSessionManager()->removeSession($player);
						});
					}
					$player->sendMessage(TextFormat::YELLOW . "Saving game session data...");
					return;
				}else{
					$serv->transfer($player, TextFormat::GN . "Teleported to " . TextFormat::YELLOW . $pl);
				}

			}
			$player->showModal(new TeleportUi($player, "Player by username '" . $pl . "' no longer online."));
			return;
		}

		$player->teleport($teleport->getLocation());
		$player->sendMessage(TextFormat::GN . "Teleported to " . $teleport->getName());
	}
}
