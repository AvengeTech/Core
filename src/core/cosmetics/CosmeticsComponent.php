<?php

namespace core\cosmetics;

use pocketmine\entity\Skin;
use pocketmine\player\{
	GameMode
};
use pocketmine\scheduler\ClosureTask;
use pocketmine\world\Position;

use core\{
	Core,
	AtPlayer as Player
};
use core\cosmetics\CosmeticData;
use core\cosmetics\cape\Cape;
use core\cosmetics\effect\{
	trail\TrailEffect,
	idle\IdleEffect,
	doublejump\DoubleJumpEffect,
	arrow\ArrowEffect,
	snowball\SnowballEffect
};
use core\cosmetics\layer\{
	Hat,
	Back,
	Shoes,
	Suit
};
use core\session\component\{
	ComponentRequest,
	SaveableComponent
};
use core\session\mysqli\data\MySqlQuery;
use core\utils\{
	SkinUtils,
};

class CosmeticsComponent extends SaveableComponent {

	const PROJECTILE_EFFECT_LIMIT = 5;

	public ?Skin $originalSkin = null;
	public ?Player $player = null;
	public ?Position $lastPosition = null;

	public array $capes = [];
	public ?Cape $equippedCape = null;

	public array $trails = [];
	public ?TrailEffect $equippedTrail = null;

	public array $idle = [];
	public ?IdleEffect $equippedIdle = null;

	public array $doubleJumps = [];
	public ?DoubleJumpEffect $equippedDoubleJump = null;

	public array $arrow = [];
	public ?ArrowEffect $equippedArrow = null;

	public array $snowball = [];
	public ?SnowballEffect $equippedSnowball = null;

	public int $totalProjectiles = 0;


	public array $hats = [];
	public ?Hat $equippedHat = null;

	public array $backs = [];
	public ?Back $equippedBack = null;

	public array $shoes = [];
	public ?Shoes $equippedShoes = null;

	public array $suits = [];
	public ?Suit $equippedSuit = null;

	public ?Skin $layeredSkin = null;

	public function getName(): string {
		return "cosmetics";
	}

	public function tick(): void {
		if (
			($player = $this->getCachedPlayer()) !== null &&
			$player->isConnected() && !$player->isVanished() &&
			$player->getGamemode() !== GameMode::SPECTATOR() &&
			!Core::getInstance()->getTutorials()->inTutorial($player)
		) {
			$trail = $this->getEquippedTrail();
			$idle = $this->getEquippedIdle();
			if ($trail !== null || $idle !== null) {
				if ($this->getLastPosition()->equals($player->getPosition()) || $this->getLastPosition()->distance($player->getPosition()) <= 0.5) {
					$idle?->activate($player);
					//echo "player not moving!!", PHP_EOL;
				} else {
					$trail?->activate($player);
					$this->setLastPosition($player->getPosition());
					//echo "player moving!!", PHP_EOL;
				}
			}
		}
	}

	public function getOriginalSkin(bool $withCape = false): ?Skin {
		if (!$withCape)
			return $this->originalSkin;

		return SkinUtils::getSkinWithCape($this->originalSkin ?? $this->getPlayer()->getSkin(), $this->getEquippedCape()?->getImageName() ?? "");
	}

	public function setOriginalSkin(Skin $skin): void {
		$this->originalSkin = $skin;
	}

	public function getCachedPlayer(): ?Player {
		return $this->player;
	}

	public function setCachedPlayer(Player $player): void {
		$this->player = $player;
	}

	public function getLastPosition(): ?Position {
		return $this->lastPosition;
	}

	public function setLastPosition(Position $pos): void {
		$this->lastPosition = $pos;
	}

	public function addCosmetic(Cosmetic $cosmetic): void {
		switch ($cosmetic->getType()) {
			case CosmeticData::TYPE_CAPE:
				if (!$this->hasCape($cosmetic)) $this->addCape($cosmetic);
				break;
			case CosmeticData::TYPE_IDLE_EFFECT:
				if (!$this->hasIdle($cosmetic)) $this->addIdle($cosmetic);
				break;
			case CosmeticData::TYPE_TRAIL_EFFECT:
				if (!$this->hasTrail($cosmetic)) $this->addTrail($cosmetic);
				break;
			case CosmeticData::TYPE_DOUBLE_JUMP_EFFECT:
				if (!$this->hasDoubleJump($cosmetic)) $this->addDoubleJump($cosmetic);
				break;
			case CosmeticData::TYPE_ARROW_EFFECT:
				if (!$this->hasArrow($cosmetic)) $this->addArrow($cosmetic);
				break;
			case CosmeticData::TYPE_SNOWBALL_EFFECT:
				if (!$this->hasSnowball($cosmetic)) $this->addSnowball($cosmetic);
				break;

			case CosmeticData::TYPE_HAT:
				if (!$this->hasHat($cosmetic)) $this->addHat($cosmetic);
				break;
			case CosmeticData::TYPE_BACK:
				if (!$this->hasBack($cosmetic)) $this->addBack($cosmetic);
				break;
			case CosmeticData::TYPE_SHOES:
				if (!$this->hasShoes($cosmetic)) $this->addShoes($cosmetic);
				break;
			case CosmeticData::TYPE_SUIT:
				if (!$this->hasSuit($cosmetic)) $this->addSuit($cosmetic);
				break;
		}
	}

	public function hasCosmetic(Cosmetic $cosmetic): bool {
		switch ($cosmetic->getType()) {
			case CosmeticData::TYPE_CAPE:
				return $this->hasCape($cosmetic);

			case CosmeticData::TYPE_IDLE_EFFECT:
				return $this->hasIdle($cosmetic);
			case CosmeticData::TYPE_TRAIL_EFFECT:
				return $this->hasTrail($cosmetic);
			case CosmeticData::TYPE_DOUBLE_JUMP_EFFECT:
				return $this->hasDoubleJump($cosmetic);
			case CosmeticData::TYPE_ARROW_EFFECT:
				return $this->hasArrow($cosmetic);
			case CosmeticData::TYPE_SNOWBALL_EFFECT:
				return $this->hasSnowball($cosmetic);

			case CosmeticData::TYPE_HAT:
				return $this->hasHat($cosmetic);
			case CosmeticData::TYPE_BACK:
				return $this->hasBack($cosmetic);
			case CosmeticData::TYPE_SHOES:
				return $this->hasShoes($cosmetic);
			case CosmeticData::TYPE_SUIT:
				return $this->hasSuit($cosmetic);
		}
		return false;
	}

	/**
	 * Returns available cosmetics of whatever type (in cosmetic form)
	 */
	public function getAvailableCosmetics(int $type): array {
		$cosmetics = [];
		switch ($type) {
			case CosmeticData::TYPE_CAPE:
				foreach ($this->getAvailableCapes() as $cape) {
					if (($cape = Core::getInstance()->getCosmetics()->getCape($cape)) !== null) {
						$cosmetics[] = $cape;
					}
				}
				break;
			case CosmeticData::TYPE_TRAIL_EFFECT:
				foreach ($this->getAvailableTrails() as $trail) {
					if (($trail = Core::getInstance()->getCosmetics()->getTrail($trail)) !== null) {
						$cosmetics[] = $trail;
					}
				}
				break;
			case CosmeticData::TYPE_IDLE_EFFECT:
				foreach ($this->getAvailableIdle() as $idle) {
					if (($idle = Core::getInstance()->getCosmetics()->getIdle($idle)) !== null) {
						$cosmetics[] = $idle;
					}
				}
				break;
			case CosmeticData::TYPE_DOUBLE_JUMP_EFFECT:
				foreach ($this->getAvailableDoubleJumps() as $doubleJump) {
					if (($doubleJump = Core::getInstance()->getCosmetics()->getDoubleJump($doubleJump)) !== null) {
						$cosmetics[] = $doubleJump;
					}
				}
				break;
			case CosmeticData::TYPE_ARROW_EFFECT:
				foreach ($this->getAvailableArrow() as $arrow) {
					if (($arrow = Core::getInstance()->getCosmetics()->getArrow($arrow)) !== null) {
						$cosmetics[] = $arrow;
					}
				}
				break;
			case CosmeticData::TYPE_SNOWBALL_EFFECT:
				foreach ($this->getAvailableSnowball() as $snowball) {
					if (($snowball = Core::getInstance()->getCosmetics()->getSnowball($snowball)) !== null) {
						$cosmetics[] = $snowball;
					}
				}
				break;

			case CosmeticData::TYPE_HAT:
				foreach ($this->getAvailableHats() as $hat) {
					if (($hat = Core::getInstance()->getCosmetics()->getHat($hat)) !== null) {
						$cosmetics[] = $hat;
					}
				}
				break;
			case CosmeticData::TYPE_BACK:
				foreach ($this->getAvailableBacks() as $back) {
					if (($back = Core::getInstance()->getCosmetics()->getBack($back)) !== null) {
						$cosmetics[] = $back;
					}
				}
				break;
			case CosmeticData::TYPE_SHOES:
				foreach ($this->getAvailableShoes() as $shoes) {
					if (($shoes = Core::getInstance()->getCosmetics()->getShoes($shoes)) !== null) {
						$cosmetics[] = $shoes;
					}
				}
				break;
			case CosmeticData::TYPE_SUIT:
				foreach ($this->getAvailableSuits() as $suit) {
					if (($suit = Core::getInstance()->getCosmetics()->getSuit($suit)) !== null) {
						$cosmetics[] = $suit;
					}
				}
				break;
		}
		return $cosmetics;
	}

	/**
	 * Shows all unlocked capes from loot boxes
	 */
	public function getCapes(): array {
		return $this->capes;
	}

	/**
	 * Shows all available capes (including discord cape + any ranked capes)
	 */
	public function getAvailableCapes(): array {
		$special = [];
		$unlocked = $this->getCapes();
		if ($this->getCachedPlayer()?->getSession()->getDiscord()->isVerified()) {
			$special[] = CosmeticData::CAPE_DISCORD;
		}

		$rh = $this->getCachedPlayer()?->getSession()->getRank()->getRankHierarchy();
		if ($rh >= 1) $special[] = CosmeticData::CAPE_ENDERMITE;
		if ($rh >= 2) $special[] = CosmeticData::CAPE_BLAZE;
		if ($rh >= 3) $special[] = CosmeticData::CAPE_GHAST;
		if ($rh >= 4) $special[] = CosmeticData::CAPE_ENDERMAN;
		if ($rh >= 5) $special[] = CosmeticData::CAPE_WITHER;
		if ($rh >= 6) $special[] = CosmeticData::CAPE_ENDERDRAGON;

		if ($this->getCachedPlayer()?->getSession()->getRank()->hasSub()) {
			$special[] = CosmeticData::CAPE_WARDEN;
		}
		return array_merge($special, $unlocked);
	}

	public function hasCape(Cape|int $cape): bool {
		return in_array(($cape instanceof Cape ? $cape->getId() : $cape), $this->getAvailableCapes());
	}

	public function addCape(Cape|int $cape): void {
		if (!$this->hasCape($cape)) {
			$this->capes[] = ($cape instanceof Cape ? $cape->getId() : $cape);
			$this->setChanged();
		}
	}

	public function hasEquippedCape(): bool {
		return $this->getEquippedCape() !== null;
	}

	public function getEquippedCape(): ?Cape {
		return $this->equippedCape;
	}

	public function equipCape(?Cape $cape = null): void {
		$this->equippedCape = $cape;
		$this->setChanged();
		if (($pl = $this->getPlayer()) instanceof Player) {

			$this->updateLayers();
		}
	}

	public function getTrails(): array {
		return $this->trails;
	}

	public function getAvailableTrails(): array {
		$special = [];
		$unlocked = $this->getTrails();

		return array_merge($special, $unlocked);
	}

	public function hasTrail(TrailEffect|int $trail): bool {
		return in_array(($trail instanceof TrailEffect ? $trail->getId() : $trail), $this->getAvailableTrails());
	}

	public function addTrail(TrailEffect|int $trail): void {
		if (!$this->hasTrail($trail)) {
			$this->trails[] = ($trail instanceof TrailEffect ? $trail->getId() : $trail);
			$this->setChanged();
		}
	}

	public function hasEquippedTrail(): bool {
		return $this->getEquippedTrail() !== null;
	}

	public function getEquippedTrail(): ?TrailEffect {
		return $this->equippedTrail;
	}

	public function equipTrail(?TrailEffect $trail = null): void {
		$this->equippedTrail = $trail;
		$this->setChanged();
	}

	public function getIdle(): array {
		return $this->idle;
	}

	public function getAvailableIdle(): array {
		$special = [];
		$unlocked = $this->getIdle();

		return array_merge($special, $unlocked);
	}

	public function hasIdle(IdleEffect|int $idle): bool {
		return in_array(($idle instanceof IdleEffect ? $idle->getId() : $idle), $this->getAvailableIdle());
	}

	public function addIdle(IdleEffect|int $idle): void {
		if (!$this->hasIdle($idle)) {
			$this->idle[] = ($idle instanceof IdleEffect ? $idle->getId() : $idle);
			$this->setChanged();
		}
	}

	public function hasEquippedIdle(): bool {
		return $this->getEquippedIdle() !== null;
	}

	public function getEquippedIdle(): ?IdleEffect {
		return $this->equippedIdle;
	}

	public function equipIdle(?IdleEffect $idle = null): void {
		$this->equippedIdle = $idle;
		$this->setChanged();
	}

	public function getDoubleJumps(): array {
		return $this->doubleJumps;
	}

	public function getAvailableDoubleJumps(): array {
		$special = [];
		$unlocked = $this->getDoubleJumps();

		return array_merge($special, $unlocked);
	}

	public function hasDoubleJump(DoubleJumpEffect|int $doubleJump): bool {
		return in_array(($doubleJump instanceof DoubleJumpEffect ? $doubleJump->getId() : $doubleJump), $this->getAvailableDoubleJumps());
	}

	public function addDoubleJump(DoubleJumpEffect|int $doubleJump): void {
		if (!$this->hasDoubleJump($doubleJump)) {
			$this->doubleJumps[] = ($doubleJump instanceof DoubleJumpEffect ? $doubleJump->getId() : $doubleJump);
			$this->setChanged();
		}
	}

	public function hasEquippedDoubleJump(): bool {
		return $this->getEquippedDoubleJump() !== null;
	}

	public function getEquippedDoubleJump(): ?DoubleJumpEffect {
		return $this->equippedDoubleJump;
	}

	public function equipDoubleJump(?DoubleJumpEffect $doubleJump = null): void {
		$this->equippedDoubleJump = $doubleJump;
		$this->setChanged();
	}

	public function getArrow(): array {
		return $this->arrow;
	}

	public function getAvailableArrow(): array {
		$special = [];
		$unlocked = $this->getArrow();

		return array_merge($special, $unlocked);
	}

	public function hasArrow(ArrowEffect|int $arrow): bool {
		return in_array(($arrow instanceof ArrowEffect ? $arrow->getId() : $arrow), $this->getAvailableArrow());
	}

	public function addArrow(ArrowEffect|int $arrow): void {
		if (!$this->hasArrow($arrow)) {
			$this->arrow[] = ($arrow instanceof ArrowEffect ? $arrow->getId() : $arrow);
			$this->setChanged();
		}
	}

	public function hasEquippedArrow(): bool {
		return $this->getEquippedArrow() !== null;
	}

	public function getEquippedArrow(): ?ArrowEffect {
		return $this->equippedArrow;
	}

	public function equipArrow(?ArrowEffect $arrow = null): void {
		$this->equippedArrow = $arrow;
		$this->setChanged();
	}

	public function getSnowball(): array {
		return $this->snowball;
	}

	public function getAvailableSnowball(): array {
		$special = [];
		$unlocked = $this->getSnowball();

		return array_merge($special, $unlocked);
	}

	public function hasSnowball(SnowballEffect|int $snowball): bool {
		return in_array(($snowball instanceof SnowballEffect ? $snowball->getId() : $snowball), $this->getAvailableSnowball());
	}

	public function addSnowball(SnowballEffect|int $snowball): void {
		if (!$this->hasSnowball($snowball)) {
			$this->snowball[] = ($snowball instanceof SnowballEffect ? $snowball->getId() : $snowball);
			$this->setChanged();
		}
	}

	public function hasEquippedSnowball(): bool {
		return $this->getEquippedSnowball() !== null;
	}

	public function getEquippedSnowball(): ?SnowballEffect {
		return $this->equippedSnowball;
	}

	public function equipSnowball(?SnowballEffect $snowball = null): void {
		$this->equippedSnowball = $snowball;
		$this->setChanged();
	}

	public function getHats(): array {
		return $this->hats;
	}

	public function getAvailableHats(): array {
		$special = [];
		$unlocked = $this->getHats();

		return array_merge($special, $unlocked);
	}

	public function hasHat(Hat|int $hat): bool {
		return in_array(($hat instanceof Hat ? $hat->getId() : $hat), $this->getAvailableHats());
	}

	public function addHat(Hat|int $hat): void {
		if (!$this->hasHat($hat)) {
			$this->hats[] = ($hat instanceof Hat ? $hat->getId() : $hat);
			$this->setChanged();
		}
	}

	public function hasEquippedHat(): bool {
		return $this->getEquippedHat() !== null;
	}

	public function getEquippedHat(): ?Hat {
		return $this->equippedHat;
	}

	public function equipHat(?Hat $hat = null, bool $update = true): void {
		$this->equippedHat = $hat;
		$this->setChanged();

		if ($hat !== null && $this->hasEquippedSuit()) $this->equipSuit(null, false);
		if ($update) $this->updateLayers();
	}

	public function getBacks(): array {
		return $this->backs;
	}

	public function getAvailableBacks(): array {
		$special = [];
		$unlocked = $this->getBacks();

		return array_merge($special, $unlocked);
	}

	public function hasBack(Back|int $back): bool {
		return in_array(($back instanceof Back ? $back->getId() : $back), $this->getAvailableBacks());
	}

	public function addBack(Back|int $back): void {
		if (!$this->hasBack($back)) {
			$this->backs[] = ($back instanceof Back ? $back->getId() : $back);
			$this->setChanged();
		}
	}

	public function hasEquippedBack(): bool {
		return $this->getEquippedBack() !== null;
	}

	public function getEquippedBack(): ?Back {
		return $this->equippedBack;
	}

	public function equipBack(?Back $back = null, bool $update = true): void {
		$this->equippedBack = $back;
		$this->setChanged();

		if ($back !== null && $this->hasEquippedSuit()) $this->equipSuit(null, false);
		if ($update) $this->updateLayers();
	}

	public function getShoes(): array {
		return $this->shoes;
	}

	public function getAvailableShoes(): array {
		$special = [];
		$unlocked = $this->getShoes();

		return array_merge($special, $unlocked);
	}

	public function hasShoes(Shoes|int $shoes): bool {
		return in_array(($shoes instanceof Shoes ? $shoes->getId() : $shoes), $this->getAvailableShoes());
	}

	public function addShoes(Shoes|int $shoes): void {
		if (!$this->hasShoes($shoes)) {
			$this->shoes[] = ($shoes instanceof Shoes ? $shoes->getId() : $shoes);
			$this->setChanged();
		}
	}

	public function hasEquippedShoes(): bool {
		return $this->getEquippedShoes() !== null;
	}

	public function getEquippedShoes(): ?Shoes {
		return $this->equippedShoes;
	}

	public function equipShoes(?Shoes $shoes = null, bool $update = true): void {
		$this->equippedShoes = $shoes;
		$this->setChanged();

		if ($shoes !== null && $this->hasEquippedSuit()) $this->equipSuit(null, false);
		if ($update) $this->updateLayers();
	}


	public function getSuits(): array {
		return $this->suits;
	}

	public function getAvailableSuits(): array {
		$special = [];
		$unlocked = $this->getSuits();

		return array_merge($special, $unlocked);
	}

	public function hasSuit(Suit|int $suit): bool {
		return in_array(($suit instanceof Shoes ? $suit->getId() : $suit), $this->getAvailableSuits());
	}

	public function addSuit(Suit|int $suit): void {
		if (!$this->hasSuit($suit)) {
			$this->suits[] = ($suit instanceof Suit ? $suit->getId() : $suit);
			$this->setChanged();
		}
	}

	public function hasEquippedSuit(): bool {
		return $this->getEquippedSuit() !== null;
	}

	public function getEquippedSuit(): ?Suit {
		return $this->equippedSuit;
	}

	public function equipSuit(?Suit $suit = null, bool $update = true): void {
		$this->equippedSuit = $suit;

		if ($suit !== null) {
			if ($this->hasEquippedHat()) $this->equipHat(null, false);
			if ($this->hasEquippedBack()) $this->equipBack(null, false);
			if ($this->hasEquippedShoes()) $this->equipShoes(null, false);
		}

		if ($update) $this->updateLayers();

		$this->setChanged();
	}

	public function hasLayers(): bool {
		return
			$this->hasEquippedHat() ||
			$this->hasEquippedBack() ||
			$this->hasEquippedShoes() ||
			$this->hasEquippedShoes();
	}

	public function getLayeredSkin(): ?Skin {
		return $this->layeredSkin;
	}

	public function updateLayers(bool $apply = true): void {
		$cosmetics = [];
		if (($hat = $this->getEquippedHat()?->getDataName()) !== null)
			$cosmetics[] = $hat;
		if (($back = $this->getEquippedBack()?->getDataName()) !== null)
			$cosmetics[] = $back;
		if (($shoes = $this->getEquippedShoes()?->getDataName()) !== null)
			$cosmetics[] = $shoes;

		if (($suit = $this->getEquippedSuit()?->getDataName()) !== null)
			$cosmetics[] = $suit;

		if (count($cosmetics) !== 0) {
			$skin = $this->layeredSkin = SkinUtils::layerSkin($this->hasEquippedCape() ? SkinUtils::getSkinWithCape($this->getOriginalSkin(), $this->getEquippedCape()->getImageName()) : $this->getOriginalSkin(), $cosmetics);
		} else {
			$skin = $this->layeredSkin = ($this->hasEquippedCape() ? SkinUtils::getSkinWithCape($this->getOriginalSkin(), $this->getEquippedCape()->getImageName()) : $this->getOriginalSkin());
		}

		if ($apply && ($player = $this->getPlayer()) instanceof Player) {
			if (!$player->inLobby()) {
				$player->setSkin(
					$this->getOriginalSkin(true)
				);
				$player->sendSkin();
				return;
			}
			$player->setSkin($skin);
			$player->sendSkin();

			$this->sendAnimations($player, $player->getId(), true, 30);
			foreach ($player->getViewers() as $viewer) {
				$this->sendAnimations($viewer, $player->getId());
			}
		}
	}

	public function sendAnimations(Player $player, int $id = -1, bool $delayed = true, int $delay = 10): void {
		$func = function () use ($player, $id): void {
			if (!$player->isConnected()) return;
			if ($this->hasEquippedHat() && ($hat = $this->getEquippedHat())->hasAnimation())
				$hat->sendAnimation($player, ($id === -1 ? ($this->getPlayer()?->getId() ?? 0) : $id), false);
			if ($this->hasEquippedBack() && ($back = $this->getEquippedBack())->hasAnimation())
				$back->sendAnimation($player, ($id === -1 ? ($this->getPlayer()?->getId() ?? 0) : $id), false);
			if ($this->hasEquippedShoes() && ($shoes = $this->getEquippedShoes())->hasAnimation())
				$shoes->sendAnimation($player, ($id === -1 ? ($this->getPlayer()?->getId() ?? 0) : $id), false);
			if ($this->hasEquippedSuit() && ($suit = $this->getEquippedSuit())->hasAnimation())
				$suit->sendAnimation($player, ($id === -1 ? ($this->getPlayer()?->getId() ?? 0) : $id), false);
		};
		if ($delayed) {
			Core::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask($func), $delay);
		} else {
			$func();
		}
	}

	/**
	 * Projectile tracking
	 */
	public function getTotalProjectiles(): int {
		return $this->totalProjectiles;
	}

	public function addProjectile(): void {
		$this->totalProjectiles++;
	}

	public function takeProjectile(): void {
		$this->totalProjectiles--;
	}

	public function hasMaxProjectileEffects(): bool {
		return $this->getTotalProjectiles() >= self::PROJECTILE_EFFECT_LIMIT;
	}

	public function createTables(): void {
		$db = $this->getSession()->getSessionManager()->getDatabase();
		foreach ([
			"CREATE TABLE IF NOT EXISTS cosmetics(
				xuid BIGINT(16) NOT NULL UNIQUE,
				capes BLOB NOT NULL, equippedCape INT NOT NULL DEFAULT -1,
				trails BLOB NOT NULL, equippedTrail INT NOT NULL DEFAULT -1,
				idle BLOB NOT NULL, equippedIdle INT NOT NULL DEFAULT -1,
				doubleJump BLOB NOT NULL, equippedDoubleJump INT NOT NULL DEFAULT -1,
				arrow BLOB NOT NULL, equippedArrow INT NOT NULL DEFAULT -1,
				snowball BLOB NOT NULL, equippedSnowball INT NOT NULL DEFAULT -1,
				hats BLOB NOT NULL, equippedHat INT NOT NULL DEFAULT -1,
				backs BLOB NOT NULL, equippedBack INT NOT NULL DEFAULT -1,
				shoes BLOB NOT NULL, equippedShoes INT NOT NULL DEFAULT -1,
				suits BLOB NOT NULL, equippedSuits INT NOT NULL DEFAULT -1
			);",
		] as $query) $db->query($query);
	}

	public function loadAsync(): void {
		if (($pl = $this->getPlayer()) instanceof Player) {
			$this->setOriginalSkin($pl->getSkin());
		}

		$request = new ComponentRequest($this->getXuid(), $this->getName(), new MySqlQuery("main", "SELECT * FROM cosmetics WHERE xuid=?", [$this->getXuid()]));
		$this->newRequest($request, ComponentRequest::TYPE_LOAD);
		parent::loadAsync();
	}

	public function finishLoadAsync(?ComponentRequest $request = null): void {
		$result = $request->getQuery()->getResult();
		$rows = (array) $result->getRows();
		if (count($rows) > 0) {
			$data = array_shift($rows);
			$this->capes = json_decode($data["capes"], true);
			$this->equippedCape = Core::getInstance()->getCosmetics()->getCape($data["equippedCape"]);

			$this->trails = json_decode($data["trails"], true);
			$this->equippedTrail = Core::getInstance()->getCosmetics()->getTrail($data["equippedTrail"]);

			$this->idle = json_decode($data["idle"], true);
			$this->equippedIdle = Core::getInstance()->getCosmetics()->getIdle($data["equippedIdle"]);

			$this->doubleJumps = json_decode($data["doubleJump"], true);
			$this->equippedDoubleJump = Core::getInstance()->getCosmetics()->getDoubleJump($data["equippedDoubleJump"]);

			$this->arrow = json_decode($data["arrow"], true);
			$this->equippedArrow = Core::getInstance()->getCosmetics()->getArrow($data["equippedArrow"]);

			$this->snowball = json_decode($data["snowball"], true);
			$this->equippedSnowball = Core::getInstance()->getCosmetics()->getSnowball($data["equippedSnowball"]);

			$this->hats = json_decode($data["hats"], true);
			$this->equippedHat = Core::getInstance()->getCosmetics()->getHat($data["equippedHat"]);

			$this->backs = json_decode($data["backs"], true);
			$this->equippedBack = Core::getInstance()->getCosmetics()->getBack($data["equippedBack"]);

			$this->shoes = json_decode($data["shoes"], true);
			if ($data["equippedShoes"] !== -1) $this->equippedShoes = Core::getInstance()->getCosmetics()->getShoes($data["equippedShoes"]);

			$this->suits = json_decode($data["suits"], true);
			$this->equippedSuit = Core::getInstance()->getCosmetics()->getSuit($data["equippedSuit"]);

			if (($pl = $this->getPlayer()) instanceof Player) {
				$this->setCachedPlayer($pl);
				$this->setLastPosition($pl->getPosition());

				$this->updateLayers();

				if ($pl->isSn3ak()) {
					//$this->equippedTrail = new \core\cosmetics\effect\trail\dust\LightBlueDustTrail();
					//$this->equippedIdle = new \core\cosmetics\effect\idle\circle\OrangeCircleIdle();
					//$this->equippedDoubleJump = new \core\cosmetics\effect\doublejump\FartedDje();

					$this->capes = [];
					foreach (Core::getInstance()->getCosmetics()->getCapes() as $id => $cape) {
						$this->capes[] = $id;
					}
					$this->trails = [];
					foreach (Core::getInstance()->getCosmetics()->getTrails() as $id => $trail) {
						$this->trails[] = $id;
					}
					$this->idle = [];
					foreach (Core::getInstance()->getCosmetics()->getIdles() as $id => $idle) {
						$this->idle[] = $id;
					}
					$this->doubleJumps = [];
					foreach (Core::getInstance()->getCosmetics()->getDoubleJumps() as $id => $doubleJump) {
						$this->doubleJumps[] = $id;
					}
					$this->arrow = [];
					foreach (Core::getInstance()->getCosmetics()->getArrows() as $id => $arrow) {
						$this->arrow[] = $id;
					}
					$this->snowball = [];
					foreach (Core::getInstance()->getCosmetics()->getSnowballs() as $id => $snowball) {
						$this->snowball[] = $id;
					}

					$this->hats = [];
					foreach (Core::getInstance()->getCosmetics()->getHats() as $id => $hat) {
						$this->hats[] = $id;
					}
					$this->backs = [];
					foreach (Core::getInstance()->getCosmetics()->getBacks() as $id => $back) {
						$this->backs[] = $id;
					}
					$this->shoes = [];
					foreach (Core::getInstance()->getCosmetics()->getShoes() as $id => $shoes) {
						$this->shoes[] = $id;
					}
					$this->suits = [];
					foreach (Core::getInstance()->getCosmetics()->getSuits() as $id => $suits) {
						$this->suits[] = $id;
					}
				}
			}
		}

		parent::finishLoadAsync($request);
	}

	public function verifyChange(): bool {
		$verify = $this->getChangeVerify();
		return $verify["capes"] !== $this->getCapes() ||
			$verify["trails"] !== $this->getTrails() ||
			$verify["idle"] !== $this->getIdle() ||
			$verify["doubleJumps"] !== $this->getDoubleJumps() ||
			$verify["arrow"] !== $this->getArrow() ||
			$verify["snowball"] !== $this->getSnowball();
	}

	public function saveAsync(): void {
		if (!$this->isLoaded() || !$this->hasChanged()) return;

		$this->setChangeVerify([
			"capes" => $this->getCapes(),
			"trails" => $this->getTrails(),
			"idle" => $this->getIdle(),
			"doubleJumps" => $this->getDoubleJumps(),
			"arrow" => $this->getArrow(),
			"snowball" => $this->getSnowball(),
		]);

		$request = new ComponentRequest($this->getXuid(), $this->getName(), new MySqlQuery(
			"main",
			"INSERT INTO cosmetics(xuid, capes, equippedCape, trails, equippedTrail, idle, equippedIdle, doubleJump, equippedDoubleJump, arrow, equippedArrow, snowball, equippedSnowball, hats, equippedHat, backs, equippedBack, shoes, equippedShoes, suits, equippedSuit) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE	
				capes=VALUES(capes), equippedCape=VALUES(equippedCape),
				trails=VALUES(trails), equippedTrail=VALUES(equippedTrail),
				idle=VALUES(idle), equippedIdle=VALUES(equippedIdle),
				doubleJump=VALUES(doubleJump), equippedDoubleJump=VALUES(equippedDoubleJump),
				arrow=VALUES(arrow), equippedArrow=VALUES(equippedArrow),
				snowball=VALUES(snowball), equippedSnowball=VALUES(equippedSnowball),
				hats=VALUES(hats), equippedHat=VALUES(equippedHat),
				backs=VALUES(backs), equippedBack=VALUES(equippedBack),
				shoes=VALUES(shoes), equippedShoes=VALUES(equippedShoes),
				suits=VALUES(suits), equippedSuit=VALUES(equippedSuit)
			",
			[
				$this->getXuid(),
				json_encode($this->getCapes()), (($cape = $this->getEquippedCape()) !== null ? $cape->getId() : -1),
				json_encode($this->getTrails()), (($trail = $this->getEquippedTrail()) !== null ? $trail->getId() : -1),
				json_encode($this->getIdle()), (($idle = $this->getEquippedIdle()) !== null ? $idle->getId() : -1),
				json_encode($this->getDoubleJumps()), (($doubleJump = $this->getEquippedDoubleJump()) !== null ? $doubleJump->getId() : -1),
				json_encode($this->getArrow()), (($arrow = $this->getEquippedArrow()) !== null ? $arrow->getId() : -1),
				json_encode($this->getSnowball()), (($snowball = $this->getEquippedSnowball()) !== null ? $snowball->getId() : -1),
				json_encode($this->getHats()), (($hat = $this->getEquippedHat()) !== null ? $hat->getId() : -1),
				json_encode($this->getBacks()), (($back = $this->getEquippedBack()) !== null ? $back->getId() : -1),
				json_encode($this->getShoes()), (($shoes = $this->getEquippedShoes()) !== null ? $shoes->getId() : -1),
				json_encode($this->getSuits()), (($suit = $this->getEquippedSuit()) !== null ? $suit->getId() : -1),
			]
		));
		$this->newRequest($request, ComponentRequest::TYPE_SAVE);
		parent::saveAsync();
	}

	public function save(): bool {
		if (!$this->isLoaded() || !$this->hasChanged()) return false;

		$xuid = $this->getXuid();
		$capes = json_encode($this->getCapes());
		$equippedCape = $this->hasEquippedCape() ? $this->getEquippedCape()->getId() : -1;
		$trails = json_encode($this->getTrails());
		$equippedTrail = $this->hasEquippedTrail() ? $this->getEquippedTrail()->getId() : -1;
		$idle = json_encode($this->getIdle());
		$equippedIdle = $this->hasEquippedIdle() ? $this->getEquippedIdle()->getId() : -1;
		$doubleJumps = json_encode($this->getDoubleJumps());
		$equippedDoubleJump = $this->hasEquippedDoubleJump() ? $this->getEquippedDoubleJump()->getId() : -1;
		$arrow = json_encode($this->getArrow());
		$equippedArrow = $this->hasEquippedArrow() ? $this->getEquippedArrow()->getId() : -1;
		$snowball = json_encode($this->getSnowball());
		$equippedSnowball = $this->hasEquippedSnowball() ? $this->getEquippedSnowball()->getId() : -1;

		$hats = json_encode($this->getHats());
		$equippedHat = $this->hasEquippedHat() ? $this->getEquippedHat()->getId() : -1;
		$backs = json_encode($this->getBacks());
		$equippedBack = $this->hasEquippedBack() ? $this->getEquippedBack()->getId() : -1;
		$shoes = json_encode($this->getShoes());
		$equippedShoes = $this->hasEquippedShoes() ? $this->getEquippedShoes()->getId() : -1;
		$suits = json_encode($this->getSuits());
		$equippedSuit = $this->hasEquippedSuit() ? $this->getEquippedSuit()->getId() : -1;

		$db = $this->getSession()->getSessionManager()->getDatabase();
		$stmt = $db->prepare(
			"INSERT INTO cosmetics(xuid, capes, equippedCape, trails, equippedTrail, idle, equippedIdle, doubleJump, equippedDoubleJump, arrow, equippedArrow, snowball, equippedSnowball, hats, equippedHat, backs, equippedBack, shoes, equippedShoes, suits, equippedSuit) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE
				capes=VALUES(capes), equippedCape=VALUES(equippedCape),
				trails=VALUES(trails), equippedTrail=VALUES(equippedTrail),
				idle=VALUES(idle), equippedIdle=VALUES(equippedIdle),
				doubleJump=VALUES(doubleJump), equippedDoubleJump=VALUES(equippedDoubleJump),
				arrow=VALUES(arrow), equippedArrow=VALUES(equippedArrow),
				snowball=VALUES(snowball), equippedSnowball=VALUES(equippedSnowball),
				hats=VALUES(hats), equippedHat=VALUES(equippedHat),
				backs=VALUES(backs), equippedBack=VALUES(equippedBack),
				shoes=VALUES(shoes), equippedShoes=VALUES(equippedShoes),
				suits=VALUES(suits), equippedSuit=VALUES(equippedSuit)
			"
		);
		$stmt->bind_param("isisisisisisisisisisi", $xuid, $capes, $equippedCape, $trails, $equippedTrail, $idle, $equippedIdle, $doubleJumps, $equippedDoubleJump, $arrow, $equippedArrow, $snowball, $equippedSnowball, $hats, $equippedHat, $backs, $equippedBack, $shoes, $equippedShoes, $suits, $equippedSuit);
		$stmt->execute();
		$stmt->close();

		return parent::save();
	}

	public function getSerializedData(): array {
		return [
			"capes" => json_encode($this->getCapes()),
			"equippedCape" => (($cape = $this->getEquippedCape()) !== null ? $cape->getId() : -1),
			"trails" => json_encode($this->getTrails()),
			"equippedTrail" => (($trail = $this->getEquippedTrail()) !== null ? $trail->getId() : -1),
			"idle" => json_encode($this->getIdle()),
			"equippedIdle" => (($idle = $this->getEquippedIdle()) !== null ? $idle->getId() : -1),
			"doubleJump" => json_encode($this->getDoubleJumps()),
			"equippedDoubleJump" => (($doubleJump = $this->getEquippedDoubleJump()) !== null ? $doubleJump->getId() : -1),
			"arrow" => json_encode($this->getArrow()),
			"equippedArrow" => (($arrow = $this->getEquippedArrow()) !== null ? $arrow->getId() : -1),
			"snowball" => json_encode($this->getSnowball()),
			"equippedSnowball" => (($snowball = $this->getEquippedSnowball()) !== null ? $snowball->getId() : -1),
			"hats" => json_encode($this->getHats()),
			"equippedHat" => (($hat = $this->getEquippedHat()) !== null ? $hat->getId() : -1),
			"backs" => json_encode($this->getBacks()),
			"equippedBack" => (($back = $this->getEquippedBack()) !== null ? $back->getId() : -1),
			"shoes" => json_encode($this->getShoes()),
			"equippedShoes" => (($shoes = $this->getEquippedShoes()) !== null ? $shoes->getId() : -1),
			"suits" => json_encode($this->getSuits()),
			"equippedSuit" => (($suit = $this->getEquippedSuit()) !== null ? $suit->getId() : -1),
		];
	}

	public function applySerializedData(array $data): void {
		$this->capes = json_decode($data["capes"], true);
		$this->equippedCape = Core::getInstance()->getCosmetics()->getCape($data["equippedCape"]);

		$this->trails = json_decode($data["trails"], true);
		$this->equippedTrail = Core::getInstance()->getCosmetics()->getTrail($data["equippedTrail"]);

		$this->idle = json_decode($data["idle"], true);
		$this->equippedIdle = Core::getInstance()->getCosmetics()->getIdle($data["equippedIdle"]);

		$this->doubleJumps = json_decode($data["doubleJump"], true);
		$this->equippedDoubleJump = Core::getInstance()->getCosmetics()->getDoubleJump($data["equippedDoubleJump"]);

		$this->arrow = json_decode($data["arrow"], true);
		$this->equippedArrow = Core::getInstance()->getCosmetics()->getArrow($data["equippedArrow"]);

		$this->snowball = json_decode($data["snowball"], true);
		$this->equippedSnowball = Core::getInstance()->getCosmetics()->getSnowball($data["equippedSnowball"]);

		$this->hats = json_decode($data["hats"], true);
		$this->equippedHat = Core::getInstance()->getCosmetics()->getHat($data["equippedHat"]);

		$this->backs = json_decode($data["backs"], true);
		$this->equippedBack = Core::getInstance()->getCosmetics()->getBack($data["equippedBack"]);

		$this->shoes = json_decode($data["shoes"], true);
		if ($data["equippedShoes"] !== -1) $this->equippedShoes = Core::getInstance()->getCosmetics()->getShoes($data["equippedShoes"]);

		$this->suits = json_decode($data["suits"], true);
		$this->equippedSuit = Core::getInstance()->getCosmetics()->getSuit($data["equippedSuit"]);

		if (($pl = $this->getPlayer()) instanceof Player) {
			$this->setCachedPlayer($pl);
			$this->setLastPosition($pl->getPosition());

			$this->updateLayers();

			if ($pl->isSn3ak()) {
				//$this->equippedTrail = new \core\cosmetics\effect\trail\dust\LightBlueDustTrail();
				//$this->equippedIdle = new \core\cosmetics\effect\idle\circle\OrangeCircleIdle();
				//$this->equippedDoubleJump = new \core\cosmetics\effect\doublejump\FartedDje();

				$this->capes = [];
				foreach (Core::getInstance()->getCosmetics()->getCapes() as $id => $cape) {
					$this->capes[] = $id;
				}
				$this->trails = [];
				foreach (Core::getInstance()->getCosmetics()->getTrails() as $id => $trail) {
					$this->trails[] = $id;
				}
				$this->idle = [];
				foreach (Core::getInstance()->getCosmetics()->getIdles() as $id => $idle) {
					$this->idle[] = $id;
				}
				$this->doubleJumps = [];
				foreach (Core::getInstance()->getCosmetics()->getDoubleJumps() as $id => $doubleJump) {
					$this->doubleJumps[] = $id;
				}
				$this->arrow = [];
				foreach (Core::getInstance()->getCosmetics()->getArrows() as $id => $arrow) {
					$this->arrow[] = $id;
				}
				$this->snowball = [];
				foreach (Core::getInstance()->getCosmetics()->getSnowballs() as $id => $snowball) {
					$this->snowball[] = $id;
				}

				$this->hats = [];
				foreach (Core::getInstance()->getCosmetics()->getHats() as $id => $hat) {
					$this->hats[] = $id;
				}
				$this->backs = [];
				foreach (Core::getInstance()->getCosmetics()->getBacks() as $id => $back) {
					$this->backs[] = $id;
				}
				$this->shoes = [];
				foreach (Core::getInstance()->getCosmetics()->getShoes() as $id => $shoes) {
					$this->shoes[] = $id;
				}
				$this->suits = [];
				foreach (Core::getInstance()->getCosmetics()->getSuits() as $id => $suits) {
					$this->suits[] = $id;
				}
			}
		}
	}
}
