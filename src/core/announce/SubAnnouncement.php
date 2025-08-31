<?php

namespace core\announce;

class SubAnnouncement {

	public $title;
	public $bullets = [];

	public $start = 0;
	public $end = 999999999999;

	public function __construct(string $title, array $bullets, string $start = "", string $end = "") {
		$this->title = $title;
		$this->bullets = $bullets;

		if ($start !== "")
			$this->start = strtotime($start);
		if ($end !== "")
			$this->end = strtotime($end);
	}

	public function getTitle(): string {
		return $this->title;
	}

	public function getBullets(): array {
		return $this->bullets;
	}

	public function getStart(): int {
		return $this->start;
	}

	public function hasStarted(): bool {
		return $this->getStart() < time();
	}

	public function getEnd(): int {
		return $this->end;
	}

	public function hasEnded(): bool {
		return $this->getEnd() < time();
	}

	public function canDisplay(): bool {
		return $this->hasStarted() && !$this->hasEnded();
	}
}
