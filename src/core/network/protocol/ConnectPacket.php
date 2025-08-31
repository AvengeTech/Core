<?php

namespace core\network\protocol;

use pmmp\thread\ThreadSafeArray;

use core\Core;

abstract class ConnectPacket implements PacketIds {

	const PROCESS_TIMEOUT = 10;

	const PACKET_ID = 0;

	public int $runtimeId;
	public int $created;
	public bool $sent = false;
	public int $timeoutOffset = 0;

	public array $data = [];
	public array $response = [];

	public function __construct(array|ThreadSafeArray $data = [], ?int $runtimeId = null, int $created = -1, array|ThreadSafeArray $response = []) {
		$this->data = empty($data) ? $this->getDefaultPacketData() : (array) $data;

		$this->runtimeId = $runtimeId ?? ConnectPacketHandler::newRuntimeId();
		$this->created = $created == -1 ? time() : $created;
		$this->response = (array) $response;
	}

	public function getPacketId(): int {
		return $this::PACKET_ID;
	}

	public function getRuntimeId(): int {
		return $this->runtimeId;
	}

	public function getCreated(): int {
		return $this->created;
	}

	public function hasSent(): bool {
		return $this->sent;
	}

	public function setSent(): void {
		$this->sent = true; //todo: add offset for packet timeout
		$this->timeoutOffset = time() - $this->getCreated();
	}

	/**
	 * Returns whether packet can be sent or not
	 *
	 * @return bool
	 */
	public function verifySend(): bool {
		return true;
	}

	/**
	 * Called after packet is processed through socket
	 */
	public function send(ConnectPacketHandler $handler): void {
	}

	public function sendReturn(ConnectPacketHandler $handler): void {
	}

	public function getTimeoutOffset(): int {
		return $this->timeoutOffset;
	}

	public function toJson(bool $includeResponse = false): string {
		$json = [
			"packetId" => $this->getPacketId(),
			"runtimeId" => $this->getRuntimeId(),
			"created" => $this->getCreated(),
			"data" => $this->getPacketData()
		];
		if ($includeResponse) $json["response"] = $this->getResponseData();
		return json_encode($json);
	}

	/**
	 * Puts packet in queue to send
	 */
	public function queue(): bool {
		return Core::getInstance()->getNetwork()->getServerManager()->getPacketHandler()->queuePacket($this);
	}

	/**
	 * Supplied when no packet data in constructor
	 *
	 * @return array
	 */
	public function getDefaultPacketData(): array {
		return [];
	}

	public function getPacketData(): array {
		return $this->data;
	}

	public function setPacketData(array|ThreadSafeArray $data): void {
		$this->data = (array) $data;
	}

	public function hasRemotePort(): bool {
		return isset($this->getPacketData()["remote_port"]);
	}

	public function getRemotePort(): int {
		return $this->getPacketData()["remote_port"] ?? -1;
	}

	public function getResponseData(): array {
		return $this->response;
	}

	public function hasResponseData(): bool {
		return !empty($this->getResponseData());
	}

	public function setResponseData(array|ThreadSafeArray $response): void {
		$this->response = (array) $response;
	}

	public function canTimeout(): bool {
		return time() > $this->getCreated() + $this->getTimeoutOffset() + self::PROCESS_TIMEOUT;
	}

	/**
	 * Called when data couldn't be returned after packet was sent here
	 */
	public function timeoutReturn(ConnectPacketHandler $handler): void {
	}

	/**
	 * Called when packet fails to receive response after timeout
	 */
	public function timeout(ConnectPacketHandler $handler): void {
	}

	/**
	 * Verifies whether packet data is valid or not
	 *
	 * @return bool
	 */
	public function verifyHandle(): bool {
		return true;
	}

	/**
	 * Sent when packet is received from socket, sets response
	 *
	 * @param ConnectPacketHandler $handler
	 */
	public function handle(ConnectPacketHandler $handler): void {
	}

	/**
	 * Verifies whether response data is valid or not
	 *
	 * @return bool
	 */
	public function verifyResponse(): bool {
		return true;
	}

	/**
	 * Sent after packet response is received from socket
	 *
	 * @param ConnectPacketHandler $handler
	 */
	public function handleResponse(ConnectPacketHandler $handler): void {
	}
}
