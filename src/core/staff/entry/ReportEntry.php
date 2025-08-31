<?php

namespace core\staff\entry;

use core\Core;
use core\user\User;

class ReportEntry {

	public $id;

	public $player;
	public $reported;

	public $when;
	public $reason;

	public $images = [];
	public $responses = [];

	public $open = true;

	public function __construct(int $id, $player, $reported, int $when, string $reason = "", array $images = [], array $responses = [], $open = true) {
		$this->id = $id;

		$this->player = new User($player);
		$this->reported = new User($reported);

		$this->when = $when;
		$this->reason = $reason;

		$this->images = $images;

		$this->responses = $responses;

		$this->open = $open;
	}

	public function getId(): int {
		return $this->id;
	}

	public function getPlayer(): User {
		return $this->player;
	}

	public function getReported(): User {
		return $this->reported;
	}

	public function getWhen(): int {
		return $this->when;
	}

	public function getReason(): string {
		return $this->reason;
	}

	public function getImages(): array {
		return $this->images;
	}

	public function addImage(string $image): void {
		$this->images[] = $image;
	}

	public function getResponses(): array {
		return $this->responses;
	}

	public function addResponse(Response $response): void {
		$this->responses[] = $response;
	}

	public function getResponseString(): string {
		$string = "";
		foreach ($this->getResponses() as $response) {
			$string .= $response . ";";
		}
		return $string;
	}

	public function isOpen(): bool {
		return $this->open;
	}

	public function save(): void {
	}
}
