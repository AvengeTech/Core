<?php

namespace core\discord;

use core\AtPlayer as Player;
use core\discord\objects\{
	Post
};
use core\Core;

class ChatQueue {

	public int $queueSpeed = 15;
	public int $ticks = 0;

	public array $messages = [];

	public function tick(): void {
		$this->ticks++;
		if ($this->ticks % $this->getQueueSpeed() == 0) {
			$messages = $this->getNextMessages();

			foreach ($messages as $message) {
				$name = $message["username"];
				$nick = $message["nick"];
				$text = str_replace("@here", "\here", $message["message"]);
				$verified = ($snowflake = $message["snowflake"]) !== 0;
				$staff = $message["staff"];

				$ts = Core::getInstance()->getNetwork()->getServerManager()->getThisServer();
				$post = new Post(
					(($verified && !$staff) ? "<@" . $snowflake . "> (" . $name . ")" : $name) . ($nick !== "" ? " [*" . $nick . "]" : "") . ": " . $text,
					$name . " | " . ($verified ? "VERIFIED" : "UNVERIFIED") . ($ts->isSubServer() ? " | " . $ts->getId() : ""),
					"[REDACTED]",
				);
				$post->setWebhook(Core::getInstance()->getDiscord()->getChatWebhook());
				$post->send();
			}
		}
	}

	public function getQueueSpeed(): int {
		return $this->queueSpeed;
	}

	public function setQueueSpeed(int $speed): void {
		$this->queueSpeed = $speed;
	}

	public function addMessage(Player $player, string $message) {
		$session = $player->getSession()->getDiscord();
		$this->messages[] = [
			"username" => $player->getName(),
			"nick" => $player->getSession()->getRank()->getNick(),
			"snowflake" => $session->getSnowflake(),
			"staff" => $player->isStaff(),
			"message" => $message,
		];
	}

	public function getNextMessages(int $count = 1): array {
		$messages = [];
		while (!empty($this->messages) && $count > 0) {
			$messages[] = array_shift($this->messages);
		}
		return $messages;
	}
}
