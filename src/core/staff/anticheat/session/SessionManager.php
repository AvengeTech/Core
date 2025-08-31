<?php

namespace core\staff\anticheat\session;

use pocketmine\player\Player;

class SessionManager {

	public static array $sessions = [];

	private static self $i;

	public function __construct() {
		self::$i = $this;
	}
	public static function fetch(): self {
		return self::$i;
	}

	public function getSessionFor(Player $player): ?Session {
		return isset(self::$sessions[$player->getXuid()]) ? self::$sessions[$player->getXuid()] : null;
	}

	public function unregisterFor(Player $player) {
		if (is_null($this->getSessionFor($player))) return;
		unset(self::$sessions[$player->getXuid()]);
	}

	private function _registerSession(Player $player): Session {
		return (self::$sessions[$player->getXuid()] = ($this->getSessionFor($player) ?? new Session($player, $this)));
	}

	public static function registerSessionFor(Player $player): Session {
		return self::fetch()->_registerSession($player);
	}
}
