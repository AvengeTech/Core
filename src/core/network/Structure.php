<?php

namespace core\network;

use core\utils\TextFormat;

class Structure {

	const PROXY_PORTAL = 0;
	const PROXY_WATERDOG = 1;

	const PROXY = self::PROXY_WATERDOG;

	const PORT_TO_IDENTIFIER = [
		0 => "lobby-1",
		0 => "lobby-2",
		0 => "lobby-3",

		0 => "lobby-test",

		0 => "prison-1",
		0 => "prison-1-pvp",
		0 => "prison-1-plots",
		0 => "prison-1-cells",

		0 => "prison-test",
		0 => "prison-test-pvp",
		0 => "prison-test-plots",
		0 => "prison-test-cells",

		0 => "skyblock-1",
		0 => "skyblock-1-pvp",
		0 => "skyblock-1-is1",
		0 => "skyblock-1-is2",
		0 => "skyblock-1-is3",

		0 => "skyblock-1archive",
		0 => "skyblock-2archive",

		0 => "skyblock-test",
		0 => "skyblock-test-pvp",
		0 => "skyblock-test-is1",

		0 => "pvp-1",
		0 => "pvp-test",

		0 => "faction-1",
		0 => "faction-test",

		0 => "build-test",

		0 => "creative-test",
		0 => "creative-w1",

		0 => "idle-1",
	];

	const SOCKET_PORTS = [
		"lobby-1" => [0, 0],
		"lobby-2" => [0, 0],
		"lobby-3" => [0, 0],
		"lobby-test" => [0, 0],

		"prison-1" => [0, 0],
		"prison-1-pvp" => [0, 0],
		"prison-1-plots" => [0, 0],
		"prison-1-cells" => [0, 0],

		"prison-event" => [0, 0],

		"prison-test" => [0, 0],
		"prison-test-pvp" => [0, 0],
		"prison-test-plots" => [0, 0],
		"prison-test-cells" => [0, 0],

		"skyblock-1" => [0, 0],
		"skyblock-1-pvp" => [0, 0],
		"skyblock-1-is1" => [0, 0],
		"skyblock-1-is2" => [0, 0],
		"skyblock-1-is3" => [0, 0],

		"skyblock-1archive" => [0, 0],
		"skyblock-2archive" => [0, 0],

		"skyblock-test" => [0, 0],
		"skyblock-test-pvp" => [0, 0],
		"skyblock-test-is1" => [0, 0],

		"pvp-1" => [0, 0],
		"pvp-test" => [0, 0],

		"faction-1" => [0, 0],
		"faction-test" => [0, 0],

		"build-test" => [0, 0],

		"creative-test" => [0, 0],
		"creative-test-w1" => [0, 0],

		"idle-1" => [0, 0],
	];

	const SERVER_TYPES = [
		"lobby",

		"prison",
		"skyblock",
		"creative",
		"pvp",

		"faction",
		"build",
	];

	const TYPE_TO_CASE = [
		"lobby" => "Lobby",

		"prison" => "Prison",
		"skyblock" => "SkyBlock",
		"creative" => "Creative",
		"pvp" => "PvP",

		"faction" => "Faction",
		"build" => "Build",

		"idle" => "Idle",
	];

	const RESTART_TIMES = [
		"lobby" => 90,

		"prison" => 120,
		"prison-1-pvp" => 1440,
		"prison-1-plots" => 240,

		"prison-test" => 180, // test servers probably don't need to restart as often, less players most of the time
		"prison-test-pvp" => 1440, // push the limits on how long the pvp server can be up for, pvp shouldn't cause lag unless we have memory leaks
		"prison-test-plots" => 360,

		"skyblock" => 60,
		"skyblock-1-is1" => 90,
		"skyblock-1-is2" => 90,
		"skyblock-1-is3" => 90,
		"skyblock-1-pvp" => 150,

		"skyblock-test" => 180,
		"skyblock-test-is1" => 120,
		"skyblock-test-is2" => 120,
		"skyblock-test-pvp" => 150,

		"creative" => 1440,
		"pvp" => 300,

		"faction" => 360,
		"build" => 1440,

		"idle" => 180
	];

	const MAX_PLAYERS = [
		"lobby" => 100,
		//"lobby-test" => 1,

		"prison" => 200,
		"skyblock" => 250,
		"creative" => 200,
		"pvp" => 100,

		"faction" => 200,
		"build" => 200,

		"idle" => 1000,
	];

	const DOWNTIMES = [
		[
			"start" => 1748696400, // 2025-05-31 09:00:00 EST
			"end" => 1748707200, // 2025-05-31 12:00:00 EST
			"message" => TextFormat::AQUA . "SKYBLOCK SZN 2 UPGRADES IN PROGRESS..."
		]
	];

	const RESTRICTED = [
		//"prison-1" => "mod",
		"prison-test" => "mod",
		"lobby-test" => "mod",
		"skyblock-test" => "mod",
		"faction-test" => "mod",
	];

	const GAME_DESCRIPTIONS = [
		"lobby" =>
		TextFormat::GRAY . "Our " . TextFormat::YELLOW . "lobby" . TextFormat::GRAY . " is where the fun starts!" . PHP_EOL . PHP_EOL .
			"Open " . TextFormat::AQUA . "loot boxes" . TextFormat::GRAY . ", complete " . TextFormat::YELLOW . "epic parkour courses" . TextFormat::GRAY . ", fly on a " . TextFormat::GOLD . "sub sandwich" . TextFormat::GRAY . ", " . TextFormat::GREEN . "ride a jetski" . TextFormat::GRAY . ", and many other things on this server." . PHP_EOL . PHP_EOL .
			"Explore the lobby to discover all of it's " . TextFormat::LIGHT_PURPLE . "secrets!!!",
		"prison" =>
		TextFormat::GRAY . "Learn what it's like to live in a " . TextFormat::RED . "prison" . TextFormat::GRAY . " by playing our (totally) accurate representation, recreated in Minecraft!" . PHP_EOL . PHP_EOL .
			"Mine " . TextFormat::RED . "alone" . TextFormat::GRAY . " or " . TextFormat::GREEN . "with some friends" . TextFormat::GRAY . ", and unlock all of the mines to become the " . TextFormat::GREEN . "richest prisoner" . TextFormat::GRAY . " of them all! Create a " . TextFormat::DARK_RED . "gang" . TextFormat::GRAY . " and " . TextFormat::RED . "conquer the PvP mine" . TextFormat::GRAY . ", or relax and build in multiple different " . TextFormat::AQUA . "plot worlds" . PHP_EOL . PHP_EOL .
			TextFormat::ITALIC . TextFormat::MINECOIN_GOLD . "Last wipe: 6/22/23",
		"skyblock" =>
		TextFormat::GRAY . "Good ol' classic " . TextFormat::AQUA . "SkyBlock" . TextFormat::GRAY . "." . PHP_EOL . PHP_EOL .
			"Start an island " . TextFormat::RED . "alone" . TextFormat::GRAY . " or " . TextFormat::GREEN . "with some friends" . TextFormat::GRAY . ", and complete over " . TextFormat::YELLOW . "100 different challenges! " . TextFormat::GRAY . "Work to create the best island of them all!" . PHP_EOL . PHP_EOL .
			TextFormat::GREEN . ":$: REAL MONEY PRIZES :$:" . TextFormat::GRAY . " given away at the end of " . TextFormat::YELLOW . "every month" . TextFormat::GRAY . " via the leaderboards! :wow:" . PHP_EOL . PHP_EOL .
			TextFormat::ITALIC . TextFormat::MINECOIN_GOLD . "Last wipe: 7/28/23",
		"pvp" =>
		TextFormat::GRAY . "Fight to the " . TextFormat::RED . TextFormat::EMOJI_SKULL . " DEATH " . TextFormat::EMOJI_SKULL . TextFormat::GRAY . " in our all-in-one PvP experience!" . PHP_EOL . PHP_EOL .
			"Engage in never ending combat in the " . TextFormat::YELLOW . "arenas " . TextFormat::GRAY . "to train your skills, or play one of our many minigames including:" . PHP_EOL .
			TextFormat::AQUA . TextFormat::EMOJI_ARROW_RIGHT . " OITQ" . PHP_EOL .
			TextFormat::EMOJI_ARROW_RIGHT . " Duels" . PHP_EOL .
			TextFormat::EMOJI_ARROW_RIGHT . " SkyWars" . PHP_EOL .
			TextFormat::EMOJI_ARROW_RIGHT . " Sudden Death" . PHP_EOL .
			TextFormat::EMOJI_ARROW_RIGHT . " Practice Bots" . PHP_EOL .
			TextFormat::EMOJI_ARROW_RIGHT . " And more to come!" . PHP_EOL . PHP_EOL .
			TextFormat::EMOJI_CAUTION . TextFormat::YELLOW . " NOTE: This is a beta experience. Many aspects are unfinished, and crashes/shutdowns may occur while we stabilize and improve the server.",
		"faction" => TextFormat::GRAY . "Shane has a big forehead", // nice one Miggy
	];
}