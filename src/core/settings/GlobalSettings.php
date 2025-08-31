<?php

namespace core\settings;

interface GlobalSettings {

	const VERSION = "1.0.8";

	//Normal
	const OPEN_DMS = 1;
	const DISPLAY_COSMETIC_EFFECTS = 2;
	const CPS_PING_COUNTER = 3;
	const PARTICLES = 4;
	const ENCHANTMENT_SOUNDS = 5;
	const LEGACY_RANK_ICONS = 6;

	//Premium
	const JOIN_MESSAGE = 50;
	const TECHIE_MESSAGES = 51;
	const ANNOUNCEMENT_BOARD = 52;

	//Staff
	const VANISHED = 100;
	const ANTICHEAT_MESSAGES = 101;
	const COMMAND_SEE = 102;
	const TELL_SEE = 103;
	const STAFFCHAT_JOIN = 104;

	const DEFAULT_SETTINGS = [
		self::OPEN_DMS => true,
		self::DISPLAY_COSMETIC_EFFECTS => true,
		self::CPS_PING_COUNTER => false,
		self::PARTICLES => true,
		self::ENCHANTMENT_SOUNDS => true,
		self::LEGACY_RANK_ICONS => false,

		self::JOIN_MESSAGE => true,
		self::TECHIE_MESSAGES => true,
		self::ANNOUNCEMENT_BOARD => true,

		self::VANISHED => false,
		self::ANTICHEAT_MESSAGES => true,
		self::COMMAND_SEE => false,
		self::TELL_SEE => false,
		self::STAFFCHAT_JOIN => false
	];

	const SETTING_UPDATES = [
		"1.0.2" => [
			self::DISPLAY_COSMETIC_EFFECTS => true
		],
		"1.0.3" => [
			self::CPS_PING_COUNTER => false
		],
		"1.0.4" => [
			self::PARTICLES => true,
			self::ENCHANTMENT_SOUNDS => true
		],
		"1.0.5" => [
			self::PARTICLES => true,
			self::ENCHANTMENT_SOUNDS => true
		],
		"1.0.6" => [
			self::LEGACY_RANK_ICONS => false
		],
		"1.0.7" => [
			self::LEGACY_RANK_ICONS => false
		],
		"1.0.8" => [
			self::STAFFCHAT_JOIN => false
		]
	];
}
