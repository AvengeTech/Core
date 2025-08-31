<?php

namespace core\friends;

use core\AtPlayer as Player;
use core\utils\TextFormat;

/**
 * @deprecated
 */
class FriendRequest {

	const TIMEOUT = 120;

	public int $created;

	public function __construct(
		public Player $from,
		public Player $to
	) {
		$this->created = time();
	}

	public function getFrom(): Player {
		return $this->from;
	}

	public function getTo(): Player {
		return $this->to;
	}

	public function getCreated(): int {
		return $this->created;
	}

	public function tick(): bool {
		return time() >= $this->getCreated() + self::TIMEOUT;
	}

	public function accept(): void {
		if (($from = $this->getFrom())->isConnected()) {
			$from->sendMessage(TextFormat::GI . $this->getTo()->getName() . " accepted your friend request!");
		}
	}

	public function deny(): void {
		if (($from = $this->getFrom())->isConnected()) {
			$from->sendMessage(TextFormat::GI . $this->getTo()->getName() . " denied your friend request!");
		}
	}

	public function timeout(): void {
		if (($from = $this->getFrom())->isConnected()) {
			$from->sendMessage(TextFormat::GI . "Your friend request to " . $this->getTo()->getName() . " timed out!");
		}
	}
}
