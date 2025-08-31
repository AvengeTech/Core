<?php

namespace core\entities\floatingtext;

use core\utils\TextFormat;

class TextData {

	const TEXT_DATA = [
		"lobby" => [
			"prison-tag" => [
				"text" => TextFormat::GOLD . TextFormat::BOLD . "PRISON",
				"position" => "1916.5,65.8,780.5",
				"level" => "sn3ak",
			],
			"prison-season" => [
				"text" => TextFormat::EMOJI_CONFETTI . TextFormat::YELLOW . TextFormat::BOLD . " [SEASON 1] " . TextFormat::EMOJI_CONFETTI,
				"position" => "1916.5,65.5,780.5",
				"level" => "sn3ak",
			],
			"prison-count" => [
				"text" => TextFormat::YELLOW . "{prison} "  . TextFormat::GRAY . "online",
				"position" => "1916.5,63,780.5",
				"level" => "sn3ak",
			],
			"skyblock-tag" => [
				"text" => TextFormat::AQUA . TextFormat::BOLD . "SKYBLOCK",
				"position" => "1916.5,66.8,788.5",
				"level" => "sn3ak",
			],
			"skyblock-season" => [
				"text" => TextFormat::EMOJI_CONFETTI . " " . TextFormat::YELLOW . TextFormat::BOLD . "[SEASON 2] " . TextFormat::RESET . TextFormat::EMOJI_CONFETTI,
				"position" => "1916.5,66.5,788.5",
				"level" => "sn3ak",
			],
			"skyblock-count" => [
				"text" => TextFormat::YELLOW . "{skyblock} " . TextFormat::GRAY . "online",
				"position" => "1916.5,63,788.5",
				"level" => "sn3ak",
			],

			/**"pvp-tag" => [
				"text" => TextFormat::YELLOW . TextFormat::BOLD . "PvP" . TextFormat::AQUA . " (BETA)",
				"position" => "1913.5,65.5,794.5",
				"level" => "sn3ak",
			],
			"pvp-count" => [
				"text" => TextFormat::YELLOW . "{pvp} " . TextFormat::GRAY . "online",
				"position" => "1913.5,63,794.5",
				"level" => "sn3ak",
			],*/



			"red-completion-lb-1" => [
				"text" => TextFormat::EMOJI_TROPHY . TextFormat::RED . TextFormat::BOLD . " Red parkour " . TextFormat::EMOJI_TROPHY,
				"position" => "1949.5,63,764",
				"level" => "sn3ak",
			],
			"red-completion-lb-2" => [
				"text" => TextFormat::RED . TextFormat::BOLD . "completion leaderboards",
				"position" => "1949.5,62.7,764",
				"level" => "sn3ak",
			],

			"green-completion-lb-1" => [
				"text" => TextFormat::EMOJI_TROPHY . TextFormat::GREEN . TextFormat::BOLD . " Green parkour " . TextFormat::EMOJI_TROPHY,
				"position" => "1949.5,63,805",
				"level" => "sn3ak",
			],
			"green-completion-lb-2" => [
				"text" => TextFormat::GREEN . TextFormat::BOLD . "completion leaderboards",
				"position" => "1949.5,62.7,805",
				"level" => "sn3ak",
			],

			"island-lb-1" => [
				"text" => TextFormat::EMOJI_TROPHY . TextFormat::AQUA . TextFormat::BOLD . " Island parkour leaderboards " . TextFormat::EMOJI_TROPHY,
				"position" => "2045.5,50.5,682.5",
				"level" => "sn3ak",
			],


			"welcome-1" => [
				"text" => TextFormat::MINECOIN_GOLD . "Welcome to " . TextFormat::BOLD . TextFormat::AQUA . "Avenge" . TextFormat::GOLD . "Tech",
				"position" => "1909.5,65.5,784.5",
				"level" => "sn3ak"
			],
			"welcome-2" => [
				"text" => TextFormat::MINECOIN_GOLD . "There are " . TextFormat::YELLOW . "{totalplayers}" . TextFormat::MINECOIN_GOLD . " players online!",
				"position" => "1909.5,65.2,784.5",
				"level" => "sn3ak"
			],

			"store-1" => [
				"text" => TextFormat::AQUA . "This server wouldn't be possible" . PHP_EOL .
					TextFormat::AQUA . "without our " . TextFormat::YELLOW . "donators." . TextFormat::AQUA . " They" . PHP_EOL .
					TextFormat::AQUA . "pay for our host, our webstore," . PHP_EOL .
					TextFormat::AQUA . "and even " . TextFormat::GREEN . "this lobby " . TextFormat::EMOJI_EXCLAMATION . TextFormat::EMOJI_EXCLAMATION,
				"position" => "1910.5,66,807.5",
				"level" => "sn3ak"
			],
			"store-2" => [
				"text" => TextFormat::AQUA . "Consider supporting our server's" . PHP_EOL .
					TextFormat::AQUA . "continued development by donating" . PHP_EOL .
					TextFormat::AQUA . "at our webstore!" . PHP_EOL .
					TextFormat::YELLOW . TextFormat::EMOJI_DOLLAR_SIGN . " store.avengetech.net " . TextFormat::EMOJI_DOLLAR_SIGN,
				"position" => "1910.5,64.5,807.5",
				"level" => "sn3ak"
			],

			"free-rank-1" => [
				"text" => TextFormat::AQUA . "Want a " . TextFormat::BOLD . TextFormat::YELLOW . "FREE RANK UPGRADE?" . PHP_EOL .
					TextFormat::RESET . TextFormat::AQUA . "Then vote " . TextFormat::GREEN . "every day " . TextFormat::AQUA . "of the month" . PHP_EOL .
					TextFormat::AQUA . "at " . TextFormat::YELLOW . "avengetech.net/vote",
				"position" => "1896.5,66,807.5",
				"level" => "sn3ak"
			],
			"free-rank-2" => [
				"text" => TextFormat::AQUA . "Learn more by tapping the" . PHP_EOL .
					TextFormat::RED . "paper " . TextFormat::AQUA . "in your hotbar, or " . PHP_EOL .
					TextFormat::AQUA . "by typing " . TextFormat::GREEN . "/topvoters",
				"position" => "1896.5,65,807.5",
				"level" => "sn3ak"
			],

			"discord-1" => [
				"text" => TextFormat::AQUA . "Participate in " . TextFormat::BOLD . TextFormat::YELLOW . "EPIC GIVEAWAYS" . TextFormat::RESET . TextFormat::AQUA . "," . PHP_EOL .
					TextFormat::LIGHT_PURPLE . "LIMITED TIME EVENTS" . TextFormat::AQUA . ", and get the" . PHP_EOL .
					TextFormat::GREEN . "LATEST NEWS " . TextFormat::AQUA . "by joining our" . PHP_EOL .
					TextFormat::BLUE . "Discord server!!",
				"position" => "1910.5,66,761.5",
				"level" => "sn3ak"
			],
			"discord-2" => [
				"text" => TextFormat::AQUA . "Join now to help expand our" . PHP_EOL .
					TextFormat::AQUA . "evergrowing community!" . PHP_EOL .
					TextFormat::YELLOW . "avengetech.net/discord" . PHP_EOL .
					TextFormat::RED . "(Must be 13 or older)",
				"position" => "1910.5,64.5,761.5",
				"level" => "sn3ak"
			],

			"rules-1" => [
				"text" => TextFormat::YELLOW . "Please make sure you read",
				"position" => "1945.5,62,784.5",
				"level" => "sn3ak",
			],
			"rules-2" => [
				"text" => TextFormat::YELLOW . "all of our " . TextFormat::AQUA . "rules" . TextFormat::YELLOW . " by tapping",
				"position" => "1945.5,61.7,784.5",
				"level" => "sn3ak",
			],
			"rules-3" => [
				"text" => TextFormat::YELLOW . "the " . TextFormat::LIGHT_PURPLE . "book" . TextFormat::YELLOW . " in your inventory.",
				"position" => "1945.5,61.4,784.5",
				"level" => "sn3ak",
			],
			"rules-4" => [
				"text" => TextFormat::YELLOW . "You can also view them",
				"position" => "1945.5,60.8,784.5",
				"level" => "sn3ak",
			],
			"rules-5" => [
				"text" => TextFormat::YELLOW . "by typing " . TextFormat::GREEN . "/rules",
				"position" => "1945.5,60.5,784.5",
				"level" => "sn3ak",
			],

			"rules-6" => [
				"text" => TextFormat::YELLOW . "Thanks! " . TextFormat::EMOJI_HEART_RED,
				"position" => "1945.5,59.9,784.5",
				"level" => "sn3ak",
			],
		],

		"prison" => [
			"rules-1" => [
				"text" => TextFormat::GRAY . "Make sure you've read the",
				"position" => "-817.5,29.5,383.5",
				"level" => "newpsn"
			],
			"rules-2" => [
				"text" => TextFormat::AQUA . "rules" . TextFormat::GRAY . " by typing" . TextFormat::YELLOW . " /rules",
				"position" => "-817.5,29.2,383.5",
				"level" => "newpsn"
			],

			"hangout-1" => [
				"text" => TextFormat::AQUA . TextFormat::BOLD . "Hangout",
				"position" => "-836.5,25.5,383.5",
				"level" => "newpsn",
			],
			"hangout-2" => [
				"text" => TextFormat::AQUA . "Where the fun happens.",
				"position" => "-836.5,25.2,383.5",
				"level" => "newpsn",
			],

			"cells-1" => [
				"text" => TextFormat::GOLD . TextFormat::BOLD . "Area Closed",
				"position" => "-805.5,25.5,350.5",
				"level" => "newpsn",
			],
			"cells-2" => [
				"text" => TextFormat::RED . "Area Under Construction",
				"position" => "-805.5,25.2,350.5",
				"level" => "newpsn",
			],

			"grinder-1" => [
				"text" => TextFormat::GOLD . TextFormat::BOLD . "Area Closed",
				"position" => "-774.5,25.5,383.5",
				"level" => "newpsn",
			],
			"grinder-2" => [
				"text" => TextFormat::RED . "Area Under Construction",
				"position" => "-774.5,25.2,383.5",
				"level" => "newpsn",
			],

			"mines-1" => [
				"text" => TextFormat::GOLD . TextFormat::BOLD . "Mines",
				"position" => "-805.5,25.5,416.5",
				"level" => "newpsn",
			],
			"mines-2" => [
				"text" => TextFormat::YELLOW . "Easily access every mine.",
				"position" => "-805.5,25.2,416.5",
				"level" => "newpsn",
			],

			"questmaster" => [
				"text" => "{qm}",
				"position" => "-855.5,30.85,370.5",
				"level" => "newpsn",
			],
			"blackmarket" => [
				"text" => TextFormat::LIGHT_PURPLE . "You need it?... I got it.",
				"position" => "-855.5,30.85,396.5",
				"level" => "newpsn",
			],

			"blacksmith" => [
				"text" => TextFormat::GRAY . "I'm the fixer-upper here!",
				"position" => "-850.5,30.85,391.5",
				"level" => "newpsn",
			],
			"sellhand" => [
				"text" => TextFormat::YELLOW . "Tap with item to sell!",
				"position" => "-850.5,30.85,375.5",
				"level" => "newpsn",
			],

			"enchanter" => [
				"text" => TextFormat::YELLOW . "Enchant your tools!",
				"position" => "-883.5,27.85,383.5",
				"level" => "newpsn",
			],

			//Plots world
			//plots are even numbered, so XZ need to be whole numbers
			"plots-1" => [
				"text" => TextFormat::GREEN . "Welcome to the " . TextFormat::BOLD . TextFormat::AQUA . "legacy plots" . TextFormat::RESET . TextFormat::GREEN . " world for Season 1!",
				"position" => "16,52,23.5",
				"level" => "plots-season1p1",
			],
			"plots-2" => [
				"text" => TextFormat::GREEN . "Use " . TextFormat::GRAY . "/p help " . TextFormat::GREEN . "for a detailed list",
				"position" => "16,51.7,23.5",
				"level" => "plots-season1p1",
			],
			"plots-3" => [
				"text" => TextFormat::GREEN . "of plot commands!",
				"position" => "16,51.4,23.5",
				"level" => "plots-season1p1",
			],

			"plots-1-2" => [
				"text" => TextFormat::GREEN . "Welcome to the " . TextFormat::BOLD . TextFormat::AQUA . "legacy plots" . TextFormat::RESET . TextFormat::GREEN . " world for Season 2!",
				"position" => "16,52,23.5",
				"level" => "plots-season1p2",
			],
			"plots-2-2" => [
				"text" => TextFormat::GREEN . "Use " . TextFormat::GRAY . "/p help " . TextFormat::GREEN . "for a detailed list",
				"position" => "16,51.7,23.5",
				"level" => "plots-season1p2",
			],
			"plots-3-2" => [
				"text" => TextFormat::GREEN . "of plot commands!",
				"position" => "16,51.4,23.5",
				"level" => "plots-season1p2",
			],


			"new-plots-1" => [
				"text" => TextFormat::GRAY . "Welcome to the " . TextFormat::AQUA . "Basic Plots " . TextFormat::GRAY . "world!",
				"position" => "32,58,44.5",
				"level" => "s0plots"
			],
			"new-plots-2" => [
				"text" => TextFormat::GRAY . "Type " . TextFormat::YELLOW . "/p help " . TextFormat::GRAY . "for a list",
				"position" => "32,57.7,44.5",
				"level" => "s0plots"
			],
			"new-plots-3" => [
				"text" => TextFormat::GRAY . "of plot commands!",
				"position" => "32,57.4,44.5",
				"level" => "s0plots"
			],

			"new-plots-4" => [
				"text" => TextFormat::GRAY . "To teleport to a specific",
				"position" => "44.5,58,32",
				"level" => "s0plots"
			],
			"new-plots-5" => [
				"text" => TextFormat::GRAY . "plot, type " . TextFormat::YELLOW . "/p warp <id>" . TextFormat::GRAY . " with",
				"position" => "44.5,57.7,32",
				"level" => "s0plots"
			],
			"new-plots-6" => [
				"text" => TextFormat::GRAY . "the plot's ID!",
				"position" => "44.5,57.4,32",
				"level" => "s0plots"
			],

			"new-plots-7" => [
				"text" => TextFormat::GRAY . "Each " . TextFormat::AQUA . "Basic Plot" . TextFormat::GRAY . " in this world",
				"position" => "19.5,58,32",
				"level" => "s0plots"
			],
			"new-plots-8" => [
				"text" => TextFormat::GRAY . "costs " . TextFormat::AQUA . "5,000 Techits" . TextFormat::GRAY . " to claim, and",
				"position" => "19.5,57.7,32",
				"level" => "s0plots"
			],
			"new-plots-9" => [
				"text" => TextFormat::GRAY . "is a " . TextFormat::YELLOW . "64x64 " . TextFormat::GRAY . "block square!",
				"position" => "19.5,57.4,32",
				"level" => "s0plots"
			],


			"nether-plots-1" => [
				"text" => TextFormat::GRAY . "Welcome to the " . TextFormat::RED . "Nether Plots " . TextFormat::GRAY . "world!",
				"position" => "36,58,48.5",
				"level" => "nether_plots_s0"
			],
			"nether-plots-2" => [
				"text" => TextFormat::GRAY . "Type " . TextFormat::YELLOW . "/p help " . TextFormat::GRAY . "for a list",
				"position" => "36,57.7,48.5",
				"level" => "nether_plots_s0"
			],
			"nether-plots-3" => [
				"text" => TextFormat::GRAY . "of plot commands!",
				"position" => "36,57.4,48.5",
				"level" => "nether_plots_s0"
			],

			"nether-plots-4" => [
				"text" => TextFormat::GRAY . "To teleport to a specific",
				"position" => "47.5,58,38",
				"level" => "nether_plots_s0"
			],
			"nether-plots-5" => [
				"text" => TextFormat::GRAY . "plot, type " . TextFormat::YELLOW . "/p warp <id>" . TextFormat::GRAY . " with",
				"position" => "47.5,57.7,38",
				"level" => "nether_plots_s0"
			],
			"nether-plots-6" => [
				"text" => TextFormat::GRAY . "the plot's ID!",
				"position" => "47.5,57.4,38",
				"level" => "nether_plots_s0"
			],


			"nether-plots-7" => [
				"text" => TextFormat::GRAY . "Each " . TextFormat::RED . "Nether Plot" . TextFormat::GRAY . " in this world",
				"position" => "25.5,58,38",
				"level" => "nether_plots_s0"
			],
			"nether-plots-8" => [
				"text" => TextFormat::GRAY . "costs " . TextFormat::AQUA . "250,000 Techits" . TextFormat::GRAY . " to claim, and",
				"position" => "25.5,57.7,38",
				"level" => "nether_plots_s0"
			],
			"nether-plots-9" => [
				"text" => TextFormat::GRAY . "is a " . TextFormat::YELLOW . "76x76 " . TextFormat::GRAY . "block square!",
				"position" => "25.5,57.4,38",
				"level" => "nether_plots_s0"
			],

			"end-plots-1" => [
				"text" => TextFormat::GRAY . "Welcome to the " . TextFormat::LIGHT_PURPLE . "End Plots " . TextFormat::GRAY . "world!",
				"position" => "63.5,53,92.5",
				"level" => "end_plots_s0"
			],
			"end-plots-2" => [
				"text" => TextFormat::GRAY . "Type " . TextFormat::YELLOW . "/p help " . TextFormat::GRAY . "for a list",
				"position" => "63.5,52.7,92.5",
				"level" => "end_plots_s0"
			],
			"end-plots-3" => [
				"text" => TextFormat::GRAY . "of plot commands!",
				"position" => "63.5,52.4,92.5",
				"level" => "end_plots_s0"
			],

			"end-plots-4" => [
				"text" => TextFormat::GRAY . "To teleport to a specific",
				"position" => "68.5,53,87.5",
				"level" => "end_plots_s0"
			],
			"end-plots-5" => [
				"text" => TextFormat::GRAY . "plot, type " . TextFormat::YELLOW . "/p warp <id>" . TextFormat::GRAY . " with",
				"position" => "68.5,52.7,87.5",
				"level" => "end_plots_s0"
			],
			"end-plots-6" => [
				"text" => TextFormat::GRAY . "the plot's ID!",
				"position" => "68.5,52.4,87.5",
				"level" => "end_plots_s0"
			],

			"end-plots-7" => [
				"text" => TextFormat::GRAY . "Each " . TextFormat::LIGHT_PURPLE . "End Plot" . TextFormat::GRAY . " in this world",
				"position" => "58.5,53,87.5",
				"level" => "end_plots_s0"
			],
			"end-plots-8" => [
				"text" => TextFormat::GRAY . "costs " . TextFormat::AQUA . "1,000,000 Techits" . TextFormat::GRAY . " to claim, and",
				"position" => "58.5,52.7,87.5",
				"level" => "end_plots_s0"
			],
			"end-plots-9" => [
				"text" => TextFormat::GRAY . "is a " . TextFormat::YELLOW . "128x128 " . TextFormat::GRAY . "block square!",
				"position" => "58.5,52.4,87.5",
				"level" => "end_plots_s0"
			],




			"bt-data-title" => [
				"text" => TextFormat::GOLD . TextFormat::BOLD . "Your Stats",
				"position" => "-793.5,30.9,391.5",
				"level" => "newpsn",
			],
			"bt-data-1" => [
				"text" => TextFormat::YELLOW . "{btstarted}" . TextFormat::GRAY . " matches started",
				"position" => "-793.5,30.4,391.5",
				"level" => "newpsn",
			],
			"bt-data-2" => [
				"text" => TextFormat::YELLOW . "{btwins}" . TextFormat::GRAY . " matches won",
				"position" => "-793.5,30.1,391.5",
				"level" => "newpsn",
			],
			"bt-data-3" => [
				"text" => TextFormat::YELLOW . "{btmined}" . TextFormat::GRAY . " blocks mined",
				"position" => "-793.5,29.8,391.5",
				"level" => "newpsn",
			],

			"bt-title" => [
				"text" => TextFormat::BOLD . TextFormat::AQUA . "Block Tournament Stats",
				"position" => "-795.5,31.5,393.5",
				"level" => "newpsn",
			],
			"bt-title-2" => [
				"text" => TextFormat::ITALIC . TextFormat::GRAY . "Type " . TextFormat::YELLOW . "/bt help " . TextFormat::GRAY . "to learn more",
				"position" => "-795.5,31.2,393.5",
				"level" => "newpsn",
			],



			"bt-lb-title" => [
				"text" => TextFormat::GREEN . TextFormat::BOLD . "Current Game Leaderboard",
				"position" => "-797.5,30.9,395.5",
				"level" => "newpsn",
			],
			"bt-lb-1" => [
				"text" => "{bts:1}",
				"position" => "-797.5,30.4,395.5",
				"level" => "newpsn",
			],
			"bt-lb-2" => [
				"text" => "{bts:2}",
				"position" => "-797.5,30.1,395.5",
				"level" => "newpsn",
			],
			"bt-lb-3" => [
				"text" => "{bts:3}",
				"position" => "-797.5,29.8,395.5",
				"level" => "newpsn",
			],
			"bt-lb-4" => [
				"text" => "{bts:4}",
				"position" => "-797.5,29.5,395.5",
				"level" => "newpsn",
			],
			"bt-lb-5" => [
				"text" => "{bts:5}",
				"position" => "-797.5,29.2,395.5",
				"level" => "newpsn",
			],
			"bt-lb-6" => [
				"text" => "{bts:6}",
				"position" => "-797.5,28.9,395.5",
				"level" => "newpsn",
			],
			"bt-lb-7" => [
				"text" => "{bts:7}",
				"position" => "-797.5,28.6,395.5",
				"level" => "newpsn",
			],
			"bt-lb-8" => [
				"text" => "{bts:8}",
				"position" => "-797.5,28.3,395.5",
				"level" => "newpsn",
			],
			"bt-lb-9" => [
				"text" => "{bts:9}",
				"position" => "-797.5,28,395.5",
				"level" => "newpsn",
			],
			"bt-lb-10" => [
				"text" => "{bts:10}",
				"position" => "-797.5,27.7,395.5",
				"level" => "newpsn",
			],


			"battle-spectate" => [
				"text" => TextFormat::YELLOW . TextFormat::BOLD . "{bg} " . TextFormat::RESET . TextFormat::GRAY . "gangs in battle!",
				"position" => "-815.5,29.85,373.5",
				"level" => "newpsn",
			],

			"death-lb-text-1" => [
				"text" => TextFormat::GOLD . TextFormat::BOLD . "Death Leaderboards",
				"position" => "-810,28.5,441.5",
				"level" => "newpsn",
			],
			"death-lb-text-2" => [
				"text" => TextFormat::YELLOW . "For noobs ONLY!",
				"position" => "-810,28.2,441.5",
				"level" => "newpsn",
			],
			"death-lb-text-3" => [
				"text" => TextFormat::YELLOW . "(Updates every 10 minutes)",
				"position" => "-810,27.9,441.5",
				"level" => "newpsn",
			],
		],

		"skyblock-archive" => [
			"main-1" => [
				"text" => TextFormat::GREEN . "Welcome to " . TextFormat::BOLD . TextFormat::AQUA . "Avenge" . TextFormat::GOLD . "Tech",
				"position" => "-1215.5,173.3,3147.5",
				"level" => "skyblock",
			],
			"main-2" => [
				"text" => TextFormat::GREEN . "skyblock!",
				"position" => "-1215.5,173,3147.5",
				"level" => "skyblock"
			],

			"rules-1" => [
				"text" => TextFormat::GREEN . "Make sure you've read the",
				"position" => "-1212,161.5,3179.5",
				"level" => "skyblock"
			],
			"rules-2" => [
				"text" => TextFormat::AQUA . "rules" . TextFormat::GREEN . " by typing" . TextFormat::YELLOW . " /rules",
				"position" => "-1212,161.2,3179.5",
				"level" => "skyblock"
			],

			"sub-1" => [
				"text" => TextFormat::GREEN . "Follow the bridge! Don't fall!",
				"position" => "-1215,172,3160.5",
				"level" => "skyblock"
			],

			"info-1" => [
				"text" => TextFormat::GREEN . "Type " . TextFormat::AQUA . "/island" . TextFormat::GREEN . " to open the",
				"position" => "-1205.5,161.3,3218.5",
				"level" => "skyblock"
			],
			"info-2" => [
				"text" => TextFormat::GREEN . "Island Menu!",
				"position" => "-1205.5,161,3218.5",
				"level" => "skyblock"
			],

			"info-3" => [
				"text" => TextFormat::GREEN . "Crates are down this path!",
				"position" => "-1193.5,163,3223.5",
				"level" => "skyblock"
			],

			"arena-1" => [
				"text" => TextFormat::RED . "WARNING: NO KEEP INVENTORY!",
				"position" => "-1222.5,174.425,3141.5",
				"level" => "skyblock"
			],
			"arena-2" => [
				"text" => TextFormat::YELLOW . "{arena} " . TextFormat::GRAY . "players in arena!",
				"position" => "-1222.5,173.825,3141.5",
				"level" => "skyblock"
			],

			"lb-1" => [
				"text" => TextFormat::GREEN . "Real " . TextFormat::EMOJI_DOLLAR_SIGN . TextFormat::EMOJI_DOLLAR_SIGN . TextFormat::EMOJI_DOLLAR_SIGN . " and",
				"position" => "-1221.5,174.125,3145.5",
				"level" => "skyblock"
			],
			"lb-2" => [
				"text" => TextFormat::AQUA . "techit " . TextFormat::ICON_TOKEN . " prizes!!",
				"position" => "-1221.5,173.825,3145.5",
				"level" => "skyblock"
			],


			"island-lb-1" => [
				"text" => TextFormat::AQUA . "Stay on this leaderboard until" . PHP_EOL .
					TextFormat::AQUA . "the end of the season to win" . PHP_EOL .
					TextFormat::YELLOW . "a prize!",
				"position" => "-1214.5,167,3244.5",
				"level" => "skyblock"
			],
			"island-lb-2" => [
				"text" => TextFormat::DARK_PURPLE . TextFormat::BOLD . "PRIZES" . PHP_EOL .
					TextFormat::YELLOW . "1st: " . TextFormat::EMOJI_DOLLAR_SIGN . " $75 PayPal" . PHP_EOL .
					TextFormat::GOLD . "2nd: " . TextFormat::EMOJI_DOLLAR_SIGN . " $50 PayPal" . PHP_EOL .
					TextFormat::RED . "3rd: " . TextFormat::EMOJI_DOLLAR_SIGN . " $25 PayPal" . PHP_EOL .
					TextFormat::YELLOW . "The rest: " . TextFormat::EMOJI_MONEY_BAG . TextFormat::LIGHT_PURPLE . " $10 store credit",
				"position" => "-1214.5,165.5,3244.5",
				"level" => "skyblock"
			],

		],

		"skyblock-1" => [
			"duel-1" => [
				"text" => TextFormat::YELLOW . "{duel} " . TextFormat::GRAY . "players dueling!",
				"position" => "-1223.5,174.125,3136.5",
				"level" => "skyblock"
			],
			"duel-2" => [
				"text" => TextFormat::YELLOW . "{dqueue} " . TextFormat::GRAY . "in queue!",
				"position" => "-1223.5,173.825,3136.5",
				"level" => "skyblock"
			],
			"duel-3" => [
				"text" => TextFormat::GREEN . "KEEP INVENTORY ENABLED!",
				"position" => "-1223.5,174.425,3136.5",
				"level" => "skyblock"
			],
		],

		"skyblock" => [
			"main-1" => [
				"text" => TextFormat::GREEN . "Welcome to " . TextFormat::BOLD . TextFormat::AQUA . "Avenge" . TextFormat::GOLD . "Tech",
				"position" => "-14592,121.8,13583.5",
				"level" => "scifi1",
			],
			"main-2" => [
				"text" => TextFormat::GREEN . "skyblock!",
				"position" => "-14592,121.5,13583.5",
				"level" => "scifi1"
			],

			"sits-1" => [
				"text" => TextFormat::GREEN . "Top 2 players with the highest",
				"position" => "-14562,120,13583.5",
				"level" => "scifi1"
			],
			"sits-2" => [
				"text" => TextFormat::GREEN . "island level will sit at the table",
				"position" => "-14562,119.7,13583.5",
				"level" => "scifi1"
			],
			"sits-3" => [
				"text" => TextFormat::GREEN . "for the next season! " . TextFormat::EMOJI_WOW,
				"position" => "-14562,119.4,13583.5",
				"level" => "scifi1"
			],

			"island-lb-1" => [
				"text" => TextFormat::AQUA . "Stay on this leaderboard until" . PHP_EOL .
					TextFormat::AQUA . "the end of the season to win" . PHP_EOL .
					TextFormat::YELLOW . "a prize!",
				"position" => "-14560,125,13567",
				"level" => "scifi1"
			],
			"island-lb-2" => [
				"text" => TextFormat::DARK_PURPLE . TextFormat::BOLD . "PRIZES" . PHP_EOL .
					TextFormat::YELLOW . "1st: " . TextFormat::EMOJI_DOLLAR_SIGN . " $75 PayPal" . PHP_EOL .
					TextFormat::GOLD . "2nd: " . TextFormat::EMOJI_DOLLAR_SIGN . " $50 PayPal" . PHP_EOL .
					TextFormat::RED . "3rd: " . TextFormat::EMOJI_DOLLAR_SIGN . " $25 PayPal" . PHP_EOL .
					TextFormat::YELLOW . "The rest: " . TextFormat::EMOJI_MONEY_BAG . TextFormat::LIGHT_PURPLE . " $10 store credit",
				"position" => "-14560,123.7,13567",
				"level" => "scifi1"
			],

			"leaderboards-1" => [
				"text" => TextFormat::YELLOW . TextFormat::BOLD . "LEADERBOARDS",
				"position" => "-14627.5,118.5,13598.5",
				"level" => "scifi1"
			],

			"warzone-1" => [
				"text" => TextFormat::DARK_RED . TextFormat::BOLD . "WARZONE",
				"position" => "-14635,118.5,13583.5",
				"level" => "scifi1"
			],
			"warzone-2" => [
				//"text" => TextFormat::RED . "Current map: " . TextFormat::YELLOW . "{warzone}",
				"text" => TextFormat::RED . "Fight to the death!",
				"position" => "-14635,118.2,13583.5",
				"level" => "scifi1"
			],

			"warzone-3" => [
				"text" => TextFormat::EMOJI_CAUTION . TextFormat::YELLOW . TextFormat::BOLD . " CAUTION! " . TextFormat::RESET . TextFormat::EMOJI_CAUTION,
				"position" => "-14647.5,122.5,13583.5",
				"level" => "scifi1"
			],
			"warzone-4" => [
				"text" => TextFormat::RED . "In the warzone, PvP is enabled",
				"position" => "-14647.5,122.2,13583.5",
				"level" => "scifi1"
			],
			"warzone-5" => [
				"text" => TextFormat::RED . "at " . TextFormat::DARK_RED . "all times..." . TextFormat::RED . " So be prepared!",
				"position" => "-14647.5,121.9,13583.5",
				"level" => "scifi1"
			],
			"warzone-6" => [
				"text" => TextFormat::RED . "Have a safe landing " . TextFormat::EMOJI_PEACE_OUT,
				"position" => "-14647.5,121.4,13583.5",
				"level" => "scifi1"
			],

			"crates-1" => [
				"text" => TextFormat::AQUA . TextFormat::BOLD . "CRATES",
				"position" => "-14627.5,118.5,13568.5",
				"level" => "scifi1"
			],

			"parkour-ez-1" => [
				"text" => TextFormat::GOLD . TextFormat::BOLD . "PARKOUR",
				"position" => "-14612.5,118.5,13604.5",
				"level" => "scifi1"
			],
			"parkour-ez-2" => [
				"text" => TextFormat::YELLOW . "Difficulty: " . TextFormat::GREEN . "EASY",
				"position" => "-14612.5,118.2,13604.5",
				"level" => "scifi1"
			],
			"parkour-ez-start" => [
				"text" => TextFormat::YELLOW . "Start",
				"position" => "-14612.5,118,13648.5",
				"level" => "scifi1"
			],
			"parkour-ez-checkpoint-1" => [
				"text" => TextFormat::AQUA . "Checkpoint",
				"position" => "-14636.5,115,13692.5",
				"level" => "scifi1"
			],
			"parkour-ez-fin" => [
				"text" => TextFormat::GREEN . "Finish",
				"position" => "-14573.5,152,13670.5",
				"level" => "scifi1"
			],

			"parkour-hard-1" => [
				"text" => TextFormat::GOLD . TextFormat::BOLD . "PARKOUR",
				"position" => "-14612.5,118.5,13562.5",
				"level" => "scifi1"
			],
			"parkour-hard-2" => [
				"text" => TextFormat::YELLOW . "Difficulty: " . TextFormat::RED . "NOT AS EASY",
				"position" => "-14612.5,118.2,13562.5",
				"level" => "scifi1"
			],
			"parkour-hard-start" => [
				"text" => TextFormat::YELLOW . "Start",
				"position" => "-14612.5,118,13518.5",
				"level" => "scifi1"
			],
			"parkour-hard-checkpoint-1" => [
				"text" => TextFormat::AQUA . "Checkpoint 1",
				"position" => "-14658.5,90,13485.5",
				"level" => "scifi1"
			],
			"parkour-hard-checkpoint-2" => [
				"text" => TextFormat::AQUA . "Checkpoint 2",
				"position" => "-14622.5,146,13450.5",
				"level" => "scifi1"
			],
			"parkour-hard-fin" => [
				"text" => TextFormat::GREEN . "Finish",
				"position" => "-14561.5,163,13533.5",
				"level" => "scifi1"
			],


			"vote-party-1" => [
				"text" => TextFormat::BOLD . TextFormat::YELLOW . "Vote Party",
				"position" => "-14747.5,122,13583.5",
				"level" => "scifi1"
			],
			"vote-party-2" => [
				"text" => TextFormat::GRAY . "Items will drop here for",
				"position" => "-14747.5,121.7,13583.5",
				"level" => "scifi1"
			],
			"vote-party-3" => [
				"text" => TextFormat::GRAY . "every " . TextFormat::YELLOW . "50 votes" . TextFormat::GRAY . " we get!",
				"position" => "-14747.5,121.4,13583.5",
				"level" => "scifi1"
			],

			"vote-party-4" => [
				"text" => TextFormat::GRAY . "Vote now! Or I will",
				"position" => "-14747.5,120.9,13583.5",
				"level" => "scifi1"
			],
			"vote-party-5" => [
				"text" => TextFormat::RED . TextFormat::BOLD . "kill" . TextFormat::RESET . TextFormat::GRAY . " your family.",
				"position" => "-14747.5,120.6,13583.5",
				"level" => "scifi1"
			],
		],

		"skyblock-1archive" => [
			"island-lb-1" => [
				"text" => "",
				"position" => "-1214.5,167,3244.5",
				"level" => "skyblock"
			],
			"island-lb-2" => [
				"text" => "",
				"position" => "-1214.5,167,3244.5",
				"level" => "skyblock"
			],
		],
		"skyblock-2archive" => [
			"island-lb-1" => [
				"text" => "",
				"position" => "-1214.5,167,3244.5",
				"level" => "skyblock"
			],
			"island-lb-2" => [
				"text" => "",
				"position" => "-1214.5,167,3244.5",
				"level" => "skyblock"
			],
		],

		"pvp" => [
			"welcome-1" => [
				"text" => TextFormat::GRAY . "Welcome to " . TextFormat::BOLD . TextFormat::AQUA . "Avenge" . TextFormat::GOLD . "Tech",
				"position" => "0.5,65.2,9.5",
				"level" => "spawn"
			],
			"welcome-2" => [
				"text" => TextFormat::YELLOW . TextFormat::BOLD . "PvP" . TextFormat::BOLD . TextFormat::RED . " (BETA)",
				"position" => "0.5,64.9,9.5",
				"level" => "spawn"
			],

			"suggest-1" => [
				"text" => TextFormat::YELLOW . "Have a suggestion? We're",
				"position" => "-9.5,64.2,9.5",
				"level" => "spawn"
			],
			"suggest-2" => [
				"text" => TextFormat::YELLOW . "open to new ideas! Leave one",
				"position" => "-9.5,63.9,9.5",
				"level" => "spawn"
			],
			"suggest-3" => [
				"text" => TextFormat::YELLOW . "at " . TextFormat::AQUA . "avengetech.net/discord",
				"position" => "-9.5,63.6,9.5",
				"level" => "spawn"
			],
		],

		"creative" => [
			"join-1" => [
				"text" => TextFormat::GRAY . "Welcome to the " . TextFormat::BOLD . TextFormat::AQUA . "Avenge" . TextFormat::GOLD . "Tech",
				"position" => "-13.5,122.6,-7.5",
				"level" => "crhub"
			],
			"join-2" => [
				"text" => TextFormat::YELLOW . "Creative " . TextFormat::RED . "BETA",
				"position" => "-13.5,122.3,-7.5",
				"level" => "crhub"
			],

			"lb-1" => [
				"text" => TextFormat::YELLOW . "Leaderboards",
				"position" => "-19.5,123.3,1.5",
				"level" => "crhub"
			],
			"lb-2" => [
				"text" => TextFormat::YELLOW . "Showing type: " . TextFormat::GOLD . "{lbtype}",
				"position" => "-19.5,123,1.5",
				"level" => "crhub"
			],

			"we-1" => [
				"text" => TextFormat::AQUA . "Vote for the server with " . TextFormat::DARK_AQUA . "/vote",
				"position" => "-9.5,123,4.5",
				"level" => "crhub"
			],
			"we-2" => [
				"text" => TextFormat::AQUA . "To access world edit commands!",
				"position" => "-9.5,122.7,4.5",
				"level" => "crhub"
			],

		],

	];
}