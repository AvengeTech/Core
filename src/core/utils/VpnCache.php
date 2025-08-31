<?php

namespace core\utils;

class VpnCache{

	const IP_WHITELIST = [/*[REDACTED]*/];

	public $entries = [];

	public function getEntries(): array {
		return $this->entries;
	}

	public function addEntry(string $ip, bool $value = true): void {
		$this->entries[$ip] = $value;
	}

	public function entryExists(string $ip): bool {
		return isset($this->entries[$ip]);
	}

	public function getEntryValue(string $ip): bool {
		return $this->entries[$ip] ?? false;
	}
}
