<?php

namespace core\staff;

use pocketmine\Server;
use pocketmine\network\mcpe\protocol\{
	LevelSoundEventPacket,
	types\LevelSoundEvent
};
use core\AtPlayer as Player;
use pocketmine\world\{
	Position,
	particle\HugeExplodeSeedParticle
};

use core\Core;
use core\session\PlayerSession;
use core\session\component\{
	ComponentRequest,
	SaveableComponent
};
use core\session\mysqli\data\MySqlQuery;
use core\session\stray\StrayRequest;
use core\staff\entry\{
	BanEntry,
	BanManager,
	MuteEntry,
	IPBanEntry,
	DeviceBanEntry,
	MuteManager,
	WarnManager,
	WarnEntry
};
use core\staff\utils\{
	Watchlist
};
use core\utils\TextFormat;

use alemiz\sga\StarGateAtlantis;
use alemiz\sga\protocol\{
	PlayerPingRequestPacket,
	PlayerPingResponsePacket
};
use core\staff\anticheat\session\SessionManager;
use core\user\User;
use pocketmine\network\mcpe\NetworkBroadcastUtils;

class StaffComponent extends SaveableComponent {

	const LIMIT_BPS = 10;
	const LIMIT_CPS = 16;
	//const LIMIT_SPEED = 7.4;
	const LIMIT_SPEED = 20;
	const LIMIT_REACH = 6.02;

	const MAX_WARNS_MINUTE = 10;

	public array $bps = [];
	public array $cps = [];
	public array $displayCPS = [];
	public array $reach = [];
	public array $speed = [];

	public float $bps_current = 0;
	public int $cps_current = 0;

	public ?Position $last_position = null;

	public int $ping = 10;

	public int $ticks = 0;

	//Staff only stuff//
	public int $punchTimer = 0;
	public string $lastPunched = "";
	public bool $inAirFromPunch = false;
	public float $punched = 0; //time last punched

	public bool $staffChat = false;
	public bool $anticheat = true;
	public string $anticheatServer = "";

	public Watchlist $watchlist;
	public WarnManager $warnManager;
	public BanManager $banManager;
	public MuteManager $muteManager;

	public function __construct(PlayerSession $session) {
		parent::__construct($session);

		$player = $this->getPlayer();
		if (!$player instanceof Player) {
			$this->last_position = new Position(0, 0, 0, Server::getInstance()->getWorldManager()->getDefaultWorld());
		} else {
			$this->last_position = $player->getPosition();
		}

		$this->watchlist = new Watchlist($player?->getName() ?? $this->getGamertag());

		$this->warnManager = new WarnManager($this->getUser());
		$this->banManager = new BanManager($this->getUser());
		$this->muteManager = new MuteManager($this->getUser());
	}

	public function tick(): void {
		$this->ticks++;
		$player = $this->getPlayer();
		if ($player !== null && $player->onGround && $this->inAirFromPunch() == microtime(true) - $this->punched > 3) {
			$this->inAirFromPunch = false;
		}
		if ($player === null) return;

		if ($this->ticks % 4 !== 0) return;

		if ($this->punchTimer > 0) {
			$this->punchTimer--;
		}

		if ($player->isVanished()) {
			$bold = $this->ticks % 2 == 0 ? TextFormat::BOLD : "";
			$player->sendActionBarMessage(TextFormat::YELLOW . "You are in " . TextFormat::RED . $bold . "VANISH" . TextFormat::RESET . TextFormat::YELLOW . " mode!");
		}

		if ($this->ticks % 20 == 0) {
			if ($player->isFromProxy()) {
				$packet = new PlayerPingRequestPacket();
				$packet->setPlayerName($this->getUser()->getGamertag());
				StarGateAtlantis::getInstance()->getDefaultClient()?->responsePacket($packet)->whenComplete(function (PlayerPingResponsePacket $packet): void {
					$this->setPing($packet->getUpstreamPing());
				});
			} else {
				$this->setPing($player->getNetworkSession()->getPing());
			}
		}

		$this->getWatchlist()->exhaust();
	}

	public function getDisplayCPS(): float {
		if (is_null($this->getPlayer())) return 0;
		return $this->getPlayer()->getAntiCheatSession()?->getCPS()[0] ?? 0;
	}

	public function getPing(): int {
		return $this->ping;
	}

	/**
	 * @internal
	 */
	public function setPing(int $ping): void {
		$this->ping = $ping;
	}

	public function canBePunched(Player $trying): bool {
		$player = $this->getPlayer();
		if ($player === null) return false;
		if (!$player->isStaff()) {
			return false;
		}
		if (!$trying->isStaff() && $trying->getRank() != "enderdragon") {
			return false;
		}
		if ($this->punchTimer > 0) {
			$trying->sendMessage(TextFormat::RI . "This staff member can be punched again in " . TextFormat::WHITE . gmdate("i:s", $this->punchTimer));
			return false;
		}
		return true;
	}

	public function canPunchBack(Player $player): bool {
		return $this->getLastPunched() == $player->getName();
	}

	public function punch(Player $player): void {
		if (!$this->canBePunched($player)) {
			return;
		}
		$this->punchTimer = 60;
		$this->lastPunched = $player->getName();
		$this->inAirFromPunch = true;
		$this->punched = microtime(true);

		$staff = $this->getPlayer();
		$player->sendMessage(TextFormat::GI . "Woah! " . TextFormat::YELLOW . $staff->getName() . TextFormat::GRAY . " was sent " . TextFormat::WHITE . TextFormat::BOLD . "FLYING");
		$staff->sendMessage(TextFormat::YI . "You were sent flying by " . $player->getName() . "!");

		$staff->getWorld()->addParticle($staff->getPosition(), new HugeExplodeSeedParticle());
		NetworkBroadcastUtils::broadcastPackets($staff->getViewers(), [LevelSoundEventPacket::create(LevelSoundEvent::EXPLODE, $staff->getPosition(), -1, ":", false, false, -1)]);

		$dv = $player->getDirectionVector();
		$staff->knockback($dv->x, $dv->z, 1.75);
	}

	public function getLastPunched(): string {
		return $this->lastPunched;
	}

	public function punchBack(Player $player): void {
		$pl = $this->getPlayer();
		if ($pl instanceof Player && $this->canPunchBack($player)) {
			if (!$pl->isStaff()) return;
			$player->getWorld()->addParticle($player->getPosition(), new HugeExplodeSeedParticle());
			NetworkBroadcastUtils::broadcastPackets($player->getViewers(), [LevelSoundEventPacket::create(LevelSoundEvent::EXPLODE, $player->getPosition(), -1, ":", false, false, -1)]);

			$dv = $pl->getDirectionVector();
			$player->knockback($dv->x, $dv->z, 1.95);
			if ($player->isStaff()) {
				$ses = $player->getSession()->getStaff();
				$ses->lastPunched = $pl->getName();
				$ses->punchTimer = 60;
				$ses->inAirFromPunch = true;
				$ses->punched = microtime(true);
			}
			$player->sendMessage(TextFormat::YI . "You got punched back by " . TextFormat::YELLOW . $pl->getName() . "!");
			$pl->sendMessage(TextFormat::YI . "You punched " . TextFormat::AQUA . $player->getName() . TextFormat::GRAY . " back! Maybe that'll teach them a lesson...");
			$this->lastPunched = "";
		}
	}

	public function inAirFromPunch(): bool {
		return $this->inAirFromPunch;
	}

	public function inStaffChat(): bool {
		return $this->staffChat;
	}

	public function toggleStaffChat(): bool {
		return $this->staffChat = !$this->staffChat;
	}

	public function inAnticheat(): bool { //todo: toggle for different servers
		return $this->anticheat;
	}

	public function toggleAnticheat(): bool {
		return $this->anticheat = !$this->anticheat;
	}

	public function getWatchlist(): Watchlist {
		return $this->watchlist;
	}

	public function getWarnManager(): WarnManager {
		return $this->warnManager;
	}

	public function getBanManager(): BanManager {
		return $this->banManager;
	}

	public function getMuteManager(): MuteManager {
		return $this->muteManager;
	}

	public function getName(): string {
		return "staff";
	}

	public function createTables(): void {
		$db = $this->getSession()->getSessionManager()->getDatabase();
		foreach (
			[
				"CREATE TABLE IF NOT EXISTS bans(
					id BIGINT(16) NOT NULL,
					`type` INT NOT NULL DEFAULT 0,
					`by` BIGINT(16) NOT NULL,
					`when` INT NOT NULL,
					reason VARCHAR(120) NOT NULL,
					identifier VARCHAR(20) NOT NULL,
					until INT NOT NULL,
					revoked TINYINT(1) NOT NULL DEFAULT 0,
					PRIMARY KEY (id, `when`)
				)",
				"CREATE TABLE IF NOT EXISTS mutes(
					xuid BIGINT(16) NOT NULL,
					`by` BIGINT(16) NOT NULL,
					`when` INT NOT NULL,
					reason VARCHAR(120) NOT NULL,
					identifier VARCHAR(20) NOT NULL,
					until INT NOT NULL,
					revoked TINYINT(1) NOT NULL DEFAULT 0,
					PRIMARY KEY (xuid, `when`)
				)",
				"CREATE TABLE IF NOT EXISTS warns(
					xuid VARCHAR(36) NOT NULL,
					`by` BIGINT(16) NOT NULL,
					`when` INT NOT NULL,
					reason VARCHAR(120) NOT NULL,
					`type` INT NOT NULL DEFAULT 0,
					severe TINYINT(1) NOT NULL DEFAULT 0,
					identifier VARCHAR(20) NOT NULL,
					revoked TINYINT(1) NOT NULL DEFAULT 0,
					PRIMARY KEY (xuid, `when`)
				)"
			] as $query
		) $db->query($query);
	}

	public function loadAsync(): void {
		// Loading will be initiated by the network component
		// This is to ensure that the staff component is loaded after the player's network data is available
		parent::loadAsync();
	}

	public function load(): void {
		$request = new ComponentRequest($this->getXuid(), $this->getName(), [
			new MySqlQuery("ban", "SELECT * FROM bans WHERE id IN (?, ?, ?)", [$this->getXuid(), $this->getPlayer()?->getDeviceId() ?? "", $this->getPlayer()?->getIp() ?? ""]),
			new MySqlQuery("mute", "SELECT * FROM mutes WHERE xuid=?", [$this->getXuid()]),
			new MySqlQuery("warnings", "SELECT * FROM warns WHERE xuid=?", [$this->getXuid()]),
		]);
		$this->newRequest($request, ComponentRequest::TYPE_LOAD);
	}

	public function finishLoadAsync(?ComponentRequest $request = null): void {
		$warnings = $request->getQuery("warnings")->getResult();
		$rows = (array) $warnings->getRows();
		foreach ($rows as $warning) {
			$this->getWarnManager()->addWarn(new WarnEntry($this->getUser(), $warning["by"], $warning["reason"], $warning["identifier"], $warning["when"], $warning["type"], (bool) $warning["severe"]));
		}

		$ban = $request->getQuery("ban")->getResult();
		$rows = (array) $ban->getRows();
		foreach ($rows as $data) {
			$this->getBanManager()->addBan(new BanEntry($data["id"], $data["by"], $data["reason"], $data["identifier"], $data["when"], $data["until"], (bool) $data["revoked"], $data["type"]));
		}
		if ($this->getBanManager()->isBanned() && !is_null($ban = $this->getBanManager()->getRecentBan())) {
			$byUser = $ban->getByUser();
			if ($byUser instanceof User) $this->getPlayer()?->kickPlayer($byUser->getGamertag(), TextFormat::RED . "You were banned!" . PHP_EOL .
				TextFormat::RED . "Reason: " . TextFormat::YELLOW . $ban->getReason() . PHP_EOL .
				TextFormat::RED . "Time Left: " . TextFormat::YELLOW . $ban->getFormattedTimeLeft() . PHP_EOL .
				TextFormat::RED . "Appeal for an unban at " . TextFormat::YELLOW . "avengetech.net/discord");
			else $byUser->onCompletion(function (User $byUser) use ($ban): void {
				$this->getPlayer()?->kickPlayer($byUser->getGamertag(), TextFormat::RED . "You were banned!" . PHP_EOL .
					TextFormat::RED . "Reason: " . TextFormat::YELLOW . $ban->getReason() . PHP_EOL .
					TextFormat::RED . "Time Left: " . TextFormat::YELLOW . $ban->getFormattedTimeLeft() . PHP_EOL .
					TextFormat::RED . "Appeal for an unban at " . TextFormat::YELLOW . "avengetech.net/discord");
			}, fn() => null);
		}

		$mute = $request->getQuery("mute")->getResult();
		$rows = (array) $mute->getRows();
		foreach ($rows as $data) {
			$this->getMuteManager()->addMute(new MuteEntry($this->getUser(), $data["by"], $data["reason"], $data["identifier"], $data["when"], $data["until"], (bool) $data["revoked"]));
		}

		parent::finishLoadAsync();
	}

	public function saveAsync(): void {
		$this->finishSaveAsync(); //no actual saving here.. everything needs to save as it's changed to work multiserver
	}

	public function getSerializedData(): array {
		$warnings = [];
		foreach ($this->warnManager->getWarns() as $warn) {
			$warnings[] = [
				"xuid" => $warn->getUser()->getXuid(),
				"by" => $warn->getBy(),
				"reason" => $warn->getReason(),
				"when" => $warn->getWhen(),
				"severe" => $warn->isSevere(),
				"identifier" => $warn->getIdentifier(),
				"type" => $warn->getType()
			];
		}
		$bans = [];
		foreach ($this->banManager->getBans() as $ban) {
			$bans[] = [
				"id" => $ban->getId(),
				"type" => $ban->getType(),
				"by" => $ban->getBy(),
				"when" => $ban->getWhen(),
				"reason" => $ban->getReason(),
				"identifier" => $ban->getIdentifier(),
				"until" => $ban->getUntil(),
				"revoked" => $ban->isRevoked()
			];
		}
		$mutes = [];
		foreach ($this->muteManager->getMutes() as $mute) {
			$mutes[] = [
				"xuid" => $mute->getUser()->getXuid(),
				"by" => $mute->getBy(),
				"when" => $mute->getWhen(),
				"reason" => $mute->getReason(),
				"identifier" => $mute->getIdentifier(),
				"until" => $mute->getUntil(),
				"revoked" => $mute->isRevoked() ? 1 : 0
			];
		}
		return [
			"warnings" => $warnings,
			"bans" => $bans,
			"mutes" => $mutes
		];
	}

	public function applySerializedData(array $data): void {
		$warnings = $data["warnings"];
		foreach ($warnings as $warning) {
			$this->getWarnManager()->addWarn(new WarnEntry($this->getUser(), $warning["warned_by"], $warning["reason"], $warning["identifier"], $warning["warned_when"], $warning["wtype"], (bool) $warning["severe"]));
		}

		$ban = $data["bans"];
		foreach ($ban as $data) {
			$this->getBanManager()->addBan(new BanEntry($data["id"], $data["by"], $data["reason"], $data["identifier"], $data["when"], $data["until"], (bool) $data["revoked"], $data["type"]));
		}

		$mute = $data["mutes"];
		foreach ($mute as $data) {
			$this->getMuteManager()->addMute(new MuteEntry($this->getUser(), $data["by"], $data["when"], $data["reason"], $data["identifier"], $data["until"], (bool) $data["revoked"]));
		}
	}
}
