<?php

namespace core\network\protocol;

use pmmp\thread\ThreadSafeArray;

use pocketmine\Server;

use core\network\server\ServerManager;
use core\network\thread\ConnectUnitedUdpThread;
use core\network\Structure;

class ConnectPacketHandler {

	public static int $runtimeId = 0;
	public static array $registeredPackets = [];

	public int $ticks = 0;

	public ConnectUnitedUdpThread $thread;

	public array $waitingPackets = [];
	public array $returningPackets = [];

	public function __construct(public ServerManager $serverManager) {
		$this->registerPackets();

		$this->thread = new ConnectUnitedUdpThread(
			Structure::SOCKET_PORTS[$serverManager->getThisServer()->getIdentifier()][0],
			Structure::SOCKET_PORTS[$serverManager->getThisServer()->getIdentifier()][1]
		);

		$this->queuePacket(new ServerSetStatusPacket([
			"online" => true
		]));
		$this->queuePacket(new ServerGetAllPlayersPacket());
		$server = $serverManager->getThisServer();
		$this->queuePacket(new ServerWhitelistPacket([
			"identifier" => $server->getIdentifier(),
			"whitelisted" => $server->restricted,
			"whitelist" => $server->getWhitelist()
		]));
	}

	public function registerPackets(): void {
		self::registerPacket(PacketIds::SERVER_ALIVE, ServerAlivePacket::class);
		self::registerPacket(PacketIds::SERVER_GET_PLAYERS, ServerGetPlayersPacket::class);
		self::registerPacket(PacketIds::SERVER_GET_ALL_PLAYERS, ServerGetAllPlayersPacket::class);
		self::registerPacket(PacketIds::SERVER_POST_PLAYERS, ServerPostPlayersPacket::class);
		self::registerPacket(PacketIds::SERVER_GET_STATUS, ServerGetStatusPacket::class);
		self::registerPacket(PacketIds::SERVER_SET_STATUS, ServerSetStatusPacket::class);
		self::registerPacket(PacketIds::SERVER_WHITELIST, ServerWhitelistPacket::class);
		self::registerPacket(PacketIds::SERVER_ANNOUNCEMENT, ServerAnnouncementPacket::class);

		self::registerPacket(PacketIds::SERVER_SUB_UPDATE, ServerSubUpdatePacket::class);

		self::registerPacket(PacketIds::STAFF_CHAT, StaffChatPacket::class);
		self::registerPacket(PacketIds::STAFF_BAN, StaffBanPacket::class);
		self::registerPacket(PacketIds::STAFF_BAN_IP, StaffBanIpPacket::class);
		self::registerPacket(PacketIds::STAFF_BAN_DEVICE, StaffBanDevicePacket::class);
		self::registerPacket(PacketIds::STAFF_MUTE, StaffMutePacket::class);
		self::registerPacket(PacketIds::STAFF_WARN, StaffWarnPacket::class);
		self::registerPacket(PacketIds::STAFF_ANTICHEAT_NOTICE, StaffAnticheatPacket::class);
		self::registerPacket(PacketIds::STAFF_COMMAND_SEE, StaffCommandSeePacket::class);

		self::registerPacket(PacketIds::PLAYER_MESSAGE, PlayerMessagePacket::class);
		self::registerPacket(PacketIds::PLAYER_TRANSFER, PlayerTransferPacket::class);
		self::registerPacket(PacketIds::PLAYER_TRANSFER_COMPLETE, PlayerTransferCompletePacket::class);
		self::registerPacket(PacketIds::PLAYER_CONNECT, PlayerConnectPacket::class);
		self::registerPacket(PacketIds::PLAYER_DISCONNECT, PlayerDisconnectPacket::class);
		self::registerPacket(PacketIds::PLAYER_SUMMON, PlayerSummonPacket::class);
		self::registerPacket(PacketIds::PLAYER_CHAT, PlayerChatPacket::class);
		self::registerPacket(PacketIds::PLAYER_SESSION_SAVED, PlayerSessionSavedPacket::class);
		self::registerPacket(PacketIds::PLAYER_RECONNECT, PlayerReconnectPacket::class);
		self::registerPacket(PacketIds::PLAYER_LOAD_ACTION, PlayerLoadActionPacket::class);

		self::registerPacket(PacketIds::DATA_SYNC, DataSyncPacket::class);
	}

	public static function registerPacket(int $packetId, string $class): bool {
		if (isset(self::$registeredPackets[$packetId])) {
			return false; //Packet already registered with same ID
		}
		$packet = new $class();
		if (!$packet instanceof ConnectPacket) {
			return false;
		}
		self::$registeredPackets[$packetId] = $class;
		return true;
	}

	public static function getPacketClass(int $packetId): ?string {
		if (!isset(self::$registeredPackets[$packetId]))
			return null;
		return self::$registeredPackets[$packetId];
	}

	public function getPacketFromData(array|ThreadSafeArray $data): ?ConnectPacket {
		if (!isset($data["packetId"]))
			return null;
		$class = self::getPacketClass($data["packetId"]);
		if ($class == null) return null;
		return new $class($data["data"] ?? [], $data["runtimeId"] ?? null, $data["created"] ?? -1, $data["response"] ?? []);
	}

	public static function newRuntimeId(): int {
		return self::$runtimeId++;
	}

	public function getServerManager(): ServerManager {
		return $this->serverManager;
	}

	public function getThread(): ?ConnectUnitedUdpThread {
		return $this->thread;
	}

	public function close(): void {
		if (($thread = $this->getThread()) !== null) {
			$thread->needReconnect = false;
			$thread->alive = false;
			$thread->shutdown = true;
		}
	}

	public function tick(): void {
		$this->ticks++;
		//Packet processing
		$thread = $this->getThread();
		foreach ($thread->processedPackets as $runtimeId) {
			$packet = $this->getWaitingPacket($runtimeId);
			if ($packet instanceof ConnectPacket) {
				$packet->setSent();
				$packet->send($this);
			}
		}
		while (count($thread->processedPackets) > 0) $thread->processedPackets->shift(); //not sure if this will cause race conditions

		foreach ($thread->responsePackets as $runtimeId => $data) {
			$packet = $this->getWaitingPacket($runtimeId);
			if ($packet instanceof ConnectPacket) {
				$packet->setResponseData($data["response"] ?? []);
				if ($packet->verifyResponse()) {
					$packet->handleResponse($this);
				}
				unset($this->waitingPackets[$runtimeId]);
			}
		}
		while (count($thread->responsePackets) > 0) $thread->responsePackets->shift();

		foreach ($this->waitingPackets as $runtimeId => $packet) {
			if ($packet->canTimeout()) {
				$packet->timeout($this);
				unset($this->waitingPackets[$runtimeId]);
			}
		}

		foreach ($thread->receivedPackets as $runtimeId => $data) {
			$packet = $this->getPacketFromData($data);
			if ($packet instanceof ConnectPacket) {
				if ($packet->verifyHandle()) {
					$packet->handle($this);
					if (!$packet instanceof OneWayPacket) {
						$thread->returningPackets[] = $packet->toJson(true);
						$packet->created = time(); //reset timeout
						$this->returningPackets[$runtimeId] = $packet;
					}
				}
			}
		}
		while (count($thread->receivedPackets) > 0) $thread->receivedPackets->shift();

		foreach ($thread->returnedPackets as $runtimeId) {
			$packet = $this->getReturningPacket($runtimeId);
			if ($packet instanceof ConnectPacket) {
				$packet->sendReturn($this);
				unset($this->returningPackets[$runtimeId]);
			}
		}
		while (count($thread->returnedPackets) > 0) $thread->returnedPackets->shift();

		foreach ($this->returningPackets as $runtimeId => $packet) {
			if ($packet->canTimeout()) {
				$packet->timeoutReturn($this);
				unset($this->returningPackets[$runtimeId]);
			}
		}

		if ($this->ticks % 8 == 0) { // 2 seconds
			$this->queuePacket(new ServerGetAllPlayersPacket());

			$packet = new ServerPostPlayersPacket();
			foreach (Server::getInstance()->getOnlinePlayers() as $player) {
				$packet->addPlayer($player);
			}
			$this->queuePacket($packet);
		}
	}

	public function getWaitingPackets(): array {
		return $this->waitingPackets;
	}

	public function getWaitingPacket(int $runtimeId): ?ConnectPacket {
		return $this->waitingPackets[$runtimeId] ?? null;
	}

	public function getReturningPacket(int $runtimeId): ?ConnectPacket {
		return $this->returningPackets[$runtimeId] ?? null;
	}

	public function queuePacket(ConnectPacket $packet): bool {
		if ($packet->verifySend()) {
			if (!$packet->hasResponseData() && !$packet instanceof OneWayPacket) $this->waitingPackets[$packet->getRuntimeId()] = $packet;
			$this->getThread()->pendingPackets[] = $packet->toJson($packet->hasResponseData());
			return true;
		}
		return false;
	}
}
