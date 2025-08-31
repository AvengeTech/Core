<?php

namespace core\staff\entry;

use core\Core;
use core\discord\objects\Embed;
use core\discord\objects\Field;
use core\discord\objects\Post;
use core\discord\objects\Webhook;
use core\session\mysqli\data\MySqlQuery;
use core\session\stray\StrayRequest;
use core\user\User;
use pocketmine\promise\Promise;
use pocketmine\promise\PromiseResolver;

class BanEntry {

	public const TYPE_REGULAR = 0;
	public const TYPE_IP = 1;
	public const TYPE_DEVICE = 2;

	public ?User $cachedUser = null;
	public ?User $cachedByUser = null;

	public function __construct(
		public string|int $id,
		public int $by,
		public string $reason,
		public string $identifier,
		public int $when,
		public int $until,
		public bool $revoked = false,
		public int $type = self::TYPE_REGULAR,
	) {
		$this->getUser();
		$this->getByUser();
	}

	public function getId(): string|int {
		return $this->id;
	}

	/**
	 * @return Promise<User>|User
	 */
	public function getUser(): Promise|User {
		if (!is_null($this->cachedUser)) {
			return $this->cachedUser;
		}
		$resolver = new PromiseResolver();

		Core::getInstance()->getUserPool()->useUser($this->getId(), function (User $user) use ($resolver) {
			if (!$user->valid()) $resolver->reject();
			else {
				$this->cachedUser = $user;
				$resolver->resolve($user);
			}
		});

		return $resolver->getPromise();
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

	public function getType(): int {
		return $this->type;
	}

	public function isBanned(): bool {
		return ($this->until > time() || $this->isInfinite()) && !$this->isRevoked();
	}

	public function isRevoked(): bool {
		return $this->revoked;
	}

	public function revoke(?User $moderator, bool $notify = true): void {
		if (is_null($moderator)) $moderator = new User(0, "Unknown");
		$this->revoked = true;
		$this->save();

		if ($notify) {
			$user = $this->getUser();
			$completion = function (User $user) use ($moderator) {
				$post = new Post("", "Ban Log - " . Core::getInstance()->getNetwork()->getIdentifier(), "[REDACTED]", false, "", [
					new Embed("", "rich", "**" . $user->getGamertag() . "** has been unbanned!", "", "ffb106", null, "", "[REDACTED]", null, [
						new Field("Moderator", $moderator->getGamertag())
					])
				]);
				$post->setWebhook(Webhook::getWebhookByName("ban-log"));
				$post->send();
			};
			if ($user instanceof Promise) $user->onCompletion($completion, fn() => null);
			else $completion($user);
		}
	}

	public function save(): void {
		Core::getInstance()->getSessionManager()->sendStrayRequest(
			new StrayRequest(
				"save_ban_" . $this->getId(),
				new MySqlQuery(
					"main",
					"INSERT INTO bans(id, `by`, reason, identifier, `when`, until, revoked, `type`) VALUES (?, ?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE `by`=VALUES(`by`), reason=VALUES(reason), identifier=VALUES(identifier), until=VALUES(until), revoked=VALUES(revoked), `type`=VALUES(`type`)",
					[
						$this->getId(),
						$this->getBy(),
						$this->getReason(),
						$this->getIdentifier(),
						$this->getWhen(),
						$this->getUntil(),
						(int)$this->isRevoked(),
						$this->getType()
					]
				)
			),
			function () {
			}
		);
	}
}
