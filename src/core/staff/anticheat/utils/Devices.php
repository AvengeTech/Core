<?php

namespace core\staff\anticheat\utils;

use pocketmine\network\mcpe\protocol\types\DeviceOS;
use pocketmine\network\mcpe\protocol\types\InputMode;

interface Devices {

	public const TRANSLATE_FROM = [
		-1 => "UNKNOWN",
		1 => "ANDROID",
		2 => "IOS",
		3 => "OSX",
		4 => "AMAZON",
		5 => "GEAR_VR",
		6 => "HOLOLENS",
		7 => "W10",
		8 => "WIN32",
		9 => "DEDICATED",
		10 => "TVOS",
		11 => "PLAYSTATION",
		12 => "NINTENDO",
		13 => "XBOX",
		14 => "WINDOWS_PHONE"
	];

	public const TRANSLATE_TO = [
		"UNKNOWN" => -1,
		"ANDROID" => 1,
		"IOS" => 2,
		"OSX" => 3,
		"AMAZON" => 4,
		"GEAR_VR" => 5,
		"HOLOLENS" => 6,
		"W10" => 7,
		"WIN32" => 8,
		"DEDICATED" => 9,
		"TVOS" => 10,
		"PLAYSTATION" => 11,
		"NINTENDO" => 12,
		"XBOX" => 13,
		"WINDOWS_PHONE" => 14
	];

	public const INPUT_MODES = [
		-1 => "UNKNOWN",
		InputMode::MOUSE_KEYBOARD => "KEYBOARD",
		InputMode::TOUCHSCREEN => "TOUCH",
		InputMode::GAME_PAD => "CONTROLLER",
		InputMode::MOTION_CONTROLLER => "VR / MOTION"
	];
}
