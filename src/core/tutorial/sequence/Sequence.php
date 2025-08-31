<?php

namespace core\tutorial\sequence;

use pocketmine\entity\Location;

use core\AtPlayer as Player;
use core\staff\anticheat\session\SessionManager;
use core\utils\TextFormat;

class Sequence {

	public int $ticks = 0;

	public function __construct(
		public string $name,
		public int $length,
		public Location $location,
		public string $message = "",
		public string $title = "",
		public string $subTitle = "",
		public array $titleLengths = []
	) {
	}

	public function tick(): bool {
		$this->ticks++;
		return $this->ticks >= $this->getLength();
	}

	public function getName(): string {
		return $this->name;
	}

	public function getLength(): int {
		return $this->length;
	}

	public function getLocation(): Location {
		return $this->location;
	}

	public function getMessage(): string {
		return $this->message;
	}

	public function getMessageFormat(): string {
		return TextFormat::EMOJI_TECHIE . TextFormat::BOLD . TextFormat::YELLOW . " Techie: " . TextFormat::RESET . TextFormat::YELLOW;
	}

	public function getTitle(): string {
		return $this->title;
	}

	public function getSubTitle(): string {
		return $this->subTitle;
	}

	public function getTitleLengths(): array {
		return $this->titleLengths;
	}

	public function start(Player $player): void {
		$player->teleport($this->getLocation());
		if ($this->getMessage() !== "")
			$player->sendMessage($this->getMessageFormat() . $this->getMessage());
		if ($this->getTitle() !== "" || $this->getSubTitle() !== "")
			$player->sendTitle(
				$this->getTitle(),
				$this->getSubTitle(),
				$this->getTitleLengths()[0] ?? 10,
				$this->getTitleLengths()[1] ?? 20,
				$this->getTitleLengths()[2] ?? 10
			);
	}

	public function end(Player $player): void {
	}
}
