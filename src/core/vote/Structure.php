<?php

namespace core\vote;

use pocketmine\utils\TextFormat;

class Structure {

	const DAY_NORMAL = 0;
	const DAY_WEEKEND = 1;
	const DAY_HOLIDAY = 2;
	const DAY_FIRST = 3;

	const VOTE_BOX_LOCATIONS = [
		"prison" => [
			"x" => -794.5,
			"y" => 29,
			"z" => 374.5,
			"level" => "newpsn",
		],
		"skyblock" => [
			"x" => -14587.5,
			"y" => 120,
			"z" => 13598.5,
			"level" => "scifi1",
		],
	];

	const VOTE_PARTY_LOCATIONS = [
		"skyblock" => [
			"spawn" => [
				"x" => -14731.5,
				"y" => 121,
				"z" => 13583.5,
			],
			"drops" => [
				"x" => -14747.5,
				"y" => 136,
				"z" => 13583.5,
			],
			"world" => "scifi1",
		],
		"prison" => [
			"spawn" => [
				"x" => -864.5,
				"y" => 26,
				"z" => 383.5
			],
			"drops" => [
				"x" => -864.5,
				"y" => 40,
				"z" => 383.5
			],
			"world" => "newpsn"
		]
	];

	const VOTE_BOX_TEXTS = [
		[
			TextFormat::RED . "Hey you!1!!1!1!",
			TextFormat::RED . "Yeah... I'm talking to you...",
			TextFormat::RED . "Have you " . TextFormat::DARK_RED . "voted" . TextFormat::RED . " today?",
			TextFormat::RED . "You better have... OR ELSE!",
		],
		[
			TextFormat::YELLOW . "Look at me!!!!",
			TextFormat::YELLOW . "I'm a talking ballot box!",
			TextFormat::YELLOW . "Tap me to learn how to vote!",
		],
		[
			TextFormat::AQUA . "Hey you.....",
			TextFormat::AQUA . "You should tap me...",
			TextFormat::AQUA . "I dare you!",
		],
		["I am very hungry..", "Someone feed me pls"],
		["HELP!!!", "I'M STUCK IN THE BOX", "It's cold... Dark..", "AND IT SMELLS!!!"],
	];

	const HOLIDAYS = [
		"1/1" => "New Years Day",
		"2/14" => "Valentines Day",

		"3/17" => "St. Patricks Day",

		"4/1" => "April Fools Day",

		"4/12" => "Easter",

		"7/4" => "4th of July",

		"8/13" => "Miguel's Birthday",

		"9/25" => "Sn3ak's Birthday",

		"11/13" => "Pring's Birthday",

		"12/22" => "Kally's Birthday",

		"12/24" => "Christmas Eve",
		"12/25" => "Christmas Day",

		"12/31" => "New Years Eve",
	];

	public static function getHoliday(): string {
		foreach (self::HOLIDAYS as $date => $holiday) {
			if (date("n/j") == $date) return $holiday;
		}
		return "";
	}

	public static function isWeekend(): bool {
		$dowMap = array('Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday');
		$dow_numeric = date("w");
		$day = $dowMap[$dow_numeric];
		return $day == "Friday" || $day == "Saturday" || $day == "Sunday";
	}

	public static function getDayType(): int {
		if (date("j") == 1) return self::DAY_FIRST;
		if (self::getHoliday() !== "") return self::DAY_HOLIDAY;
		if (self::isWeekend()) return self::DAY_WEEKEND;
		return self::DAY_NORMAL;
	}
}
