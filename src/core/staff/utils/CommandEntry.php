<?php

namespace core\staff\utils;

class CommandEntry {

	public $data = [];

	public $commandStr = "";
	public $time = 0;

	public $base = "";
	public $args = [];

	public function __construct(array $data) {
		$this->data = $data;

		$this->commandStr = $data["command"] ?? "/ipsum latin";
		$this->time = $data["time"] ?? 0;

		$break = explode(" ", $data["command"]);
		$this->base = array_shift($break);
		$this->args = $break;
	}

	public function getData(): array {
		return $this->data;
	}

	public function getRawCommand(): string {
		return $this->commandStr;
	}

	public function getTime(): int {
		return $this->time;
	}

	public function getFormattedTime(): string {
		return date("m/d/Y H:i", $this->getTime());
	}

	public function getBaseCommand(): string {
		return $this->base;
	}

	public function getArguments(): array {
		return $this->args;
	}

	public function getFormattedCommand(array $colors = []): string {
		return "/" . $this->getBaseCommand() . " " . implode(" ", $this->getArguments());
	}
}
