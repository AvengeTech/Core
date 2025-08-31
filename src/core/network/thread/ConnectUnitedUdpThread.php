<?php

namespace core\network\thread;

use connect\Server;
use pmmp\thread\Thread;
use pmmp\thread\ThreadSafeArray;

use pocketmine\thread\Worker;

use core\Core;

class ConnectUnitedUdpThread extends Worker {

	const NAME = "UNITED";
	const SOCKET_ADDRESS = "127.0.0.1";

	public $host_socket;
	public $client_socket;

	public ThreadSafeArray $pendingPackets;
	public ThreadSafeArray $processedPackets;
	public ThreadSafeArray $responsePackets;

	public ThreadSafeArray $receivedPackets;
	public ThreadSafeArray $returningPackets;
	public ThreadSafeArray $returnedPackets;

	public bool $needReconnect = false;
	public bool $alive = true;
	public bool $shutdown = false;

	public function __construct(public int $host_port, public int $client_port) {
		$this->pendingPackets = new ThreadSafeArray();
		$this->processedPackets = new ThreadSafeArray();
		$this->responsePackets = new ThreadSafeArray();

		$this->receivedPackets = new ThreadSafeArray();
		$this->returningPackets = new ThreadSafeArray();
		$this->returnedPackets = new ThreadSafeArray();

		$connected = true;
		if (!($host_socket = @socket_create(AF_INET, SOCK_DGRAM, SOL_UDP))) {
			$errorcode = socket_last_error($host_socket ? $host_socket : null);
			$errormsg = socket_strerror($errorcode);
			Core::getInstance()->getLogger()->error("Couldn't create " . self::NAME . " UDP socket: [$errorcode] $errormsg");
			$this->needReconnect = true;
			$connected = false;
		}
		if (!@socket_bind($host_socket, self::SOCKET_ADDRESS, $host_port)) {
			$errorcode = socket_last_error($host_socket);
			$errormsg = socket_strerror($errorcode);
			Core::getInstance()->getLogger()->error("Couldn't bind to " . self::NAME . " host UDP socket: [$errorcode] $errormsg");
			$this->needReconnect = true;
			$connected = false;
		}

		if (!($client_socket = @socket_create(AF_INET, SOCK_DGRAM, SOL_UDP))) {
			$errorcode = socket_last_error($client_socket ? $client_socket : null);
			$errormsg = socket_strerror($errorcode);
			Core::getInstance()->getLogger()->error("Couldn't create " . self::NAME . " UDP socket: [$errorcode] $errormsg");
			$this->needReconnect = true;
			$connected = false;
		}

		if ($connected) {
			@socket_set_nonblock($host_socket);
			@socket_set_nonblock($client_socket);
			$this->host_socket = $host_socket;
			$this->client_socket = $client_socket;
			Core::getInstance()->getLogger()->notice("Successfully created " . self::NAME . " UDP sockets!");
		} else {
			Core::getInstance()->getLogger()->error("Failed to create " . self::NAME . " UDP sockets, retrying in 3 seconds...");
		}

		$this->start(Thread::INHERIT_INI | Thread::INHERIT_CONSTANTS);
	}

	public function tryReconnect(): void {
		//echo "Attempting socket reconnect...", PHP_EOL;
		if (!($host_socket = @socket_create(AF_INET, SOCK_DGRAM, SOL_UDP))) {
			$errorcode = socket_last_error($host_socket ? $host_socket : null);
			$errormsg = socket_strerror($errorcode);
			echo "Couldn't create " . self::NAME . " host socket while reconnecting: [$errorcode] $errormsg", PHP_EOL;
			return;
		}
		if (!@socket_bind($host_socket, self::SOCKET_ADDRESS, $this->host_port)) {
			$errorcode = socket_last_error($host_socket);
			$errormsg = socket_strerror($errorcode);
			echo "Couldn't bind to " . self::NAME . " host UDP socket: [$errorcode] $errormsg", PHP_EOL;
			return;
		}

		if (!($client_socket = @socket_create(AF_INET, SOCK_DGRAM, SOL_UDP))) {
			$errorcode = socket_last_error($client_socket ? $client_socket : null);
			$errormsg = socket_strerror($errorcode);
			Core::getInstance()->getLogger()->error("Couldn't create client " . self::NAME . " UDP socket: [$errorcode] $errormsg");
			return;
		}

		@socket_set_nonblock($host_socket);
		@socket_set_nonblock($client_socket);
		$this->host_socket = $host_socket;
		$this->client_socket = $client_socket;

		$this->alive = true;
		$this->needReconnect = false;

		echo "Successfully reestablished " . self::NAME . " UDP sockets!", PHP_EOL;
	}

	public function getHostSocket(): false|\Socket|null {
		return $this->host_socket;
	}

	public function getClientSocket(): false|\Socket|null {
		return $this->client_socket;
	}

	public function onRun(): void {
		while (
			!$this->shutdown &&
			$this->getHostSocket() === null ||
			$this->getClientSocket() === null ||
			$this->needReconnect
		) {
			sleep(3);
			$this->tryReconnect();
		}

		$host_socket = $this->getHostSocket();
		$client_socket = $this->getClientSocket();
		while ($this->alive && !$this->shutdown) {
			$input = @socket_recvfrom($host_socket, $buf, 36900, MSG_DONTWAIT, $remote_ip, $remote_port);
			if ($input !== false) {
				//var_dump($buf);
				$data = json_decode($buf, true);
				if ($data !== null) {
					if (($runtimeId = $data["runtimeId"] ?? -1) !== -1) {
						if (empty($data["response"])) {
							$data["data"]["remote_port"] = $remote_port;
							$this->receivedPackets[$runtimeId] = ThreadSafeArray::fromArray($data);
						}
					}
				}
			}

			$input = @socket_recvfrom($client_socket, $buf, 36900, MSG_DONTWAIT, $remote_ip, $remote_port);
			if ($input !== false) {
				$data = json_decode($buf, true);
				if ($data !== null) {
					if (($runtimeId = $data["runtimeId"] ?? -1) !== -1) {
						$this->responsePackets[$runtimeId] = ThreadSafeArray::fromArray($data);
					}
				}
			}

			while (count($this->pendingPackets) != 0) {
				$command = $this->pendingPackets->shift();
				//var_dump($command);
				if (!@socket_sendto($client_socket, $command, strlen($command), MSG_DONTWAIT, self::SOCKET_ADDRESS, $this->client_port)) {
					$errorcode = socket_last_error();
					$errormsg = socket_strerror($errorcode);
					echo "Could not send pending packet data to " . self::NAME . " UDP socket: [$errorcode] $errormsg", PHP_EOL;
					var_dump($command);
					$this->alive = false;
					$this->needReconnect = true;
					break;
				}
				$data = json_decode($command, true);
				if (($runtimeId = $data["runtimeId"] ?? -1) !== -1) {
					$this->processedPackets[] = $runtimeId;
				}
			}

			while (count($this->returningPackets) != 0) {
				$command = $this->returningPackets->shift();
				//var_dump($command);
				$data = json_decode($command, true);
				if (isset($data["data"]["remote_port"])) {
					$port = $data["data"]["remote_port"];
				}
				if (!@socket_sendto($host_socket, $command, strlen($command), MSG_DONTWAIT, self::SOCKET_ADDRESS, $port ?? 0)) {
					$errorcode = socket_last_error();
					$errormsg = socket_strerror($errorcode);
					echo "Could not return packet data to " . self::NAME . " UDP socket: [$errorcode] $errormsg", PHP_EOL;
					$this->alive = false;
					$this->needReconnect = true;
					break;
				}
				if (($runtimeId = $data["runtimeId"] ?? -1) !== -1) {
					$this->returnedPackets[] = $runtimeId;
				}
			}
			usleep(1);
		}
		if ($host_socket !== null) socket_close($host_socket);
		if ($client_socket !== null) socket_close($client_socket);

		while ($this->needReconnect && !$this->shutdown) {
			sleep(3);
			$this->tryReconnect();
		}

		if ($this->alive && !$this->shutdown) $this->run();
	}
}
