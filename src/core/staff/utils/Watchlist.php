<?php

namespace core\staff\utils;

use pocketmine\{
	player\Player,
	Server
};
use pocketmine\utils\TextFormat;

use core\Core;

class Watchlist {

	public $name = "";

	public $viewers = [];
	public $lastCommands = [];
	public $tellViewers = [];
	public $lastMessages = [];

	public function __construct(string $name) {
		$this->name = $name;
	}

	public function getName(): string {
		return $this->name;
	}

	public function getViewers(): array {
		$viewers = $this->viewers;
		$all = Core::getInstance()->getStaff()->getSeeAll();
		if (!empty($all))
			foreach ($all as $a)
				$viewers[] = $a;

		foreach ($viewers as $key => $viewer) {
			$player = Server::getInstance()->getPlayerExact($viewer);
			if ($player instanceof Player) {
				$viewers[$key] = $player;
			} else {
				unset($viewers[$key]);
			}
		}

		return $viewers;
	}

	public function addViewer(string $name): bool {
		if (!in_array($name, $this->viewers)) {
			$this->viewers[] = $name;
			return true;
		}
		return false;
	}

	public function removeViewer(string $name): bool {
		if (($key = array_search($name, $this->viewers)) !== false) {
			unset($this->viewers[$key]);
			return true;
		}
		return false;
	}

	public function hasAudience(): bool {
		return count($this->getViewers()) > 0;
	}

	public function getCommands(): array {
		return $this->lastCommands;
	}

	public function addCommand(string $command) {
		$this->lastCommands[] = new CommandEntry(["command" => $command, "time" => time()]);
	}

	public function getTellViewers(): array {
		$viewers = $this->tellViewers;
		$all = Core::getInstance()->getStaff()->getTellSeeAll();
		if (!empty($all))
			foreach ($all as $a)
				$viewers[] = $a;

		foreach ($viewers as $key => $viewer) {
			$player = Server::getInstance()->getPlayerExact($viewer);
			if ($player instanceof Player) {
				$viewers[$key] = $player;
			} else {
				unset($viewers[$key]);
			}
		}

		return $viewers;
	}

	public function addTellViewer(string $name): bool {
		if (!in_array($name, $this->tellViewers)) {
			$this->tellViewers[] = $name;
			return true;
		}
		return false;
	}

	public function removeTellViewer(string $name): bool {
		if (($key = array_search($name, $this->tellViewers)) !== false) {
			unset($this->tellViewers[$key]);
			return true;
		}
		return false;
	}

	public function hasTellAudience(): bool {
		return count($this->getTellViewers()) > 0;
	}

	public function getMessages(): array {
		return $this->lastMessages;
	}

	public function addMessage($to, string $message) {
		$this->lastMessages[] = new TellEntry(["to" => $to instanceof Player ? $to->getName() : $to, "message" => $message, "time" => time()]);
	}

	public function exhaust(): void {
		foreach ($this->getViewers() as $viewer) {
			foreach ($this->getCommands() as $entry) $viewer->sendMessage(TextFormat::BOLD . TextFormat::YELLOW . "[" . TextFormat::OBFUSCATED . "|||" . TextFormat::RESET . TextFormat::BOLD . TextFormat::YELLOW . "] " . TextFormat::RESET . TextFormat::AQUA . $this->getName() . ": " . TextFormat::ITALIC . TextFormat::GRAY . $entry->getFormattedCommand());
		}

		foreach ($this->getTellViewers() as $viewer) {
			foreach ($this->getMessages() as $entry) $viewer->sendMessage(TextFormat::BOLD . TextFormat::RED . "[" . TextFormat::OBFUSCATED . "|||" . TextFormat::RESET . TextFormat::BOLD . TextFormat::RED . "] " . TextFormat::RESET . TextFormat::YELLOW . "[" . TextFormat::RED . $this->getName() . TextFormat::GRAY . " -> " . TextFormat::GREEN . $entry->getTo() . TextFormat::YELLOW . "] " . TextFormat::GRAY . $entry->getMessage());
		}

		$this->lastCommands = [];
		$this->lastMessages = [];
	}
}
