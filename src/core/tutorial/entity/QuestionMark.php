<?php

namespace core\tutorial\entity;

use pocketmine\entity\{
	Entity,
	EntitySizeInfo,
	Location
};
use pocketmine\event\entity\{
	EntityDamageEvent,
	EntityDamageByEntityEvent
};
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\world\{
	World,
	ChunkLoader,
	format\Chunk
};

use core\AtPlayer as Player;
use core\Core;
use core\utils\TextFormat;

class QuestionMark extends Entity implements ChunkLoader {

	public float $gravity = 0;

	public int $aliveTicks = 0;

	public int $loaderId = 0;
	public int $lastChunkHash;
	public array $loadedChunks = [];

	protected function getInitialDragMultiplier(): float {
		return 0.0;
	}

	protected function getInitialGravity(): float {
		return 0.0;
	}

	public static function getNetworkTypeId(): string {
		return "core:question";
	}

	public function __construct(Location $location, ?CompoundTag $nbt = null) {
		parent::__construct($location, $nbt);

		$this->setNametagAlwaysVisible(true);
		$this->setNametag(TextFormat::BOLD . TextFormat::YELLOW . "Start tutorial");
		$this->setScale(1.5);

		$this->loaderId = $this->getId();
	}

	public function canSaveWithChunk(): bool {
		return false;
	}

	public function attack(EntityDamageEvent $source): void {
		$source->cancel();
		if ($source instanceof EntityDamageByEntityEvent) {
			$player = $source->getDamager();
			if (!($player instanceof Player)) return;
			if (($tut = Core::getInstance()->getTutorials())->getTutorial() !== null) {
				$tut->startTutorial($player);
			} else {
				$player->sendMessage(TextFormat::RI . "No tutorial available!");
			}
		}
	}

	protected function initEntity(CompoundTag $nbt): void {
		parent::initEntity($nbt);
		$this->getWorld()->registerChunkLoader($this, $this->getPosition()->getFloorX() >> 4, $this->getPosition()->getFloorZ() >> 4);
		$this->lastChunkHash = World::chunkHash($this->getPosition()->getFloorX() >> 4, $this->getPosition()->getFloorZ() >> 4);
	}

	public function entityBaseTick(int $tickDiff = 1): bool {
		if ($this->lastChunkHash !== ($hash = World::chunkHash($x = $this->getPosition()->getFloorX() >> 4, $z = $this->getPosition()->getFloorZ() >> 4))) {
			$this->registerToChunk($x, $z);

			World::getXZ($this->lastChunkHash, $oldX, $oldZ);
			$this->unregisterFromChunk($oldX, $oldZ);

			$this->lastChunkHash = $hash;
		}

		return parent::entityBaseTick($tickDiff);
	}

	protected function getInitialSizeInfo(): EntitySizeInfo {
		return new EntitySizeInfo(2, 3, 2);
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
