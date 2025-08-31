<?php

namespace core\network\server;

class SubServer extends ServerInstance {

	public function __construct(
		public ServerInstance $parentServer,

		string $identifier,
		int $port,

		int $max = 100,
		bool $private = false,
		string $restricted = ""
	) {
		parent::__construct($identifier, $port, $max, $private, $restricted);
	}

	public function getParentServer(): ServerInstance {
		return $this->parentServer;
	}

	public function getSubId(bool $linkParent = false): int|string {
		return ($linkParent ? $this->getTypeId() . "-" : "") . explode("-", $this->getId())[2];
	}
}
