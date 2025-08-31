<?php

namespace core\network\protocol;

use core\AtPlayer;
use core\Core;
use core\network\Structure;
use core\session\component\SaveableComponent;
use core\session\CoreSession;
use core\user\User;
use core\utils\Utils;
use pmmp\thread\ThreadSafeArray;
use prison\Prison;
use prison\PrisonPlayer;
use prison\PrisonSession;
use skyblock\SkyBlock;
use skyblock\SkyBlockPlayer;
use skyblock\SkyBlockSession;

class DataSyncPacket extends OneWayPacket {

	const PACKET_ID = self::DATA_SYNC;

	public function __construct(array|ThreadSafeArray $data = [], ?int $runtimeId = null, int $created = -1, array|ThreadSafeArray $response = []) {
		if (!empty($data)) {
			$data = [...$data];
			$data = array_merge($data, [
				"identifier" => Core::thisServer()->getIdentifier(),
				"response" => $data["response"] ?? false,
			]);
			$data = ThreadSafeArray::fromArray($data);
		}
		parent::__construct($data, $runtimeId, $created, $response);
	}

	public function verifyHandle(): bool {
		$data = $this->getPacketData();
		return isset($data["identifier"], Structure::SOCKET_PORTS[$data["identifier"]], $data["xuid"], $data["schema"], $data["table"], $data["response"], $data["lastUpdate"], $data["data"]);
	}

	public function handle(ConnectPacketHandler $handler): void {
		$pkData = $this->getPacketData();
		$identifier = $pkData["identifier"];
		$xuid = $pkData["xuid"];
		$schema = $pkData["schema"];
		$table = $pkData["table"];
		$isResponse = $pkData["response"];
		$lastUpdate = $pkData["lastUpdate"];
		$existingData = $pkData["data"] ?? [];
		if ($existingData instanceof ThreadSafeArray) $existingData = Utils::threadSafeToArray($existingData);
		if (isset($existingData["lastUpdate"])) unset($existingData["lastUpdate"]);
		$rid = $pkData["rid"] ?? null;
		$qid = $pkData["qid"] ?? null;

		Core::getInstance()->getUserPool()->useUser($xuid, function (User $user) use ($identifier, $xuid, $schema, $table, $isResponse, $lastUpdate, $existingData, $qid, $rid) {
			/** @var AtPlayer|SkyBlockPlayer|PrisonPlayer */
			$player = $user->getPlayer();
			if (!$user->valid() || !$user->validPlayer() || is_null($player)) {
				$sessionManager = match ($schema) {
					"core" => Core::getInstance()->getSessionManager(),
					"skyblock_test", "skyblock_1" => SkyBlock::getInstance()->getSessionManager(),
					"prison_1", "prison_test" => Prison::getInstance()->getSessionManager(),
					default => null
				};
				if (is_null($sessionManager)) {
					if ($isResponse) return;
					(new DataSyncPacket([
						"identifier" => $identifier,
						"xuid" => $xuid,
						"schema" => $schema,
						"table" => $table,
						"response" => true,
						"data" => [],
						"lastUpdate" => -1
					]))->queue();
					return;
				}
				$sessionManager->useSession($user, function (CoreSession|SkyBlockSession|PrisonSession $session) use ($identifier, $xuid, $schema, $table, $isResponse, $lastUpdate, $existingData, $qid, $rid) {
					[$componentName, $_] = explode(":", $rid, 2);
					$component = $session->getComponent($componentName);
					if (!$component instanceof SaveableComponent) {
						if ($isResponse) return;
						(new DataSyncPacket([
							"identifier" => $identifier,
							"xuid" => $xuid,
							"schema" => $schema,
							"table" => $table,
							"response" => true,
							"data" => [],
							"lastUpdate" => microtime(true)
						]))->queue();
						return;
					}
					$cLastUpdate = $component->getLastUpdateTime();
					if ($isResponse) {
						if ($cLastUpdate <= $lastUpdate) $session->getSessionManager()->processSyncReturn($rid, $qid, $existingData);
						else {
							(new DataSyncPacket([
								"identifier" => $identifier,
								"xuid" => $xuid,
								"schema" => $schema,
								"table" => $table,
								"response" => true,
								"data" => $component->getSerializedData(),
								"lastUpdate" => $cLastUpdate
							]))->queue();
						}
					} else {
						if ($cLastUpdate > $lastUpdate) {
							(new DataSyncPacket([
								"identifier" => $identifier,
								"xuid" => $xuid,
								"schema" => $schema,
								"table" => $table,
								"response" => true,
								"data" => $component->getSerializedData(),
								"lastUpdate" => $cLastUpdate
							]))->queue();
						} else {
							$session->getSessionManager()->processSyncReturn($rid, $qid, $existingData);
							(new DataSyncPacket([
								"identifier" => $identifier,
								"xuid" => $xuid,
								"schema" => $schema,
								"table" => $table,
								"response" => true,
								"data" => $existingData,
								"lastUpdate" => microtime(true)
							]))->queue();
						}
					}
				}, true);
			} else {
				$session = match ($schema) {
					"core" => $player->getSession(),
					default => $player->getGameSession()
				};
				if (is_null($session)) {
					if ($isResponse) return;
					(new DataSyncPacket([
						"identifier" => $identifier,
						"xuid" => $xuid,
						"schema" => $schema,
						"table" => $table,
						"response" => true,
						"data" => [],
						"lastUpdate" => -1
					]))->queue();
					return;
				}
				[$componentName, $_] = explode(":", $rid, 2);
				$component = $session->getComponent($componentName);
				if (!$component instanceof SaveableComponent) {
					if ($isResponse) return;
					(new DataSyncPacket([
						"identifier" => $identifier,
						"xuid" => $xuid,
						"schema" => $schema,
						"table" => $table,
						"response" => true,
						"data" => [],
						"lastUpdate" => microtime(true)
					]))->queue();
					return;
				}
				$cLastUpdate = $component->getLastUpdateTime();
				if ($isResponse) {
					if ($cLastUpdate <= $lastUpdate) $session->getSessionManager()->processSyncReturn($rid, $qid, $existingData);
					else {
						(new DataSyncPacket([
							"identifier" => $identifier,
							"xuid" => $xuid,
							"schema" => $schema,
							"table" => $table,
							"response" => true,
							"data" => $component->getSerializedData(),
							"lastUpdate" => $cLastUpdate
						]))->queue();
					}
				} else {
					if ($cLastUpdate > $lastUpdate) {
						(new DataSyncPacket([
							"identifier" => $identifier,
							"xuid" => $xuid,
							"schema" => $schema,
							"table" => $table,
							"response" => true,
							"data" => $component->getSerializedData(),
							"lastUpdate" => $cLastUpdate
						]))->queue();
					} else {
						$session->getSessionManager()->processSyncReturn($rid, $qid, $existingData);
						(new DataSyncPacket([
							"identifier" => $identifier,
							"xuid" => $xuid,
							"schema" => $schema,
							"table" => $table,
							"response" => true,
							"data" => $existingData,
							"lastUpdate" => microtime(true)
						]))->queue();
					}
				}
			}
		});
	}
}
