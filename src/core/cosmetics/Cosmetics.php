<?php

namespace core\cosmetics;

use pocketmine\Server;
use pocketmine\entity\Skin;
use pocketmine\item\{
	ItemIdentifier,
	ItemTypeIds,
};
use pocketmine\world\Position;

use core\{
	Core,
	AtPlayer as Player
};
use core\cosmetics\cape\Cape;
use core\cosmetics\command\{
	AddCosmetic as AddCosmeticCommand,
	Cosmetics as CosmeticsCommand,
	Capes as CapesCommand,
	LayerTest as LayerTestCommand,
	AnimTest as AnimTestCommand
};
use core\cosmetics\effect\Effect;
use core\cosmetics\effect\idle\IdleEffect;
use core\cosmetics\effect\idle\circle\{
	RedCircleIdle,
	OrangeCircleIdle,
	YellowCircleIdle,
	GreenCircleIdle,
	DarkBlueCircleIdle,
	LightBlueCircleIdle,
	DarkPurpleCircleIdle,
	LightPurpleCircleIdle,
	BlackCircleIdle,
	WhiteCircleIdle,
	RainbowCircleIdle,
	BlackWhiteCircleIdle
};
use core\cosmetics\effect\trail\{
	TrailEffect,

	FlamingRocksTrail,
	SmokeTrail,
	FlamesTrail,
	SplashTrail
};
use core\cosmetics\effect\trail\dust\{
	RedDustTrail,
	OrangeDustTrail,
	YellowDustTrail,
	GreenDustTrail,
	DarkBlueDustTrail,
	LightBlueDustTrail,
	DarkPurpleDustTrail,
	LightPurpleDustTrail,
	BlackDustTrail,
	WhiteDustTrail,
	RainbowDustTrail,
	BlackWhiteDustTrail
};
use core\cosmetics\effect\doublejump\{
	DoubleJumpEffect,

	MeeyowDje,
	KaboomDje,
	FartedDje,
	WoofDje,
	HmmmmDje
};
use core\cosmetics\effect\arrow\{
	ArrowEffect
};
use core\cosmetics\effect\arrow\dust\{
	RedDustArrow,
	OrangeDustArrow,
	YellowDustArrow,
	GreenDustArrow,
	DarkBlueDustArrow,
	LightBlueDustArrow,
	DarkPurpleDustArrow,
	LightPurpleDustArrow,
	BlackDustArrow,
	WhiteDustArrow,
	RainbowDustArrow,
	BlackWhiteDustArrow
};
use core\cosmetics\effect\snowball\{
	SnowballEffect
};
use core\cosmetics\effect\snowball\dust\{
	RedDustSnowball,
	OrangeDustSnowball,
	YellowDustSnowball,
	GreenDustSnowball,
	DarkBlueDustSnowball,
	LightBlueDustSnowball,
	DarkPurpleDustSnowball,
	LightPurpleDustSnowball,
	BlackDustSnowball,
	WhiteDustSnowball,
	RainbowDustSnowball,
	BlackWhiteDustSnowball
};
use core\cosmetics\item\{
	Snowball,
	Bow
};
use core\cosmetics\layer\{
	Hat,
	Back,
	Shoes,
	Suit,
	LayerCosmetic
};
use core\lootboxes\LootBoxData;
use core\settings\GlobalSettings;
use core\utils\ItemRegistry;

class Cosmetics {

	public array $capes = [];

	public array $idle = [];
	public array $trails = [];
	public array $doubleJumps = [];
	public array $arrow = [];
	public array $snowball = [];

	public array $morphs = [];
	public array $pets = [];

	public array $hats = [];
	public array $backs = [];
	public array $shoes = [];

	public array $suits = [];

	public function __construct(public Core $plugin) {
		$this->setupCosmetics();

		ItemRegistry::registerSimpleItem(new Snowball(new ItemIdentifier(ItemTypeIds::SNOWBALL, 0), "Snowball"), ['snowball']);
		ItemRegistry::registerSimpleItem(new Bow(new ItemIdentifier(ItemTypeIds::BOW, 0), "Bow"), ['bow']);

		$plugin->getServer()->getCommandMap()->registerAll("cosmetics", [
			new AddCosmeticCommand($plugin, "addcosmetic", "Add cosmetic to player"),
			new CosmeticsCommand($plugin, "cosmetics", "Open the cosmetics menu"),
			new CapesCommand($plugin, "capes", "View your unlocked capes"),
			new LayerTestCommand($plugin, "layertest", "cosmetic layer test"),
			new AnimTestCommand($plugin, "animtest", "animation test"),
		]);
	}

	/**
	 * Get the viewers for a position, disregarding the current player
	 * @param Player $player | Player to ignore
	 * @param Position $pos
	 */
	public static function getEffectViewers(Player $player, Position $pos): array {
		$viewers = $pos->getWorld()->getViewersForPosition($pos);
		/** @var Player $viewer */
		foreach ($viewers as $key => $viewer) {
			if ($viewer->getName() !== $player->getName() && ($viewer->isLoaded() &&
				!$viewer->getSession()->getSettings()->getSetting(GlobalSettings::DISPLAY_COSMETIC_EFFECTS)
			)) {
				unset($viewers[$key]);
			}
		}
		return $viewers;
	}

	/**
	 * Shifts cosmetic effects between worlds for a given player
	 * @param Player $player
	 * @param string $newLevel
	 */
	public function changeLevel(Player $player, string $newLevel): void {
		if (!$player->isLoaded() || !$player->getSession()->getCosmetics()->hasLayers()) return;
		if (
			$newLevel !== Server::getInstance()->getWorldManager()->getDefaultWorld()->getDisplayName() &&
			count(explode("-", $newLevel)) !== 3
		) {
			$player->setSkin(
				$player->getSession()->getCosmetics()->getOriginalSkin(true)
			);
			$player->sendSkin();
		} else {
			if (($ls = ($cos = $player->getSession()->getCosmetics())->getLayeredSkin()) !== null) {
				$player->setSkin($ls);
				$player->sendSkin();
				$cos->sendAnimations($player);
			}
		}
	}

	public static function getLayeredSkin(Skin $skin, ?Cape $cape = null, ?Hat $hat = null, ?Back $back = null, ?Shoes $shoes = null): Skin {
		return $skin;
		return new Skin(
			$skin->getSkinId(),
			$skin->getSkinData(),
			$cape->getImageName(),
			$skin->getGeometryName(),
			$skin->getGeometryData()
		); //TODO
	}

	public function setupCosmetics(): void {
		foreach (CosmeticData::CAPES as $id => $data) {
			$this->capes[$id] = new Cape($id, $data["name"], $data["rarity"] ?? LootBoxData::RARITY_COMMON, $data["imgName"], $data["lootboxes"] ?? true);
		}
		$this->idle = [
			CosmeticData::IDLE_RED_CIRCLE => new RedCircleIdle(),
			CosmeticData::IDLE_ORANGE_CIRCLE => new OrangeCircleIdle(),
			CosmeticData::IDLE_YELLOW_CIRCLE => new YellowCircleIdle(),
			CosmeticData::IDLE_GREEN_CIRCLE => new GreenCircleIdle(),
			CosmeticData::IDLE_DARK_BLUE_CIRCLE => new DarkBlueCircleIdle(),
			CosmeticData::IDLE_LIGHT_BLUE_CIRCLE => new LightBlueCircleIdle(),
			CosmeticData::IDLE_DARK_PURPLE_CIRCLE => new DarkPurpleCircleIdle(),
			CosmeticData::IDLE_LIGHT_PURPLE_CIRCLE => new LightPurpleCircleIdle(),
			CosmeticData::IDLE_BLACK_CIRCLE => new BlackCircleIdle(),
			CosmeticData::IDLE_WHITE_CIRCLE => new WhiteCircleIdle(),
			CosmeticData::IDLE_RAINBOW_CIRCLE => new RainbowCircleIdle(),
			CosmeticData::IDLE_BLACK_WHITE_CIRCLE => new BlackWhiteCircleIdle(),
		];
		$this->trails = [
			CosmeticData::TRAIL_RED_DUST => new RedDustTrail(),
			CosmeticData::TRAIL_ORANGE_DUST => new OrangeDustTrail(),
			CosmeticData::TRAIL_YELLOW_DUST => new YellowDustTrail(),
			CosmeticData::TRAIL_GREEN_DUST => new GreenDustTrail(),
			CosmeticData::TRAIL_DARK_BLUE_DUST => new DarkBlueDustTrail(),
			CosmeticData::TRAIL_LIGHT_BLUE_DUST => new LightBlueDustTrail(),
			CosmeticData::TRAIL_DARK_PURPLE_DUST => new DarkPurpleDustTrail(),
			CosmeticData::TRAIL_LIGHT_PURPLE_DUST => new LightPurpleDustTrail(),
			CosmeticData::TRAIL_BLACK_DUST => new BlackDustTrail(),
			CosmeticData::TRAIL_WHITE_DUST => new WhiteDustTrail(),
			CosmeticData::TRAIL_RAINBOW_DUST => new RainbowDustTrail(),
			CosmeticData::TRAIL_BLACK_WHITE_DUST => new BlackWhiteDustTrail(),

			CosmeticData::TRAIL_FLAMING_ROCKS => new FlamingRocksTrail(),
			CosmeticData::TRAIL_SMOKE => new SmokeTrail(),
			CosmeticData::TRAIL_FLAMES => new FlamesTrail(),
			CosmeticData::TRAIL_SPLASH => new SplashTrail(),
		];
		$this->doubleJumps = [
			CosmeticData::DJ_MEEYOW => new MeeyowDje(),
			CosmeticData::DJ_KABOOM => new KaboomDje(),
			CosmeticData::DJ_FARTED => new FartedDje(),
			CosmeticData::DJ_WOOF => new WoofDje(),
			CosmeticData::DJ_HMMMM => new HmmmmDje(),
		];
		$this->arrow = [
			CosmeticData::ARROW_RED_DUST => new RedDustArrow(),
			CosmeticData::ARROW_ORANGE_DUST => new OrangeDustArrow(),
			CosmeticData::ARROW_YELLOW_DUST => new YellowDustArrow(),
			CosmeticData::ARROW_GREEN_DUST => new GreenDustArrow(),
			CosmeticData::ARROW_DARK_BLUE_DUST => new DarkBlueDustArrow(),
			CosmeticData::ARROW_LIGHT_BLUE_DUST => new LightBlueDustArrow(),
			CosmeticData::ARROW_DARK_PURPLE_DUST => new DarkPurpleDustArrow(),
			CosmeticData::ARROW_LIGHT_PURPLE_DUST => new LightPurpleDustArrow(),
			CosmeticData::ARROW_BLACK_DUST => new BlackDustArrow(),
			CosmeticData::ARROW_WHITE_DUST => new WhiteDustArrow(),
			CosmeticData::ARROW_RAINBOW_DUST => new RainbowDustArrow(),
			CosmeticData::ARROW_BLACK_WHITE_DUST => new BlackWhiteDustArrow(),
		];
		$this->snowball = [
			CosmeticData::SNOWBALL_RED_DUST => new RedDustSnowball(),
			CosmeticData::SNOWBALL_ORANGE_DUST => new OrangeDustSnowball(),
			CosmeticData::SNOWBALL_YELLOW_DUST => new YellowDustSnowball(),
			CosmeticData::SNOWBALL_GREEN_DUST => new GreenDustSnowball(),
			CosmeticData::SNOWBALL_DARK_BLUE_DUST => new DarkBlueDustSnowball(),
			CosmeticData::SNOWBALL_LIGHT_BLUE_DUST => new LightBlueDustSnowball(),
			CosmeticData::SNOWBALL_DARK_PURPLE_DUST => new DarkPurpleDustSnowball(),
			CosmeticData::SNOWBALL_LIGHT_PURPLE_DUST => new LightPurpleDustSnowball(),
			CosmeticData::SNOWBALL_BLACK_DUST => new BlackDustSnowball(),
			CosmeticData::SNOWBALL_WHITE_DUST => new WhiteDustSnowball(),
			CosmeticData::SNOWBALL_RAINBOW_DUST => new RainbowDustSnowball(),
			CosmeticData::SNOWBALL_BLACK_WHITE_DUST => new BlackWhiteDustSnowball(),
		];

		foreach (CosmeticData::HATS as $id => $data) {
			$this->hats[$id] = new Hat($id, $data["name"], $data["rarity"] ?? LootBoxData::RARITY_COMMON, $data["dataName"], $data["animation"] ?? "", $data["lootboxes"] ?? true);
		}
		foreach (CosmeticData::BACKS as $id => $data) {
			$this->backs[$id] = new Back($id, $data["name"], $data["rarity"] ?? LootBoxData::RARITY_COMMON, $data["dataName"], $data["animation"] ?? "", $data["lootboxes"] ?? true);
		}
		foreach (CosmeticData::SHOES as $id => $data) {
			$this->shoes[$id] = new Shoes($id, $data["name"], $data["rarity"] ?? LootBoxData::RARITY_COMMON, $data["dataName"], $data["animation"] ?? "", $data["lootboxes"] ?? true);
		}
		foreach (CosmeticData::SUITS as $id => $data) {
			$this->suits[$id] = new Suit($id, $data["name"], $data["rarity"] ?? LootBoxData::RARITY_COMMON, $data["dataName"], $data["animation"] ?? "", $data["lootboxes"] ?? true);
		}
	}

	public function getCapes(): array {
		return $this->capes;
	}

	public function getCape(int $id): ?Cape {
		return $this->capes[$id] ?? null;
	}

	public function getIdles(): array {
		return $this->idle;
	}

	public function getIdle(int $id): ?IdleEffect {
		return $this->idle[$id] ?? null;
	}

	public function getTrails(): array {
		return $this->trails;
	}

	public function getTrail(int $id): ?TrailEffect {
		return $this->trails[$id] ?? null;
	}

	public function getDoubleJumps(): array {
		return $this->doubleJumps;
	}

	public function getDoubleJump(int $id): ?DoubleJumpEffect {
		return $this->doubleJumps[$id] ?? null;
	}

	public function getArrows(): array {
		return $this->arrow;
	}

	public function getArrow(int $id): ?ArrowEffect {
		return $this->arrow[$id] ?? null;
	}

	public function getSnowballs(): array {
		return $this->snowball;
	}

	public function getSnowball(int $id): ?SnowballEffect {
		return $this->snowball[$id] ?? null;
	}

	public function getHats(): array {
		return $this->hats;
	}

	public function getHat(int $id): ?Hat {
		return $this->hats[$id] ?? null;
	}

	public function getBacks(): array {
		return $this->backs;
	}

	public function getBack(int $id): ?Back {
		return $this->backs[$id] ?? null;
	}

	public function getShoes(int $id = -1): array|Shoes|null {
		if ($id === -1) return $this->shoes;
		return $this->shoes[$id] ?? null;
	}

	public function getSuits(): array {
		return $this->suits;
	}

	public function getSuit(int $id): ?Suit {
		return $this->suits[$id] ?? null;
	}

	public function getRandomEffect(bool $lootBoxes = true): ?Effect {
		$effects = [];
		foreach ($this->getTrails() as $cosmetic) if (!$lootBoxes || $cosmetic->canWin()) $effects[] = $cosmetic;
		foreach ($this->getIdles() as $cosmetic) if (!$lootBoxes || $cosmetic->canWin()) $effects[] = $cosmetic;
		foreach ($this->getArrows() as $cosmetic) if (!$lootBoxes || $cosmetic->canWin()) $effects[] = $cosmetic;
		foreach ($this->getSnowballs() as $cosmetic) if (!$lootBoxes || $cosmetic->canWin()) $effects[] = $cosmetic;
		foreach ($this->getDoubleJumps() as $cosmetic) if (!$lootBoxes || $cosmetic->canWin()) $effects[] = $cosmetic;

		return $effects[array_rand($effects)];
	}

	public function getRandomClothing(bool $lootBoxes = true): ?LayerCosmetic {
		$clothing = [];
		foreach ($this->getHats() as $cosmetic) if (!$lootBoxes || $cosmetic->canWin()) $clothing[] = $cosmetic;
		foreach ($this->getBacks() as $cosmetic) if (!$lootBoxes || $cosmetic->canWin()) $clothing[] = $cosmetic;
		foreach ($this->getShoes() as $cosmetic) if (!$lootBoxes || $cosmetic->canWin()) $clothing[] = $cosmetic;

		return $clothing[array_rand($clothing)];
	}
}
