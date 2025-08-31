<?php

namespace core\network\commands;

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

class Reply extends CoreCommand {

	public function __construct(public Core $plugin, string $name, string $description) {
		parent::__construct($name, $description);
		$this->setAliases(["r", "re"]);
		$this->setInGameOnly();
	}

	public function handlePlayer(Player $sender, string $commandLabel, array $args)
	{
		$sm = Core::getInstance()->getNetwork()->getServerManager();
		$t = $sender->getLastMessaged();
		$message = implode(" ", $args);
		if (!$sender instanceof Player || $sender->hasRank()) $message = Chat::convertWithEmojis($message);
		if (!$t instanceof Player) {
			$t = $sender->getLastMessagedName();
			if ($t == "") {
				$sender->sendMessage(TextFormat::RI . "No one to reply to!");
				return;
			}

			if (!$sm->isPlayerOnline($t)) {
				$sender->sendMessage(TextFormat::RI . "Player not online!");
				return;
			}

			$handler = $sm->getPacketHandler();
			$pk = new PlayerMessagePacket([
				"from" => $sender->getName(),
				"to" => $t,
				"message" => $message
			]);
			$handler->queuePacket($pk);
			return;
		}
		if (!$t->isLoaded() || !$t->getSession()->getSettings()->getSetting(GlobalSettings::OPEN_DMS)) {
			$sender->sendMessage(TextFormat::RI . "This player has their direct messages closed!");
			return;
		}
		$t->setLastMessaged($sender);
		$sender->setLastMessaged($t);
		$session = $sender->getSession()->getStaff();
		$session->getWatchList()->addMessage($t, $message);

		$t->sendMessage($fm = TextFormat::YELLOW . "[" . TextFormat::RED . $sender->getName() . TextFormat::GRAY . " -> " . TextFormat::GREEN . $t->getName() . TextFormat::YELLOW . "] " . TextFormat::GRAY . $message);
		$sender->sendMessage(TextFormat::YELLOW . "[" . TextFormat::GREEN . $sender->getName() . TextFormat::GRAY . " -> " . TextFormat::RED . $t->getName() . TextFormat::YELLOW . "] " . TextFormat::GRAY . $message);

		$post = new Post(TextFormat::clean($fm), "Tell Log - " . Core::getInstance()->getNetwork()->getIdentifier(), "[REDACTED]", false, "");
		$post->setWebhook(Webhook::getWebhookByName("tell-log"));
		$post->send();
	}

	public function getPlugin(): Plugin {
		return $this->plugin;
	}
}
