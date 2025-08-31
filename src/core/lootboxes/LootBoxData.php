<?php

namespace core\lootboxes;

use core\utils\TextFormat;

class LootBoxData {

	const PRIZE_COSMETIC = 0;
	const PRIZE_CAPE = 1;
	const PRIZE_EFFECT = 2;
	const PRIZE_GADGET = 3;

	const CHANCE_EFFECT = 200;
	const CHANCE_CAPE = 100;
	const CHANCE_COSMETIC = 50;

	const RARITY_COMMON = 0;
	const RARITY_UNCOMMON = 1;
	const RARITY_RARE = 2;
	const RARITY_LEGENDARY = 3;
	const RARITY_DIVINE = 4;

	const RARITY_COLORS = [
		self::RARITY_COMMON => TextFormat::GREEN,
		self::RARITY_UNCOMMON => TextFormat::DARK_GREEN,
		self::RARITY_RARE => TextFormat::YELLOW,
		self::RARITY_LEGENDARY => TextFormat::GOLD,
		self::RARITY_DIVINE => TextFormat::RED,
	];

	const LOCATIONS = [
		"lobby" => [
			"world" => "sn3ak",
			"positions" => [
				[1842.5, 75, 769.5, 0],
				[1853.5, 75, 773, 45],
				[1857.5, 75, 784.5, 90],
				[1853.5, 75, 796, 135],
				[1842.5, 75, 799.5, 180],
			]
		],
		"skyblock" => [
			"world" => "scifi1",
			"positions" => [
				[-14589.5, 120, 13569.5, -45],
			]
		]
	];
}
