<?php

namespace core\staff\anticheat\session;

use core\Core;
use core\AtPlayer as Player;
use core\staff\anticheat\utils\Devices;
use core\utils\TextFormat;
use pocketmine\Server;
use core\discord\objects\Embed;
use core\discord\objects\Field;
use core\discord\objects\Footer;
use core\discord\objects\Post;
use core\discord\objects\Webhook;
use core\network\protocol\StaffAnticheatPacket;
use core\settings\GlobalSettings;
use core\staff\anticheat\AntiCheat;
use core\user\User;
use pocketmine\entity\Entity;
use pocketmine\event\Listener;

use skyblock\SkyBlockPlayer;

class Session implements Listener {

	public float $time = 0;
	public array $lastAlert = [];

	public array $clickData = [];

	public bool $sneaking = false;
	public bool $invOpen = false;
	public float $invOpenTime = 0;

	public Entity $lastAttackedBy;
	public float $lastAttacked = 0;
	public float $lastTotalHitTick = 0;
	public array $hitTicks = [];

	public int $inputMode = -1;

	public float $reeltime = -1;
	public float $reelflag = 0;
	public float $fishflag = 0;

	/* */
	public function __construct(public Player $player, public SessionManager $sessionManager) {
		$this->player = $player;
		$this->sessionManager = $sessionManager;
	}
	/* */

	public function openedInv(): void {
		$this->invOpen = true;
		$this->invOpenTime = $this->time;
	}

	public function closedInv(): void {
		$this->invOpen = false;
		$this->invOpenTime = 0;
	}

	public function isInvOpen(): bool {
		return $this->invOpen;
	}

	public function justOpenedInv(): bool {
		return $this->time - $this->invOpenTime < 0.5;
	}

	/* NETWORK FUNCTIONS */
	public function getPing(): int {
		return $this->player->getSession()?->getStaff()->getPing() ?? 10;
	}

	public function getInputMode(): int {
		return $this->inputMode;
	}

	public function disconnected() {
		$this->sessionManager->unregisterFor($this->player);
	}
	
	public function setSneaking(bool $sneaking = true) {
		$this->sneaking = $sneaking;
		$this->player->setSneaking($sneaking);
	}

	public function isSneaking(): bool {
		return $this->sneaking;
	}

	public function getDevice(): int {
		return $this->player->getPlayerInfo()->getExtraData()["DeviceOS"];
	}
	/* */

	/* HIT REGISTRY FUNCTIONS */
	public function canHit(Entity $what): bool {
		$this->hitTicks[$what->getId()] ??= 0;
		return $this->time - $this->hitTicks[$what->getId()] > 0.485;
	}

	public function canBeHit(): bool {
		return $this->time - $this->lastAttacked >= 0.485;
	}

	public function hit(Entity $what) {
		if ($what->getId() == $this->player->getId()) {
			# IMPOSSIBLE THAT THIS FAILED | INSTA-BAN PLAYER
			Core::getInstance()->getStaff()->ban($this->player, new User(-100, AntiCheat::USER_NAME), "Cheating (impossible hits)", 31 * (60 * 60 * 24));
			return;
		}
		$this->hitTicks[$what->getId()] = $this->time;
		$this->lastTotalHitTick = Server::getInstance()->getTick();
	}

	public function attacked(Entity $by) {
		$this->lastAttackedBy = $by;
		$this->lastAttacked = $this->time;
	}

	public function clicked(Entity|Player|null $who = null) {
		$this->clickData[] = $this->time;
		$cpsData = $this->getCPS();
		if (($cpsData[0] >= 17 && $cpsData[1] >= 17 - abs($cpsData[0] - 17))) {
			$this->flag('autoclick', ["cps" => $cpsData[0], "avg" => $cpsData[1]]);
		}
		if (
			$this->player->isLoaded() && !$this->player->isTransferring() &&
			$this->player->getSession()->getSettings()->getSetting(GlobalSettings::CPS_PING_COUNTER) &&
			$this->player->isConnected()
		) {
			$tping = $who instanceof Player ? $who->getSession()?->getStaff()->getPing() ?? ($this->player instanceof SkyBlockPlayer ? ($this->player->getGameSession()?->getCombat()->getCombatMode()?->getHit()?->getSession()?->getStaff()->getPing() ?? "0") : "0") : "0";
			$this->player->sendActionBarMessage(TextFormat::YELLOW . "CPS: " . TextFormat::GOLD . $cpsData[0] . TextFormat::GRAY . " | " . TextFormat::YELLOW . "Ping: " . TextFormat::GREEN . $this->getPing() . "ms" . TextFormat::AQUA . "/" . TextFormat::RED . $tping . "ms");
		}
	}

	/**
	 * @return int[]
	 */
	public function getCPS(): array {
		$cps = [];
		$avg = [];
		foreach ($this->clickData as $k => $time) {
			if ($this->time - $time > 3.99) unset($this->clickData[$k]);
			elseif ($this->time - $time <= 0.99) $cps[] = $time;
			elseif ($this->time - $time <= 3.99) $avg[] = $time;
		}
		foreach ($cps as $t) $avg[] = $t;
		return [count($cps), round(count($avg) / 3.99)];
	}

	public function isMobile(): bool {
		return in_array($this->getDevice(), [1, 2, 14, 4]);
	}

	public function isConsole(): bool {
		return in_array($this->getDevice(), [11, 12, 13]);
	}

	public function reeledRod(): void {
		$timebetween = microtime(true) - $this->reeltime - ($this->getPing() / 1000);
		if ($timebetween < 0.125) $this->reelflag += 1;
		else $this->reelflag = max(0, $this->reelflag - 1.02);

		if ($this->reelflag >= 3.01) {
			$this->flag("AutoFish", [
				"Reaction Speed" => round($timebetween * 1000) . "ms"
			]);
			if ($this->reelflag >= 9.01) {
				$dur = $this->player->getSession()?->getStaff()->getBanManager()->getNextDuration("Cheating") ?? (7 * 86400);
				Core::getInstance()->getStaff()->ban($this->player, new User(-100, AntiCheat::USER_NAME), "AutoFish", $dur);
			}
		}
	}

	public function flag(string $offense, array $data) {
		$this->lastAlert[$offense] ??= 0;
		if ($this->time - $this->lastAlert[$offense] > 3) {
			$this->lastAlert[$offense] = $this->time;
			foreach ($data as $name => $value) {
				unset($data[$name]);
				if (strval(floatval($value)) == $value) $value = strval(round(floatval($value), 3));
				$data[strtoupper($name)] = $value;
			}
			Core::getInstance()->getStaff()->anticheatAlert(TextFormat::RI . TextFormat::YELLOW . ($message = $this->player->getName() . TextFormat::RED . " should be monitored for " . strtoupper($offense) . " " . TextFormat::DARK_RED . "(Data: " . TextFormat::GRAY . str_replace(["\"", ":", ","], ["", ": ", ", "], json_encode(array_merge($data, ["PLATFORM" => str_replace("_", " ", Devices::TRANSLATE_FROM[$this->getDevice()]), "PING" => $this->getPing(), "INPUT" => Devices::INPUT_MODES[$this->inputMode]]))) . TextFormat::DARK_RED . ")"));
			(new StaffAnticheatPacket([
				"message" => $message
			]))->queue();
			$type = Core::getInstance()->getNetwork()->getServerType();
			if ($offense !== "" && in_array($type, ["prison", "skyblock", "pvp"])) {
				$feilds = [];
				foreach ($data as $name => $value) {
					$feilds[] = new Field(strtoupper($name), strval($value), true);
				}
				$post = new Post("", "AntiCheat - " . Core::getInstance()->getNetwork()->getIdentifier(), "[REDACTED]", false, "", [
					new Embed("", "rich", "**" . $this->player->getName() . "** should be monitored!", "", Core::thisServer()->isTestServer() ? "902CAD" : "FF2900", new Footer("OMG CHEATER 😡"), "", "[REDACTED]", null, [
						new Field("Offense", strtoupper($offense), true),
						...$feilds,
						new Field("Platform", str_replace("_", " ", Devices::TRANSLATE_FROM[$this->getDevice()]), true),
						new Field("Input Mode", Devices::INPUT_MODES[$this->inputMode], true),
						new Field("Ping", (int) $this->getPing(), true),
						new Field("TPS", Server::getInstance()->getTicksPerSecond(), true),
						new Field("TPS Avg", Server::getInstance()->getTicksPerSecondAverage(), true),
					])
				]);
				$post->setWebhook(Webhook::getWebhookByName("anticheat-" . $type));
				$post->send();
			}
		}
	}
}
