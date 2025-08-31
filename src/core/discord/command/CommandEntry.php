<?php

namespace core\discord\command;

use pocketmine\Server;

use core\Core;

class CommandEntry {

	public $snowflake;
	public $command;

	public $base;
	public $args = [];

	public function __construct(int $snowflake, string $command) {
		$this->snowflake = $snowflake;
		$this->command = $command;

		$ca = explode(" ", $command);
		$this->base = array_shift($ca);
		$this->args = $ca;
	}

	public function getSnowflake(): int {
		return $this->snowflake;
	}

	public function getCommand(): string {
		return $this->command;
	}

	public function getBase(): string {
		return $this->base;
	}

	public function getArgs(): array {
		return $this->args;
	}

	public function execute(): bool {
		$sender = Core::getInstance()->getDiscord()->getCommandManager()->getSender($this->getSnowflake());
		return Server::getInstance()->dispatchCommand($sender, $this->getCommand());
	}
}
