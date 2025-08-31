<?php

namespace core\staff\utils;

use pocketmine\Server;
use pocketmine\world\Position;
use pocketmine\player\GameMode;

class PlayerDummy {

	public $seeInv;

	public function getName(): string {
		return "Dummy";
	}

	public function getGamemode(): GameMode {
		return GameMode::SURVIVAL();
	}

	public function asPosition(): Position {
		return new Position(0, 0, 0, Server::getInstance()->getWorldManager()->getDefaultWorld());
	}

	public function getPosition(): Position {
		return $this->asPosition();
	}

	public function isGliding(): bool {
		return false;
	}

	public function isStaff(): bool {
		return false;
	}

	public function getRank(): string {
		return "default";
	}

	public function getGenericFlag(int $flag): bool {
		return false;
	}

	public function isSprinting(): bool {
		return false;
	}

	public function isOnGround(): bool {
		return true;
	}

	public function getInAirTicks(): int {
		return 0;
	}

	public function isFlying(): bool {
		return false;
	}

	public function isVanished(): bool {
		return false;
	}

	public function getPing(): int {
		return 0;
	}

	public function kick(string $message = "", bool $bool = false): void {
	}
	public function sendPopup(string $message = ""): void {
	}
}
