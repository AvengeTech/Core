<?php

namespace core\user;

use pocketmine\Server;
use core\AtPlayer as Player;
use core\rank\Structure as RankStructure;

class User {

	public function __construct(
		public int $xuid = 0,
		public string $gamertag = "Server",
		public bool $rankLoaded = false,
		public string $rank = "default",
		public ?string $nickname = null
	) {
	}

	public function getXuid(): int {
		return $this->xuid;
	}

	public function getGamertag(): string {
		return $this->gamertag;
	}

	public function getName(): string {
		return $this->getGamertag();
	}

	public function hasNick(): bool {
		return !is_null($this->nickname);
	}

	public function getNick(): ?string {
		return $this->nickname;
	}

	public function rankLoaded(): bool {
		return $this->rankLoaded;
	}

	public function setRank(string $rank): void {
		$this->rank = $rank;
		$this->rankLoaded = true;
	}

	public function getRank(): string {
		return $this->rank;
	}

	public function getRankHierarchy(): int {
		return RankStructure::RANK_HIERARCHY[$this->getRank()] ?? 0;
	}

	public function getPlayer(): ?Player {
		return Server::getInstance()->getPlayerExact($this->getGamertag());
	}

	public function belongsTo(Player|User $player): bool {
		return $this->getXuid() == $player->getXuid();
	}

	public function validPlayer(): bool {
		return $this->getPlayer() !== null && $this->getPlayer()->isConnected();
	}

	public function isOnline(): bool {
		return $this->validPlayer();
	}

	public function validXuid(): bool {
		return $this->getXuid() > 0;
	}

	public function valid(): bool {
		return $this->getGamertag() !== "Server" && $this->validXuid();
	}

	public function __toString() {
		return $this->getGamertag();
	}
}
