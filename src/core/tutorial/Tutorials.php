<?php

namespace core\tutorial;

use pocketmine\Server;
use pocketmine\entity\Location;

use core\AtPlayer as Player;
use core\Core;
use core\tutorial\command\TutorialCommand;
use core\tutorial\entity\QuestionMark;
use core\tutorial\sequence\{
	Sequence,
};
use core\utils\TextFormat;

class Tutorials {

	public ?Tutorial $tutorial = null;

	public array $activeTutorials = [];

	public function __construct(public Core $core) {
		$core->getServer()->getCommandMap()->register("tutorial", new TutorialCommand($core, "tutorial", "Start tutorial"));

		switch (($ts = $core::thisServer())->getType()) {
			case "lobby":
				$world = Server::getInstance()->getWorldManager()->getWorldByName("sn3ak");
				$this->setTutorial(new Tutorial(0, "lobby", [
					new Sequence(
						"the start",
						100,
						new Location(1903.5, 70, 784.5, $world, -90, 20),
						"Welcome to the AvengeTech lobby! From here, you can join any of our available gamemodes! (Prisons and skyblock as of now)",
						TextFormat::ICON_AVENGETECH,
						TextFormat::YELLOW . "Lobby tutorial started!"
					),
					new Sequence(
						"gamemode",
						100,
						new Location(1904.5, 64, 772.5, $world, -60, 0),
						"To join, tap any of these gamemode bots in the front of spawn! You can also use the compass in your hotbar",
					),
					new Sequence(
						"lootboxes",
						140,
						new Location(1835.5, 86, 784.5, $world, -90, 45),
						"This is the loot box area. Loot boxes are used to earn lobby gadgets, and various other types of cosmetics that can be equipped server wide!",
						TextFormat::EMOJI_WOW,
						TextFormat::AQUA . "Loot boxes!!!!"
					),
					new Sequence(
						"how to get em",
						120,
						new Location(1852.5, 75, 784.5, $world, -90, 0),
						"Loot boxes are earned in various ways throughout the server, or purchased at " . TextFormat::AQUA . "store.avengetech.net"
					),
					new Sequence(
						"voting",
						160,
						new Location(1852.5, 75, 784.5, $world, -90, 0),
						"The easiest way to get some is by voting. Every time you vote, you get at least 100 shards (more on weekends + 1st of the month.) You can also earn whole loot boxes by holding a vote streak!"
					),
					new Sequence(
						"loot box crafting",
						140,
						new Location(1850.5, 75, 792.5, $world, -45, 0),
						"Shards are used to craft more loot boxes. Access the loot box crafting menu from any loot box in the lobby!"
					),
					new Sequence(
						"red parkour",
						120,
						new Location(1941, 80, 753.5, $world, -145, 40),
						"Parkour courses are found throughout the lobby. Each course completion will earn you " . TextFormat::AQUA . "3 shards.",
						TextFormat::EMOJI_COLD,
						TextFormat::RED . "Parkour!!!"
					),
					new Sequence(
						"green parkour",
						120,
						new Location(1941.5, 76, 817.5, $world, -45, 30),
						"Complete each course as many times as you'd like! Try to get the lowest possible time to end up on the leaderboards"
					),
					new Sequence(
						"island parkour",
						140,
						new Location(2035.5, 50, 689.5, $world, -90, 0),
						"Whoever completes each parkour course the most times at the end of each month will earn " . TextFormat::GOLD . "1 rank upgrade" . TextFormat::YELLOW . " OR a free month of Warden rank " . TextFormat::ICON_WARDEN,
						TextFormat::EMOJI_WINGED_MONEY,
						TextFormat::YELLOW . "Free ranks!"
					),
					new Sequence(
						"end",
						10,
						new Location(1903.5, 65, 784.5, $world, -90, 0),
						"You have completed the lobby tutorial! I hope you have a great experience on our server " . TextFormat::EMOJI_HAPPIER,
						TextFormat::ICON_AVENGETECH,
						TextFormat::YELLOW . "Tutorial complete!"
					),
				]));
				break;
			case "skyblock":
				$world = Server::getInstance()->getWorldManager()->getWorldByName("scifi1");
				$this->setTutorial(new Tutorial(0, "skyblock", [
					new Sequence(
						"the start",
						80,
						new Location(-14581.5, 135, 13583.5, $world, 90, -10),
						"Welcome to AvengeTech SkyBlock: Season 5! Check out this cool view " . TextFormat::EMOJI_COOL,
						TextFormat::ICON_AVENGETECH,
						TextFormat::YELLOW . "SkyBlock tutorial started!"
					),
					new Sequence(
						"island",
						70,
						new Location(-14599.5, 120, 13583.5, $world, 90, 10),
						"To access all island features, walk into this big ahhhh globe.",
					),
					new Sequence(
						"island2",
						70,
						new Location(-14599.5, 120, 13583.5, $world, 90, 10),
						"From this menu, you can create an island, manage island invites, and view public islands!",
					),
					new Sequence(
						"island3",
						60,
						new Location(-14599.5, 120, 13583.5, $world, 90, 10),
						"TIP: You can also access this menu anywhere by typing /is",
					),
					new Sequence(
						"surrounding",
						60,
						new Location(-14598.5, 116.5, 13578.5, $world, 70, 0),
						"Surrounding the globe are a few other shortcut menus... Stats, shop, techit games, and the auction house.",
					),
					new Sequence(
						"surrounding2",
						60,
						new Location(-14598.5, 116.5, 13578.5, $world, 70, 0),
						"For more information, tap on one of them!",
					),
					new Sequence(
						"easy parkour",
						100,
						new Location(-14612.5, 120, 13631.5, $world, 0, 0),
						"Parkour courses are found on the left and right sides of the lobby. Each course completion will earn you " . TextFormat::AQUA . "5-10 shards.",
						TextFormat::EMOJI_COLD,
						TextFormat::RED . "Parkour!!!"
					),
					new Sequence(
						"hard parkour",
						100,
						new Location(-14612.5, 120, 13537.5, $world, 180, 0),
						"Complete each course as many times as you'd like! Try to get the lowest possible time to end up on the leaderboards"
					),
					new Sequence(
						"warzone",
						80,
						new Location(-14637.5, 136, 13583.5, $world, 90, 30),
						"The warzone is a dangerous place... Home of many warriors and dangerous creatures.",
						TextFormat::EMOJI_SKULL,
						TextFormat::RED . "WARZONE"
					),
					new Sequence(
						"warzone2",
						80,
						new Location(-14637.5, 136, 13583.5, $world, 90, 30),
						"Think you have what it takes to defeat them? Jump into the portal!",
					),
					new Sequence(
						"warzone3",
						70,
						new Location(-14637.5, 136, 13583.5, $world, 90, 30),
						"Various forms of loot also spawn here, such as supply drops and money bags.",
					),
					new Sequence(
						"warzone4",
						80,
						new Location(-14637.5, 136, 13583.5, $world, 90, 30),
						"Supply drops offer a wide variety of loot, from max enchantment books and ore generators, to divine keys!",
					),
					new Sequence(
						"warzone5",
						80,
						new Location(-14637.5, 136, 13583.5, $world, 90, 30),
						"Money bags contain techits, ranging from 2,500 up to 100,000. Try your luck!",
					),
					new Sequence(
						"leaderboards",
						100,
						new Location(-14701.5, 126, 13667.5, $world, 25, 30),
						"Here are the main leaderboards! Think you have what it takes to reach the top of them???",
					),
					new Sequence(
						"leaderboards2",
						80,
						new Location(-14701.5, 126, 13667.5, $world, 25, 30),
						"No? Well I do, I believe in you. Keep following your dreams!!!!",
					),
					new Sequence(
						"leaderboards3",
						60,
						new Location(-14701.5, 126, 13667.5, $world, 25, 30),
						"Some reset weekly and monthly, and some give you prizes when they reset"
					),
					new Sequence(
						"leaderboards4",
						60,
						new Location(-14693.5, 124, 13665.5, $world, 0, 20),
						"For more info on prizes, tap this lil trophy thang, or type /lbprizes"
					),
					new Sequence(
						"crates",
						80,
						new Location(-14699.5, 125, 13496.5, $world, 135, 20),
						"These are the crates. Pretty majestic looking, right?"
					),
					new Sequence(
						"crates2",
						80,
						new Location(-14699.5, 125, 13496.5, $world, 135, 20),
						"Crates can be found by mining cobblestone and ores on your island, or killing mobs"
					),
					new Sequence(
						"crates3",
						100,
						new Location(-14705.5, 121, 13490.5, $world, 135, 20),
						"Except for these bad boys. These are the divine crates, and there are only a few ways to get their keys"
					),
					new Sequence(
						"crates3",
						100,
						new Location(-14705.5, 121, 13490.5, $world, 135, 20),
						"They can be found in supply drops, and 3 are earned for every 5 island levels you reach after level 15"
					),
					new Sequence(
						"crates4",
						80,
						new Location(-14705.5, 121, 13490.5, $world, 135, 20),
						"You can also get some by voting every day of a single month."
					),
					new Sequence(
						"vote",
						80,
						new Location(-14587.5, 120, 13592.5, $world, 20, 0),
						"Speaking of voting... Type /vote to learn more about it!!!"
					),
					new Sequence(
						"vote2",
						120,
						new Location(-14587.5, 120, 13592.5, $world, 20, 0),
						"The longer you hold a vote streak, the better prizes you get. And the best part, it's FREE!!!"
					),
					new Sequence(
						"end",
						10,
						new Location(-14583.5, 121, 13583.5, $world, 90, 0),
						"You have completed the SkyBlock tutorial! I hope you have a great experience on our server " . TextFormat::EMOJI_HAPPIER,
						TextFormat::ICON_AVENGETECH,
						TextFormat::YELLOW . "Tutorial complete!"
					),
				]));
				break;
			case "prison":

				break;
		}

		if (isset(TutorialData::QUESTION_LOCATIONS[$type = $ts->getType()])) {
			$world = Server::getInstance()->getWorldManager()->getWorldByName(TutorialData::QUESTION_LOCATIONS[$type]["world"]);
			if ($world === null) {
				Server::getInstance()->getWorldManager()->loadWorld(TutorialData::QUESTION_LOCATIONS[$type]["world"]);
				$world = Server::getInstance()->getWorldManager()->getWorldByName(TutorialData::QUESTION_LOCATIONS[$type]["world"]);
				if ($world === null) return;
			}
			foreach (TutorialData::QUESTION_LOCATIONS[$type]["positions"] as $pos) {
				$box = new QuestionMark(new Location(array_shift($pos), array_shift($pos), array_shift($pos), $world, 0, 0));
				$box->spawnToAll();
			}
		}
	}

	public function tick(): void {
		if ($this->getTutorial() !== null) {
			foreach ($this->getActiveTutorials() as $name => $tutorial) {
				if ($tutorial->getCurrentSequence()->tick()) {
					$player = Server::getInstance()->getPlayerExact($name);
					if ($player !== null) {
						$tutorial->getCurrentSequence()->end($player);
						if ($tutorial->hasNextSequence()) {
							$tutorial->currentSequence++;
							$tutorial->getCurrentSequence()->start($player);
						} else {
							$tutorial->end($player);
							unset($this->activeTutorials[$name]);
						}
					} else {
						unset($this->activeTutorials[$name]);
					}
				}
			}
		}
	}

	public function getTutorial(): ?Tutorial {
		return $this->tutorial;
	}

	public function setTutorial(Tutorial $tutorial): void {
		$this->tutorial = $tutorial;
	}

	public function getActiveTutorials(): array {
		return $this->activeTutorials;
	}

	public function getActiveTutorial(Player $player): ?Tutorial {
		return $this->activeTutorials[$player->getName()] ?? null;
	}

	public function inTutorial(Player $player): bool {
		return isset($this->activeTutorials[$player->getName()]);
	}

	public function startTutorial(Player $player): void {
		$this->activeTutorials[$player->getName()] = clone $this->getTutorial();
		$this->activeTutorials[$player->getName()]->start($player);
	}

	public function endTutorial(Player $player): void {
		if ($this->inTutorial($player)) {
			$tutorial = $this->getActiveTutorial($player);
			unset($this->activeTutorials[$player->getName()]);
			$tutorial->end($player);
		}
	}
}
