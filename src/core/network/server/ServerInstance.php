<?php

namespace core\network\server;

use pocketmine\Server;
use pocketmine\scheduler\ClosureTask;

use paroxity\portal\Portal;

use core\{
	Core,
	AtPlayer as Player
};
use core\network\Structure;
use core\network\protocol\{
	PlayerConnectPacket,
	PlayerDisconnectPacket,
	PlayerReconnectPacket,
	PlayerTransferCompletePacket,
	PlayerTransferPacket,
	PlayerSessionSavedPacket,
	ServerWhitelistPacket
};
use core\network\server\player\{
	ConnectionData,
	ConnectionCluster
};
use core\rank\Structure as RS;
use core\session\mysqli\data\{
	MySqlQuery,
	MySqlRequest
};
use core\user\User;
use core\utils\TextFormat;
use pmmp\thread\ThreadSafeArray;

/** @method self getParentServer() */
class ServerInstance {

	const ADDRESS = "play.avengetech.net";

	public bool $online = false;
	public array $whitelist = [];

	public ConnectionCluster $cluster;

	public int $ticks = 0;

	public array $summoning = [];
	public array $pendingConnect = [];

	public array $subServerCache = [];

	public Queue $queue;
	public ?FullQueue $fullQueue = null;

	public function __construct(
		public string $identifier,
		public int $port,

		public int $max = 100,
		public bool $private = false,
		public string $restricted = ""
	) {
		$this->loadWhitelist();
		$this->cluster = new ConnectionCluster($this->getId());
		$this->queue = new Queue($this);
		if (!$this->isSubServer() && $this->getType() !== "lobby") $this->fullQueue = new FullQueue($this, 75);


		Core::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function (): void {
			$this->loadSubServerCache();
		}), 5);
	}

	public function close(): void {
		if ($this->isThis())
			$this->saveWhitelist();
	}

	public function getIdentifier(): string {
		return $this->identifier;
	}

	public function getId(): string {
		return $this->getIdentifier();
	}

	public function getTypeCase(): string {
		return Structure::TYPE_TO_CASE[$this->getType()];
	}

	public function getType(): string {
		return explode("-", $this->getId())[0];
	}

	public function getTypeId(): string|int {
		return explode("-", $this->getId())[1];
	}

	protected static bool $isTest;

	public function isTestServer(): bool {
		return (self::$isTest ??= ($this->getTypeId() == "test"));
	}

	protected static bool $isIdle;

	public function isIdle(): bool {
		return (self::$isIdle ??= ($this->getTypeId() == "idle"));
	}

	public function isSubServer(): bool {
		return $this instanceof SubServer;
	}

	public function loadSubServerCache(): void {
		foreach (Core::getInstance()->getNetwork()->getServerManager()->getServers() as $server) {
			if (
				$server->getType() == $this->getType() &&
				$server->getTypeId() == $this->getTypeId()
			) {
				if ($server->isSubServer()) {
					$this->subServerCache[$server->getIdentifier()] = $server;
				}
			}
		}
	}

	public function getAllSubServers() : array{
		return $this->subServerCache;
	}

	/**
	 * @return SubServer[]
	 */
	public function getSubServers(bool $includeSelf = true, bool $includeMain = true): array {
		$servers = [];
		foreach ($this->subServerCache as $id => $server) {
			if ($includeSelf || $id !== $this->getIdentifier())
				$servers[$server->getIdentifier()] = $server;
		}
		if ($includeMain) {
			if (!$this->isSubServer()) {
				if ($includeSelf) $servers[] = $this;
			} else {
				$servers[$this->getParentServer()->getIdentifier()] = $this->getParentServer();
			}
		}
		if (
			($this->isSubServer() && $includeSelf) ||
			(!$this->isSubServer() && $includeMain && $includeSelf)
		) $servers[$this->getIdentifier()] = $this;

		return $servers;

		/**foreach(Core::getInstance()->getNetwork()->getServerManager()->getServers() as $server){
			if(
				$server->getType() == $this->getType() &&
				$server->getTypeId() == $this->getTypeId()
			){
				if($server->isSubServer()){
					if(!$server->isThis() || $includeSelf){
						$servers[] = $server;
					}
				}else{
					if($includeMain && (!$server->isThis() || $includeSelf)){
						$servers[] = $server;
					}
				}
			}
		}
		return $servers;*/
	}

	public function isRelated(ServerInstance|string $server): bool {
		if ($server instanceof ServerInstance) $server = $server->getIdentifier();
		if ($this->getType() == "lobby" && explode("-", $server)[0] == "lobby") return true;
		foreach ($this->getSubServers() as $sub) {
			if ($sub->getIdentifier() == $server) return true;
		}
		return false;
	}

	public function getMaxPlayers(): int {
		return $this->max;
	}

	public function getPort(): int {
		return $this->port;
	}

	public function isPrivate(): bool {
		return $this->private;
	}

	public function isOnline(): bool {
		return $this->online;
	}

	public function setOnline(bool $online = true): void {
		$this->online = $online;
		if ($online) {
			foreach (Core::getInstance()->getNetwork()->getServerManager()->getReconnects() as $key => $reconnect) {
				if ($reconnect->getIdentifier() == $this->getIdentifier() && $reconnect->isRestart()) {
					$player = $reconnect->getPlayer();
					if ($player instanceof Player) {
						$this->delayedTransfer($player, TextFormat::GI . ($reconnect->isRestart() ? "Server has restarted.. But you were reconnected!" : "Successfully reconnected to " . TextFormat::YELLOW . $reconnect->getIdentifier()));
					}
					unset(Core::getInstance()->getNetwork()->getServerManager()->reconnects[$key]);
				}
			}
		}
	}

	public function getWhitelist(): array {
		return $this->whitelist;
	}

	public function loadWhitelist(): void {
		$db = Core::getInstance()->getDatabase();
		$this->whitelist = [];
		Core::getInstance()->getSessionManager()->sendStrayRequest(
			new MySqlRequest(
				"network_load_whitelist_" . $this->getId(),
				new MySqlQuery("main", "SELECT xuid FROM network_whitelist WHERE identifier=?", [$this->getId()])
			),
			function (MySqlRequest $request): void {
				$rows = $request->getQuery()->getResult()->getRows();
				foreach ($rows as $row) {
					$this->whitelist[] = $row["xuid"];
				}
			}
		);
	}

	public function saveWhitelist(): void {
		$identifier = $this->getIdentifier();
		$whitelist = $this->getWhitelist();
		$db = Core::getInstance()->getSessionManager()->getDatabase();
		/**$stmt = $db->prepare("DELETE FROM network_whitelist WHERE identifier=?");
		$stmt->bind_param("s", $identifier);
		$stmt->execute();
		$stmt->close();*/

		$stmt = $db->prepare("INSERT INTO network_whitelist(identifier, xuid) VALUES(?, ?) ON DUPLICATE KEY UPDATE xuid=VALUES(xuid)");
		foreach ($whitelist as $xuid) {
			$stmt->bind_param("si", $identifier, $xuid);
			$stmt->execute();
		}
		$stmt->close();
	}

	public function updateWhitelist(string $restricted = "", array|ThreadSafeArray $whitelist = []) {
		$this->restricted = $restricted;
		$this->whitelist = (array) $whitelist;
	}

	public function sendWhitelist(): void {
		$packet = new ServerWhitelistPacket([
			"whitelisted" => $this->restricted,
			"whitelist" => $this->getWhitelist()
		]);
		$packet->queue();
	}

	public function onWhitelist(Player|User $player): bool {
		return in_array($player->getXuid(), $this->getWhitelist());
	}

	public function whitelist(Player|User $player): bool {
		if ($this->onWhitelist($player)) return false;
		$this->whitelist[] = $player->getXuid();
		$this->sendWhitelist();
		return true;
	}

	public function unwhitelist(Player|User $player): bool {
		if (!$this->onWhitelist($player)) return false;
		foreach ($this->whitelist as $key => $xuid) {
			if ($xuid == $player->getXuid()) {
				unset($this->whitelist[$key]);
			}
		}

		Core::getInstance()->getSessionManager()->sendStrayRequest(
			new MySqlRequest(
				"network_unwhitelist_" . $player->getXuid(),
				new MySqlQuery("main", "DELETE FROM network_whitelist WHERE xuid=?", [$player->getXuid()])
			),
			function (MySqlRequest $request): void {
			}
		);

		$this->sendWhitelist();
		return true;
	}

	public function isRestricted(): bool {
		return $this->restricted !== "";
	}

	public function getRestrictedRank(): string {
		return $this->restricted;
	}

	public function getRestricted(): int {
		return RS::RANK_HIERARCHY[$this->restricted] ?? 0;
	}

	public function setRestricted(string $rank = ""): void {
		$this->restricted = $rank;
	}

	public function addSummoning(string $player, ?string $summonedBy = ""): void {
		$this->summoning[strtolower($player)] = new Summoning($player, $summonedBy);
	}

	public function isBeingSummoned(Player $player): bool {
		return isset($this->summoning[strtolower($player->getName())]);
	}

	public function onSummon(Player $player): void {
		$summoning = $this->summoning[strtolower($player->getName())] ?? null;
		if ($summoning === null) return;
		unset($this->summoning[strtolower($player->getName())]);

		$sent = Server::getInstance()->getPlayerExact($summoning->sentBy);
		if ($sent instanceof Player) {
			$sent->sendMessage(TextFormat::GI . "Successfully summoned " . TextFormat::YELLOW . $player->getName());
		}
	}

	public function transfer(Player $player, string $message = ""): void {
		$packet = new PlayerTransferPacket([
			"player" => $player->getName(),
			"to" => $this->getIdentifier(),
			"message" => $message
		]);
		$packet->queue();

		$player->setTransferring($this->getId());
		if (!$player->isFromProxy()) {
			$player->transfer(self::ADDRESS, $this->getPort());
		} else {
			switch (Core::getInstance()->getNetwork()->getProxy()) {
				case Structure::PROXY_PORTAL:
					Portal::getInstance()->transferPlayer($player, $this->getIdentifier(), null);
					break;
				case Structure::PROXY_WATERDOG:
					$player->transfer($this->getIdentifier());
					break;
			}
		}
		//echo "Transferring " . $player->getName() . " - Reason: " . $message, PHP_EOL;
	}

	public function delayedTransfer(Player $player, string $reason = "", int $delay = 10): void {
		Core::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function () use ($player, $reason): void {
			if ($player->isConnected()) {
				$this->transfer($player, $reason);
			}
		}), $delay);
	}

	public function canTransfer(Player $player, bool $includeFull = true): bool {
		$ss = $this->isSubServer();
		return $this->isOnline() && ($player->isStaff() || $player->isTier3() || (
			(!$this->isFull() || !$includeFull) &&
			(!$this->isPrivate() || ($this->isRestricted() || ($ss && $this->getParentServer()->isRestricted()
			)
			))
		) && (!$this->isRestricted() ||
			($ss && !$this->getParentServer()->isRestricted()) ||
			RS::RANK_HIERARCHY[$player->getRank()] >= $this->getRestricted() ||
			($ss && RS::RANK_HIERARCHY[$player->getRank()] >= $this->getParentServer()->getRestricted()) ||
			$this->onWhitelist($player) ||
			($this->isSubServer() && $this->getParentServer()->onWhitelist($player)) ||
			$this->isBeingSummoned($player)
		)
		);
	}

	public function sendSessionSavedPacket(Player $player, int $type = 0): void {
		$pk = new PlayerSessionSavedPacket([
			"player" => $player->getName(),
			"server" => $this->getId(),
			"type" => $type
		]);
		$pk->queue();
	}

	public function getCluster(): ConnectionCluster {
		return $this->cluster;
	}

	public function getPlayerCount(): int {
		return $this->getCluster()->getPlayerCount();
	}

	public function isFull(): bool {
		return $this->getPlayerCount() >= $this->getMaxPlayers();
	}

	public function isFullQueueBased(): bool {
		return $this->getPlayerCount() >= ($queue = $this->getFullQueue() ?? $this->getQueue())->getMaxPlayers();
	}

	protected static bool $isThis;
	public function isThis(): bool {
		return Server::getInstance()->getPort() == $this->getPort();
	}

	public function addPendingConnection(string $name, string $from, string $message): void {
		$this->pendingConnect[$name] = new PendingConnection($name, $from, $message);
	}

	public function hasPendingConnection(Player $player): bool {
		return isset($this->pendingConnect[$player->getName()]);
	}

	public function shiftPendingConnection(Player $player): ?PendingConnection {
		$msg = $this->pendingConnect[$player->getName()] ?? null;
		unset($this->pendingConnect[$player->getName()]);
		return $msg;
	}

	public function getQueue(): Queue {
		return $this->queue;
	}

	public function getFullQueue(): ?FullQueue {
		return $this->fullQueue;
	}

	public function reconnect(Player $player, bool $restart = false): void {
		$player->sendMessage(TextFormat::YI . "Reconnecting to server...");
		if (!$player->isFromProxy() && !$restart) {
			$this->transfer($player, TextFormat::GI . "Successfully reconnected to " . TextFormat::YELLOW . $this->getIdentifier());
		} else {
			$lobby = ($sm = Core::getInstance()->getNetwork()->getServerManager())->getServerById("idle-1");
			if ($lobby === null) {
				$player->sendMessage(TextFormat::RI . "Idle server currently available, please try again soon!");
				return;
			}
			if ($restart) {
				$pk = new PlayerSessionSavedPacket([
					"player" => $player->getName(),
					"server" => $lobby->getIdentifier(),
					"type" => PlayerSessionSavedPacket::TYPE_CORE
				]);
				$pk->queue();
				if ($this->getType() == "lobby") {
					$pk = new PlayerSessionSavedPacket([
						"player" => $player->getName(),
						"server" => $lobby->getIdentifier(),
						"type" => PlayerSessionSavedPacket::TYPE_GAME
					]);
					$pk->queue();
				}
			}
			$pk = new PlayerReconnectPacket([
				"player" => $player->getName(),
				"rfrom" => $lobby->getIdentifier(),
				"server" => ($this->isSubServer() ? $this->getParentServer()->getIdentifier() : $this->getIdentifier()),
				"restart" => $this->isSubServer() ? false : $restart
			]);
			$pk->queue();
			$lobby->transfer($player, TextFormat::GI . "Reconnecting...");
		}
	}

	public function reconnectAll(bool $restart = true): void {
		foreach (Server::getInstance()->getOnlinePlayers() as $player) {
			$this->reconnect($player, $restart);
		}
	}

	public function onConnect(Player $player): void {
		$this->getCluster()->addPlayer(new ConnectionData($player->getUser(), $this->getIdentifier()));
		if ($this->hasPendingConnection($player)) {
			$connection = $this->shiftPendingConnection($player);
			if (($msg = $connection->getMessage()) != "") $player->sendMessage($msg);
			$player->setFirstConnection(false);
			$player->setTransferredFrom($connection->getFrom());
			if (!$player->hasSessionSaved()) $player->setSessionSaved(stristr($msg, "reconnected") ? true : Core::getInstance()->getSessionManager()->shiftSessionSaved($player));
			if (!$player->hasGameSessionSaved()) $player->setGameSessionSaved($this->isRelated($connection->getFrom()) ? Core::getInstance()->getSessionManager()->shiftGameSessionSaved($player) : true);
			$pk = new PlayerTransferCompletePacket(["player" => $player->getName(), "xuid" => $player->getXuid()]);
			$pk->queue();
		} else {
			$player->setSessionSaved();
			$player->setGameSessionSaved();
			$pk = new PlayerConnectPacket(["player" => $player->getName(), "xuid" => $player->getXuid()]);
			$pk->queue();
		}
		/**if($this->isBeingSummoned($player)){
			$this->onSummon($player);
		}*/
	}

	public function onDisconnect(Player $player): void {
		$this->getCluster()->removePlayer((int) $player->getXuid());
		if (!$player->isTransferring()) {
			$pk = new PlayerDisconnectPacket(["player" => $player->getName()]);
			$pk->queue();
		}
	}
}
