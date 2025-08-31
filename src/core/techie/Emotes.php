<?php

namespace core\techie;

class Emotes {

	const FILE = "/[REDACTED]/plugins/Core/src/core/techie/unlockedemotes.txt";

	const WAVE = "4c8ae710-df2e-47cd-814d-cc7bf21a3d67";
	const CLAP = "9a469a61-c83b-4ba9-b507-bdbe64430582";
	const WOODPUNCH = "42fde774-37d4-4422-b374-89ff13a6535a";
	const PICKAXE = "d7519b5a-45ec-4d27-997c-89d402c6b57f";
	const BREAKDANCE = "1dbaa006-0ec6-42c3-9440-a3bfa0c6fdbe";

	const EMOTES = [
		self::WAVE, self::CLAP, self::WOODPUNCH, self::PICKAXE, self::BREAKDANCE,
	];

	public static function getRandomEmote(): string {
		$array = array_merge(self::getDefaultEmotes(), self::getSavedEmotes());
		return $array[array_rand($array)];
	}

	public static function getDefaultEmotes(): array {
		return self::EMOTES;
	}

	public static function getSavedEmotes(): array {
		return file(self::FILE, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
	}

	public static function isSaved(string $emoteId): bool {
		return in_array($emoteId, self::getSavedEmotes());
	}

	public static function saveEmote(string $emoteId): bool {
		if (self::isSaved($emoteId)) return false;

		$file = fopen(self::FILE, "a");
		fwrite($file, $emoteId . "\n");
		fclose($file);
		return true;
	}
}
