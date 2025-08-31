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

use core\etc\pieces\skin\Skin as CSkin;

class ScavTest extends Human implements ChunkLoader {

	public int $aliveTicks = 0;

	public $gravity = 0;

	public $loaderId = 0;
	public $lastChunkHash;
	public $loadedChunks = [];

	public function __construct(Location $level, Skin $nbt, string $name) {
		parent::__construct($level, $nbt);

		$customGeometry = json_decode(file_get_contents("/[REDACTED]/skins/custom/" . $name . ".geo.json"), true, 512, JSON_THROW_ON_ERROR);
		$geometry = $customGeometry["minecraft:geometry"][0]["description"]["identifier"];
		$geometryData = json_encode($customGeometry, JSON_THROW_ON_ERROR);

		$this->setSkin(new Skin($name, CSkin::getSkinData("custom/" . $name), "", $geometry, $geometryData));
		$this->setCanSaveWithChunk(false);
		$this->setNametag("");

		$this->loaderId = $this->getId();
	}

	protected function initEntity(CompoundTag $nbt): void {
		parent::initEntity($nbt);
		$this->getWorld()->registerChunkLoader($this, $this->getPosition()->x >> 4, $this->getPosition()->z >> 4);
		$this->lastChunkHash = World::chunkHash($this->getPosition()->x >> 4, $this->getPosition()->z >> 4);
	}

	public function entityBaseTick(int $tickDiff = 1): bool {
		if ($this->lastChunkHash !== ($hash = World::chunkHash($x = $this->getPosition()->x >> 4, $z = $this->getPosition()->z >> 4))) {
			$this->registerToChunk($x, $z);

			World::getXZ($this->lastChunkHash, $oldX, $oldZ);
			$this->unregisterFromChunk($oldX, $oldZ);

			$this->lastChunkHash = $hash;
		}

		$hasUpdate = parent::entityBaseTick($tickDiff);
		$this->aliveTicks++;

		//$this->setRotation($this->getLocation()->getYaw() + 4, 0);

		/**if($this->aliveTicks % 20 == 0){
		$this->getWorld()->addParticle($this->getPosition()->add(mt_rand(-5, 5) / 10, mt_rand(0, 10) / 10, mt_rand(-5, 5) / 10), new HappyVillagerParticle());
		}*/
		return true;
	}

	public function attack(EntityDamageEvent $source): void {
		$source->cancel();
		if ($source instanceof EntityDamageByEntityEvent) {
			$player = $source->getDamager();
		}
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
