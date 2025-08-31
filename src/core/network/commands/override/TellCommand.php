<?php

namespace core\network\commands\override;

use pocketmine\Server;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\plugin\Plugin;

use core\{
	Core,
	AtPlayer as Player
};
use core\chat\Chat;
use core\command\type\CoreCommand;
use core\discord\objects\{
	Post,
	Webhook
};
use core\network\protocol\PlayerMessagePacket;
use core\settings\GlobalSettings;
use core\utils\TextFormat;

class TellCommand extends CoreCommand {

	public function __construct(public Core $plugin, string $name, string $description) {
		parent::__construct($name, $description);
		$this->setAliases(["tell", "w", "msg"]);
	}

	public function handle(CommandSender $sender, string $label, array $args): void {
		if (count($args) < 2) {
			$sender->sendMessage(TextFormat::RI . "Usage: /" . $label . " <player> <message>");
			return;
		}
		$sm = Core::getInstance()->getNetwork()->getServerManager();
		$targetname = array_shift($args);
		$t = Server::getInstance()->getPlayerByPrefix($targetname);
		$message = implode(" ", $args);
		if (!$sender instanceof Player || $sender->hasRank()) $message = Chat::convertWithEmojis($message);
		if ($t instanceof Player) {
			if ($t->isStaff() && $t->isVanished() && ($sender instanceof Player && !$sender->isStaff())) {
				$sender->sendMessage(TextFormat::RI . "Player not online!");
				return;
			}
			if ($t === $sender) {
				$sender->sendMessage(TextFormat::RI . "You cannot send messages to yourself!");
				return;
			}
			if (!$t->isLoaded() || !$t->getSession()->getSettings()->getSetting(GlobalSettings::OPEN_DMS)) {
				$sender->sendMessage(TextFormat::RI . "This player has their direct messages closed!");
				return;
			}
			if ($sender instanceof Player) {
				$t->setLastMessaged($sender);
				$sender->setLastMessaged($t);

				$session = $sender->getSession()->getStaff();
				$session->getWatchList()->addMessage($t, $message);
			}

			$t->sendMessage($fm = TextFormat::YELLOW . "[" . TextFormat::RED . $sender->getName() . TextFormat::GRAY . " -> " . TextFormat::GREEN . $t->getName() . TextFormat::YELLOW . "] " . TextFormat::GRAY . $message);
			$sender->sendMessage(TextFormat::YELLOW . "[" . TextFormat::GREEN . $sender->getName() . TextFormat::GRAY . " -> " . TextFormat::RED . $t->getName() . TextFormat::YELLOW . "] " . TextFormat::GRAY . $message);

			$post = new Post(TextFormat::clean($fm), "Tell Log - " . Core::getInstance()->getNetwork()->getIdentifier(), "[REDACTED]", false, "");
			$post->setWebhook(Webhook::getWebhookByName("tell-log"));
			$post->send();
			return;
		}

		$players = [];
		foreach(Core::getInstance()->getNetwork()->getServerManager()->getServers() as $server){
			foreach($server->getCluster()->getPlayers() as $pl){
				$players[$pl->getGamertag()] = $server->getId();
			}
		}

		$found = null;
		$server = null;
		$name = strtolower($targetname);
		$delta = PHP_INT_MAX;
		foreach($players as $pl => $serv){
			if(stripos($pl, $name) === 0){
				$curDelta = strlen($pl) - strlen($name);
				if($curDelta < $delta){
					$found = $pl;
					$server = $serv;
					$delta = $curDelta;
				}
				if($curDelta === 0){
					break;
				}
			}
		}

		if(!$found){
			$sender->sendMessage(TextFormat::RI . "Player not online!");
			return;
		}

		$handler = $sm->getPacketHandler();
		$pk = new PlayerMessagePacket([
			"from" => $sender->getName(),
			"to" => $found,
			"message" => $message
		]);
		$pk->queue();
	}

	public function getPlugin(): Plugin {
		return $this->plugin;
	}
}
