<?php

namespace core\rules;

use pocketmine\utils\TextFormat;

class RuleData {

	const RULES = [
		"categories" => [
			[
				"name" => "General",
				"color" => TextFormat::AQUA,
				"servers" => [],
				"rules" => [
					[
						"name" => "Unacceptable Skins",
						"description" =>
						"Do not join our server with an inappropriate username or skin." . PHP_EOL . PHP_EOL .
							"Anything considered offensive or explicit is decided by staff, and bannable if not changed when asked (e.g. a nude skin)." . PHP_EOL . PHP_EOL .
							"This rule ALSO applies to using small/invisible skins in PvP, which is unacceptable.",
					],
					[
						"name" => "Impersonation of Staff",
						"description" => "Do not impersonate staff or pretend you are a staff member, personating staff will result in a warning or a ban. This includes changing your Warden nickname to resemble a staff member.",
					],
					[
						"name" => "Hacking / Client Advantages",
						//"description" => "Do not use any mods or game clients that grant you an advantage in our server, this includes any texture packs that provide benefits such as using x-ray. You will be banned for this.",
						"description" => "Do not use any macros, mods, or game clients that grant you an advantage in our server, this includes but is not limited to:" . PHP_EOL .
							"- Speed boost" . PHP_EOL .
							"- Jump boost" . PHP_EOL .
							"- Reach" . PHP_EOL .
							"- Autoclicker" . PHP_EOL .
							"- Debounce lower than " . TextFormat::YELLOW . "10ms" . TextFormat::WHITE . PHP_EOL . PHP_EOL .
							"Some modifications are excused from this rule, including toggle sprint and armor durability + keystroke HUDs. Using any unapproved client advantages will result in a 31 day ban at first, and then a permanent ban if continued after."
					],
					[
						"name" => "Exploitation of Bugs",
						"description" =>
						"Do not exploit any glitches that give you an advantage on our server, or you will be banned if found using them. This includes duplication bugs, or any other bug that gives you free items or techits." . PHP_EOL . PHP_EOL .
							"If you find any type of exploit, report it to a staff member either in-game or with a ticket in our Discord server.",
					],
					[
						"name" => "Bystander Rule",
						"description" =>
						"If a player is caught helping a player abuse a bug, they are subject to the same punishment as the person abusing it." . PHP_EOL . PHP_EOL .
							"This rule also applies if we find out you are aware of a player abusing bugs in any of our gamemodes and " . TextFormat::RED . "aren't" . TextFormat::WHITE . " reporting them."
					],
					[
						"name" => "Transferring Account Data",
						"description" =>
						"If you need to transfer any data to a new account, let the staff know by opening a ticket on Discord and explain why you need your data transferred." . PHP_EOL . PHP_EOL .
							"We have the right to deny these transfers if unnecessary, so please avoid this issue by changing your gamertag instead.",
					],
				]
			],
			[
				"name" => "Chat",
				"color" => TextFormat::RED,
				"servers" => [], //empty = all
				"rules" => [
					[
						"name" => "Spam and Chat Fill",
						"description" =>
						"Do not spam or fill chat with messages. Spam is considered a message (or similar/a few repeating messages) that is repeated 4+ times. Chat fill is considered messages of text that exceed 4 lines for mobile players and 1 line for players with bigger screens, excessively bolded and bright messages, and other messages that overall fill the chat unnecessarily. This does not count towards long messages continuing a conversation.\n\n(NOTE: This also applies to countdowns longer than 5 numbers, and spamming sentences in separate messages.)",
					],
					[
						"name" => "Vulgar Swearing",
						"description" =>
						"Casual swearing IS allowed, but please keep vulgar swear words, anything sexual or considered offensive out of the mix. (See Offensive remarks for clarification)\n\nSwearing punishments will be decided by staff depending on how severe it is.",
					],
					[
						"name" => "Offensive Remarks/Behavior",
						"description" =>
						"No homophobic, transphobic, xenophobic, racist, sexist, or ableist slurs/behavior. This rule also covers joking about ANY type of mental illness, or sexual harassment. Any offensive behavior of this type will result in a severe warning.\nTalking about killing yourself in such a way is also against the rules, and results in a normal warning.\n\nYou CANNOT bypass this rule by spelling an offensive term in a different way - they carry the same offense.\n(e.g. ret4rd, sp3d, and f4ggot would be breaking this rule.)",
					],
					[
						"name" => "Suicidal Encouragement",
						"description" =>
						"Do NOT encourage anyone to commit suicide, whether it is a joke or not.\n\nThis will result in an automatic " . TextFormat::RED . "7 day mute (Permanent if done twice)",
					],
					[
						"name" => "Bullying/Harassment",
						"description" =>
						"Banter is allowed to an extent, but do not continuously bully or harass other players (constantly teasing them via harmful nicknames or targeting them). Continuous bullying and harassment will end in a warning, and a mute if continued. However, if it is back and forth, it may not be considered bullying or harassment.",
					],
					[
						"name" => "Advertisement",
						"description" => "Do not advertise other server IPs or discord servers. Unless it is AT related, advertising of YouTube, other social media accounts, or other websites is NOT permitted. (This does not include only mentioning a username on a social media, but anything else is not permitted.)\n\nJoining AvengeTech just to advertise is not acceptable, and may result in a ban.",
					],
					[
						"name" => "Harassment of Staff",
						"description" => "Do not harass staff on how to do their job. They have the staff role for a reason, and are fully aware of their responsibilities within the server. This includes trainees.",
					],
					[
						"name" => "Provoking Players",
						"description" =>
						"Purposely provoking another player to break a rule is not tolerated on AvengeTech, doing so will result in a warning.\n\nOur definition of provoking is what falls in line of the act of a group or person purposefully seeking out a negative reaction from others.",
					],
					[
						"name" => "Threats",
						"description" =>
						"Threatening another player's life, a DDoS attack, or threatening to leak private information is not permitted and will result in severe punishment.\n\n(NOTE: This is different from video game banter, e.g. telling someone you will kill them after they kill you in an arena)",
					],
					[
						"name" => "Leaking Private Info",
						"description" =>
						"Do not share or leak IP addresses or other sensitive information, such as a home address or phone number of another player.\n\nIf staff discovers you are leaking any private information about a player, you will be banned.",
					],
					[
						"name" => "Pay Message/Whisper Spam",
						"description" => "Do not spam another player with the /pay or /tell command.",
					],
					[
						"name" => "Scamming",
						"description" =>
						"Scamming techits or in-game items IS allowed. HOWEVER, do not scam players for real money in trades, or it will result in a permanent ban.\n\nIf this happens to you, please open a ticket in our Discord server with proof.",
					],
					[
						"name" => "Inappropriate Item Names",
						"description" =>
						"Do not apply any inappropriate names or death messages to your items. This includes messages that spam the chat, or include remarks that break any of the rules above." . PHP_EOL . PHP_EOL .
							"You will be asked to remove any inappropriate item name or death message, and punished if you don't listen.",
					],
				],
			],
			[
				"name" => "Punishment Information",
				"color" => TextFormat::DARK_PURPLE,
				"servers" => [],
				"rules" => [
					[
						"name" => "Alternate Accounts",
						"description" =>
						"Alternate accounts are not allowed on our server unless it is a different person's account." . PHP_EOL . PHP_EOL .
							"If any of your accounts are banned for being an alt, you'll need to provide proof via Discord voice chat, or another form of proof to avoid the punishment.",
					],
					[
						"name" => "Ban Evasion",
						"description" => "Avoiding a ban by using a 2nd account will result in both accounts being permanently banned, regardless of what the original offense was.",
					],
					[
						"name" => "Mute Evasion",
						"description" =>
						"Avoiding a mute by using a 2nd account will result in both accounts being permanently muted, regardless of what the original offense was." . PHP_EOL . PHP_EOL .
							"If you are found to be making chat offenses while muted (via book, sign, etc...), you will be banned without question.",
					],
					[
						"name" => "Warning Evasion",
						"description" => "Do not evade warnings. Creating another account just to have a 'fresh' start does NOT mean you'll be free of warnings.",
					],
					[
						"name" => "Warning Information",
						"description" =>
						"Warnings are in place so players have a chance to redeem themselves if they break a rule. Your punishment will be based on how many warnings you get." . PHP_EOL . PHP_EOL .

							"Chat rules use the following guide to determine your punishment:" . PHP_EOL .
							TextFormat::RED . "3 warnings" . TextFormat::GRAY . " -> " . TextFormat::DARK_RED . "3 day mute" . PHP_EOL .
							TextFormat::RED . "6 warnings" . TextFormat::GRAY . " -> " . TextFormat::DARK_RED . "7 day mute" . PHP_EOL .
							TextFormat::RED . "9 warnings" . TextFormat::GRAY . " -> " . TextFormat::DARK_RED . "31 day mute" . PHP_EOL .
							TextFormat::RED . "12 warnings" . TextFormat::GRAY . " -> " . TextFormat::DARK_RED . "Permanent mute" . PHP_EOL .
							TextFormat::RED . "15 warnings" . TextFormat::GRAY . " -> " . TextFormat::DARK_RED . "Permanent ban" . PHP_EOL . PHP_EOL . TextFormat::WHITE .

							"Severe warnings use the following guide to determine your punishment:" . PHP_EOL .
							TextFormat::RED . "2 severe warnings" . TextFormat::GRAY . " -> " . TextFormat::DARK_RED . "7 day mute" . PHP_EOL .
							TextFormat::RED . "3 severe warnings" . TextFormat::GRAY . " -> " . TextFormat::DARK_RED . "Permanent mute" . PHP_EOL .
							TextFormat::RED . "4 severe warnings" . TextFormat::GRAY . " -> " . TextFormat::DARK_RED . "Permanent ban" . PHP_EOL . PHP_EOL . TextFormat::WHITE .

							"Any other type of rule that doesn't involve chat follows this guide:" . PHP_EOL .
							TextFormat::RED . "3 warnings" . TextFormat::GRAY . " -> " . TextFormat::DARK_RED . "31 day ban" . PHP_EOL .
							TextFormat::RED . "6 warnings" . TextFormat::GRAY . " -> " . TextFormat::DARK_RED . "Permanent ban" . PHP_EOL . PHP_EOL . TextFormat::WHITE .

							"As you can see, you have " . TextFormat::YELLOW . "many " . TextFormat::WHITE . "chances not to get permanently muted or banned, so please refrain from doing so. (NOTE: This does NOT apply to severe offenses like hacking, as they have a different punishment guide.)" . PHP_EOL . PHP_EOL .

							"To view your existing warnings, type " . TextFormat::YELLOW . "/mywarns" . TextFormat::WHITE . " in the chat!"
					],
					[
						"name" => "Ban Explanation",
						"description" => "A normal ban will usually result in 31 days, unless found lying in an appeal or being banned for the second time. These actions will result in a permanent ban.",
					],
					[
						"name" => "Screenshare Request",
						"description" =>
						"If you are asked to screenshare to prove you aren't using any cheats/game clients and deny this request, you will remain banned for 31 days." . PHP_EOL . PHP_EOL .
							"This ban will become permanent if you are caught lying about not cheating.",
					],
				],
			],
			[
				"name" => "Prison",
				"color" => TextFormat::DARK_PURPLE,
				"servers" => ["lobby", "prison"],
				"rules" => [
					[
						"name" => "Unacceptable Plots",
						"description" =>
						"Do not create water plots or help someone create one, doing so will result in a perm ban." . PHP_EOL . PHP_EOL .
							"Inappropriate plots are not acceptable either, this includes anything explicit/suggestive or offensive/racist. This will result in a warning, and eventually a plot clear or ban if nothing is changed.",
					],
					[
						"name" => "Plot Denied",
						"description" => "If you are denied from a plot, then you are to stay out of the plot. Usage of bugs to avoid the plot deny feature is NOT acceptable.",
					],
				],
			],
			[
				"name" => "SkyBlock",
				"color" => TextFormat::GREEN,
				"servers" => ["lobby", "skyblock"],
				"rules" => [
					[
						"name" => "Lag Creations",
						"description" => "Do not create contraptions that result in purposefully producing lag on our server. This includes water/lava spam",
					],
				],
			],

		]
	];
}
