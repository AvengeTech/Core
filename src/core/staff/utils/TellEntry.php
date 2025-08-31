<?php

namespace core\staff\utils;

class TellEntry {

	public $data = [];

	public $to = "";
	public $message = "";

	public $time = 0;

	public function __construct(array $data) {
		$this->data = $data;

		$this->to = $data["to"] ?? "fred";
		$this->message = $data["message"] ?? "";

		$this->time = $data["time"] ?? 0;
	}

	public function getData(): array {
		return $this->data;
	}

	public function getTo(): string {
		return $this->to;
	}

	public function getMessage(): string {
		return $this->message;
	}

	public function getTime(): int {
		return $this->time;
	}

	public function getFormattedTime(): string {
		return date("m/d/Y H:i", $this->getTime());
	}
}
