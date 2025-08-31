<?php

namespace core\network\data;

use core\network\protocol\DataSyncPacket;

class DataSyncQuery {

	/** @var DataSyncPacket[] */
	public array $packets = [];
	public ?DataSyncResult $result = null;

	public function __construct(
		public string $id,
		DataSyncPacket|array $packets = []
	) {
		$this->packets = is_array($packets) ? $packets : [$packets];
	}

	public function getId(): string {
		return $this->id;
	}

	/** @return DataSyncPacket[] */
	public function getPackets(): array {
		return $this->packets;
	}

	public function send(): void {
		foreach ($this->packets as $packet) {
			$packet->queue();
		}
	}

	public function getResult(): ?DataSyncResult {
		return $this->result;
	}

	public function setResult(?DataSyncResult $result): void {
		$this->result = $result;
	}
}
