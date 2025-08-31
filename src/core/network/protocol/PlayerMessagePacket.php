<?php

namespace core\network\protocol;

use pocketmine\Server;

use core\{
	Core,
	AtPlayer as Player
};
use core\discord\objects\{
	Post,
	Webhook
};
use core\settings\GlobalSettings;
use core\utils\TextFormat;

class PlayerMessagePacket extends ConnectPacket {

	const PACKET_ID = self::PLAYER_MESSAGE;

	public function verifyHandle(): bool {
		$data = $this->getPacketData();
		return isset($data["from"], $data["to"], $data["message"]);
	}

	public function handle(ConnectPacketHandler $handler): void {
		$data = $this->getPacketData();
		$from = $data["from"];
		$to = $data["to"];
		$message = $data["message"];

		$to = Server::getInstance()->getPlayerExact($to);
		if ($to instanceof Player) {
			if (($to->isLoaded() && $to->getSession()->getSettings()->getSetting(GlobalSettings::OPEN_DMS)) || $from === "ERROR") {
				$to->sendMessage(TextFormat::YELLOW . "[" . TextFormat::RED . $from . TextFormat::GRAY . " -> " . TextFormat::GREEN . $to->getName() . TextFormat::YELLOW . "] " . TextFormat::GRAY . $message);
				if ($from !== "ERROR") $to->setLastMessaged($from);
			} else {
				$pk = new PlayerMessagePacket([
					"to" => $from,
					"from" => "ERROR",
					"message" => $data["to"] . " has their direct messages closed!"
				]);
				$pk->queue();
			}
		}
	}

	public function verifyResponse(): bool {
		$response = $this->getResponseData();
		return isset($response["error"], $response["message"]);
	}

	public function handleResponse(ConnectPacketHandler $handler): void {
		$response = $this->getResponseData();
		$data = $this->getPacketData();
		$from = $data["from"];
		$to = $data["to"];
		$message = $data["message"];

		$from = Server::getInstance()->getPlayerExact($from);
		if ($from instanceof Player) {
			if ($response["error"]) {
				$message = $response["message"];
				$from->sendMessage(TextFormat::RI . $message);
			} else {
				$from->sendMessage($fm = TextFormat::YELLOW . "[" . TextFormat::GREEN . $from->getName() . TextFormat::GRAY . " -> " . TextFormat::RED . $to . TextFormat::YELLOW . "] " . TextFormat::GRAY . $message);
				$from->setLastMessaged($to);

				$post = new Post(TextFormat::clean($fm), "Tell Log - " . Core::getInstance()->getNetwork()->getIdentifier(), "[REDACTED]", false, "");
				$post->setWebhook(Webhook::getWebhookByName("tell-log"));
				$post->send();
			}
		}
	}
}
