<?php

namespace core\staff\entry;

use core\Core;
use core\user\User;

class Response {

	public $player;
	public $message;

	public function __construct($player, string $message = "") {
		$this->player = new User($player);
		$this->message = $message;
	}

	public function getPlayer(): User {
		return $this->player;
	}

	public function getMessage(): string {
		return $this->message;
	}

	public function __toString(): string {
		return $this->getPlayer()->getXuid() . "|" . $this->getMessage();
	}
}
