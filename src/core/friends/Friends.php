<?php

namespace core\friends;

use core\Core;

/**
 * @deprecated
 */
class Friends {

	public array $requests = [];

	public function __construct(public Core $plugin) {
	}

	public function tick(): void {
		foreach ($this->getRequests() as $key => $request) {
			if ($request->tick() || !$request->getTo()->isConnected()) {
				$request->timeout();
			}
		}
	}

	public function getRequests(): array {
		return $this->requests;
	}

	public function addRequest(FriendRequest $request): void {
		$this->requests[] = $request;
	}
}
