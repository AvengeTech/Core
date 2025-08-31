<?php

namespace core\staff\tasks;

use pocketmine\scheduler\Task;
use pocketmine\utils\TextFormat;

use core\AtPlayer as Player;
use core\Core;
use core\staff\entry\{
	BanEntry,
	IPBanEntry,
	DeviceBanEntry
};
use core\user\User;
use pocketmine\promise\Promise;

class BanCheckDelayedTask extends Task {

	public function __construct(private Player $player) {
	}

	public function onRun(): void {
		$player = $this->player;
		if ($player->isConnected()) {
			Core::getInstance()->getStaff()->loadBans($player, function (BanEntry $ban): void {
				$by = $ban->getByUser();
				if ($by instanceof Promise) $by->onCompletion(fn(User $byUser) => $this->execute($ban, $byUser), fn() => null);
				else $this->execute($ban, $by);
			});
		}
	}

	public function execute(BanEntry $ban, User $by): void {
		if (!$this->player->isConnected()) return;
		$this->player->kick(
			TextFormat::RED . "You were banned by " . TextFormat::YELLOW . $by->getGamertag() . PHP_EOL .
				TextFormat::RED . "Reason: " . TextFormat::YELLOW . "'" . $ban->getReason() . "'" . PHP_EOL .
				TextFormat::RED . "Length: " . TextFormat::YELLOW . $ban->getFormattedUntil() . PHP_EOL .
				TextFormat::RED . "Appeal for an unban at " . TextFormat::YELLOW . "avengetech.net/discord",
			false
		);
	}
}
