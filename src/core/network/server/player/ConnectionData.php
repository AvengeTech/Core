<?php

namespace core\network\server\player;

use Ramsey\Uuid\{
	Uuid,
	UuidInterface
};

use core\user\User;

class ConnectionData {

	public UuidInterface $uuid; //used for subserver list

	public function __construct(
		public User $user,
		public string $identifier = ""
	) {
		$this->uuid = Uuid::uuid4();
	}

	public function getUniqueId(): UuidInterface {
		return $this->uuid;
	}

	public function getUser(): User {
		return $this->user;
	}

	public function getGamertag(): string {
		return $this->getUser()->getGamertag();
	}

	public function getXuid(): int {
		return $this->getUser()->getXuid();
	}

	public function getIdentifier(): string {
		return $this->identifier;
	}
}
