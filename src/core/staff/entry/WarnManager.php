<?php

namespace core\staff\entry;

use core\Core;
use core\session\CoreSession;
use core\staff\anticheat\AntiCheat;
use core\user\User;
use core\utils\TextFormat;

class WarnManager {

	const WTYPEMAP = [
		"Spam/Chatfill" => WarnEntry::TYPE_CHAT,
		"Inappropriate comment/discussion" => WarnEntry::TYPE_CHAT,
		"Overly vulgar comment" => WarnEntry::TYPE_CHAT,
		"Anti-LBGTQ comment" => WarnEntry::TYPE_CHAT,
		"Antisemitic comment" => WarnEntry::TYPE_CHAT,
		"Racist comment" => WarnEntry::TYPE_CHAT,
		"Ableist comment" => WarnEntry::TYPE_CHAT,
		"Suicidal remark" => WarnEntry::TYPE_CHAT,
		"Suicidal encouragement" => WarnEntry::TYPE_CHAT,
		"Inappropriate structure" => WarnEntry::TYPE_MISC,
		"Inappropriate/Invisible skin" => WarnEntry::TYPE_MISC,
		"Large 3D skin" => WarnEntry::TYPE_MISC,
	];
	const RTOSEV = [
		"Spam/Chatfill" => false,
		"Inappropriate comment/discussion" => false,
		"Overly vulgar comment" => false,
		"Anti-LBGTQ comment" => true,
		"Antisemitic comment" => true,
		"Racist comment" => true,
		"Ableist comment" => true,
		"Suicidal remark" => false,
		"Suicidal encouragement" => true,
		"Inappropriate structure" => false,
		"Inappropriate/Invisible skin" => false,
		"Large 3D skin" => false,
	];

	public array $warns = [];
	public bool $changedSinceLastSort = true;

	public function __construct(public User $user) {
	}

	public function getUser(): User {
		return $this->user;
	}

	/** @return WarnEntry[] */
	public function getWarns(): array {
		$this->attemptSort();
		return $this->warns;
	}

	private function attemptSort(): void {
		if (!$this->changedSinceLastSort) return;
		uksort($this->warns, function ($a, $b) {
			return $b <=> $a;
		});
		$this->changedSinceLastSort = false;
	}

	public function addWarn(WarnEntry $warn): void {
		$this->warns[$warn->getWhen()] = $warn;
		$this->changedSinceLastSort = true;
	}

	public function removeWarn(WarnEntry $warn, ?User $moderator = null): void {
		foreach ($this->getWarns() as $when => $w) {
			if ($warn->getWhen() === $w->getWhen()) {
				$warn->revoke($moderator);
				$this->warns[$when] = $warn;
				$this->changedSinceLastSort = true;
				return;
			}
		}
	}

	public function checkNeedsMute(?User $moderator = null, bool $fromSevere = false, bool $silent = false): void {
		$moderator ??= new User(-100, AntiCheat::USER_NAME);
		$pts = [0, 0];
		foreach ($this->getWarns() as $warn) {
			if ($warn->isRevoked()) continue;
			if ($warn->getType() === WarnEntry::TYPE_CHAT) {
				if ($warn->isSevere()) $pts[1]++;
				else $pts[0]++;
			}
		}
		Core::getInstance()->getSessionManager()->useSession($this->getUser(), function (CoreSession $session) use ($pts, $moderator, $fromSevere, $silent) {
			$mm = $session->getStaff()->getMuteManager();
			if ($mm->isMuted() && $mm->getRecentMute()->isInfinite()) return;
			$nxtDur = $mm->getNextDuration("Warning Limit");
			if ($mm->isMuted()) $nxtDur += ($mm->getRecentMute()->getUntil() - time());
			$nrc = 0;
			foreach ($mm->getMutes() as $m) if (!$m->isRevoked()) $nrc++;
			$rw = ($pts[0] + ($pts[1] * 1.5)) / 3;
			if ($rw - $nrc >= 1) { // asigning point values to warnings ensures deterministic auto-mute.
				$fromSevere = $fromSevere && (($rw - (($pts[1] * 1.5) / 3)) - $nrc) < 0.99; // mathematically verify the mute was supposed to come from a severe warning
				Core::getInstance()->getStaff()->mute($this->getUser(), $moderator, ($fromSevere ? "Severe " : "") . "Warning Limit", $nxtDur);
				if (!$silent) Core::announceToSS(TextFormat::RI . TextFormat::YELLOW . $this->getUser()->getGamertag() . TextFormat::RED . " has been " . TextFormat::BOLD . TextFormat::DARK_RED . "MUTED " . TextFormat::RESET . TextFormat::RED . "for: Warning Limit", "random.anvil_land");
			}
		});
	}
}
