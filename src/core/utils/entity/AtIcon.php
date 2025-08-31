<?php

namespace core\utils\entity;

use pocketmine\world\{
	World,
	ChunkLoader,
	format\Chunk,
};
use pocketmine\entity\{
	Human,
	Location,
	Skin
};
use pocketmine\event\entity\{
	EntityDamageEvent,
	EntityDamageByEntityEvent
};
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;

use core\AtPlayer as Player;
use core\etc\pieces\skin\Skin as CSkin;

class AtIcon extends Human implements ChunkLoader {

	public int $aliveTicks = 0;

	public float $gravity = 0;
	protected bool $gravityEnabled = false;

	public $loaderId = 0;
	public $lastChunkHash;
	public $loadedChunks = [];

	public function __construct(Location $level, Skin $nbt, string $nametag = "", public ?\Closure $tapClosure = null, float $scale = 1, public bool $spin = true) {
		parent::__construct($level, $nbt);

		$customGeometry = json_decode(file_get_contents("/[REDACTED]/skins/custom/logo.geo.json"), true, 512, JSON_THROW_ON_ERROR);
		$geometry = $customGeometry["minecraft:geometry"][0]["description"]["identifier"];
		$geometryData = json_encode($customGeometry, JSON_THROW_ON_ERROR);

		$this->setSkin(new Skin("AtIcon", CSkin::getSkinData("custom/logo"), "", $geometry, $geometryData));
		$this->setCanSaveWithChunk(false);
		if ($nametag !== "") {
			$this->setNametagAlwaysVisible(true);
			$this->setNametag($nametag);
		}
		$this->setScale($scale);

		$this->loaderId = $this->getId();
	}

	protected function initEntity(CompoundTag $nbt): void {
		parent::initEntity($nbt);
		$this->getWorld()->registerChunkLoader($this, $this->getPosition()->getFloorX() >> 4, $this->getPosition()->getFloorZ() >> 4);
		$this->lastChunkHash = World::chunkHash($this->getPosition()->getFloorX() >> 4, $this->getPosition()->getFloorZ() >> 4);
	}

	public function entityBaseTick(int $tickDiff = 1): bool {
		if ($this->lastChunkHash !== ($hash = World::chunkHash($x = $this->getPosition()->getFloorX() >> 4, $z = (int) $this->getPosition()->z >> 4))) {
			$this->registerToChunk($x, $z);

			World::getXZ($this->lastChunkHash, $oldX, $oldZ);
			$this->unregisterFromChunk($oldX, $oldZ);

			$this->lastChunkHash = $hash;
		}

		$hasUpdate = parent::entityBaseTick($tickDiff);
		$this->aliveTicks++;

		if ($this->spin) $this->setRotation($this->getLocation()->getYaw() + 4, 0);
		return true;
	}

	public function attack(EntityDamageEvent $source): void {
		$source->cancel();
		if ($source instanceof EntityDamageByEntityEvent) {
			$player = $source->getDamager();
			if ($this->hasTapClosure() && $player instanceof Player) {
				$this->getTapClosure()($player);
			}
		}
	}

	public function hasTapClosure(): bool {
		return $this->getTapClosure() !== null;
	}

	public function getTapClosure(): ?\Closure {
		return $this->tapClosure;
	}

	public function registerToChunk(int $chunkX, int $chunkZ) {
		if (!isset($this->loadedChunks[World::chunkHash($chunkX, $chunkZ)])) {
			$this->loadedChunks[World::chunkHash($chunkX, $chunkZ)] = true;
			$this->getWorld()->registerChunkLoader($this, $chunkX, $chunkZ);
		}
	}

	public function unregisterFromChunk(int $chunkX, int $chunkZ) {
		if (isset($this->loadedChunks[World::chunkHash($chunkX, $chunkZ)])) {
			unset($this->loadedChunks[World::chunkHash($chunkX, $chunkZ)]);
			$this->getWorld()->unregisterChunkLoader($this, $chunkX, $chunkZ);
		}
	}

	public function onChunkChanged(Chunk $chunk) {
	}

	public function onChunkLoaded(Chunk $chunk) {
	}

	public function onChunkUnloaded(Chunk $chunk) {
	}

	public function onChunkPopulated(Chunk $chunk) {
	}

	public function onBlockChanged(Vector3 $block) {
	}

	public function getLoaderId(): int {
		return $this->loaderId;
	}

	public function isLoaderActive(): bool {
		return !$this->isFlaggedForDespawn() && !$this->closed;
	}
}
