<?php

namespace core\techie;

use core\utils\TextFormat;

class Structure {

	const CONVERSATION_PROMPT = "[REDACTED]";

	const TECHIE_DATA = [
		"lobby" => [
			"pos" => [
				"x" => 236.5,
				"y" => 19,
				"z" => 248.5,
				"level" => "lobby",
			],
			"dialogue" => [
				"Tap a gamemode bot to easily access a gamemode!",
				"Stay on the monthly parkour leaderboards for a chance to win a rank upgrade and shards every month! Type /lbprizes for more info",
			],
		],
		"prison" => [
			"pos" => [
				"x" => -815.5,
				"y" => 28,
				"z" => 393.5,
				"level" => "newpsn",
			],
			"dialogue" => [
				"Did you know you can vote for a TON of vote keys? Go do it. /vote",
				"Did you know that you can make secure item trades by using /trade? Don't get scammed!",
				"Use /eguide to view how every custom enchantment works!",
				"Visit the Blacksmith at the hangout to repair and rename tools, and add custom death messages!",
				"To use an Enchantment Book, visit the Enchanter, located at the hangout!",
				"Open Mystery Boxes at the hangout! Get Mystery Boxes by mining, or voting!",
				"Want to support the server? Go vote! You'll also get a ton of prizes too! Type /vote to learn more",
				"Help me get some new shiny parts by purchasing something at {store}! You won't regret it :D",
				"Access the plot world by typing /plots! Let your imagination run wild here!",
				"Need more space to build in? Get a rank! Up to 8 extra plots! {store}",
				"Easily kill people in the PvP mine! Put your custom enchantments to good use!",
				"Need to get rid of useless items? Use /trash, save the environment by NOT throwing items on the ground!",
				"Use /aguide to view a description of every Animator!",
				"Want to put a custom death animation on your sword? Collect an Animator in the mystery boxes and visit the Blacksmith to apply!",
				"Want to learn everything about gangs? Type /gangs now!",
				"Learn how cells work by typing /cells in the chat!",
				"Did you know that players with ghast rank or above can use /repair for easy item repairing?",
				"Everytime you prestige, you get 2 divine keys! Use these in the Hangout for divine crate exclusive prizes!",
				"Did you know that ranked players can open their cell stores while in a cell queue? Purchase one now at store.avengetech.net",
				"Auction off your items in the Auction House by using /ah",
				"These guards sure are protective! I wouldn't swing my sword around them if I were you...",
				"Did you know that Enderdragon " . TextFormat::ICON_ENDERDRAGON . " ranked players can reset mines every 5 minutes? To do so, type /mine <letter> reset",
				"Did you know you can get FREE stone tools in the black market? Help a new player out if they need tools! " . TextFormat::EMOJI_HEART_RED,
				"Stay on the monthly KOTH leaderboards for a chance to win Discord Nitro Classic or store credit at the end of every month! Type /lbprizes for more info",
			],
		],
		"skyblock" => [
			"pos" => [
				"x" => -14557.5,
				"y" => 120,
				"z" => 13581.5,
				"level" => "scifi1",
				"sitting" => true
			],
			"dialogue" => [
				"Use /island to open the island menu!",
				"Did you know that you can make secure item trades by using /trade? Don't get scammed!",
				"Islands that are public can be visited from the island menu!",
				"Type /challenges to see all the challenges you have unlocked!",
				"The more you level up your island, the more challenges you unlock!",
				"Type /kit to see all available kits you have!",
				"Want to open crates faster? Purchase a rank and get rid of those keys! {store}",
				"Want to open crates faster? Purchase a rank and get rid of those keys! {store}",
				"Did you know that if you have Blaze " . TextFormat::ICON_BLAZE . " rank or higher, you can fly in any island? It's amazing! Buy a rank at {store}",
				"Each paid rank has it's own kit that can be used. The higher your rank, the more kits you have access to! {store}",
				"Mess up your island? Type /kit starter to get all of the chest items back!",
				"You can go into your game settings and toggle whether you want XP or block drops to automatically collect.",
				"Save the environment by keeping AutoInv and AutoXP toggled ON in the settings!",
				"Tired of watching crate animations? Use /vote to get 10 minutes of instant crate opening without a rank!",
				"Tired of opening crates? Use /vote to get 10 minutes of instant crate opening without a rank!",
				"Did you know you can win " . TextFormat::EMOJI_DOLLAR_SIGN . TextFormat::YELLOW . " REAL MONEY " . TextFormat::EMOJI_DOLLAR_SIGN . TextFormat::AQUA . " through our leaderboards? Type /lbprizes to learn more!",
				"Some leaderboards reset weekly and monthly. Stay on them until they reset to get free prizes! /leaderboards",
				"Upgrade your island level to increase the size of it!",
				"Use /trade to make safe trades with other players! Don't get scammed",
				"Earn shards that can be used to craft loot boxes by by completing the spawn parkour!",
				"Did you know that enderdragon ranked players can make a SECOND island? Double the islands, double the fun! {store}",
				"Stay on the monthly leaderboards for a chance to win PayPal, store credit or techit prizes at the end of every month! Type /lbprizes for more info",
				"Type /tree to learn how to upgrade your tools!",
				"Wondering what essence is or does? Type /essenceguide!",
				"Confused on what pets do? Type /petguide!"
			],
		],
		"creative" => [
			"pos" => [
				"x" => -5.5,
				"y" => 120,
				"z" => -14.5,
				"level" => "crhub"
			],
			"dialogue" => [
				"CREATIVE MODE??? bro",
				"Purchasing ghast rank or higher will give you UNLIMITED world edit priveleges in build areas! store.avengetech.net",
				"Did you know you can vote to unlock temporary world edit access? Type /vote to learn more!",
				"Manage your build area warps by running /area warps while at your area!",
				//"This gamemode server is in BETA. Expect weekly gameplay updates and tweaks until we release the gamemode to everyone!"
			],
		],

		"pvp" => [
			"pos" => [
				"x" => 5.5,
				"y" => 64,
				"z" => 0.5,
				"level" => "spawn"
			],
			"dialogue" => [
				"This is a beta gamemode! Report new bugs you find to our discord at avengetech.net/discord",
			]
		],
	];

	const GLOBAL_DIALOGUE = [
		"Make sure you read the /rules! You don't want to get muted or banned... Or do you? " . TextFormat::EMOJI_DEVIL_HAPPY,
		"Read all of the /rules to make sure you don't break any of them! Thanks " . TextFormat::EMOJI_HEART_RED,
		"Learn how to link your Discord account ingame by using /verify",
		"Want to sync your ingame rank with our Discord server? Use /verify",
		"Did you know that players verified in our Discord can activate a special Discord Cape? Crazy stuff!",
		"Use the /capes menu to activate your Discord Cape!",
		"Sheeeesh.. This Discord cape is lookin SUPA HEAT! " . TextFormat::EMOJI_FIRE . TextFormat::EMOJI_FIRE . " Unlock it now by typing /verify",
		"Did you know you can easily link your Discord account ingame with /verify? Me neither!",
		"Enter exclusive giveaways and be FIRST to know what's up with our server by joining our Discord! avengetech.net/discord",
		"Enter exclusive giveaways and be FIRST to know what's up with our server by joining our Discord! avengetech.net/discord",
		"Vote for our server! You can receive cool stuff ingame and we gain more players! Win win! Type /vote to learn more",
		"Vote for our server! You can receive cool stuff ingame and we gain more players! Win win! Type /vote to learn more",
		"Vote for our server! You support us, and we give you free stuff. Win win! Type /vote to learn more",
		"Did you know that Enderdragon ranked players have godlike fists, and can Falcon PUUNCH staff members in lobbys? It's really fun!",
		"Support our server by purchasing a rank at store.avengetech.net!",
		"Support our server by purchasing a rank at store.avengetech.net!",
		"Support our server by purchasing a rank at store.avengetech.net!",
		"Support our server by purchasing a rank at store.avengetech.net!",
		"Want to win a free rank this month? Learn how by typing /topvoters",
		"Vote everyday this month for a chance to win a free rank! Learn more with /topvoters",
		"Vote everyday this month for a chance to win a free rank! Learn more with /topvoters",
		"You have a chance to win a free rank EVERY MONTH! Type /topvoters for more info!",
		"You have a chance to win a free rank EVERY MONTH! Type /topvoters for more info!",
		"You have a chance to win a free rank EVERY MONTH! Type /topvoters for more info!",
		"The more days you vote in a row, the better your vote prizes will be! Type /vote to learn more about vote streaks!",
		"The more days you vote in a row, the better your vote prizes will be! Type /vote to learn more about vote streaks!",
		"Have a cool build you want to show off? Visit our Discord and type -submit in the #commands channel to submit your build!",
		"Have a cool build you want to show off? Visit our Discord and type -submit in the #commands channel to submit your build!",
		"Did you know that the owner of AvengeTech (Sn3ak) makes SICK music? Check out his music on Spotify, Apple Music, and more! Just search 'Sn3ak'!",
		"Hey you! When's the last time you drank any water? " . TextFormat::EMOJI_DROP,
		"Make sure you remember to drink water! It'd be a shame if you didn't... " . TextFormat::EMOJI_DROP . TextFormat::EMOJI_DROP,
		"Need assistance from a staff member? Look for a player with a shield next to their name in chat! " . TextFormat::ICON_TRAINEE . TextFormat::ICON_MOD,
		"Need assistance from a staff member? Look for a player with a shield next to their name in chat! " . TextFormat::ICON_TRAINEE . TextFormat::ICON_MOD,
		"Hey! My name's Techie, but you can call me Techie!",
		"A wise man once said, 'Buy enderdragon rank'. I think they were on to something!",
		"Boost our Discord server to get a cool Nitro Booster cape for FREE!!! (Make sure your account is linked with /verify)",
		"Boost our Discord server to get a cool Nitro Booster cape for FREE!!! (Make sure your account is linked with /verify)",
		"Did you know you can earn free loot boxes every 5 days you vote in a row?",
		"Did you know with the Warden subscription you can access nicknames, custom rank icons, and more? Check it out at {store}!"
	];

	const BUBBLES = [
		"default" => [
			TextFormat::EMOJI_EXPLOSION . TextFormat::AQUA . " Store.avengetech.net " . TextFormat::EMOJI_EXPLOSION,
			TextFormat::EMOJI_100 . TextFormat::AQUA . " avengetech.net/discord " . TextFormat::EMOJI_100,
			TextFormat::ICON_AVENGETECH . TextFormat::YELLOW . "avengetech.net/vote" . TextFormat::ICON_AVENGETECH,
			TextFormat::YELLOW . "Learn how to vote! " . TextFormat::AQUA . "/vote",

			TextFormat::EMOJI_EAR_LEFT . TextFormat::EMOJI_EYE . TextFormat::EMOJI_MOUTH . TextFormat::EMOJI_EYE . TextFormat::EMOJI_EAR_RIGHT,

			TextFormat::YELLOW . "Tap me for info!",
			TextFormat::YELLOW . "Hey! I'm " . TextFormat::AQUA . TextFormat::BOLD . "Techie " . TextFormat::RESET . TextFormat::EMOJI_TECHIE,
			TextFormat::YELLOW . "Who are YOU?",
			TextFormat::YELLOW . "Who am I???",

			TextFormat::YELLOW . "Check this out " . TextFormat::EMOJI_GOTEM,

			TextFormat::YELLOW . "Sn3ak is my dad " . TextFormat::EMOJI_HEART_RED,
			TextFormat::YELLOW . "I love you! " . TextFormat::EMOJI_HEART_RED,
			TextFormat::YELLOW . "Make sure you read the /rules",
			TextFormat::YELLOW . "Cheese, beans, gravy...",
			TextFormat::YELLOW . "Corn, squash, peas...",
			TextFormat::YELLOW . "Burgre " . TextFormat::EMOJI_BURGER,
		],
		"lobby" => [],
		"prison" => [
			TextFormat::YELLOW . "The Grinder STINKS!",
			TextFormat::YELLOW . "Cells??? LET'S GOOOOOO",
			TextFormat::YELLOW . "A guard looked at me funny " . TextFormat::EMOJI_FROWN,
		],
		"skyblock" => [
			TextFormat::YELLOW . "Try /island!",
			TextFormat::YELLOW . "I fell off my island " . TextFormat::EMOJI_FROWN,
		],
		"creative" => [
			TextFormat::YELLOW . "Broooo... Creative mode??",
		],
		"pvp" => [
			TextFormat::YELLOW . "Try SkyWars"
		],
	];
}
