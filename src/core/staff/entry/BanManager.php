<?php

namespace core\staff\entry;

use core\user\User;

class BanManager {

	const DAY_SECONDS = 86400;
	const STIMEMAP = [
		"Cheating" => 0,
		"Bug/Glitch Abuse" => 1,
		"Duplication Glitch" => 2,
		"DDoS Threat(s)" => 2,
		"Doxxing" => 2,
		"Inappropriate Username" => 0,
		"Inappropriate Skin" => 0
	];
	const TTK = [
		0 => 7 * self::DAY_SECONDS,
		1 => 31 * self::DAY_SECONDS,
		2 => -1
	];

	/** @var BanEntry[] */
	public array $bans = [];
	private bool $changedSinceLastSort = true;

	public function __construct(public User $user) {
	}

	/** @return User */
	public function getUser(): User {
		return $this->user;
	}

	/** @return BanEntry[] */
	public function getBans(): array {
		$this->attemptSort();
		return $this->bans;
	}

	private function attemptSort(): void {
		if (!$this->changedSinceLastSort) return;
		uksort($this->bans, function ($a, $b) {
			return $b <=> $a;
		});
		$this->changedSinceLastSort = false;
	}

	public function addBan(BanEntry $ban): void {
		$this->bans[$ban->getWhen()] = $ban;
		$this->changedSinceLastSort = true;
	}

	public function isBanned(): bool {
		foreach ($this->getBans() as $ban) {
			if ($ban->isBanned() && !$ban->isRevoked()) {
				return true;
			}
		}
		return false;
	}

	public function getRecentBan(): ?BanEntry {
		$bans = $this->getBans();
		if (empty($bans)) {
			return null;
		}
		$latest = max(array_keys($bans));
		return $bans[$latest];
	}

	public function revokeBan(BanEntry $ban, ?User $moderator = null): void {
		foreach ($this->getBans() as $when => $b) {
			if ($ban->getWhen() === $b->getWhen() && $ban->getId() === $b->getId()) {
				$ban->revoke($moderator);
				$this->bans[$when] = $ban;
				$this->changedSinceLastSort = true;
				return;
			}
		}
	}

	/**
	 * Calculates the next ban duration based on the provided reason and the user's ban history.
	 *
	 * @param string $reason The reason for the ban, used to determine the duration increment.
	 * @return int The duration (in seconds) for the next ban, defaults to infinite.
	 */
	public function getNextDuration(string $reason): int {
		$invttk = array_flip(self::TTK);
		$timeIndex = 0;
		foreach ($this->getBans() as $ban) {
			if ($ban->isRevoked()) continue;
			if (!$ban->isInfinite()) {
				$timeIndex = max($timeIndex, $invttk[($ban->getUntil() - $ban->getWhen())] ?? -1);
			} else {
				$timeIndex = array_key_last(self::TTK);
				break;
			}
		}
		$timeIndex = min($timeIndex + (self::STIMEMAP[$reason] ?? 0) + 1, array_key_last(self::TTK));
		return self::TTK[$timeIndex] ?? -1;
	}
}
