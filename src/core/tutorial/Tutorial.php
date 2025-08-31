<?php

namespace core\tutorial;

use core\AtPlayer as Player;
use core\tutorial\sequence\Sequence;

class Tutorial {

	public int $currentSequence = 0;

	public ?Player $player = null;

	public function __construct(
		public int $id,
		public string $name,
		public array $sequences = []
	) {
	}

	public function getId(): int {
		return $this->id;
	}

	public function getName(): string {
		return $this->name;
	}

	public function getSequences(): array {
		return $this->sequences;
	}

	public function getCurrentSequence(): ?Sequence {
		return $this->sequences[$this->getCurrentSequenceId()] ?? null;
	}

	public function hasNextSequence(): bool {
		return isset($this->sequences[$this->getCurrentSequenceId() + 1]);
	}

	public function getCurrentSequenceId(): int {
		return $this->currentSequence;
	}

	public function getPlayer(): ?Player {
		return $this->player;
	}

	public function start(Player $player): void {
		$this->player = $player;
		$player->setNoClientPredictions(true);
		$player->despawnFromAll();

		$this->getCurrentSequence()->start($player);
	}

	public function end(Player $player): void {
		$player->setNoClientPredictions(false);
		$player->spawnToAll();
	}

	public function __clone() {
		foreach ($this->sequences as $key => $sequence) {
			$this->sequences[$key] = clone $sequence;
		}
	}
}
