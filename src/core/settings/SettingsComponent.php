<?php

namespace core\settings;

use core\session\PlayerSession;
use core\session\component\{
	ComponentRequest,
	SaveableComponent
};
use core\session\mysqli\data\MySqlQuery;
use core\utils\Version;

class SettingsComponent extends SaveableComponent {

	public Version $version;
	public array $settings = [];

	public function __construct(
		PlayerSession $session,
		public Version $latestVersion,
		public array $defaultSettings = [],
		public array $settingUpdates = []
	) {
		parent::__construct($session);
	}

	public function getName(): string {
		return "settings";
	}

	public function getLatestVersion(): Version {
		return $this->latestVersion;
	}

	public function getVersion(): Version {
		return $this->version;
	}

	public function setVersion(Version $version): void {
		$this->version = $version;
		$this->setChanged();
	}

	public function getSettings(): array {
		return $this->settings;
	}

	public function getDefaultSettings(): array {
		return $this->defaultSettings;
	}

	public function getSettingUpdates(): array {
		return $this->settingUpdates;
	}

	public function setSettings(array $settings): void {
		$this->settings = $settings;
		$this->setChanged();
	}

	public function setSetting(int $flag, mixed $value): void {
		$prev = isset($this->settings[$flag]) ? $this->settings[$flag] : null;
		$this->settings[$flag] = $value;
		if ($prev !== $value) $this->setChanged();
	}

	public function getSetting(int $flag): mixed {
		return $this->settings[$flag] ?? false;
	}

	public function getEncodedSettings(): string {
		return json_encode($this->settings);
	}

	public function createTables(): void {
		$db = $this->getSession()->getSessionManager()->getDatabase();
		foreach ([
			"CREATE TABLE IF NOT EXISTS settings(xuid BIGINT(16) NOT NULL UNIQUE, version VARCHAR(10) NOT NULL, settings TEXT NOT NULL)",
		] as $query) $db->query($query);
	}

	public function loadAsync(): void {
		$request = new ComponentRequest($this->getXuid(), $this->getName(), new MySqlQuery("main", "SELECT * FROM settings WHERE xuid=?", [$this->getXuid()]));
		$this->newRequest($request, ComponentRequest::TYPE_LOAD);
		parent::loadAsync();
	}

	public function finishLoadAsync(?ComponentRequest $request = null): void {
		$result = $request->getQuery()->getResult();
		$rows = $result->getRows();
		if (count($rows) > 0) {
			$data = array_shift($rows);
			$this->version = Version::fromString($data["version"]);
			$this->settings = json_decode($data["settings"], true);

			if (!$this->getVersion()->equals($this->getLatestVersion()) && !$this->getVersion()->newerThan($this->getLatestVersion())) {
				//echo "settings version outdated! updating with new settings...", PHP_EOL;
				foreach ($this->getSettingUpdates() as $vstr => $updates) {
					$version = Version::fromString($vstr);
					if ($version->newerThan($this->getVersion())) {
						foreach ($updates as $flag => $value) {
							$this->setSetting($flag, $value);
							//echo "set $flag setting to $value", PHP_EOL;
						}
					}
				}
				$this->setVersion($this->getLatestVersion());
			}
		} else {
			$this->setVersion($this->getLatestVersion());
			$this->setSettings($this->getDefaultSettings());
		}

		parent::finishLoadAsync($request);
	}

	public function verifyChange(): bool {
		$verify = $this->getChangeVerify();
		return $this->settings !== $verify["settings"];
	}

	public function saveAsync(): void {
		if (!$this->hasChanged() || !$this->isLoaded()) return;

		$this->setChangeVerify(["settings" => $this->settings]);

		$request = new ComponentRequest($this->getXuid(), $this->getName(), new MySqlQuery("main", "INSERT INTO settings(xuid, version, settings) VALUES(?, ?, ?) ON DUPLICATE KEY UPDATE version=VALUES(version), settings=VALUES(settings)", [$this->getXuid(), $this->getVersion()->toString(), $this->getEncodedSettings()]));
		$this->newRequest($request, ComponentRequest::TYPE_SAVE);
		parent::saveAsync();
	}

	public function save(): bool {
		if (!$this->hasChanged() || !$this->isLoaded()) return false;

		$xuid = $this->getXuid();
		$version = $this->getVersion()->toString();
		$settings = $this->getEncodedSettings();

		$db = $this->getSession()->getSessionManager()->getDatabase();
		$stmt = $db->prepare("INSERT INTO settings(xuid, version, settings) VALUES(?, ?, ?) ON DUPLICATE KEY UPDATE version=VALUES(version), settings=VALUES(settings)");
		$stmt->bind_param("iss", $xuid, $version, $settings);
		$stmt->execute();
		$stmt->close();

		return parent::save();
	}

	public function getSerializedData(): array
	{
		return [
			"version" => $this->getVersion()->toString(),
			"settings" => $this->getEncodedSettings()
		];
	}

	public function applySerializedData(array $data): void
	{
		$this->version = Version::fromString($data["version"]);
		$this->settings = json_decode($data["settings"], true);
	}
}
