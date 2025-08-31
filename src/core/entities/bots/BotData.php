<?php

namespace core\entities\bots;

use core\utils\TextFormat;
use Dom\Text;

class BotData {

	const BOT_DATA = [
		"loadbalancer" => [],
		"lobby" => [
			"gmbot-skyblock" => [
				"nametag" => TextFormat::AQUA . TextFormat::BOLD . "SkyBlock",
				"x" => 272.5,
				"y" => 15,
				"z" => 264.5,
				"pitch" => 0,
				"yaw" => 135,
				"level" => "lobby",
				"item" => [52, 0],
				"turn" => true,
				"scale" => 1.5,
				"skin" => [
					"enabled" => true,
					"name" => "plastic_steve"
				],
				"config" => [
					"type" => "statue",
					"name" => "SkyBlock",
					"messages" => [
						"New season releases on " . TextFormat::YELLOW . "July 1st at 4pm EST" . TextFormat::GRAY . ", see you then!",
					]
				]
			],
			"gmbot-prisons" => [
				"nametag" => TextFormat::AQUA . TextFormat::BOLD . "Prisons",
				"x" => 272.5,
				"y" => 15,
				"z" => 240.5,
				"pitch" => 0,
				"yaw" => 45,
				"level" => "lobby",
				"item" => [278, 0],
				"armor" => [306, 0, 0, 0],
				"turn" => true,
				"scale" => 1.5,
				"skin" => [
					"enabled" => true,
					"name" => "prisoner"
				],
				"config" => [
					"type" => "transfer",
					"server" => "prison",
				]
			],
			/**"gmbot-creative" => [
			"nametag" => TextFormat::YELLOW . TextFormat::BOLD . TextFormat::OBFUSCATED . "|o|o|o|o|o|" . TextFormat::RESET . TextFormat::BOLD . TextFormat::RED . " (BETA)",
			"x" => 276.5,
			"y" => 15,
			"z" => 252.5,
			"pitch" => 0,
			"yaw" => 90,
			"level" => "lobby",
			"item" => [2, 0],
			"armor" => [0, 0, 0, 0],
			"turn" => true,
			"scale" => 1.5,
			"skin" => [
			"enabled" => true,
			"name" => "blockman"
			],
			"config" => [
			"type" => "command",
			"commands" => ["transfer creative-1"],
			]
			],*/

		],

		"prison" => [
			"questmaster" => [
				"nametag" => TextFormat::YELLOW . "Questmaster",
				"x" => -855.5,
				"y" => 29,
				"z" => 370.5,
				"pitch" => 0,
				"yaw" => 0,
				"level" => "newpsn",
				"item" => [403, 0],
				"turn" => true,
				"skin" => [
					"enabled" => true,
					"name" => "questmaster"
				],
				"config" => [
					"type" => "command",
					"commands" => ["q"]
				]
			],
			"sellhand" => [
				"nametag" => TextFormat::YELLOW . "Sell Hand",
				"x" => -850.5,
				"y" => 29,
				"z" => 375.5,
				"pitch" => 0,
				"yaw" => 0,
				"level" => "newpsn",
				"item" => [0, 0],
				"turn" => true,
				"skin" => [
					"enabled" => false,
					"name" => "blackmarket"
				],
				"config" => [
					"type" => "command",
					"commands" => ["sellhand"]
				]
			],

			"blacksmith" => [
				"nametag" => TextFormat::DARK_GRAY . TextFormat::BOLD . "Blacksmith",
				"x" => -850.5,
				"y" => 29,
				"z" => 391.5,
				"pitch" => 0,
				"yaw" => 180,
				"level" => "newpsn",
				"item" => [258, 0],
				"turn" => true,
				"skin" => [
					"enabled" => true,
					"name" => "pblacksmith"
				],
				"config" => [
					"type" => "command",
					"commands" => ["bs"]
				]
			],
			"blackmarket" => [
				"nametag" => TextFormat::LIGHT_PURPLE . TextFormat::BOLD . "Black Market",
				"x" => -855.5,
				"y" => 29,
				"z" => 396.5,
				"pitch" => 0,
				"yaw" => 180,
				"level" => "newpsn",
				"item" => [353, 0],
				"turn" => true,
				"skin" => [
					"enabled" => true,
					"name" => "blackmarket"
				],
				"config" => [
					"type" => "command",
					"commands" => ["bm"]
				]
			],

			"enchanter" => [
				"nametag" => TextFormat::DARK_PURPLE . TextFormat::BOLD . "Enchanter",
				"x" => -883.5,
				"y" => 26,
				"z" => 383.5,
				"pitch" => 0,
				"yaw" => 270,
				"level" => "newpsn",
				"item" => [403, 0],
				"turn" => true,
				"skin" => [
					"enabled" => true,
					"name" => "enchanter"
				],
				"config" => [
					"type" => "command",
					"commands" => ["enchanter"]
				]
			],
			"conjuror" => [
				"nametag" => TextFormat::BLUE . TextFormat::BOLD . "Conjuror",
				"x" => -883.5,
				"y" => 35.5,
				"z" => 383.5,
				"pitch" => 0,
				"yaw" => 270,
				"level" => "newpsn",
				"turn" => true,
				"sitting" => true,
				"item" => [0, 0],
				"skin" => [
					"enabled" => true,
					"name" => "conjurorfr"
				],
				"config" => [
					"type" => "command",
					"commands" => ["conjuror"]
				]
			],
			"mine-pvp-new" => [
				"nametag" => TextFormat::BOLD . TextFormat::RED . "PvP Mine " . TextFormat::RESET . TextFormat::DARK_RED . "(BEWARE!)",
				"x" => -817.5,
				"y" => 26,
				"z" => 430.5,
				"pitch" => 0,
				"yaw" => 0,
				"level" => "newpsn",
				"item" => [276, 0],
				"turn" => true,
				"skin" => [
					"enabled" => true,
					"name" => "pvpmine"
				],
				"config" => [
					"type" => "command",
					"commands" => ["mine pvp"]
				]
			],
			"mine-vip-new" => [
				"nametag" => TextFormat::BOLD . TextFormat::GOLD . "Mine " . TextFormat::AQUA . "VIP",
				"x" => -817.5,
				"y" => 26,
				"z" => 440.5,
				"pitch" => 0,
				"yaw" => 180,
				"level" => "newpsn",
				"item" => [278, 0],
				"turn" => true,
				"skin" => [
					"enabled" => true,
					"name" => "prisoner"
				],
				"config" => [
					"type" => "command",
					"commands" => ["mine vip"]
				]
			],
			"mine-a-new" => [
				"nametag" => TextFormat::BOLD . TextFormat::GOLD . "Mine " . TextFormat::AQUA . "A",
				"x" => -822.5,
				"y" => 26,
				"z" => 440.5,
				"pitch" => 0,
				"yaw" => 180,
				"level" => "newpsn",
				"item" => [278, 0],
				"turn" => true,
				"skin" => [
					"enabled" => true,
					"name" => "prisoner"
				],
				"config" => [
					"type" => "command",
					"commands" => ["mine a"]
				]
			],
			"mine-b-new" => [
				"nametag" => TextFormat::BOLD . TextFormat::GOLD . "Mine " . TextFormat::AQUA . "B",
				"x" => -827.5,
				"y" => 26,
				"z" => 440.5,
				"pitch" => 0,
				"yaw" => 180,
				"level" => "newpsn",
				"item" => [278, 0],
				"turn" => true,
				"skin" => [
					"enabled" => true,
					"name" => "prisoner"
				],
				"config" => [
					"type" => "command",
					"commands" => ["mine b"]
				]
			],
			"mine-c-new" => [
				"nametag" => TextFormat::BOLD . TextFormat::GOLD . "Mine " . TextFormat::AQUA . "C",
				"x" => -832.5,
				"y" => 26,
				"z" => 440.5,
				"pitch" => 0,
				"yaw" => 180,
				"level" => "newpsn",
				"item" => [278, 0],
				"turn" => true,
				"skin" => [
					"enabled" => true,
					"name" => "prisoner"
				],
				"config" => [
					"type" => "command",
					"commands" => ["mine c"]
				]
			],
			"mine-d-new" => [
				"nametag" => TextFormat::BOLD . TextFormat::GOLD . "Mine " . TextFormat::AQUA . "D",
				"x" => -837.5,
				"y" => 26,
				"z" => 440.5,
				"pitch" => 0,
				"yaw" => 180,
				"level" => "newpsn",
				"item" => [278, 0],
				"turn" => true,
				"skin" => [
					"enabled" => true,
					"name" => "prisoner"
				],
				"config" => [
					"type" => "command",
					"commands" => ["mine d"]
				]
			],
			"mine-e-new" => [
				"nametag" => TextFormat::BOLD . TextFormat::GOLD . "Mine " . TextFormat::AQUA . "E",
				"x" => -842.5,
				"y" => 26,
				"z" => 440.5,
				"pitch" => 0,
				"yaw" => 180,
				"level" => "newpsn",
				"item" => [278, 0],
				"turn" => true,
				"skin" => [
					"enabled" => true,
					"name" => "prisoner"
				],
				"config" => [
					"type" => "command",
					"commands" => ["mine e"]
				]
			],
			"mine-f-new" => [
				"nametag" => TextFormat::BOLD . TextFormat::GOLD . "Mine " . TextFormat::AQUA . "F",
				"x" => -847.5,
				"y" => 26,
				"z" => 440.5,
				"pitch" => 0,
				"yaw" => 180,
				"level" => "newpsn",
				"item" => [278, 0],
				"turn" => true,
				"skin" => [
					"enabled" => true,
					"name" => "prisoner"
				],
				"config" => [
					"type" => "command",
					"commands" => ["mine f"]
				]
			],
			"mine-g-new" => [
				"nametag" => TextFormat::BOLD . TextFormat::GOLD . "Mine " . TextFormat::AQUA . "G",
				"x" => -852.5,
				"y" => 26,
				"z" => 440.5,
				"pitch" => 0,
				"yaw" => 180,
				"level" => "newpsn",
				"item" => [278, 0],
				"turn" => true,
				"skin" => [
					"enabled" => true,
					"name" => "prisoner"
				],
				"config" => [
					"type" => "command",
					"commands" => ["mine g"]
				]
			],
			"mine-h-new" => [
				"nametag" => TextFormat::BOLD . TextFormat::GOLD . "Mine " . TextFormat::AQUA . "H",
				"x" => -857.5,
				"y" => 26,
				"z" => 440.5,
				"pitch" => 0,
				"yaw" => 180,
				"level" => "newpsn",
				"item" => [278, 0],
				"turn" => true,
				"skin" => [
					"enabled" => true,
					"name" => "prisoner"
				],
				"config" => [
					"type" => "command",
					"commands" => ["mine h"]
				]
			],
			"mine-i-new" => [
				"nametag" => TextFormat::BOLD . TextFormat::GOLD . "Mine " . TextFormat::AQUA . "I",
				"x" => -862.5,
				"y" => 26,
				"z" => 440.5,
				"pitch" => 0,
				"yaw" => 180,
				"level" => "newpsn",
				"item" => [278, 0],
				"turn" => true,
				"skin" => [
					"enabled" => true,
					"name" => "prisoner"
				],
				"config" => [
					"type" => "command",
					"commands" => ["mine i"]
				]
			],

			"mine-j-new" => [
				"nametag" => TextFormat::BOLD . TextFormat::GOLD . "Mine " . TextFormat::AQUA . "J",
				"x" => -867.5,
				"y" => 26,
				"z" => 440.5,
				"pitch" => 0,
				"yaw" => 180,
				"level" => "newpsn",
				"item" => [278, 0],
				"turn" => true,
				"skin" => [
					"enabled" => true,
					"name" => "prisoner"
				],
				"config" => [
					"type" => "command",
					"commands" => ["mine j"]
				]
			],
			"mine-k-new" => [
				"nametag" => TextFormat::BOLD . TextFormat::GOLD . "Mine " . TextFormat::AQUA . "K",
				"x" => -872.5,
				"y" => 26,
				"z" => 440.5,
				"pitch" => 0,
				"yaw" => 180,
				"level" => "newpsn",
				"item" => [278, 0],
				"turn" => true,
				"skin" => [
					"enabled" => true,
					"name" => "prisoner"
				],
				"config" => [
					"type" => "command",
					"commands" => ["mine k"]
				]
			],
			"mine-l-new" => [
				"nametag" => TextFormat::BOLD . TextFormat::GOLD . "Mine " . TextFormat::AQUA . "L",
				"x" => -877.5,
				"y" => 26,
				"z" => 440.5,
				"pitch" => 0,
				"yaw" => 180,
				"level" => "newpsn",
				"item" => [278, 0],
				"turn" => true,
				"skin" => [
					"enabled" => true,
					"name" => "prisoner"
				],
				"config" => [
					"type" => "command",
					"commands" => ["mine l"]
				]
			],
			"mine-m-new" => [
				"nametag" => TextFormat::BOLD . TextFormat::GOLD . "Mine " . TextFormat::AQUA . "M",
				"x" => -882.5,
				"y" => 26,
				"z" => 440.5,
				"pitch" => 0,
				"yaw" => 180,
				"level" => "newpsn",
				"item" => [278, 0],
				"turn" => true,
				"skin" => [
					"enabled" => true,
					"name" => "prisoner"
				],
				"config" => [
					"type" => "command",
					"commands" => ["mine m"]
				]
			],
			"mine-n-new" => [
				"nametag" => TextFormat::BOLD . TextFormat::GOLD . "Mine " . TextFormat::AQUA . "N",
				"x" => -882.5,
				"y" => 26,
				"z" => 430.5,
				"pitch" => 0,
				"yaw" => 0,
				"level" => "newpsn",
				"item" => [278, 0],
				"turn" => true,
				"skin" => [
					"enabled" => true,
					"name" => "prisoner"
				],
				"config" => [
					"type" => "command",
					"commands" => ["mine n"]
				]
			],
			"mine-o-new" => [
				"nametag" => TextFormat::BOLD . TextFormat::GOLD . "Mine " . TextFormat::AQUA . "O",
				"x" => -877.5,
				"y" => 26,
				"z" => 430.5,
				"pitch" => 0,
				"yaw" => 0,
				"level" => "newpsn",
				"item" => [278, 0],
				"turn" => true,
				"skin" => [
					"enabled" => true,
					"name" => "prisoner"
				],
				"config" => [
					"type" => "command",
					"commands" => ["mine o"]
				]
			],
			"mine-p-new" => [
				"nametag" => TextFormat::BOLD . TextFormat::GOLD . "Mine " . TextFormat::AQUA . "P",
				"x" => -872.5,
				"y" => 26,
				"z" => 430.5,
				"pitch" => 0,
				"yaw" => 0,
				"level" => "newpsn",
				"item" => [278, 0],
				"turn" => true,
				"skin" => [
					"enabled" => true,
					"name" => "prisoner"
				],
				"config" => [
					"type" => "command",
					"commands" => ["mine p"]
				]
			],
			"mine-q-new" => [
				"nametag" => TextFormat::BOLD . TextFormat::GOLD . "Mine " . TextFormat::AQUA . "Q",
				"x" => -867.5,
				"y" => 26,
				"z" => 430.5,
				"pitch" => 0,
				"yaw" => 0,
				"level" => "newpsn",
				"item" => [278, 0],
				"turn" => true,
				"skin" => [
					"enabled" => true,
					"name" => "prisoner"
				],
				"config" => [
					"type" => "command",
					"commands" => ["mine q"]
				]
			],
			"mine-r-new" => [
				"nametag" => TextFormat::BOLD . TextFormat::GOLD . "Mine " . TextFormat::AQUA . "R",
				"x" => -862.5,
				"y" => 26,
				"z" => 430.5,
				"pitch" => 0,
				"yaw" => 0,
				"level" => "newpsn",
				"item" => [278, 0],
				"turn" => true,
				"skin" => [
					"enabled" => true,
					"name" => "prisoner"
				],
				"config" => [
					"type" => "command",
					"commands" => ["mine r"]
				]
			],
			"mine-s-new" => [
				"nametag" => TextFormat::BOLD . TextFormat::GOLD . "Mine " . TextFormat::AQUA . "S",
				"x" => -857.5,
				"y" => 26,
				"z" => 430.5,
				"pitch" => 0,
				"yaw" => 0,
				"level" => "newpsn",
				"item" => [278, 0],
				"turn" => true,
				"skin" => [
					"enabled" => true,
					"name" => "prisoner"
				],
				"config" => [
					"type" => "command",
					"commands" => ["mine s"]
				]
			],

			"mine-t-new" => [
				"nametag" => TextFormat::BOLD . TextFormat::GOLD . "Mine " . TextFormat::AQUA . "T",
				"x" => -852.5,
				"y" => 26,
				"z" => 430.5,
				"pitch" => 0,
				"yaw" => 0,
				"level" => "newpsn",
				"item" => [278, 0],
				"turn" => true,
				"skin" => [
					"enabled" => true,
					"name" => "prisoner"
				],
				"config" => [
					"type" => "command",
					"commands" => ["mine t"]
				]
			],
			"mine-u-new" => [
				"nametag" => TextFormat::BOLD . TextFormat::GOLD . "Mine " . TextFormat::AQUA . "U",
				"x" => -847.5,
				"y" => 26,
				"z" => 430.5,
				"pitch" => 0,
				"yaw" => 0,
				"level" => "newpsn",
				"item" => [278, 0],
				"turn" => true,
				"skin" => [
					"enabled" => true,
					"name" => "prisoner"
				],
				"config" => [
					"type" => "command",
					"commands" => ["mine u"]
				]
			],
			"mine-v-new" => [
				"nametag" => TextFormat::BOLD . TextFormat::GOLD . "Mine " . TextFormat::AQUA . "V",
				"x" => -842.5,
				"y" => 26,
				"z" => 430.5,
				"pitch" => 0,
				"yaw" => 0,
				"level" => "newpsn",
				"item" => [278, 0],
				"turn" => true,
				"skin" => [
					"enabled" => true,
					"name" => "prisoner"
				],
				"config" => [
					"type" => "command",
					"commands" => ["mine v"]
				]
			],
			"mine-w-new" => [
				"nametag" => TextFormat::BOLD . TextFormat::GOLD . "Mine " . TextFormat::AQUA . "W",
				"x" => -837.5,
				"y" => 26,
				"z" => 430.5,
				"pitch" => 0,
				"yaw" => 0,
				"level" => "newpsn",
				"item" => [278, 0],
				"turn" => true,
				"skin" => [
					"enabled" => true,
					"name" => "prisoner"
				],
				"config" => [
					"type" => "command",
					"commands" => ["mine w"]
				]
			],
			"mine-x-new" => [
				"nametag" => TextFormat::BOLD . TextFormat::GOLD . "Mine " . TextFormat::AQUA . "X",
				"x" => -832.5,
				"y" => 26,
				"z" => 430.5,
				"pitch" => 0,
				"yaw" => 0,
				"level" => "newpsn",
				"item" => [278, 0],
				"turn" => true,
				"skin" => [
					"enabled" => true,
					"name" => "prisoner"
				],
				"config" => [
					"type" => "command",
					"commands" => ["mine x"]
				]
			],
			"mine-y-new" => [
				"nametag" => TextFormat::BOLD . TextFormat::GOLD . "Mine " . TextFormat::AQUA . "Y",
				"x" => -827.5,
				"y" => 26,
				"z" => 430.5,
				"pitch" => 0,
				"yaw" => 0,
				"level" => "newpsn",
				"item" => [278, 0],
				"turn" => true,
				"skin" => [
					"enabled" => true,
					"name" => "prisoner"
				],
				"config" => [
					"type" => "command",
					"commands" => ["mine y"]
				]
			],
			"mine-z-new" => [
				"nametag" => TextFormat::BOLD . TextFormat::GOLD . "Mine " . TextFormat::AQUA . "Z",
				"x" => -822.5,
				"y" => 26,
				"z" => 430.5,
				"pitch" => 0,
				"yaw" => 0,
				"level" => "newpsn",
				"item" => [278, 0],
				"turn" => true,
				"skin" => [
					"enabled" => true,
					"name" => "prisoner"
				],
				"config" => [
					"type" => "command",
					"commands" => ["mine z"]
				]
			],

			"mines-list" => [
				"nametag" => TextFormat::BOLD . TextFormat::DARK_YELLOW . "Mine List",
				"x" => -812.5,
				"y" => 24,
				"z" => 430.5,
				"pitch" => 0,
				"yaw" => 270,
				"level" => "newpsn",
				"item" => [278, 0],
				"turn" => true,
				"skin" => [
					"enabled" => true,
					"name" => "prisoner"
				],
				"config" => [
					"type" => "command",
					"commands" => ["mines"]
				]
			],

			"mines-prestige" => [
				"nametag" => TextFormat::BOLD . TextFormat::YELLOW . "Prestige Mines",
				"x" => -885.5,
				"y" => 26,
				"z" => 435.5,
				"pitch" => 0,
				"yaw" => 270,
				"level" => "newpsn",
				"item" => [278, 0],
				"turn" => true,
				"skin" => [
					"enabled" => true,
					"name" => "prisoner"
				],
				"config" => [
					"type" => "command",
					"commands" => ["mines prestige"]
				]
			],

			"battle-spectate" => [
				"nametag" => TextFormat::BOLD . TextFormat::GOLD . "Spectate Gang Battles",
				"x" => -815.5,
				"y" => 28,
				"z" => 373.5,
				"pitch" => 0,
				"yaw" => 315,
				"level" => "newpsn",
				"item" => [743, 0],
				"turn" => true,
				"skin" => [
					"enabled" => true,
					"name" => "gladiator"
				],
				"config" => [
					"type" => "command",
					"commands" => ["gg battles"]
				]
			],


			"auctioneer" => [
				"nametag" => TextFormat::BOLD . TextFormat::AQUA . "Auctioneer",
				"x" => -888.5,
				"y" => 24,
				"z" => 330.5,
				"pitch" => 0,
				"yaw" => 235,
				"level" => "newpsn",
				"item" => [339, 0],
				"turn" => true,
				"skin" => [
					"enabled" => true,
					"name" => "auctioneer"
				],
				"config" => [
					"type" => "command",
					"commands" => ["ah"]
				]
			],

			"new-plots-auto" => [
				"nametag" => TextFormat::AQUA . "Teleport to open plot",
				"x" => 43.5,
				"y" => 56,
				"z" => 41.5,
				"pitch" => 0,
				"yaw" => 135,
				"level" => "s0plots",
				"item" => [2, 0],
				"turn" => true,
				"skin" => [
					"enabled" => true,
					"name" => "noob"
				],
				"config" => [
					"type" => "command",
					"commands" => ["p auto"]
				]
			],
			"new-plots-homes" => [
				"nametag" => TextFormat::AQUA . "Your plots",
				"x" => 41.5,
				"y" => 56,
				"z" => 43.5,
				"pitch" => 0,
				"yaw" => 135,
				"level" => "s0plots",
				"item" => [57, 0],
				"turn" => true,
				"skin" => [
					"enabled" => true,
					"name" => "noob"
				],
				"config" => [
					"type" => "command",
					"commands" => ["p homes"]
				]
			],

			"nether-plots-auto" => [
				"nametag" => TextFormat::RED . "Teleport to open plot",
				"x" => 46.5,
				"y" => 56,
				"z" => 45.5,
				"pitch" => 0,
				"yaw" => 135,
				"level" => "nether_plots_s0",
				"item" => [2, 0],
				"turn" => true,
				"skin" => [
					"enabled" => true,
					"name" => "noob"
				],
				"config" => [
					"type" => "command",
					"commands" => ["p auto"]
				]
			],
			"nether-plots-homes" => [
				"nametag" => TextFormat::RED . "Your plots",
				"x" => 44.5,
				"y" => 56,
				"z" => 47.5,
				"pitch" => 0,
				"yaw" => 135,
				"level" => "nether_plots_s0",
				"item" => [57, 0],
				"turn" => true,
				"skin" => [
					"enabled" => true,
					"name" => "noob"
				],
				"config" => [
					"type" => "command",
					"commands" => ["p homes"]
				]
			],

			"end-plots-auto" => [
				"nametag" => TextFormat::LIGHT_PURPLE . "Teleport to open plot",
				"x" => 72.5,
				"y" => 51,
				"z" => 96.5,
				"pitch" => 0,
				"yaw" => 180,
				"level" => "end_plots_s0",
				"item" => [2, 0],
				"turn" => true,
				"skin" => [
					"enabled" => true,
					"name" => "noob"
				],
				"config" => [
					"type" => "command",
					"commands" => ["p auto"]
				]
			],
			"end-plots-homes" => [
				"nametag" => TextFormat::LIGHT_PURPLE . "Your plots",
				"x" => 54.5,
				"y" => 51,
				"z" => 96.5,
				"pitch" => 0,
				"yaw" => 180,
				"level" => "end_plots_s0",
				"item" => [57, 0],
				"turn" => true,
				"skin" => [
					"enabled" => true,
					"name" => "noob"
				],
				"config" => [
					"type" => "command",
					"commands" => ["p homes"]
				]
			],



		],

		/**"prison-event" => [
			"prize-6-9" => [
				"nametag" => TextFormat::BOLD . TextFormat::LIGHT_PURPLE . "DISCORD NITRO " . TextFormat::GRAY . "(" . TextFormat::YELLOW . "Claim" . TextFormat::GRAY . ")",
				"x" => 458.5,
				"y" => 51,
				"z" => 671.5,
				"pitch" => 0,
				"yaw" => 0,
				"level" => "s3plots",
				"item" => [57,0],
				"turn" => true,
				"skin" => [
					"enabled" => true,
					"name" => "noob"
				],
				"config" => [
					"type" => "command",
					"message" => TextFormat::GI . "Screenshot you claiming this plot and send in a question ticket on Discord to claim prize!",
					"commands" => ["p claim"]
				]
			],
			"prize-1-1" => [
				"nametag" => TextFormat::BOLD . TextFormat::LIGHT_PURPLE . "DISCORD NITRO " . TextFormat::GRAY . "(" . TextFormat::YELLOW . "Claim" . TextFormat::GRAY . ")",
				"x" => 103.5,
				"y" => 51,
				"z" => 103.5,
				"pitch" => 0,
				"yaw" => 0,
				"level" => "s3plots",
				"item" => [57,0],
				"turn" => true,
				"skin" => [
					"enabled" => true,
					"name" => "noob"
				],
				"config" => [
					"type" => "command",
					"message" => TextFormat::GI . "Screenshot you claiming this plot and send in a question ticket on Discord to claim prize!",
					"commands" => ["p claim"]
				]
			],
			"prize-1-44" => [
				"nametag" => TextFormat::BOLD . TextFormat::LIGHT_PURPLE . "DISCORD NITRO " . TextFormat::GRAY . "(" . TextFormat::YELLOW . "Claim" . TextFormat::GRAY . ")",
				"x" => 103.5,
				"y" => 51,
				"z" => 3156.5,
				"pitch" => 0,
				"yaw" => 0,
				"level" => "s3plots",
				"item" => [57,0],
				"turn" => true,
				"skin" => [
					"enabled" => true,
					"name" => "noob"
				],
				"config" => [
					"type" => "command",
					"message" => TextFormat::GI . "Screenshot you claiming this plot and send in a question ticket on Discord to claim prize!",
					"commands" => ["p claim"]
				]
			],
			"prize-4-20" => [
				"nametag" => TextFormat::BOLD . TextFormat::LIGHT_PURPLE . "DISCORD NITRO " . TextFormat::GRAY . "(" . TextFormat::YELLOW . "Claim" . TextFormat::GRAY . ")",
				"x" => 316.5,
				"y" => 51,
				"z" => 1452.5,
				"pitch" => 0,
				"yaw" => 0,
				"level" => "s3plots",
				"item" => [57,0],
				"turn" => true,
				"skin" => [
					"enabled" => true,
					"name" => "noob"
				],
				"config" => [
					"type" => "command",
					"message" => TextFormat::GI . "Screenshot you claiming this plot and send in a question ticket on Discord to claim prize!",
					"commands" => ["p claim"]
				]
			],
			"prize-2-48" => [
				"nametag" => TextFormat::BOLD . TextFormat::LIGHT_PURPLE . "DISCORD NITRO " . TextFormat::GRAY . "(" . TextFormat::YELLOW . "Claim" . TextFormat::GRAY . ")",
				"x" => 1736.5,
				"y" => 51,
				"z" => 600.5,
				"pitch" => 0,
				"yaw" => 0,
				"level" => "s3plots",
				"item" => [57,0],
				"turn" => true,
				"skin" => [
					"enabled" => true,
					"name" => "noob"
				],
				"config" => [
					"type" => "command",
					"message" => TextFormat::GI . "Screenshot you claiming this plot and send in a question ticket on Discord to claim prize!",
					"commands" => ["p claim"]
				]
			],
		],*/


		"skyblock" => [
			"conjuror" => [
				"nametag" => TextFormat::BLUE . TextFormat::BOLD . "Conjuror",
				"x" => -14556.5,
				"y" => 120,
				"z" => 13570.5,
				"pitch" => 0,
				"yaw" => 270,
				"level" => "scifi1",
				"turn" => true,
				"sitting" => true,
				"item" => [0, 0],
				"skin" => [
					"enabled" => true,
					"name" => "conjurorfr"
				],
				"config" => [
					"type" => "command",
					"commands" => ["conjuror"]
				]
			],
			"blacksmith" => [
				"nametag" => TextFormat::DARK_GRAY . TextFormat::BOLD . "Blacksmith",
				"x" => -14556.5,
				"y" => 120,
				"z" => 13596.5,
				"pitch" => 0,
				"yaw" => 135,
				"level" => "scifi1",
				"item" => [258, 0],
				"sitting" => true,
				"turn" => true,
				"skin" => [
					"enabled" => true,
					"name" => "pblacksmith"
				],
				"config" => [
					"type" => "command",
					"commands" => ["bs"]
				]
			],

			"enchanter" => [
				"nametag" => TextFormat::DARK_PURPLE . TextFormat::BOLD . "Enchanter",
				"x" => -14563.5,
				"y" => 120,
				"z" => 13596.5,
				"pitch" => 0,
				"yaw" => 225,
				"level" => "scifi1",
				"item" => [403, 0],
				"sitting" => true,
				"turn" => true,
				"skin" => [
					"enabled" => true,
					"name" => "enchanter"
				],
				"config" => [
					"type" => "command",
					"commands" => ["enchanter"]
				]
			],

			"sn3aky" => [
				"nametag" => TextFormat::ICON_OWNER . TextFormat::YELLOW . " sn3akrr",
				"x" => -14557.5,
				"y" => 120,
				"z" => 13585.5,
				"pitch" => 0,
				"yaw" => 90,
				"level" => "scifi1",
				"item" => [260, 0],
				"sitting" => true,
				"turn" => true,
				"skin" => [
					"enabled" => true,
					"name" => "malone"
				],
				"config" => [
					"name" => TextFormat::ICON_OWNER . " sn3akrr",
					"type" => "statue",
					"messages" => [
						"Yum, this meal is quite delectable.",
						"Cheese burger",
						"Yes, I am the server owner",
						"Bagel",
					]
				]
			],

			"pringel" => [
				"nametag" => TextFormat::ICON_ENDERDRAGON . TextFormat::YELLOW . " PringelzDaddy48",
				"x" => -14627.5,
				"y" => 147.5,
				"z" => 13549.5,
				"pitch" => 0,
				"yaw" => -45,
				"level" => "scifi1",
				"item" => [256, 0],
				"sitting" => true,
				"turn" => true,
				"skin" => [
					"enabled" => true,
					"name" => "pringelbetter"
				],
				"config" => [
					"name" => TextFormat::ICON_ENDERDRAGON . " PringelzDaddy48",
					"type" => "statue",
					"messages" => [
						"spare change.... " . TextFormat::EMOJI_CRY,
						"do someone got a ladder",
						"check out my spoon",
					]
				]
			],

			"remi" => [
				"nametag" => TextFormat::ICON_MOD . TextFormat::YELLOW . " Remi5055",
				"x" => -14562.5,
				"y" => 120,
				"z" => 13585.5,
				"pitch" => 0,
				"yaw" => -90,
				"level" => "scifi1",
				"item" => [0, 0],
				"sitting" => true,
				"turn" => true,
				"skin" => [
					"enabled" => true,
					"name" => "remi2"
				],
				"config" => [
					"name" => TextFormat::ICON_MOD . " Remi5055",
					"type" => "statue",
					"messages" => [
						"Turkey Power!",
						"MrCaptainOfS is awesome :D",
						"I can smell Shane from here",
						"I hate Thanksgiving >:(",
						"Sn3aky is cool (sometimes)",
						"Shoutout to my dog (also named Remi)",
						"Turkies are the best animal!",
					]
				]
			],

			"matt" => [
				"nametag" => TextFormat::ICON_ENDERDRAGON . TextFormat::YELLOW . " mattattackalack",
				"x" => -14562.5,
				"y" => 120,
				"z" => 13581.5,
				"pitch" => 0,
				"yaw" => -90,
				"level" => "scifi1",
				"item" => [0, 0],
				"sitting" => true,
				"turn" => true,
				"skin" => [
					"enabled" => true,
					"name" => "matt"
				],
				"config" => [
					"name" => TextFormat::ICON_ENDERDRAGON . " mattattackalack",
					"type" => "statue",
					"messages" => [
						TextFormat::EMOJI_POOP,
						"I made jrbestatfortnit cry bc of cfs",
						"stop touching me",
						"you need to look more professional, wear a suit",
						"SHUT UP!",
						"THE VOICES TELL ME TO GAMBLE"
					]
				]
			],

			"kally" => [
				"nametag" => TextFormat::EMOJI_HEART_RED . TextFormat::LIGHT_PURPLE . " Kally " . TextFormat::EMOJI_HEART_RED,
				"x" => -14566.5,
				"y" => 121.4,
				"z" => 13589.5,
				"pitch" => 0,
				"yaw" => -90,
				"level" => "scifi1",
				"item" => [0, 0],
				"sitting" => true,
				"turn" => true,
				"skin" => [
					"enabled" => true,
					"name" => "kally"
				],
				"config" => [
					"name" => TextFormat::EMOJI_HEART_RED . TextFormat::LIGHT_PURPLE . " Kally",
					"type" => "statue",
					"messages" => [
						"Hey there! I'm Kally!",
						"Save me from Miguel " . TextFormat::EMOJI_CRY,
						"Beware Shane's forehead, it emits radiation",
						"UwU",
						"MIGUEL!!!!",
						"Go buy Warden rank! " . TextFormat::AQUA . "store.avengetech.net"
					]
				]
			]

		],

		"pvp" => [
			"arenas" => [
				"nametag" => TextFormat::DARK_GREEN  . "Arenas",
				"x" => 4.5,
				"y" => 63,
				"z" => 16.5,
				"pitch" => 0,
				"yaw" => 180,
				"level" => "spawn",
				"item" => [0, 0],
				"turn" => true,
				"skin" => [
					"enabled" => true,
					"name" => "noob"
				],
				"config" => [
					"type" => "command",
					"commands" => ["arena"]
				]
			],
			"duels" => [
				"nametag" => TextFormat::GREEN  . "Duels",
				"x" => 2.5,
				"y" => 63,
				"z" => 17.5,
				"pitch" => 0,
				"yaw" => 180,
				"level" => "spawn",
				"item" => [0, 0],
				"turn" => true,
				"skin" => [
					"enabled" => true,
					"name" => "noob"
				],
				"config" => [
					"type" => "command",
					"commands" => ["game duel"]
				]
			],
			"skywars" => [
				"nametag" => TextFormat::RED  . "SkyWars",
				"x" => 0.5,
				"y" => 63,
				"z" => 18.5,
				"pitch" => 0,
				"yaw" => 180,
				"level" => "spawn",
				"item" => [0, 0],
				"turn" => true,
				"skin" => [
					"enabled" => true,
					"name" => "noob"
				],
				"config" => [
					"type" => "command",
					"commands" => ["game skywars"]
				]
			],

			"oitq" => [
				"nametag" => TextFormat::AQUA  . "OITQ",
				"x" => -1.5,
				"y" => 63,
				"z" => 17.5,
				"pitch" => 0,
				"yaw" => 180,
				"level" => "spawn",
				"item" => [261, 0],
				"turn" => true,
				"skin" => [
					"enabled" => true,
					"name" => "noob"
				],
				"config" => [
					"type" => "command",
					"commands" => ["game oitq"]
				]
			],

			"botduel" => [
				"nametag" => TextFormat::YELLOW  . "Bot Duels",
				"x" => -3.5,
				"y" => 63,
				"z" => 16.5,
				"pitch" => 0,
				"yaw" => 180,
				"level" => "spawn",
				"item" => [0, 0],
				"turn" => true,
				"skin" => [
					"enabled" => true,
					"name" => "noob"
				],
				"config" => [
					"type" => "command",
					"commands" => ["game botduel true"]
				]
			],
		],

		"creative" => [
			"build-area" => [
				"nametag" => TextFormat::BOLD . TextFormat::AQUA . "Build Area",
				"x" => -6.5,
				"y" => 121,
				"z" => 16.5,
				"pitch" => 0,
				"yaw" => 180,
				"level" => "crhub",
				"item" => [2, 0],
				"turn" => true,
				"skin" => [
					"enabled" => true,
					"name" => "boy"
				],
				"config" => [
					"type" => "command",
					"commands" => ["area"]
				]
			],
			"build-battles" => [
				"nametag" => TextFormat::BOLD . TextFormat::RED . "Build Battles",
				"x" => -12.5,
				"y" => 121,
				"z" => 16.5,
				"pitch" => 0,
				"yaw" => 180,
				"level" => "crhub",
				"item" => [57, 0],
				"turn" => true,
				"skin" => [
					"enabled" => true,
					"name" => "girl"
				],
				"config" => [
					"type" => "command",
					"commands" => ["battle join"]
				]
			],

			"build-battles-leave" => [
				"nametag" => TextFormat::BOLD . TextFormat::RED . "Leave Match",
				"x" => 247.5,
				"y" => 128,
				"z" => 250.5,
				"pitch" => 0,
				"yaw" => -90,
				"level" => "bblob",
				"item" => [41, 0],
				"turn" => true,
				"skin" => [
					"enabled" => true,
					"name" => "girl"
				],
				"config" => [
					"type" => "command",
					"commands" => ["battle leave"]
				]
			],


		]
	];
}