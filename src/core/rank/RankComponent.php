<?php

namespace core\rank;

use pocketmine\scheduler\ClosureTask;

use core\{
	Core,
	AtPlayer as Player
};
use core\network\data\DataSyncQuery;
use core\network\protocol\DataSyncPacket;
use core\session\component\{
	ComponentRequest,
	ComponentSyncRequest,
	SaveableComponent
};
use core\session\mysqli\data\{
	MySqlQuery,
	MySqlRequest
};
use core\staff\utils\Disguise;
use core\utils\TextFormat;
use core\utils\Utils;
use core\network\Structure as NetworkStructure;
use core\rank\Structure as RS;

class RankComponent extends SaveableComponent {

	public string $rank = "default";

	public int $subcription = 0;
	public int $subSince = -1;

	public string $nick = "";
	public Disguise $disguise;
	public string $customIcon = "";
	public int $nameColor = -1;

	public function getName(): string {
		return "rank";
	}

	public function getRank(): string {
		return $this->rank;
	}

	public function getRankHierarchy(): int {
		return Structure::RANK_HIERARCHY[strtolower($this->getRank())] ?? 0;
	}

	public function hasRank(): bool {
		return $this->getRank() !== "default";
	}

	public function setRank(string $rank = "default"): void {
		$this->rank = $rank;
		$this->setChanged();
	}

	public function getRankIcon(): string {
		$ch = Core::getInstance()->getChat();
		return $this->isDisguiseEnabled() ? $ch->getFormattedRank($this->getDisguise()->getRank()) : (($this->hasSub() && $this->hasCustomIcon()) ? $ch->getEmojiLibrary()->getEmoji($this->getCustomIcon()) : (($this->hasSub() && $this->getRank() == "enderdragon") ? TextFormat::ICON_WARDEN : $ch->getFormattedRank($this->getRank())));
	}

	public function getSubExpiration(bool $formatted = false): int|string {
		return $formatted ? date("m/d/y", $this->subcription) : $this->subcription;
	}

	public function hasSub(): bool {
		return $this->getSubExpiration() >= time();
	}

	public function addSub(int $days): void {
		if (!$this->hasSub()) {
			$this->subcription = time() + ($days * 60 * 60 * 24);
			$this->setSubSince();
			$this->getPlayer()?->updateChatFormat();
			$this->getPlayer()?->updateNametag();
		} else {
			$this->subcription += ($days * 60 * 60 * 24);
		}
		$this->setChanged();
	}

	public function clearSub(): void {
		$this->subcription = 0;
		$this->subSince = -1;
		$this->getPlayer()?->updateChatFormat();
		$this->getPlayer()?->updateNametag();
		$this->setChanged();
	}

	public function getSubSince(bool $formatted = false): int|string {
		return $formatted ? date("m/d/y", $this->subSince) : $this->subSince;
	}

	public function setSubSince(): void {
		$this->subSince = time();
		$this->setChanged();
	}

	public function getNick(): string {
		return $this->nick;
	}

	public function hasNick(): bool {
		return $this->getNick() !== "";
	}

	public function setNick(string $nick = ""): void {
		$this->nick = $nick;
		if ($nick === "") {
			Core::getInstance()->getSessionManager()->sendStrayRequest(new MySqlRequest("nick_exists_" . $nick, new MySqlQuery(
				"main",
				"DELETE FROM nicknames WHERE xuid=?",
				[$this->getXuid()]
			)), function (MySqlRequest $request): void {
			});
		}
	}

	public function nickExists(string $nick, \Closure $whenDone): void {
		Core::getInstance()->getSessionManager()->sendStrayRequest(new MySqlRequest("nick_exists_" . $nick, new MySqlQuery(
			"main",
			"SELECT nick FROM nicknames WHERE nick=?",
			[$nick]
		)), function (MySqlRequest $request) use ($nick, $whenDone): void {
			$rows = $request->getQuery()->getResult()->getRows();
			$whenDone(count($rows) > 0);
		});
	}

	public function trySaveNick(string $nick, \Closure $whenDone): void {
		Core::getInstance()->getSessionManager()->sendStrayRequest(new MySqlRequest("nick_save_attempt_" . $this->getXuid(), new MySqlQuery(
			"main",
			"INSERT INTO nicknames(xuid, nick) VALUES(?, ?) ON DUPLICATE KEY UPDATE nick=VALUES(nick)",
			[
				$this->getXuid(),
				$nick
			]
		)), function (MySqlRequest $request) use ($nick, $whenDone): void {
			$whenDone($request->getQuery()->getResult()->getAffectedRows() > 0);
		});
	}

	public function setCustomIcon(string $icon = ""): void {
		$this->customIcon = $icon;
		if (!$this->hasSub()) $this->customIcon = "";
	}

	public function hasCustomIcon(): bool {
		return $this->customIcon !== "";
	}

	public function getCustomIcon(): string {
		return $this->customIcon;
	}

	public function setRandomDisguise(): void {
		$this->disguise = Disguise::random();
	}

	public function getDisguise(): Disguise {
		if (!$this->hasDisguise()) $this->setRandomDisguise();
		return $this->disguise;
	}

	public function toggleDisguise(): void {
		if (!$this->hasDisguise()) $this->setRandomDisguise();
		$this->disguise->toggle();
	}

	public function isDisguiseEnabled(): bool {
		if (!$this->hasDisguise()) $this->setRandomDisguise();
		return $this->disguise->isEnabled() && $this->getSession()->getPlayer()?->isStaff();
	}

	public function hasDisguise(): bool {
		return isset($this->disguise);
	}

	public function setDisguise(Disguise $disguise): void {
		$this->disguise = $disguise;
		$this->setChanged();
	}

	public function getNameColor(): int {
		return $this->nameColor;
	}

	public function hasNameColor(): bool {
		return $this->nameColor !== -1;
	}

	public function setNameColor(int $color = -1): void {
		$this->nameColor = $color;
		$this->setChanged();
	}

	public function networkChecks(): void {
		$player = $this->getPlayer();
		$server = Core::thisServer();
		$lobby = Core::getInstance()->getNetwork()->getServerManager()->getServerById("lobby-1");

		if (!$player instanceof Player || !$player->isConnected()) return;

		$dtmsg = TextFormat::RED . "The Network is currently down for maintenance and updates." . PHP_EOL .
			TextFormat::GRAY . "Remaining downtime scheduled: {time}" . PHP_EOL .
			TextFormat::YELLOW . "Check our Discord for more information: " . TextFormat::AQUA . "avengetech.net/discord";

		if (!$player->isStaff()) {
			foreach (NetworkStructure::DOWNTIMES as $downtime) {
				if (time() >= $downtime["start"] && time() <= $downtime["end"]) {
					$player->kick(
						str_replace(
							"{time}",
							TextFormat::YELLOW . Utils::getRemainingTimeSimplified($downtime["end"]) . TextFormat::GRAY,
							$downtime["message"] . PHP_EOL . PHP_EOL . $dtmsg
						)
					);
					return;
				}
			}
		}

		if (
			$server->isRestricted() &&
			$server->getRestricted() > RS::RANK_HIERARCHY[$player->getRank()] &&
			!$player->isTier3() &&
			!$server->onWhitelist($player) &&
			(!$server->isSubServer() || !$server->getParentServer()->onWhitelist($player)) &&
			!$server->isBeingSummoned($player)
		) {
			$lobby->delayedTransfer($player, TextFormat::RI . "This server is restricted! You cannot access it without " . $server->restricted . " rank or higher!");
		}
	}

	public function updatePermissions(bool $instant = true, int $delay = 20): void {
		if ($instant) {
			$player = $this->getPlayer();
			if ($player instanceof Player) {
				$player->addAttachment(Core::getInstance(), "core.staff", $player->isStaff($this->getRank()));
				$player->addAttachment(Core::getInstance(), "lobby.staff", $player->isStaff($this->getRank()));
				$player->addAttachment(Core::getInstance(), "prison.staff", $player->isStaff($this->getRank()));
				$player->addAttachment(Core::getInstance(), "skyblock.staff", $player->isStaff($this->getRank()));

				$player->addAttachment(Core::getInstance(), "core.tier3", $player->getRankHierarchy($this->getRank()) >= Rank::HIERARCHY_HEAD_MOD);
				$player->addAttachment(Core::getInstance(), "lobby.tier3", $player->getRankHierarchy($this->getRank()) >= Rank::HIERARCHY_HEAD_MOD);
				$player->addAttachment(Core::getInstance(), "prison.tier3", $player->getRankHierarchy($this->getRank()) >= Rank::HIERARCHY_HEAD_MOD);
				$player->addAttachment(Core::getInstance(), "skyblock.tier3", $player->getRankHierarchy($this->getRank()) >= Rank::HIERARCHY_HEAD_MOD);
			}
		} else {
			Core::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function (): void {
				$this->updatePermissions();
			}), $delay);
		}
	}

	public function createTables(): void {
		$db = $this->getSession()->getSessionManager()->getDatabase();
		foreach ([
			"CREATE TABLE IF NOT EXISTS rank_data(xuid BIGINT(16) NOT NULL UNIQUE, `rank` VARCHAR(16) NOT NULL DEFAULT 'default', subscription INT NOT NULL DEFAULT 0, subSince INT NOT NULL DEFAULT -1, disguiseRank VARCHAR(16) NOT NULL DEFAULT '', namecolor INT NOT NULL DEFAULT -1)",
			"CREATE TABLE IF NOT EXISTS nicknames(xuid BIGINT(16) NOT NULL PRIMARY KEY UNIQUE, nick VARCHAR(15) NOT NULL UNIQUE)",
		] as $query) $db->query($query);
	}

	public function updateTables(): void {
		$db = $this->getSession()->getSessionManager()->getDatabase();
	}

	public function loadAsync(): void {
		$request = new ComponentRequest($this->getXuid(), $this->getName(), [
			new MySqlQuery("main", "SELECT * FROM rank_data WHERE xuid=?", [$this->getXuid()]),
			new MySqlQuery("nick", "SELECT * FROM nicknames WHERE xuid=?", [$this->getXuid()]),
		]);
		$this->newRequest($request, ComponentRequest::TYPE_LOAD);
		parent::loadAsync();
		#$this->requestSync();
	}

	public function requestSync(bool $push = false): void {
		$request = new ComponentSyncRequest($this->getXuid(), $this->getName(), [
			new DataSyncQuery("main", new DataSyncPacket([
				"xuid" => $this->getXuid(),
				"table" => "rank_data",
				"data" => $this->getSerializedData(),
				"lastUpdate" => $this->getLastUpdateTime(),
				"response" => $push
			])),
			new DataSyncQuery("nick", new DataSyncPacket([
				"xuid" => $this->getXuid(),
				"table" => "nicknames",
				"data" => ["nick" => $this->getNick()],
				"lastUpdate" => $this->getLastUpdateTime(),
				"response" => $push
			])),
		]);
		$this->newRequest($request);
		parent::requestSync();
	}

	public function finishSync(?ComponentSyncRequest $request = null): void {
		$result = $request->getQuery()->getResult();
		$rows = (array) $result->getRows();
		if (count($rows) > 0) {
			$data = array_shift($rows);
			if (isset(Structure::RANK_HIERARCHY[$rank = $data["rank"]]))
				$this->rank = $rank;
			$this->subcription = $data["subscription"] ?? 0;
			$this->subSince = $data["subSince"] ?? -1;
			$this->customIcon = $data["disguiseRank"] ?? "";
			$this->nameColor = $data["nameColor"] ?? -1;
		}

		$result = $request->getQuery("nick")->getResult();
		$rows = (array) $result->getRows();
		if (count($rows) > 0) {
			$data = array_shift($rows);
			$this->nick = $data["nick"] ?? "";
		}
		$this->networkChecks();
		$this->updatePermissions();
		$this->getPlayer()?->updateChatFormat();
		$this->getPlayer()?->updateNametagFormat();
		parent::finishSync($request);
	}

	public function finishLoadAsync(?ComponentRequest $request = null): void {
		$result = $request->getQuery()->getResult();
		$rows = (array) $result->getRows();
		if (count($rows) > 0) {
			$data = array_shift($rows);
			if (isset(Structure::RANK_HIERARCHY[$rank = $data["rank"]]))
				$this->rank = $rank;
			$this->subcription = $data["subscription"];
			$this->subSince = $data["subSince"];
			$this->customIcon = $data["disguiserank"];
			$this->nameColor = $data["namecolor"] ?? -1;
		}

		$result = $request->getQuery("nick")->getResult();
		$rows = (array) $result->getRows();
		if (count($rows) > 0) {
			$data = array_shift($rows);
			$this->nick = $data["nick"];
		}
		$this->networkChecks();
		$this->updatePermissions();
		$this->getPlayer()?->updateChatFormat();
		$this->getPlayer()?->updateNametagFormat();
		parent::finishLoadAsync($request);
	}

	public function verifyChange(): bool {
		$verify = $this->getChangeVerify();
		return
			$this->getRank() !== $verify["rank"] ||
			$this->getSubExpiration() !== $verify["subscription"] ||
			$this->getSubSince() !== $verify["subSince"] ||
		$this->getCustomIcon() !== $verify["disguiseRank"] ||
			$this->getNameColor() !== $verify["nameColor"];
	}

	public function saveAsync(): void {
		if (!$this->hasChanged() || !$this->isLoaded()) return;

		$this->setChangeVerify([
			"xuid" => $this->getXuid(),
			"rank" => $this->getRank(),
			"subscription" => $this->getSubExpiration(),
			"subSince" => $this->getSubSince(),
			"disguiseRank" => $this->getCustomIcon(),
			"nameColor" => $this->getNameColor(),
		]);

		$request = new ComponentRequest($this->getXuid(), $this->getName(), new MySqlQuery(
			"main",
			"INSERT INTO rank_data(xuid, `rank`, subscription, subSince, disguiseRank, namecolor) VALUES(?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE `rank`=VALUES(`rank`), subscription=VALUES(subscription), subSince=VALUES(subSince), disguiseRank=VALUES(disguiseRank), namecolor=VALUES(namecolor)",
			[
				$this->getXuid(),
				$this->getRank(),
				$this->getSubExpiration(),
				$this->getSubSince(),
				$this->getCustomIcon(),
				$this->getNameColor(),
			]
		));
		$this->newRequest($request, ComponentRequest::TYPE_SAVE);
		parent::saveAsync();
	}

	public function save(): bool {
		if (!$this->hasChanged() || !$this->isLoaded()) return false;

		$player = $this->getPlayer();
		$xuid = $this->getXuid();
		$rank = $this->getRank();
		$subscription = $this->getSubExpiration();
		$subSince = $this->getSubSince();
		$disguiseRank = $this->getCustomIcon();
		$nameColor = $this->getNameColor();

		$db = $this->getSession()->getSessionManager()->getDatabase();
		$stmt = $db->prepare("INSERT INTO rank_data(xuid, `rank`, subscription, subSince, disguiseRank, namecolor) VALUES(?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE `rank`=VALUES(`rank`), subscription=VALUES(subscription), subSince=VALUES(subSince), disguiseRank=VALUES(disguiseRank), namecolor=VALUES(namecolor)");
		$stmt->bind_param("isiisi", $xuid, $rank, $subscription, $subSince, $disguiseRank, $nameColor);
		$stmt->execute();
		$stmt->close();

		return parent::save();
	}

	public function getSerializedData(): array {
		return [
			"rank" => $this->getRank(),
			"subscription" => $this->getSubExpiration(),
			"subSince" => $this->getSubSince(),
			"disguiseRank" => $this->getCustomIcon(),
			"nameColor" => $this->getNameColor()
		];
	}

	public function applySerializedData(array $data): void {
		if (isset(Structure::RANK_HIERARCHY[$rank = $data["rank"]])) $this->rank = $rank;
		$this->subcription = $data["subscription"];
		$this->subSince = $data["subSince"];
		$this->customIcon = $data["disguiseRank"];
		$this->nameColor = $data["nameColor"] ?? -1;
	}
}
