<?php

namespace core\network\server;

use pocketmine\Server;

use core\AtPlayer as Player;

class PendingConnection {

	public int $created;

	public function __construct(
		public string $name,
		public string $from,
		public string $message = ""
	) {
		$this->created = time();
	}

	public function getPlayer(): ?Player {
		return Server::getInstance()->getPlayerExact($this->getName());
	}

	public function getName(): string {
		return $this->name;
	}

	public function getFrom(): string {
		return $this->from;
	}

	public function getMessage(): string {
		return $this->message;
	}

	public function getCreated(): int {
		return $this->created;
	}
}
