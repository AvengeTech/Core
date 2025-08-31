<?php

namespace core\staff\utils;

use core\chat\Structure;
use pocketmine\entity\Entity;
use pocketmine\entity\Skin;
use pocketmine\network\mcpe\convert\LegacySkinAdapter;
use pocketmine\network\mcpe\protocol\types\PlayerListEntry;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class Disguise {

	public const NAMES = [
		"AegleLion5042",
		"AnastasiusDoom554",
		"Octopixy42",
		"Babelbeast5615",
		"BrackenSphinx2",
		"Antenor22",
		"zLit3rr",
		"EsterWick102",
		"Amphitrite24384",
		"Dem0nixx44",
		"TroubledLegend83922",
		"AjaxWipedUOut4021",
		"AlcibiadesBlade6233",
		"AlixiaPotion8323",
		"AngryDragonfly2580",
		"ApexStars6914",
		"RandomK1d2773"
	];

	public static function random(): self {
		return new self(new Skin("Standard_Custom", str_repeat(random_bytes(3) . "\xff", 4096)), self::NAMES[array_rand(self::NAMES)], Structure::DISGUISE_RANKS[array_rand(Structure::DISGUISE_RANKS)]);
	}

	public static function fromString(string $data): self {
		if ($data === "") return self::random();
		$d = explode("_-_", $data);
		return new self(new Skin("Standard_Custom", str_repeat(random_bytes(3) . "\xff", 4096)), $d[0], $d[1], $d[2] == "Y");
	}

	public function toString() {
		return $this->name . "_-_" . $this->rank . "_-_" . ($this->enabled ? "Y" : "N");
	}

	public function __toString() {
		return $this->toString();
	}

	public int $id;
	public UuidInterface $uuid;

	public function __construct(
		protected Skin $skin,
		protected string $name,
		protected string $rank,
		public bool $enabled = false
	) {
		$this->id = Entity::nextRuntimeId();
		$this->uuid = Uuid::getFactory()->uuid4();
	}

	public function getRank(): string {
		return $this->rank;
	}

	public function getSkin(): Skin {
		return $this->skin;
	}

	public function getName(): string {
		return $this->name;
	}

	public function isEnabled(): bool {
		return $this->enabled;
	}

	public function toggle(?bool $enable = null): void {
		$this->enabled = $enable ?? !$this->enabled;
	}

	public function getPlayerListAddEntry(): PlayerListEntry {
		return PlayerListEntry::createAdditionEntry(
			$this->uuid,
			$this->id,
			$this->getName(),
			(new LegacySkinAdapter())->toSkinData($this->skin)
		);
	}

	public function getPlayerListRemoveEntry(): PlayerListEntry {
		return PlayerListEntry::createRemovalEntry($this->uuid);
	}
}
