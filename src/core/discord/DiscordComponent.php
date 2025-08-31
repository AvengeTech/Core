<?php

namespace core\discord;

use core\{
	Core,
	AtPlayer as Player
};
use core\session\component\{
	ComponentRequest,
	SaveableComponent
};
use core\session\mysqli\data\{
	MySqlQuery,
	MySqlRequest
};
use core\utils\{
	CapeData,
};

class DiscordComponent extends SaveableComponent {

	const CAPE_VERIFIED = "atmc";

	public int $snowflake = 0;
	public int $code = 0;
	public bool $verified = false;

	public int $pendingCode = 0;

	public function getName(): string {
		return "discord";
	}

	public function getSnowflake(): int {
		return $this->snowflake;
	}

	public function getCode(): int {
		return $this->code;
	}

	public function setCode(int $code): void {
		$this->code = $code;
		$this->setChanged();
	}

	/*
	 * Verifies whether code has been used or not
	 */
	public function verifyPendingCode(int $code): void {
		$this->pendingCode = $code;
		Core::getInstance()->getSessionManager()->sendStrayRequest(new MySqlRequest(
			$code,
			new MySqlQuery(
				"main",
				"SELECT EXISTS(SELECT code FROM discord_verify WHERE code=?)",
				[$code]
			)
		), function (MySqlRequest $request): void {
			$result = (array) $request->getQuery()->getResult()->getRows()[0];
			$exists = (bool) array_shift($result);
			$this->verifyPendingCodeReturn($exists);
		});
	}

	public function getPendingCode(): int {
		return $this->pendingCode;
	}

	public function verifyPendingCodeReturn(bool $exists): void {
		if ($exists) {
			$this->generateCode();
		} else {
			$this->setCode($this->getPendingCode());
			$this->saveAsync();
		}
	}

	public function generateCode(): int {
		$code = mt_rand(1000000, 9999999);
		$this->verifyPendingCode($code);
		return $code;
	}

	public function unlink() : void{
		Core::getInstance()->getSessionManager()->sendStrayRequest(new MySqlRequest(
			"unlink_disc_" . ($xuid = $this->getXuid()),
			new MySqlQuery(
				"main",
				"DELETE FROM discord_verify WHERE xuid=?",
				[$xuid]
			)
		), function (MySqlRequest $request): void {
			$this->verified = false;
			$this->snowflake = 0;
			$this->generateCode();
		});
	}

	public function isVerified(): bool {
		return $this->verified;
	}

	public function verify(\Closure $closure): void {
		Core::getInstance()->getSessionManager()->sendStrayRequest(new MySqlRequest(
			"discord_verify_check_" . $this->getXuid(),
			new MySqlQuery(
				"main",
				"SELECT snowflake, verified FROM discord_verify WHERE xuid=?",
				[$this->getXuid()]
			)
		), function (MySqlRequest $request) use ($closure): void {
			$result = (array) $request->getQuery()->getResult()->getRows()[0];
			$snowflake = $this->snowflake = $result["snowflake"];
			$verified = $this->verified = (bool) $result["verified"];
			$closure($snowflake, $verified);
		});
	}

	public function createTables(): void {
		$db = $this->getSession()->getSessionManager()->getDatabase();
		foreach ([
			"CREATE TABLE IF NOT EXISTS discord_verify(xuid BIGINT(16) NOT NULL UNIQUE, snowflake BIGINT(18) NOT NULL, code INT(7) NOT NULL, verified TINYINT(1) NOT NULL DEFAULT '0', capeon TINYINT(1) NOT NULL DEFAULT 0);",
			"CREATE TABLE IF NOT EXISTS discord_commands(snowflake BIGINT(18) NOT NULL, identifier VARCHAR(16) NOT NULL, command VARCHAR(256) NOT NULL);",
		] as $query) $db->query($query);
	}

	public function loadAsync(): void {
		$request = new ComponentRequest($this->getXuid(), $this->getName(), new MySqlQuery("main", "SELECT * FROM discord_verify WHERE xuid=?", [$this->getXuid()]));
		$this->newRequest($request, ComponentRequest::TYPE_LOAD);
		parent::loadAsync();
	}

	public function finishLoadAsync(?ComponentRequest $request = null): void {
		$result = $request->getQuery()->getResult();
		$rows = (array) $result->getRows();
		if (count($rows) > 0) {
			$data = array_shift($rows);
			$this->snowflake = $data["snowflake"];
			$this->code = $data["code"];
			$this->verified = (bool) $data["verified"];
		} else {
			$this->generateCode();
		}
		parent::finishLoadAsync($request);
	}

	public function saveAsync(): void {
		if (!$this->hasChanged() || !$this->isLoaded()) return;

		$request = new ComponentRequest(
			$this->getXuid(),
			$this->getName(),
			new MySqlQuery(
				"main",
				"INSERT INTO discord_verify(xuid, snowflake, code, verified, capeon) VALUES(?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE snowflake=VALUES(snowflake), verified=VALUES(verified), capeon=VALUES(capeon)",
				[$this->getXuid(), $this->getSnowflake(), $this->getCode(), (int) $this->isVerified(), 0]
			)
		);
		$this->newRequest($request, ComponentRequest::TYPE_SAVE);
		parent::saveAsync();
	}

	public function save(): bool {
		if (!$this->hasChanged() || !$this->isLoaded()) return false;

		$xuid = $this->getXuid();
		$snowflake = $this->getSnowflake();
		$code = $this->getCode();
		$verified = (int) $this->isVerified();
		$capeon = 0; //lazy rn

		$db = $this->getSession()->getSessionManager()->getDatabase();

		$stmt = $db->prepare("INSERT INTO discord_verify(xuid, snowflake, code, verified, capeon) VALUES(?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE snowflake=VALUES(snowflake), verified=VALUES(verified), capeon=VALUES(capeon)");
		$stmt->bind_param("iiiii", $xuid, $snowflake, $code, $verified, $capeon);
		$stmt->execute();
		$stmt->close();
		return parent::save();
	}

	public function getSerializedData(): array {
		return [
			"snowflake" => $this->getSnowflake(),
			"code" => $this->getCode(),
			"verified" => (int) $this->isVerified(),
			"capeon" => 0
		];
	}

	public function applySerializedData(array $data): void {
		$this->snowflake = $data["snowflake"];
		$this->code = $data["code"];
		$this->verified = (bool) $data["verified"];
	}
}
