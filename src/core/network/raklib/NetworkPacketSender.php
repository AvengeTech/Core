<?php

namespace core\network\raklib;

use pocketmine\network\mcpe\PacketSender;

class NetworkPacketSender implements PacketSender {
	private bool $closed = false;

	public function __construct(
		private int $sessionId,
		private NetworkInterface $handler
	) {
	}

	public function send(string $payload, bool $immediate, ?int $receiptId): void {
		if (!$this->closed) {
			$this->handler->putPacket($this->sessionId, $payload, $immediate, $receiptId);
		}
	}

	public function close(string $reason = "unknown reason"): void {
		if (!$this->closed) {
			$this->closed = true;
			$this->handler->close($this->sessionId);
		}
	}
}