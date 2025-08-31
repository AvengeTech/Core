<?php

namespace core\lootboxes;

use pocketmine\Server;
use pocketmine\entity\Location;

use core\{
	Core,
	AtPlayer as Player
};
use core\cosmetics\Cosmetic;
use core\gadgets\type\Gadget;
use core\lootboxes\command\{
	AddLootBoxes,
	AddShards
};
use core\lootboxes\entity\LootBox;
use core\ui\windows\SimpleForm;
use core\utils\TextFormat;
use pocketmine\entity\EntityDataHelper;
use pocketmine\entity\EntityFactory;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\world\World;

class LootBoxes {

	public array $prizes = [
		LootBoxData::RARITY_COMMON => [
			LootBoxData::PRIZE_COSMETIC => [],
			LootBoxData::PRIZE_CAPE => [],
			LootBoxData::PRIZE_EFFECT => [],
			LootBoxData::PRIZE_GADGET => []
		],
		LootBoxData::RARITY_UNCOMMON => [
			LootBoxData::PRIZE_COSMETIC => [],
			LootBoxData::PRIZE_CAPE => [],
			LootBoxData::PRIZE_EFFECT => [],
			LootBoxData::PRIZE_GADGET => []
		],
		LootBoxData::RARITY_RARE => [
			LootBoxData::PRIZE_COSMETIC => [],
			LootBoxData::PRIZE_CAPE => [],
			LootBoxData::PRIZE_EFFECT => [],
			LootBoxData::PRIZE_GADGET => []
		],
		LootBoxData::RARITY_LEGENDARY => [
			LootBoxData::PRIZE_COSMETIC => [],
			LootBoxData::PRIZE_CAPE => [],
			LootBoxData::PRIZE_EFFECT => [],
			LootBoxData::PRIZE_GADGET => []
		],
		LootBoxData::RARITY_DIVINE => [
			LootBoxData::PRIZE_COSMETIC => [],
			LootBoxData::PRIZE_CAPE => [],
			LootBoxData::PRIZE_EFFECT => [],
			LootBoxData::PRIZE_GADGET => []
		]
	];

	public function __construct(public Core $plugin) {
		$plugin->getServer()->getCommandMap()->registerAll("lootboxes", [
			new AddLootBoxes($plugin, "addlootboxes", "Give loot boxes"),
			new AddShards($plugin, "addshards", "Give shards"),
		]);
		$this->setupPrizes();
		$this->spawnBoxes();
	}

	public function setupPrizes(): void {
		foreach (Core::getInstance()->getGadgets()->getGadgets() as $gadget) {
			$this->prizes[$gadget->getRarity()][LootBoxData::PRIZE_GADGET][] = $gadget;
		}
		foreach (Core::getInstance()->getCosmetics()->getCapes() as $cape) {
			if ($cape->canWin()) $this->prizes[$cape->getRarity()][LootBoxData::PRIZE_CAPE][] = $cape;
		}
		foreach (Core::getInstance()->getCosmetics()->getIdles() as $idle) {
			if ($idle->canWin()) $this->prizes[$idle->getRarity()][LootBoxData::PRIZE_EFFECT][] = $idle;
		}
		foreach (Core::getInstance()->getCosmetics()->getTrails() as $trail) {
			if ($trail->canWin()) $this->prizes[$trail->getRarity()][LootBoxData::PRIZE_EFFECT][] = $trail;
		}
		foreach (Core::getInstance()->getCosmetics()->getDoubleJumps() as $dj) {
			if ($dj->canWin()) $this->prizes[$dj->getRarity()][LootBoxData::PRIZE_EFFECT][] = $dj;
		}
		foreach (Core::getInstance()->getCosmetics()->getArrows() as $arrow) {
			if ($arrow->canWin()) $this->prizes[$arrow->getRarity()][LootBoxData::PRIZE_EFFECT][] = $arrow;
		}
		foreach (Core::getInstance()->getCosmetics()->getSnowballs() as $snowball) {
			if ($snowball->canWin()) $this->prizes[$snowball->getRarity()][LootBoxData::PRIZE_EFFECT][] = $snowball;
		}

		foreach (Core::getInstance()->getCosmetics()->getHats() as $hat) {
			if ($hat->canWin()) $this->prizes[$hat->getRarity()][LootBoxData::PRIZE_COSMETIC][] = $hat;
		}
		foreach (Core::getInstance()->getCosmetics()->getBacks() as $back) {
			if ($back->canWin()) $this->prizes[$back->getRarity()][LootBoxData::PRIZE_COSMETIC][] = $back;
		}
		foreach (Core::getInstance()->getCosmetics()->getShoes() as $shoes) {
			if ($shoes->canWin()) $this->prizes[$shoes->getRarity()][LootBoxData::PRIZE_COSMETIC][] = $shoes;
		}
		foreach (Core::getInstance()->getCosmetics()->getSuits() as $suit) {
			if ($suit->canWin()) $this->prizes[$suit->getRarity()][LootBoxData::PRIZE_COSMETIC][] = $suit;
		}
	}

	public function openMultiple(Player $player, int $amount): void {
		$player->getSession()->getLootBoxes()->takeLootBoxes($amount);
		$origAmount = $amount;
		$prizes = [];
		$shards = 0;
		$duplicates = 0;
		while ($amount > 0) {
			$prizes[] = $this->getRandomPrize();
			$amount--;
		}

		$sortedPrizes = [
			"gadgets" => [],
			"cosmetics" => [
				"cape" => [
					LootBoxData::RARITY_DIVINE => [],
					LootBoxData::RARITY_LEGENDARY => [],
					LootBoxData::RARITY_RARE => [],
					LootBoxData::RARITY_UNCOMMON => [],
					LootBoxData::RARITY_COMMON => [],
				],
				"trail effect" => [
					LootBoxData::RARITY_DIVINE => [],
					LootBoxData::RARITY_LEGENDARY => [],
					LootBoxData::RARITY_RARE => [],
					LootBoxData::RARITY_UNCOMMON => [],
					LootBoxData::RARITY_COMMON => [],
				],
				"idle effect" => [
					LootBoxData::RARITY_DIVINE => [],
					LootBoxData::RARITY_LEGENDARY => [],
					LootBoxData::RARITY_RARE => [],
					LootBoxData::RARITY_UNCOMMON => [],
					LootBoxData::RARITY_COMMON => [],
				],
				"double jump effect" => [
					LootBoxData::RARITY_DIVINE => [],
					LootBoxData::RARITY_LEGENDARY => [],
					LootBoxData::RARITY_RARE => [],
					LootBoxData::RARITY_UNCOMMON => [],
					LootBoxData::RARITY_COMMON => [],
				],
				"arrow effect" => [
					LootBoxData::RARITY_DIVINE => [],
					LootBoxData::RARITY_LEGENDARY => [],
					LootBoxData::RARITY_RARE => [],
					LootBoxData::RARITY_UNCOMMON => [],
					LootBoxData::RARITY_COMMON => [],
				],
				"snowball effect" => [
					LootBoxData::RARITY_DIVINE => [],
					LootBoxData::RARITY_LEGENDARY => [],
					LootBoxData::RARITY_RARE => [],
					LootBoxData::RARITY_UNCOMMON => [],
					LootBoxData::RARITY_COMMON => [],
				],
				"hat" => [
					LootBoxData::RARITY_DIVINE => [],
					LootBoxData::RARITY_LEGENDARY => [],
					LootBoxData::RARITY_RARE => [],
					LootBoxData::RARITY_UNCOMMON => [],
					LootBoxData::RARITY_COMMON => [],
				],
				"back" => [
					LootBoxData::RARITY_DIVINE => [],
					LootBoxData::RARITY_LEGENDARY => [],
					LootBoxData::RARITY_RARE => [],
					LootBoxData::RARITY_UNCOMMON => [],
					LootBoxData::RARITY_COMMON => [],
				],
				"shoes" => [
					LootBoxData::RARITY_DIVINE => [],
					LootBoxData::RARITY_LEGENDARY => [],
					LootBoxData::RARITY_RARE => [],
					LootBoxData::RARITY_UNCOMMON => [],
					LootBoxData::RARITY_COMMON => [],
				],
				"suits" => [
					LootBoxData::RARITY_DIVINE => [],
					LootBoxData::RARITY_LEGENDARY => [],
					LootBoxData::RARITY_RARE => [],
					LootBoxData::RARITY_UNCOMMON => [],
					LootBoxData::RARITY_COMMON => [],
				],
			]
		];
		foreach ($prizes as $prize) {
			if ($prize instanceof Gadget) {
				if (!isset($sortedPrizes["gadgets"][$prize->getName()])) {
					$sortedPrizes["gadgets"][$prize->getName()] = $prize->getLootBoxTotal();
				} else {
					$sortedPrizes["gadgets"][$prize->getName()] += $prize->getLootBoxTotal();
				}
				$player->getSession()->getGadgets()->addTotal($prize, $total = $prize->getLootBoxTotal());
			} elseif ($prize instanceof Cosmetic) {
				if ($player->getSession()->getCosmetics()->hasCosmetic($prize)) {
					$player->getSession()->getLootBoxes()->addShards($sh = $prize->getShardWorth());
					$shards += $sh;
					$duplicates++;
				} else {
					$player->getSession()->getCosmetics()->addCosmetic($prize);
					if (!in_array($prize->getName(), $sortedPrizes["cosmetics"][$prize->getTypeName()][$prize->getRarity()])) {
						$sortedPrizes["cosmetics"][$prize->getTypeName()][$prize->getRarity()][] = $prize->getName();
					}
				}
			}
		}

		$outcomeText = "Here are the results of opening " . TextFormat::YELLOW . number_format($origAmount) . TextFormat::WHITE . " loot boxes:" . PHP_EOL . PHP_EOL . "Lobby gadgets:" . PHP_EOL;

		$color = 0;
		$totalGadgets = 0;
		foreach ($sortedPrizes["gadgets"] as $name => $total) {
			$totalGadgets += $total;
			$outcomeText .= "- " . $name . ": " . ($color % 2 == 0 ? TextFormat::AQUA : TextFormat::DARK_AQUA) . number_format($total) . TextFormat::WHITE . PHP_EOL;
			$color++;
		}
		$outcomeText .= PHP_EOL . "You found a total of " . TextFormat::YELLOW . number_format($totalGadgets) . TextFormat::WHITE . " gadgets!" . PHP_EOL . PHP_EOL . "Cosmetics:" . PHP_EOL . PHP_EOL;

		$totalCosmetics = 0;
		foreach ($sortedPrizes["cosmetics"] as $categoryName => $rarities) {
			$text = ucwords($categoryName) . "s: " . PHP_EOL;
			$total = 0;
			foreach ($rarities as $rarity => $cosmetics) {
				if (count($cosmetics) > 0) {
					foreach ($cosmetics as $name) {
						$total++;
						$totalCosmetics++;
						$text .= TextFormat::GRAY . "- " . LootBoxData::RARITY_COLORS[$rarity] . $name . PHP_EOL;
					}
				}
			}
			if ($total > 0) {
				$outcomeText .= $text . TextFormat::WHITE . PHP_EOL;
			}
		}

		$outcomeText .= "You found a total of " . TextFormat::YELLOW . $totalCosmetics . TextFormat::WHITE . " new cosmetic items! You also found " . TextFormat::RED . number_format($duplicates) . TextFormat::WHITE . " duplicates, earning you a total of " . TextFormat::AQUA . number_format($shards) . TextFormat::WHITE . " shards!";

		$player->showModal(new SimpleForm("Loot box results", $outcomeText));
	}

	public function getRandomPrize(): Gadget|Cosmetic|null {
		$num = mt_rand(0, 220);
		$type = match (true) {
			($num <= LootBoxData::CHANCE_COSMETIC) => LootBoxData::PRIZE_COSMETIC,
			($num <= LootBoxData::CHANCE_CAPE) => LootBoxData::PRIZE_CAPE,
			($num <= LootBoxData::CHANCE_EFFECT) => LootBoxData::PRIZE_EFFECT,
			default => LootBoxData::PRIZE_EFFECT
		};
		$rarity = $this->getRandomPrizeRarity();
		$prizes = $this->prizes[$rarity][$type];
		try {
			return $prizes[array_rand($prizes)];
		} catch (\Error $error) {
			return null;
		}
	}

	public function getRandomPrizeRarity(): int {
		$number = mt_rand(1, 10000);
		return match (true) {
			($number >= 1 && $number <= 4000) => LootBoxData::RARITY_COMMON,
			($number > 4000 && $number <= 7000) => LootBoxData::RARITY_UNCOMMON,
			($number > 7000 && $number <= 9000) => LootBoxData::RARITY_RARE,
			($number > 9000 && $number <= 9990) => LootBoxData::RARITY_LEGENDARY,
			($number > 9990 && $number <= 10000) => LootBoxData::RARITY_DIVINE
		};
	}

	public function spawnBoxes(): void {
		$type = Core::thisServer()->getType();
		if (isset(LootBoxData::LOCATIONS[$type])) {
			$world = Server::getInstance()->getWorldManager()->getWorldByName(LootBoxData::LOCATIONS[$type]["world"]);
			if ($world === null) {
				Server::getInstance()->getWorldManager()->loadWorld(LootBoxData::LOCATIONS[$type]["world"]);
				$world = Server::getInstance()->getWorldManager()->getWorldByName(LootBoxData::LOCATIONS[$type]["world"]);
				if ($world === null) return;
			}
			foreach (LootBoxData::LOCATIONS[$type]["positions"] as $pos) {
				$box = new LootBox(new Location(array_shift($pos), array_shift($pos), array_shift($pos), $world, array_shift($pos), 0));
				$box->spawnToAll();
			}
		}
	}
}
