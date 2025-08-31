<?php

namespace core\chat;

use core\utils\TextFormat;

class Structure {

	const LEGACY_RANK_FORMATS = [
		"default" => "",

		"endermite" => TextFormat::LIGHT_PURPLE . "ENDERMITE",
		"blaze" => TextFormat::GOLD . "BLAZE",
		"ghast" => TextFormat::WHITE . "GHAST",
		"enderman" => TextFormat::DARK_PURPLE . "ENDERMAN",
		"wither" => TextFormat::DARK_RED . "WITHER",
		"enderdragon" => TextFormat::BLUE . "ED",
		"warden" => TextFormat::DARK_AQUA . "Warden",

		"youtuber" => TextFormat::RED . "Y" . TextFormat::WHITE . "T",
		"youtuber+" => TextFormat::RED . "Y" . TextFormat::WHITE . "T",
		"youtuber++" => TextFormat::RED . "Y" . TextFormat::WHITE . "T",
		"youtuber+++" => TextFormat::RED . "Y" . TextFormat::WHITE . "T",

		"builder" => TextFormat::AQUA . "BUILDER",
		"developer" => TextFormat::DARK_RED . "DEVELOPER",
		"artist" => TextFormat::LIGHT_PURPLE . "ARTIST",

		"trainee" => TextFormat::GREEN . "TRAINEE",
		"jr_mod" => TextFormat::MINECOIN_GOLD . "JR MOD",
		"mod" => TextFormat::DARK_GREEN . "MOD",
		"sr_mod" => TextFormat::DARK_AQUA . "SR MOD",
		"head_mod" => TextFormat::DARK_RED . "HEAD MOD",
		"manager" => TextFormat::DARK_PURPLE . "MANAGER",
		"owner" => TextFormat::DARK_RED . "O" . TextFormat::RED . "W" . TextFormat::DARK_RED . "N" . TextFormat::RED . "E" . TextFormat::DARK_RED . "R",
	];

	const DISGUISE_RANKS = [
		"default",
		"endermite",
		"blaze",
		"ghast",
		"enderman",
		"wither",
		"enderdragon"
	];

	const RANK_FORMATS = [
		"default" => "",

		"endermite" => TextFormat::ICON_ENDERMITE,
		"blaze" => TextFormat::ICON_BLAZE,
		"ghast" => TextFormat::ICON_GHAST,
		"enderman" => TextFormat::ICON_ENDERMAN,
		"wither" => TextFormat::ICON_WITHER,
		"enderdragon" => TextFormat::ICON_ENDERDRAGON,

		"youtuber" => TextFormat::ICON_YOUTUBE,
		"youtuber+" => TextFormat::ICON_YOUTUBE,
		"youtuber++" => TextFormat::ICON_YOUTUBE,
		"youtuber+++" => TextFormat::ICON_YOUTUBE,

		"builder" => TextFormat::ICON_BUILDER,
		"developer" => TextFormat::ICON_DEV,
		"artist" => TextFormat::ICON_ARTIST,

		"trainee" => TextFormat::ICON_TRAINEE,
		"jr_mod" => TextFormat::ICON_JR_MOD,
		"mod" => TextFormat::ICON_MOD,
		"sr_mod" => TextFormat::ICON_SR_MOD,
		"head_mod" => TextFormat::ICON_HEAD_MOD,
		"manager" => TextFormat::ICON_MANAGER,
		"owner" => TextFormat::ICON_OWNER,
	];

	const MODERN_TO_LEGACY = [
		self::RANK_FORMATS['default'] => self::LEGACY_RANK_FORMATS['default'],

		self::RANK_FORMATS["endermite"] => self::LEGACY_RANK_FORMATS["endermite"],
		self::RANK_FORMATS["blaze"] => self::LEGACY_RANK_FORMATS["blaze"],
		self::RANK_FORMATS["ghast"] => self::LEGACY_RANK_FORMATS["ghast"],
		self::RANK_FORMATS["enderman"] => self::LEGACY_RANK_FORMATS["enderman"],
		self::RANK_FORMATS["wither"] => self::LEGACY_RANK_FORMATS["wither"],
		self::RANK_FORMATS["enderdragon"] => self::LEGACY_RANK_FORMATS["enderdragon"],

		TextFormat::ICON_WARDEN => self::LEGACY_RANK_FORMATS["warden"],

		self::RANK_FORMATS["youtuber"] => self::LEGACY_RANK_FORMATS["youtuber"],
		self::RANK_FORMATS["youtuber+"] => self::LEGACY_RANK_FORMATS["youtuber+"],
		self::RANK_FORMATS["youtuber++"] => self::LEGACY_RANK_FORMATS["youtuber++"],
		self::RANK_FORMATS["youtuber+++"] => self::LEGACY_RANK_FORMATS["youtuber+++"],

		self::RANK_FORMATS["builder"] => self::LEGACY_RANK_FORMATS["builder"],
		self::RANK_FORMATS["developer"] => self::LEGACY_RANK_FORMATS["developer"],
		self::RANK_FORMATS["artist"] => self::LEGACY_RANK_FORMATS["artist"],

		self::RANK_FORMATS["trainee"] => self::LEGACY_RANK_FORMATS["trainee"],
		self::RANK_FORMATS["jr_mod"] => self::LEGACY_RANK_FORMATS["jr_mod"],
		self::RANK_FORMATS["mod"] => self::LEGACY_RANK_FORMATS["mod"],
		self::RANK_FORMATS["sr_mod"] => self::LEGACY_RANK_FORMATS["sr_mod"],
		self::RANK_FORMATS["head_mod"] => self::LEGACY_RANK_FORMATS["head_mod"],
		self::RANK_FORMATS["manager"] => self::LEGACY_RANK_FORMATS["manager"],
		self::RANK_FORMATS["owner"] => self::LEGACY_RANK_FORMATS["owner"],
	];
}
