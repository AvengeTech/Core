<?php

namespace core\staff\entry;

use core\Core;
use core\user\User;

class MuteManager {

	const DAY_SECONDS = 86400;
	const STIMEMAP = [
		"Warning Limit" => 0,
		"Misc Warning Limit" => 1,
		"Advertising" => 1,
	];
	const TTK = [
		0 => 3 * self::DAY_SECONDS,
		1 => 7 * self::DAY_SECONDS,
		2 => 31 * self::DAY_SECONDS,
		3 => -1
	];

	public array $mutes = [];
	private bool $changedSinceLastSort = true;

	public function __construct(public User $user) {
	}

	public function getUser(): User {
		return $this->user;
	}

	/** @return MuteEntry[] */
	public function getMutes(): array {
		$this->attemptSort();
		return $this->mutes;
	}

	private function attemptSort(): void {
		if (!$this->changedSinceLastSort) return;
		uksort($this->mutes, function ($a, $b) {
			return $b <=> $a;
		});
		$this->changedSinceLastSort = false;
	}

	public function addMute(MuteEntry $mute): void {
		$this->mutes[$mute->getWhen()] = $mute;
		$this->changedSinceLastSort = true;
	}

	public function isMuted(): bool {
		foreach ($this->getMutes() as $mute) {
			if ($mute->isMuted() && !$mute->isRevoked()) {
				return true;
			}
		}
		return false;
	}

	public function getRecentMute(): ?MuteEntry {
		$mutes = $this->getMutes();
		if (empty($mutes)) {
			return null;
		}
		$latest = max(array_keys($mutes));
		return $mutes[$latest];
	}

	public function removeMute(MuteEntry $mute, ?User $moderator = null): void {
		foreach ($this->getMutes() as $when => $m) {
			if ($mute->getWhen() === $m->getWhen()) {
				$mute->revoke($moderator);
				$this->mutes[$when] = $mute;
				$this->changedSinceLastSort = true;
				return;
			}
		}
	}

	/**
	 * Calculates the next mute duration based on the provided reason and the user's mute history.
	 *
	 * @param string $reason The reason for the mute, used to determine the duration increment.
	 * @return int The duration (in seconds) for the next mute, defaults to infinite.
	 */
	public function getNextDuration(string $reason): int {
		$invttk = array_flip(self::TTK);
		$timeIndex = 0;
		foreach ($this->getMutes() as $mute) {
			if ($mute->isRevoked()) continue;
			if (!$mute->isInfinite()) {
				$timeIndex = max($timeIndex, $invttk[($mute->getUntil() - $mute->getWhen())] ?? -1);
			} else {
				$timeIndex = array_key_last(self::TTK);
				break;
			}
		}
		$timeIndex = min($timeIndex + (self::STIMEMAP[$reason] ?? 0), array_key_last(self::TTK));
		return self::TTK[$timeIndex] ?? -1;
	}
}
