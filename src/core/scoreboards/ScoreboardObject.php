<?php

namespace core\scoreboards;

use pocketmine\network\mcpe\protocol\{
	RemoveObjectivePacket,
	SetDisplayObjectivePacket,
	SetScorePacket,

	types\ScorePacketEntry
};
use pocketmine\utils\TextFormat;

use core\AtPlayer as Player;

class ScoreboardObject {

	const MAX_LINES = 15;

	const DEFAULT_NAME = "lmao";
	const DEFAULT_TITLE = TextFormat::BOLD . TextFormat::AQUA . "Avenge" . TextFormat::GOLD . "Tech";

	const DEFAULT_LINES = [
		1 => " ",
		4 => "  ",
		7 => "   ",
		9 => "    ",
		11 => "     ",
		12 => TextFormat::AQUA . "store.avengetech.net",
	];

	public bool $wasSent = false;

	public function __construct(
		public Player $player,
		public string $name = self::DEFAULT_NAME,
		public string $title = self::DEFAULT_TITLE
	) {}

	public function getName(): string {
		return $this->name;
	}

	public function getTitle(): string {
		return $this->title;
	}

	public function send(array $lines = []): void {
		$pk = new SetDisplayObjectivePacket();
		$pk->displaySlot = SetDisplayObjectivePacket::DISPLAY_SLOT_SIDEBAR;
		$pk->objectiveName = $this->getName();
		$pk->displayName = $this->getTitle();
		$pk->criteriaName = "dummy";
		$pk->sortOrder = SetDisplayObjectivePacket::SORT_ORDER_ASCENDING;
		try {
			$this->getPlayer()->getNetworkSession()->sendDataPacket($pk);
		} catch (\Exception $e) {}

		$this->update($lines);
	}

	public function getPlayer(): Player {
		return $this->player;
	}

	public function update(array $lines = []): void {
		if (!$this->getPlayer() instanceof Player) return;
		if (empty($lines)) return;
		ksort($lines);

		$entries = [];
		foreach ($lines as $num => $line) {
			$entry = new ScorePacketEntry();
			$entry->objectiveName = $this->getName();
			$entry->type = ScorePacketEntry::TYPE_FAKE_PLAYER;
			$entry->customName = $line;
			$entry->score = $num;
			$entry->scoreboardId = $num;

			$entries[] = $entry;
		}

		try {
			$pk = new SetScorePacket();
			$pk->type = SetScorePacket::TYPE_REMOVE; //you must call remove first
			$pk->entries = $entries;
			$this->getPlayer()->getNetworkSession()->sendDataPacket($pk);

			$pk2 = new SetScorePacket();
			$pk2->type = SetScorePacket::TYPE_CHANGE;
			$pk2->entries = $entries;
			$this->getPlayer()->getNetworkSession()->sendDataPacket($pk2);
		} catch (\Exception $e) {}
	}

	public function remove(): void {
		$pk = new RemoveObjectivePacket();
		$pk->objectiveName = $this->getName();
		try {
			$this->getPlayer()->getNetworkSession()->sendDataPacket($pk);
		} catch (\Exception $e) {}
	}
}
