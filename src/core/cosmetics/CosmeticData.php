<?php

namespace core\cosmetics;

use core\lootboxes\LootBoxData;

class CosmeticData {

	const TYPE_CAPE = 0;

	const TYPE_IDLE_EFFECT = 1;
	const TYPE_TRAIL_EFFECT = 2;
	const TYPE_DOUBLE_JUMP_EFFECT = 3;
	const TYPE_ARROW_EFFECT = 4;
	const TYPE_SNOWBALL_EFFECT = 5;

	const TYPE_PET = 6;

	const TYPE_MORPH = 7;

	const TYPE_HAT = 8;
	const TYPE_BACK = 9;
	const TYPE_SHOES = 10;
	const TYPE_SUIT = 11;

	const CAPE_DISCORD = 0;

	const CAPE_ENDERMITE = 1;
	const CAPE_BLAZE = 2;
	const CAPE_GHAST = 3;
	const CAPE_ENDERMAN = 4;
	const CAPE_WITHER = 5;
	const CAPE_ENDERDRAGON = 6;
	const CAPE_ENDERDRAGON_PLUS = 7;
	const CAPE_WARDEN = 24;

	const CAPE_PRIDE_RAINBOW = 8;

	const CAPE_NITRO_2022 = 9;

	const CAPE_MURICA = 10;

	const CAPE_CLOVER = 11;
	const CAPE_POT_O_GOLD = 12;

	const CAPE_PRIDE_RAINBOW_PLAIN = 13;
	const CAPE_PRIDE_AGENDER = 14;
	const CAPE_PRIDE_ASEXUAL = 15;
	const CAPE_PRIDE_BISEXUAL = 16;
	const CAPE_PRIDE_GENDERFLUID = 17;
	const CAPE_PRIDE_INTERSEX = 18;
	const CAPE_PRIDE_LESBIAN = 19;
	const CAPE_PRIDE_NONBINARY = 20;
	const CAPE_PRIDE_PANSEXUAL = 21;
	const CAPE_PRIDE_TRANS = 22; //:3
	const CAPE_PRIDE_MLM = 23;


	const CAPE_GRADIENT_RED_WHITE = 100;
	const CAPE_GRADIENT_BLUE_PURPLE = 101;
	const CAPE_GRADIENT_BLUE_PURPLE_2 = 102;
	const CAPE_GRADIENT_GREEN_SEA_GREEN = 103;
	const CAPE_GRADIENT_ORANGE_BLUE = 104;
	const CAPE_GRADIENT_ORANGE_DARK_BLUE = 105;
	const CAPE_GRADIENT_ORANGE_PURPLE = 106;
	const CAPE_GRADIENT_ORANGE_YELLOW = 107;
	const CAPE_GRADIENT_RED_AQUA = 108;
	const CAPE_GRADIENT_RED_BLUE = 109;
	const CAPE_GRADIENT_RED_BLUE_2 = 110;
	const CAPE_GRADIENT_RED_GREEN = 111;
	const CAPE_GRADIENT_RED_GREEN_2 = 112;
	const CAPE_GRADIENT_RED_ORANGE = 113;
	const CAPE_GRADIENT_RED_ORANGE_2 = 114;
	const CAPE_GRADIENT_RED_PURPLE = 115;
	const CAPE_GRADIENT_RED_SEA_GREEN = 116;
	const CAPE_GRADIENT_RED_YELLOW = 117;
	const CAPE_GRADIENT_YELLOW_BLUE = 118;
	const CAPE_GRADIENT_YELLOW_GREEN = 119;
	const CAPE_GRADIENT_YELLOW_LIGHT_BLUE = 120;
	const CAPE_GRADIENT_YELLOW_LIGHT_BLUE_2 = 121;
	const CAPE_GRADIENT_YELLOW_PURPLE = 122;
	const CAPE_GRADIENT_YELLOW_SEA_GREEN = 123;
	const CAPE_GRADIENT_ORANGE_SEA_GREEN = 124;


	const CAPE_BEE = 160;
	const CAPE_CHEEEZE = 161;
	const CAPE_TNT = 162;
	const CAPE_INVERT = 163;

	const CAPE_ANGEL_WINGS = 164;
	const CAPE_ANGR = 165;
	const CAPE_BREAB = 166;
	const CAPE_CATEAR = 167;
	const CAPE_CHERRY_BLOSSOM = 168;
	const CAPE_CHEST = 169;
	const CAPE_COWPAT = 170;
	const CAPE_DERT = 171;
	const CAPE_DEVIL = 172;
	const CAPE_DINO = 173;
	const CAPE_DOGEARS = 174;
	const CAPE_EGG = 175;
	const CAPE_FROBBER = 176;
	const CAPE_GALAXY = 177;
	const CAPE_HEARTY = 178;
	const CAPE_LIL_GUY = 179;
	const CAPE_MONKEY = 180;
	const CAPE_MOUSE = 181;
	const CAPE_ORANGE_BLOSSOM = 182;
	const CAPE_SKULL = 183;
	const CAPE_UNCRACKED = 184;
	const CAPE_UWU = 185;

	const CAPE_PRISON_PICK = 186;
	const CAPE_LOGO_BARS = 187;

	const CAPE_BORGOR = 200;
	const CAPE_BUG = 201;

	const CAPES = [
		self::CAPE_DISCORD => [
			"name" => "Discord",
			"rarity" => LootBoxData::RARITY_COMMON,
			"imgName" => "atmc",
			"lootboxes" => false,
		],

		self::CAPE_ENDERMITE => [
			"name" => "Endermite",
			"rarity" => LootBoxData::RARITY_RARE,
			"imgName" => "endermite",
			"lootboxes" => false,
		],
		self::CAPE_BLAZE => [
			"name" => "Blaze",
			"rarity" => LootBoxData::RARITY_RARE,
			"imgName" => "blaze",
			"lootboxes" => false,
		],
		self::CAPE_GHAST => [
			"name" => "Ghast",
			"rarity" => LootBoxData::RARITY_RARE,
			"imgName" => "ghast",
			"lootboxes" => false,
		],
		self::CAPE_ENDERMAN => [
			"name" => "Enderman",
			"rarity" => LootBoxData::RARITY_RARE,
			"imgName" => "enderman",
			"lootboxes" => false,
		],
		self::CAPE_WITHER => [
			"name" => "Wither",
			"rarity" => LootBoxData::RARITY_RARE,
			"imgName" => "wither",
			"lootboxes" => false,
		],
		self::CAPE_ENDERDRAGON => [
			"name" => "Enderdragon",
			"rarity" => LootBoxData::RARITY_RARE,
			"imgName" => "enderdragon",
			"lootboxes" => false,
		],
		self::CAPE_ENDERDRAGON_PLUS => [
			"name" => "Enderdragon+",
			"rarity" => LootBoxData::RARITY_RARE,
			"imgName" => "enderdragon_plus",
			"lootboxes" => false,
		],
		self::CAPE_WARDEN => [
			"name" => "Warden",
			"rarity" => LootBoxData::RARITY_RARE,
			"imgName" => "warcape",
			"lootboxes" => false,
		],

		self::CAPE_PRIDE_RAINBOW => [
			"name" => "Pride Rainbow",
			"rarity" => LootBoxData::RARITY_COMMON,
			"imgName" => "pride",
			"lootboxes" => false,
		],

		self::CAPE_NITRO_2022 => [
			"name" => "Nitro",
			"rarity" => LootBoxData::RARITY_DIVINE,
			"imgName" => "nitro",
			"lootboxes" => false,
		],

		self::CAPE_MURICA => [
			"name" => "'MURICA",
			"rarity" => LootBoxData::RARITY_RARE,
			"imgName" => "murica",
			"lootboxes" => false,
		],

		self::CAPE_CLOVER => [
			"name" => "Four Leaf Clover",
			"rarity" => LootBoxData::RARITY_RARE,
			"imgName" => "clover",
			"lootboxes" => false,
		],
		self::CAPE_POT_O_GOLD => [
			"name" => "Pot o' Gold",
			"rarity" => LootBoxData::RARITY_DIVINE,
			"imgName" => "potogold",
			"lootboxes" => false,
		],

		self::CAPE_PRIDE_RAINBOW_PLAIN => [
			"name" => "Pride Rainbow (No logo)",
			"rarity" => LootBoxData::RARITY_DIVINE,
			"imgName" => "pride_plain",
			"lootboxes" => false,
		],
		self::CAPE_PRIDE_AGENDER => [
			"name" => "Pride Agender",
			"rarity" => LootBoxData::RARITY_DIVINE,
			"imgName" => "pride_ag_shaded",
			"lootboxes" => false,
		],
		self::CAPE_PRIDE_ASEXUAL => [
			"name" => "Pride Asexual",
			"rarity" => LootBoxData::RARITY_DIVINE,
			"imgName" => "pride_as_shaded",
			"lootboxes" => false,
		],
		self::CAPE_PRIDE_BISEXUAL => [
			"name" => "Pride Bisexual",
			"rarity" => LootBoxData::RARITY_DIVINE,
			"imgName" => "pride_bi_shaded",
			"lootboxes" => false,
		],
		self::CAPE_PRIDE_GENDERFLUID => [
			"name" => "Pride Genderfluid",
			"rarity" => LootBoxData::RARITY_DIVINE,
			"imgName" => "pride_gf_shaded",
			"lootboxes" => false,
		],
		self::CAPE_PRIDE_INTERSEX => [
			"name" => "Pride Intersex",
			"rarity" => LootBoxData::RARITY_DIVINE,
			"imgName" => "pride_is_shaded",
			"lootboxes" => false,
		],
		self::CAPE_PRIDE_LESBIAN => [
			"name" => "Pride Lesbian",
			"rarity" => LootBoxData::RARITY_DIVINE,
			"imgName" => "pride_lesbian_shaded",
			"lootboxes" => false,
		],
		self::CAPE_PRIDE_NONBINARY => [
			"name" => "Pride Non-Binary",
			"rarity" => LootBoxData::RARITY_DIVINE,
			"imgName" => "pride_nb_shaded",
			"lootboxes" => false,
		],
		self::CAPE_PRIDE_PANSEXUAL => [
			"name" => "Pride Pansexual",
			"rarity" => LootBoxData::RARITY_DIVINE,
			"imgName" => "pride_pan_shaded",
			"lootboxes" => false,
		],
		self::CAPE_PRIDE_TRANS => [
			"name" => "Pride Transgender",
			"rarity" => LootBoxData::RARITY_DIVINE,
			"imgName" => "pride_trans_shaded",
			"lootboxes" => false,
		],
		self::CAPE_PRIDE_MLM => [
			"name" => "Pride MLM",
			"rarity" => LootBoxData::RARITY_DIVINE,
			"imgName" => "pride_mlm",
			"lootboxes" => false,
		],

		/** Unlockable */
		self::CAPE_GRADIENT_RED_WHITE => [
			"name" => "Gradient (Red/White)",
			"rarity" => LootBoxData::RARITY_COMMON,
			"imgName" => "gradient/red_white",
		],
		self::CAPE_GRADIENT_BLUE_PURPLE => [
			"name" => "Gradient (Blue/Purple)",
			"rarity" => LootBoxData::RARITY_COMMON,
			"imgName" => "gradient/blue_purple",
		],
		self::CAPE_GRADIENT_BLUE_PURPLE_2 => [
			"name" => "Gradient (Blue/Purple 2)",
			"rarity" => LootBoxData::RARITY_COMMON,
			"imgName" => "gradient/blue_purple_2",
		],
		self::CAPE_GRADIENT_GREEN_SEA_GREEN => [
			"name" => "Gradient (Green/Sea Green)",
			"rarity" => LootBoxData::RARITY_COMMON,
			"imgName" => "gradient/green_sea_green",
		],
		self::CAPE_GRADIENT_ORANGE_BLUE => [
			"name" => "Gradient (Orange/Blue)",
			"rarity" => LootBoxData::RARITY_COMMON,
			"imgName" => "gradient/orange_blue",
		],
		self::CAPE_GRADIENT_ORANGE_DARK_BLUE => [
			"name" => "Gradient (Orange/Dark Blue)",
			"rarity" => LootBoxData::RARITY_COMMON,
			"imgName" => "gradient/orange_dark_blue",
		],
		self::CAPE_GRADIENT_ORANGE_PURPLE => [
			"name" => "Gradient (Orange/Purple)",
			"rarity" => LootBoxData::RARITY_COMMON,
			"imgName" => "gradient/orange_purple",
		],
		self::CAPE_GRADIENT_ORANGE_SEA_GREEN => [
			"name" => "Gradient (Orange/Sea Green)",
			"rarity" => LootBoxData::RARITY_COMMON,
			"imgName" => "gradient/orange_sea_green",
		],
		self::CAPE_GRADIENT_ORANGE_YELLOW => [
			"name" => "Gradient (Orange/Yellow)",
			"rarity" => LootBoxData::RARITY_COMMON,
			"imgName" => "gradient/orange_yellow",
		],
		self::CAPE_GRADIENT_RED_AQUA => [
			"name" => "Gradient (Red/Aqua)",
			"rarity" => LootBoxData::RARITY_COMMON,
			"imgName" => "gradient/red_aqua",
		],
		self::CAPE_GRADIENT_RED_BLUE => [
			"name" => "Gradient (Red/Blue)",
			"rarity" => LootBoxData::RARITY_COMMON,
			"imgName" => "gradient/red_blue",
		],
		self::CAPE_GRADIENT_RED_BLUE_2 => [
			"name" => "Gradient (Red/Blue 2)",
			"rarity" => LootBoxData::RARITY_COMMON,
			"imgName" => "gradient/red_blue_2",
		],
		self::CAPE_GRADIENT_RED_GREEN => [
			"name" => "Gradient (Red/Green)",
			"rarity" => LootBoxData::RARITY_COMMON,
			"imgName" => "gradient/red_green",
		],
		self::CAPE_GRADIENT_RED_GREEN_2 => [
			"name" => "Gradient (Red/Green 2)",
			"rarity" => LootBoxData::RARITY_COMMON,
			"imgName" => "gradient/red_green_2",
		],
		self::CAPE_GRADIENT_RED_ORANGE => [
			"name" => "Gradient (Red/Orange)",
			"rarity" => LootBoxData::RARITY_COMMON,
			"imgName" => "gradient/red_orange",
		],
		self::CAPE_GRADIENT_RED_ORANGE_2 => [
			"name" => "Gradient (Red/Orange 2)",
			"rarity" => LootBoxData::RARITY_COMMON,
			"imgName" => "gradient/red_orange_2",
		],
		self::CAPE_GRADIENT_RED_PURPLE => [
			"name" => "Gradient (Red/Purple)",
			"rarity" => LootBoxData::RARITY_COMMON,
			"imgName" => "gradient/red_purple",
		],
		self::CAPE_GRADIENT_RED_SEA_GREEN => [
			"name" => "Gradient (Red/Sea Green)",
			"rarity" => LootBoxData::RARITY_COMMON,
			"imgName" => "gradient/red_sea_green",
		],
		self::CAPE_GRADIENT_RED_YELLOW => [
			"name" => "Gradient (Red/Yellow)",
			"rarity" => LootBoxData::RARITY_COMMON,
			"imgName" => "gradient/red_yellow",
		],
		self::CAPE_GRADIENT_YELLOW_BLUE => [
			"name" => "Gradient (Yellow/Blue)",
			"rarity" => LootBoxData::RARITY_COMMON,
			"imgName" => "gradient/yellow_blue",
		],
		self::CAPE_GRADIENT_YELLOW_GREEN => [
			"name" => "Gradient (Yellow/Green)",
			"rarity" => LootBoxData::RARITY_COMMON,
			"imgName" => "gradient/yellow_green",
		],
		self::CAPE_GRADIENT_YELLOW_LIGHT_BLUE => [
			"name" => "Gradient (Yellow/Light Blue)",
			"rarity" => LootBoxData::RARITY_COMMON,
			"imgName" => "gradient/yellow_light_blue",
		],
		self::CAPE_GRADIENT_YELLOW_LIGHT_BLUE_2 => [
			"name" => "Gradient (Yellow/Light Blue 2)",
			"rarity" => LootBoxData::RARITY_COMMON,
			"imgName" => "gradient/yellow_light_blue_2",
		],
		self::CAPE_GRADIENT_YELLOW_PURPLE => [
			"name" => "Gradient (Yellow/Purple)",
			"rarity" => LootBoxData::RARITY_COMMON,
			"imgName" => "gradient/yellow_purple",
		],
		self::CAPE_GRADIENT_YELLOW_SEA_GREEN => [
			"name" => "Gradient (Yellow/Sea Green)",
			"rarity" => LootBoxData::RARITY_COMMON,
			"imgName" => "gradient/yellow_sea_green",
		],

		self::CAPE_BEE => [
			"name" => "Bee",
			"rarity" => LootBoxData::RARITY_UNCOMMON,
			"imgName" => "fun/bee",
		],
		self::CAPE_CHEEEZE => [
			"name" => "Cheeeze",
			"rarity" => LootBoxData::RARITY_LEGENDARY,
			"imgName" => "fun/cheeeze",
		],
		self::CAPE_TNT => [
			"name" => "TNT",
			"rarity" => LootBoxData::RARITY_RARE,
			"imgName" => "fun/tnt",
		],
		self::CAPE_INVERT => [
			"name" => "Invert",
			"rarity" => LootBoxData::RARITY_RARE,
			"imgName" => "invert",
			"lootboxes" => false,
		],


		self::CAPE_ANGEL_WINGS => [
			"name" => "Angel Wings",
			"rarity" => LootBoxData::RARITY_RARE,
			"imgName" => "anglewings",
		],
		self::CAPE_ANGR => [
			"name" => "Angr",
			"rarity" => LootBoxData::RARITY_RARE,
			"imgName" => "angr",
		],
		self::CAPE_BREAB => [
			"name" => "Breab",
			"rarity" => LootBoxData::RARITY_RARE,
			"imgName" => "breab",
		],
		self::CAPE_CATEAR => [
			"name" => "Cat Ears",
			"rarity" => LootBoxData::RARITY_RARE,
			"imgName" => "catearblush",
		],
		self::CAPE_CHERRY_BLOSSOM => [
			"name" => "Cherry Blossom",
			"rarity" => LootBoxData::RARITY_COMMON,
			"imgName" => "cherryblossom",
		],
		self::CAPE_CHEST => [
			"name" => "Chest",
			"rarity" => LootBoxData::RARITY_UNCOMMON,
			"imgName" => "chestcape",
		],
		self::CAPE_COWPAT => [
			"name" => "Cow Pat",
			"rarity" => LootBoxData::RARITY_UNCOMMON,
			"imgName" => "cowpat",
		],
		self::CAPE_DERT => [
			"name" => "Dert",
			"rarity" => LootBoxData::RARITY_UNCOMMON,
			"imgName" => "dert",
		],

		self::CAPE_DEVIL => [
			"name" => "Devil",
			"rarity" => LootBoxData::RARITY_RARE,
			"imgName" => "devil cape",
		],
		self::CAPE_DINO => [
			"name" => "Dino",
			"rarity" => LootBoxData::RARITY_RARE,
			"imgName" => "dino",
		],
		self::CAPE_DOGEARS => [
			"name" => "Dog Ears",
			"rarity" => LootBoxData::RARITY_COMMON,
			"imgName" => "dogearstail",
		],
		self::CAPE_EGG => [
			"name" => "Egg",
			"rarity" => LootBoxData::RARITY_UNCOMMON,
			"imgName" => "egg",
		],
		self::CAPE_FROBBER => [
			"name" => "Frobber",
			"rarity" => LootBoxData::RARITY_RARE,
			"imgName" => "frobber",
		],
		self::CAPE_GALAXY => [
			"name" => "Galaxy",
			"rarity" => LootBoxData::RARITY_RARE,
			"imgName" => "galaxy",
		],
		self::CAPE_HEARTY => [
			"name" => "Hearty",
			"rarity" => LootBoxData::RARITY_COMMON,
			"imgName" => "hearty",
		],
		self::CAPE_LIL_GUY => [
			"name" => "Lil Guy",
			"rarity" => LootBoxData::RARITY_LEGENDARY,
			"imgName" => "lilguy",
		],

		self::CAPE_MONKEY => [
			"name" => "Monkey",
			"rarity" => LootBoxData::RARITY_RARE,
			"imgName" => "monkey",
		],
		self::CAPE_MOUSE => [
			"name" => "Mouse",
			"rarity" => LootBoxData::RARITY_UNCOMMON,
			"imgName" => "mousequestionmark",
		],
		self::CAPE_ORANGE_BLOSSOM => [
			"name" => "Orange Blossom",
			"rarity" => LootBoxData::RARITY_RARE,
			"imgName" => "orangeblossom",
		],
		self::CAPE_SKULL => [
			"name" => "Skull",
			"rarity" => LootBoxData::RARITY_RARE,
			"imgName" => "skullkill",
		],
		self::CAPE_UNCRACKED => [
			"name" => "Uncracked",
			"rarity" => LootBoxData::RARITY_COMMON,
			"imgName" => "uncracked eg",
		],
		self::CAPE_UWU => [
			"name" => "UwU",
			"rarity" => LootBoxData::RARITY_LEGENDARY,
			"imgName" => "uwucape",
		],

		self::CAPE_PRISON_PICK => [
			"name" => "Pick",
			"rarity" => LootBoxData::RARITY_DIVINE,
			"imgName" => "prison_pick",
			"lootboxes" => false
		],
		self::CAPE_LOGO_BARS => [
			"name" => "BARZ",
			"rarity" => LootBoxData::RARITY_DIVINE,
			"imgName" => "atbars",
			"lootboxes" => false
		],

		self::CAPE_BORGOR => [
			"name" => "Borgor",
			"rarity" => LootBoxData::RARITY_DIVINE,
			"imgName" => "borgor",
			"lootboxes" => false,
		],
		self::CAPE_BUG => [
			"name" => "Bug Hunter",
			"rarity" => LootBoxData::RARITY_DIVINE,
			"imgName" => "bug",
			"lootboxes" => false,
		],

	];


	const TRAIL_RED_DUST = 0;
	const TRAIL_ORANGE_DUST = 1;
	const TRAIL_YELLOW_DUST = 2;
	const TRAIL_GREEN_DUST = 3;
	const TRAIL_DARK_BLUE_DUST = 4;
	const TRAIL_LIGHT_BLUE_DUST = 5;
	const TRAIL_DARK_PURPLE_DUST = 6;
	const TRAIL_LIGHT_PURPLE_DUST = 7;
	const TRAIL_PINK_DUST = 8;
	const TRAIL_BLACK_DUST = 9;
	const TRAIL_WHITE_DUST = 10;
	const TRAIL_RAINBOW_DUST = 11;
	const TRAIL_BLACK_WHITE_DUST = 12;

	const TRAIL_FLAMING_ROCKS = 13;
	const TRAIL_SMOKE = 14;
	const TRAIL_FLAMES = 15;
	const TRAIL_SPLASH = 16;


	const IDLE_RED_CIRCLE = 0;
	const IDLE_ORANGE_CIRCLE = 1;
	const IDLE_YELLOW_CIRCLE = 2;
	const IDLE_GREEN_CIRCLE = 3;
	const IDLE_DARK_BLUE_CIRCLE = 4;
	const IDLE_LIGHT_BLUE_CIRCLE = 5;
	const IDLE_DARK_PURPLE_CIRCLE = 6;
	const IDLE_LIGHT_PURPLE_CIRCLE = 7;
	const IDLE_PINK_CIRCLE = 8;
	const IDLE_BLACK_CIRCLE = 9;
	const IDLE_WHITE_CIRCLE = 10;
	const IDLE_RAINBOW_CIRCLE = 11;
	const IDLE_BLACK_WHITE_CIRCLE = 12;



	const DJ_MEEYOW = 0;
	const DJ_KABOOM = 1;
	const DJ_FARTED = 2;
	const DJ_WOOF = 3;
	const DJ_HMMMM = 4;


	const ARROW_RED_DUST = 0;
	const ARROW_ORANGE_DUST = 1;
	const ARROW_YELLOW_DUST = 2;
	const ARROW_GREEN_DUST = 3;
	const ARROW_DARK_BLUE_DUST = 4;
	const ARROW_LIGHT_BLUE_DUST = 5;
	const ARROW_DARK_PURPLE_DUST = 6;
	const ARROW_LIGHT_PURPLE_DUST = 7;
	const ARROW_PINK_DUST = 8;
	const ARROW_BLACK_DUST = 9;
	const ARROW_WHITE_DUST = 10;
	const ARROW_RAINBOW_DUST = 11;
	const ARROW_BLACK_WHITE_DUST = 12;


	const SNOWBALL_RED_DUST = 0;
	const SNOWBALL_ORANGE_DUST = 1;
	const SNOWBALL_YELLOW_DUST = 2;
	const SNOWBALL_GREEN_DUST = 3;
	const SNOWBALL_DARK_BLUE_DUST = 4;
	const SNOWBALL_LIGHT_BLUE_DUST = 5;
	const SNOWBALL_DARK_PURPLE_DUST = 6;
	const SNOWBALL_LIGHT_PURPLE_DUST = 7;
	const SNOWBALL_PINK_DUST = 8;
	const SNOWBALL_BLACK_DUST = 9;
	const SNOWBALL_WHITE_DUST = 10;
	const SNOWBALL_RAINBOW_DUST = 11;
	const SNOWBALL_BLACK_WHITE_DUST = 12;









	//hats
	const HAT_DEVIL_HORNS = 0;
	const HAT_TRAFFIC_CONE = 1;
	const HAT_TREE = 2;
	const HAT_POOP = 3;
	const HAT_CRAB_EYES = 4;
	const HAT_THING = 5;
	const HAT_SWORD = 6;
	const HAT_HALO = 7;
	const HAT_BUNNY_EARS = 8;
	const HAT_SMILEY = 9;
	const HAT_HEADPHONES = 10;
	const HAT_BIG_HEAD = 11;

	const HAT_MASK_ANONYMOUS_RED = 12;
	const HAT_MASK_ANONYMOUS_ORANGE = 13;
	const HAT_MASK_ANONYMOUS_YELLOW = 14;
	const HAT_MASK_ANONYMOUS_GREEN = 15;
	const HAT_MASK_ANONYMOUS_BLUE = 16;
	const HAT_MASK_ANONYMOUS_PURPLE = 17;

	const HAT_STRAW = 18;
	const HAT_BIG_EARS = 19;
	const HAT_TOP = 20;
	const HAT_BOOM = 21;

	const HAT_WIDE_HEAD = 22;
	const HAT_RAINBOW_AFRO = 23;

	const HAT_ALIEN_EYES = 24;
	const HAT_SIR_LION = 25;
	const HAT_GOLD_WHEEL = 26;

	const HATS = [
		self::HAT_DEVIL_HORNS => [
			"name" => "Devil Horns",
			"rarity" => LootBoxData::RARITY_RARE,
			"dataName" => "devilhorns",
		],
		self::HAT_TRAFFIC_CONE => [
			"name" => "Traffic Cone",
			"rarity" => LootBoxData::RARITY_COMMON,
			"dataName" => "trafficcone",
		],
		self::HAT_TREE => [
			"name" => "Tree",
			"rarity" => LootBoxData::RARITY_UNCOMMON,
			"dataName" => "treeonhead",
		],
		self::HAT_POOP => [
			"name" => "Poop Head",
			"rarity" => LootBoxData::RARITY_RARE,
			"dataName" => "poop",
		],
		self::HAT_CRAB_EYES => [
			"name" => "Crab Eyes",
			"rarity" => LootBoxData::RARITY_RARE,
			"dataName" => "eyeofcrab",
		],
		self::HAT_THING => [
			"name" => "Cap",
			"rarity" => LootBoxData::RARITY_COMMON,
			"dataName" => "hatofgnus",
		],
		self::HAT_SWORD => [
			"name" => "Sword",
			"rarity" => LootBoxData::RARITY_LEGENDARY,
			"dataName" => "sword",
		],
		self::HAT_HALO => [
			"name" => "Halo",
			"rarity" => LootBoxData::RARITY_RARE,
			"dataName" => "halo",
		],
		self::HAT_BUNNY_EARS => [
			"name" => "Bunny Ears",
			"rarity" => LootBoxData::RARITY_UNCOMMON,
			"dataName" => "bunnyears",
		],
		self::HAT_SMILEY => [
			"name" => "Smiley",
			"rarity" => LootBoxData::RARITY_LEGENDARY,
			"dataName" => "smiley",
		],
		self::HAT_HEADPHONES => [
			"name" => "Headphones",
			"rarity" => LootBoxData::RARITY_UNCOMMON,
			"dataName" => "headphones",
		],
		self::HAT_BIG_HEAD => [
			"name" => "Big Ahhh Head",
			"rarity" => LootBoxData::RARITY_LEGENDARY,
			"dataName" => "bighead",
		],

		self::HAT_MASK_ANONYMOUS_RED => [
			"name" => "Red Anonymous Mask",
			"rarity" => LootBoxData::RARITY_LEGENDARY,
			"dataName" => "anonymousred",
		],
		self::HAT_MASK_ANONYMOUS_ORANGE => [
			"name" => "Orange Anonymous Mask",
			"rarity" => LootBoxData::RARITY_LEGENDARY,
			"dataName" => "anonymousorange",
		],
		self::HAT_MASK_ANONYMOUS_YELLOW => [
			"name" => "Yellow Anonymous Mask",
			"rarity" => LootBoxData::RARITY_LEGENDARY,
			"dataName" => "anonymousyellow",
		],
		self::HAT_MASK_ANONYMOUS_GREEN => [
			"name" => "Green Anonymous Mask",
			"rarity" => LootBoxData::RARITY_LEGENDARY,
			"dataName" => "anonymousgreen",
		],
		self::HAT_MASK_ANONYMOUS_BLUE => [
			"name" => "Blue Anonymous Mask",
			"rarity" => LootBoxData::RARITY_LEGENDARY,
			"dataName" => "anonymousblue",
		],
		self::HAT_MASK_ANONYMOUS_PURPLE => [
			"name" => "Purple Anonymous Mask",
			"rarity" => LootBoxData::RARITY_LEGENDARY,
			"dataName" => "anonymouspurple",
		],

		self::HAT_STRAW => [
			"name" => "Straw",
			"rarity" => LootBoxData::RARITY_COMMON,
			"dataName" => "strawhat",
		],
		self::HAT_BIG_EARS => [
			"name" => "Big Ears",
			"rarity" => LootBoxData::RARITY_RARE,
			"dataName" => "bigears",
		],
		self::HAT_TOP => [
			"name" => "Top Hat",
			"rarity" => LootBoxData::RARITY_RARE,
			"dataName" => "tophat",
		],
		self::HAT_BOOM => [
			"name" => "Boom",
			"rarity" => LootBoxData::RARITY_LEGENDARY,
			"dataName" => "bombhead",
			"lootboxes" => false,
		],

		self::HAT_WIDE_HEAD => [
			"name" => "Wide Ahhh Head",
			"rarity" => LootBoxData::RARITY_LEGENDARY,
			"dataName" => "widehead",
		],
		self::HAT_RAINBOW_AFRO => [
			"name" => "Rainbow Afro",
			"rarity" => LootBoxData::RARITY_RARE,
			"dataName" => "rainbowafro",
		],
		self::HAT_ALIEN_EYES => [
			"name" => "Alien Eyes",
			"rarity" => LootBoxData::RARITY_UNCOMMON,
			"dataName" => "alieneyes",
		],
		self::HAT_SIR_LION => [
			"name" => "Sir Lion",
			"rarity" => LootBoxData::RARITY_RARE,
			"dataName" => "sirlion",
			//"lootboxes" => false
		],
		self::HAT_GOLD_WHEEL => [
			"name" => "Gold Wheel",
			"rarity" => LootBoxData::RARITY_RARE,
			"dataName" => "goldwheel"
		],
	];

	//backs
	const BACK_JETPACK = 0;
	const BACK_LONG_ARMS = 1;
	const BACK_PACK_RED = 2;
	const BACK_PACK_WHITE = 3;
	const BACK_PACK_BLACK = 4;
	const BACK_WINGS_BAT = 5;
	const BACK_WINGS_TECHNO = 6;
	const BACK_WINGS_FIRE = 7;
	const BACK_WINGS_DEVIL = 8;
	const BACK_WINGS_ANGEL = 9;


	const BACKS = [
		self::BACK_JETPACK => [
			"name" => "Jetpack",
			"rarity" => LootBoxData::RARITY_RARE,
			"dataName" => "jetpack",
		],
		self::BACK_LONG_ARMS => [
			"name" => "Long Ahhh Arms",
			"rarity" => LootBoxData::RARITY_LEGENDARY,
			"dataName" => "longarms",
		],
		self::BACK_PACK_RED => [
			"name" => "Red Backpack",
			"rarity" => LootBoxData::RARITY_UNCOMMON,
			"dataName" => "backpackred",
		],
		self::BACK_PACK_WHITE => [
			"name" => "White Backpack",
			"rarity" => LootBoxData::RARITY_UNCOMMON,
			"dataName" => "backpackwhite",
		],
		self::BACK_PACK_BLACK => [
			"name" => "Black Backpack",
			"rarity" => LootBoxData::RARITY_UNCOMMON,
			"dataName" => "backpackblack",
		],
		self::BACK_WINGS_BAT => [
			"name" => "Bat Wings",
			"rarity" => LootBoxData::RARITY_LEGENDARY,
			"dataName" => "wingsbat",
			"animation" => "animation.player.wingsbat",
		],
		self::BACK_WINGS_TECHNO => [
			"name" => "Techno Wings",
			"rarity" => LootBoxData::RARITY_LEGENDARY,
			"dataName" => "wingstechno",
			"animation" => "animation.player.wingstechno",
		],
		self::BACK_WINGS_FIRE => [
			"name" => "Fire Wings",
			"rarity" => LootBoxData::RARITY_LEGENDARY,
			"dataName" => "wingsfire",
			"animation" => "animation.player.wingsfire",
		],
		self::BACK_WINGS_DEVIL => [
			"name" => "Devil Wings",
			"rarity" => LootBoxData::RARITY_LEGENDARY,
			"dataName" => "wingsdevil",
			"animation" => "animation.player.wingsdevil",
		],
		self::BACK_WINGS_ANGEL => [
			"name" => "Angel Wings",
			"rarity" => LootBoxData::RARITY_LEGENDARY,
			"dataName" => "wingsangel",
			"animation" => "animation.player.wingsangel",
		],
	];

	//shoes
	const SHOES_JORDAN_BLUE = 0;
	const SHOES_JORDAN_BROWN = 1;
	const SHOES_JORDAN_YELLOW = 2;
	const SHOES_JORDAN_PURPLE = 3;
	const SHOES_JORDAN_GREEN = 4;
	const SHOES_JORDAN_RED = 5;
	const SHOES_JORDAN_ORANGE = 6;
	const SHOES_JORDAN_TEAL = 7;
	const SHOES_JORDAN_MAGENTA = 8;
	const SHOES_JORDAN_BLACK = 9;

	const SHOES_SLIPPERS_PEACH = 10;
	const SHOES_SLIPPERS_BLACK = 11;
	const SHOES_SLIPPERS_GREEN = 12;
	const SHOES_SLIPPERS_RED = 13;
	const SHOES_SLIPPERS_WHITE = 14;
	const SHOES_SLIPPERS_BROWN = 15;
	const SHOES_SLIPPERS_ORANGE = 16;
	const SHOES_SLIPPERS_BLUE = 17;
	const SHOES_SLIPPERS_PURPLE = 18;
	const SHOES_SLIPPERS_MAGENTA = 19;

	const SHOES = [
		self::SHOES_JORDAN_BLUE => [
			"name" => "Blue Sneakers",
			"rarity" => LootBoxData::RARITY_UNCOMMON,
			"dataName" => "jordanblue",
		],
		self::SHOES_JORDAN_BROWN => [
			"name" => "Brown Sneakers",
			"rarity" => LootBoxData::RARITY_UNCOMMON,
			"dataName" => "jordanbrown",
		],
		self::SHOES_JORDAN_YELLOW => [
			"name" => "Yellow Sneakers",
			"rarity" => LootBoxData::RARITY_UNCOMMON,
			"dataName" => "jordanyellow",
		],
		self::SHOES_JORDAN_PURPLE => [
			"name" => "Purple Sneakers",
			"rarity" => LootBoxData::RARITY_UNCOMMON,
			"dataName" => "jordanpurple",
		],
		self::SHOES_JORDAN_GREEN => [
			"name" => "Green Sneakers",
			"rarity" => LootBoxData::RARITY_UNCOMMON,
			"dataName" => "jordangreen",
		],
		self::SHOES_JORDAN_RED => [
			"name" => "Red Sneakers",
			"rarity" => LootBoxData::RARITY_UNCOMMON,
			"dataName" => "jordanred",
		],
		self::SHOES_JORDAN_ORANGE => [
			"name" => "Orange Sneakers",
			"rarity" => LootBoxData::RARITY_UNCOMMON,
			"dataName" => "jordanorange",
		],
		self::SHOES_JORDAN_TEAL => [
			"name" => "Teal Sneakers",
			"rarity" => LootBoxData::RARITY_UNCOMMON,
			"dataName" => "jordanbluec",
		],
		self::SHOES_JORDAN_MAGENTA => [
			"name" => "Magenta Sneakers",
			"rarity" => LootBoxData::RARITY_UNCOMMON,
			"dataName" => "jordanpurplec",
		],
		self::SHOES_JORDAN_BLACK => [
			"name" => "Black Sneakers",
			"rarity" => LootBoxData::RARITY_UNCOMMON,
			"dataName" => "jordanblack",
		],

		self::SHOES_SLIPPERS_PEACH => [
			"name" => "Peach Slippers",
			"rarity" => LootBoxData::RARITY_UNCOMMON,
			"dataName" => "slipperspeach",
		],
		self::SHOES_SLIPPERS_BLACK => [
			"name" => "Black Slippers",
			"rarity" => LootBoxData::RARITY_UNCOMMON,
			"dataName" => "slippersblack",
		],
		self::SHOES_SLIPPERS_GREEN => [
			"name" => "Green Slippers",
			"rarity" => LootBoxData::RARITY_UNCOMMON,
			"dataName" => "slippersgreen",
		],
		self::SHOES_SLIPPERS_RED => [
			"name" => "Red Slippers",
			"rarity" => LootBoxData::RARITY_UNCOMMON,
			"dataName" => "slippersred",
		],
		self::SHOES_SLIPPERS_WHITE => [
			"name" => "White Slippers",
			"rarity" => LootBoxData::RARITY_UNCOMMON,
			"dataName" => "slipperswhite",
		],
		self::SHOES_SLIPPERS_BROWN => [
			"name" => "Brown Slippers",
			"rarity" => LootBoxData::RARITY_UNCOMMON,
			"dataName" => "slippersbrown",
		],
		self::SHOES_SLIPPERS_ORANGE => [
			"name" => "Orange Slippers",
			"rarity" => LootBoxData::RARITY_UNCOMMON,
			"dataName" => "slippersorange",
		],
		self::SHOES_SLIPPERS_BLUE => [
			"name" => "Blue Slippers",
			"rarity" => LootBoxData::RARITY_UNCOMMON,
			"dataName" => "slippersblue",
		],
		self::SHOES_SLIPPERS_PURPLE => [
			"name" => "Purple Slippers",
			"rarity" => LootBoxData::RARITY_UNCOMMON,
			"dataName" => "slipperspurple",
		],
		self::SHOES_SLIPPERS_MAGENTA => [
			"name" => "Magenta Slippers",
			"rarity" => LootBoxData::RARITY_UNCOMMON,
			"dataName" => "slippersfuxi",
		],
	];

	//suits
	const SUIT_JETPACK = 100; //test

	const SUITS = [
		self::SUIT_JETPACK => [
			"name" => "Jetpack",
			"rarity" => LootBoxData::RARITY_LEGENDARY,
			"dataName" => "jetpack",
			"lootboxes" => false,
		],
	];
}
