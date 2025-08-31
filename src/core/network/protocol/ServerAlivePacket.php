<?php

namespace core\network\protocol;

class ServerAlivePacket extends ConnectPacket {

	const PACKET_ID = self::SERVER_ALIVE;

	public function sendReturn(ConnectPacketHandler $handler): void {
		echo "Server alive ping successfully sent back!", PHP_EOL;
	}

	public function handle(ConnectPacketHandler $handler): void {
		$this->setResponseData([
			"error" => false,
			"message" => "dis server is alive yo! cray zee" // shane has a singular braincell competing with itself in the mirror for 3rd place
		]);
	}
}
