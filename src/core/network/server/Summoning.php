<?php

namespace core\network\server;

class Summoning {

	public int $created;

	public function __construct(
		public string $player,
		public string $sentBy
	) {
		$this->created = time();
	}
}
