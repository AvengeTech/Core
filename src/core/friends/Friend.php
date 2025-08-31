<?php

namespace core\friends;

use core\user\User;
use core\utils\Version;

/**
 * @deprecated
 */
class Friend {

	public Version $settingsVersion;

	public function __construct(
		public User $user,
		public User $friend,
		public int $created,
		string $settingsVersion,
		public array $settings,
	) {
		$settingsVersion = $this->settingsVersion = Version::fromString($settingsVersion);
		if (($latest = Version::fromString(Structure::SETTING_VERSION))->newerThan($settingsVersion)) {
			foreach (Structure::SETTING_UPDATES as $ver => $settings) {
				if (Version::fromString($ver)->newerThan($settingsVersion)) {
					foreach ($settings as $key => $setting) {
						$this->settings[$key] = $setting;
					}
				}
			}
			$this->settingsVersion = $latest;
			$this->save();
		}
	}

	public function getUser(): User {
		return $this->user;
	}

	public function getFriend(): User {
		return $this->friend;
	}

	public function getCreated(): int {
		return $this->created;
	}

	public function getCreatedFormatted(): string {
		return date("m/d/y", $this->getCreated());
	}

	public function getSettingsVersion(): Version {
		return $this->settingsVersion;
	}

	public function getSettings(): array {
		return $this->settings;
	}

	public function getSetting(int $key): bool|int {
		return $this->settings[$key] ?? false; //?
	}

	/**
	 * Deletes saved friendship on both sides
	 */
	public function delete(): void {
	}

	public function save(): void {
	}
}
