<?php

namespace core\session\component;

use core\AtPlayer as Player;
use core\session\PlayerSession;
use core\user\User;

abstract class BaseComponent {

	public function __construct(private PlayerSession $session) {
	}

	abstract public function getName(): string;

	public function tick(): void {
	}

	public function forceSession(PlayerSession $session) {
		$this->session = $session;
	}

	public function getSession(): PlayerSession {
		return $this->session;
	}

	public function getUser(): User {
		return $this->getSession()->getUser();
	}

	public function getPlayer(): ?Player {
		return $this->getSession()->getPlayer();
	}

	public function getXuid(): int {
		return $this->getSession()->getXuid();
	}

	public function getGamertag(): string {
		return $this->getSession()->getGamertag();
	}
}
