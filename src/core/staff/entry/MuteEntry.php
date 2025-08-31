<?php

namespace core\staff\entry;

use core\AtPlayer;
use core\Core;
use core\discord\objects\Embed;
use core\discord\objects\Field;
use core\discord\objects\Post;
use core\discord\objects\Webhook;
use core\session\mysqli\data\MySqlQuery;
use core\session\stray\StrayRequest;
use core\user\User;
use core\utils\TextFormat;
use pocketmine\promise\Promise;
use pocketmine\promise\PromiseResolver;

class MuteEntry {

	public ?User $cachedByUser = null;

	public function __construct(
		public User $user,
		public int $by,
		public string $reason,
		public string $identifier,
		public int $when,
		public int $until,
		public bool $revoked = false
	) {
		$this->getByUser();
	}

	public function getUser(): User {
		return $this->user;
	}

	public function getBy(): int {
		return $this->by;
	}

	public function getByUser(): Promise|User {
		if (!is_null($this->cachedByUser)) {
			return $this->cachedByUser;
		}

		$resolver = new PromiseResolver();

		Core::getInstance()->getUserPool()->useUser($this->getBy(), function (User $user) use ($resolver) {
			if (!$user->valid()) {
				$resolver->reject();
			} else {
				$this->cachedByUser = $user;
				$resolver->resolve($user);
			}
		});

		return $resolver->getPromise();
	}

	public function getReason(): string {
		return $this->reason;
	}

	public function setReason(string $reason): void {
		$this->reason = $reason;
		$this->save();
	}

	public function getIdentifier(): string {
		return $this->identifier;
	}

	public function getWhen(): int {
		return $this->when;
	}

	public function getFormattedWhen(bool $extras = false): string {
		return gmdate("m-d-Y" . ($extras ? " H:i" : ""), $this->getWhen());
	}

	public function getUntil(): int {
		return $this->until;
	}

	public function setUntil(int $until): void {
		$this->until = $until;
		$this->save();
	}

	public function getFormattedUntil(bool $extras = false): string {
		return $this->isInfinite() ? "ETERNITY" : gmdate("m-d-Y" . ($extras ? " H:i" : ""), $this->getUntil());
	}

	public function getFormattedTimeLeft(): string {
		if ($this->isInfinite()) return "ETERNITY";
		$seconds = $this->getUntil() - time();

		$units = [
			'day' => 86400,
			'hour' => 3600,
			'minute' => 60,
			'second' => 1,
		];

		$result = [];
		$remaining = $seconds;

		foreach ($units as $name => $divisor) {
			if ($remaining >= $divisor) {
				$value = intdiv($remaining, $divisor);
				$remaining -= $value * $divisor;
				$result[] = $value . ' ' . $name . ($value > 1 ? 's' : '');
				if (count($result) === 2) {
					break;
				}
			}
		}

		return implode(' ', $result);
	}

	public function isInfinite(): bool {
		return $this->until === -1;
	}

	public function isMuted(): bool {
		return ($this->until > time() || $this->isInfinite()) && !$this->isRevoked();
	}

	public function isRevoked(): bool {
		return $this->revoked;
	}

	public function revoke(?User $moderator = null, bool $notify = true): void {
		$moderator ??= new User(0, "Unknown");
		$this->revoked = true;
		$this->save();

		if ($notify) {
			$p = $this->getUser()->getPlayer();
			if ($p instanceof AtPlayer && $p->isOnline()) $p->sendMessage(TextFormat::GN . "You have been unmuted!");
			$post = new Post("", "Mute Log - " . Core::getInstance()->getNetwork()->getIdentifier(), "[REDACTED]", false, "", [
				new Embed("", "rich", "**" . $this->getUser()->getGamertag() . "** has been unmuted!", "", "ffb106", null, "", "[REDACTED]", null, [
					new Field("Moderator", $moderator->getGamertag())
				])
			]);
			$post->setWebhook(Webhook::getWebhookByName("mute-log"));
			$post->send();
		}
	}

	public function save(): void {
		Core::getInstance()->getSessionManager()->sendStrayRequest(
			new StrayRequest(
				"save_mute_" . $this->getUser()->getXuid(),
				new MySqlQuery(
					"main",
					"INSERT INTO mutes(xuid, `by`, reason, identifier, `when`, until, revoked) VALUES (?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE `by`=VALUES(`by`), reason=VALUES(reason), identifier=VALUES(identifier), until=VALUES(until), revoked=VALUES(revoked)",
					[
						$this->getUser()->getXuid(),
						$this->getBy(),
						$this->getReason(),
						$this->getIdentifier(),
						$this->getWhen(),
						$this->getUntil(),
						(int)$this->isRevoked()
					]
				)
			),
			function () {
			}
		);
	}
}
