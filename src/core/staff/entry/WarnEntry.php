<?php

namespace core\staff\entry;

use core\Core;
use core\discord\objects\{
	Post,
	Embed,
	Field,
	Webhook
};
use core\session\mysqli\data\MySqlQuery;
use core\session\stray\StrayRequest;
use core\user\User;
use pocketmine\promise\Promise;
use pocketmine\promise\PromiseResolver;

class WarnEntry {

	const TYPE_CHAT = 0;
	const TYPE_MISC = 1;

	public ?User $cachedByUser = null;

	public function __construct(
		public User $user,
		public int $by,
		public string $reason,
		public string $identifier,
		public int $when,
		public int $type = self::TYPE_CHAT,
		public bool $severe = false,
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
		$resolver = new PromiseResolver;

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

	public function getType(): int {
		return $this->type;
	}

	public function getFormattedType(): string {
		return match ($this->getType()) {
			self::TYPE_CHAT => "Chat",
			self::TYPE_MISC => "Miscellaneous",
			default => "Unknown"
		};
	}

	public function isSevere(): bool {
		return $this->severe;
	}

	public function setSevere(bool $severe): void {
		$this->severe = $severe;
		$this->save();
	}

	public function isRevoked(): bool {
		return $this->revoked;
	}

	public function revoke(?User $moderator, bool $notify = true): void {
		if (is_null($moderator)) $moderator = new User(0, "Unknown");
		$this->revoked = true;
		$this->save();

		if ($notify) {
			$post = new Post("", "Warn Log - " . $this->getIdentifier(), "[REDACTED]", false, "", [
				new Embed("", "rich", "One of **" . $this->getUser()->getGamertag() . "'s** warnings has been revoked!", "", "ffb106", null, "", "[REDACTED]", null, [
					new Field("Warn reason", $this->getReason()),
					new Field("Moderator", $moderator->getGamertag()),
				])
			]);
			$post->setWebhook(Webhook::getWebhookByName("warn-log"));
			$post->send();
		}
	}

	public function save(): void {
		Core::getInstance()->getSessionManager()->sendStrayRequest(new StrayRequest(
			"save_warn_" . $this->getUser()->getXuid(),
			new MySqlQuery(
				"main",
				"INSERT INTO warns(xuid, `by`, reason, identifier, `when`, `type`, severe, revoked) VALUES(?, ?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE `by`=VALUES(`by`), reason=VALUES(reason), identifier=VALUES(identifier), `type`=VALUES(`type`), severe=VALUES(severe), revoked=VALUES(revoked)",
				[
					$this->getUser()->getXuid(),
					$this->getBy(),
					$this->getReason(),
					$this->getIdentifier(),
					$this->getWhen(),
					$this->getType(),
					(int)$this->isSevere(),
					(int)$this->isRevoked()
				]
			)
		), function (StrayRequest $request): void {
		});
	}
}
