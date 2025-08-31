<?php

namespace core\lootboxes\entity;

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
use pocketmine\network\mcpe\protocol\AnimateEntityPacket;
use pocketmine\world\{
	World,
	ChunkLoader,
	format\Chunk
};


use core\{
	Core,
	AtPlayer as Player
};
use core\cosmetics\Cosmetic;
use core\gadgets\type\Gadget;
use core\lootboxes\ui\LootBoxUi;
use core\utils\TextFormat;

class LootBox extends Entity implements ChunkLoader {

	const PHASE_IDLE = 0;
	const PHASE_OPENING = 1;
	const PHASE_OPEN = 2;

	public int $aliveTicks = 0;

	public int $loaderId = 0;
	public int $lastChunkHash;
	public array $loadedChunks = [];

	public ?Player $opening = null;
	public int $phase = self::PHASE_IDLE;
	public int $phaseTicks = 0;

	public static function getNetworkTypeId(): string {
		return "core:lootbox";
	}

	protected function getInitialDragMultiplier(): float {
		return 0.0;
	}

	protected function getInitialGravity(): float {
		return 0.1;
	}

	public function __construct(Location $location, ?CompoundTag $nbt = null) {
		parent::__construct($location, $nbt);

		$this->setNametagAlwaysVisible(true);

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

			$player->showModal(new LootBoxUi($player, $this));
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

		$hasUpdate = parent::entityBaseTick($tickDiff);
		$this->aliveTicks++;

		switch ($this->getPhase()) {
			case self::PHASE_OPENING:
				$this->phaseTicks++;
				if (!($opening = $this->getOpening())->isConnected()) {
					$this->setOpening();
					$this->setPhase(self::PHASE_IDLE);
					break;
				}
				if ($this->getPhaseTicks() >= $this->getPhaseLength()) {
					$this->setPhase(self::PHASE_OPEN);
					$this->doAnimation("open");

					$prize = Core::getInstance()->getLootBoxes()->getRandomPrize();
					while ($prize === null) {
						$prize = Core::getInstance()->getLootBoxes()->getRandomPrize();
					}

					if ($prize instanceof Gadget) {
						$opening->getSession()->getGadgets()->addTotal($prize, $total = $prize->getLootBoxTotal());
						$opening->sendMessage(TextFormat::GI . "You found " . ($prizename = $prize->getRarityColor() . "x" . $total . " " . $prize->getName() . TextFormat::GRAY . " lobby gadget" . ($total > 1 ? "s" : "")) . "!");
					} elseif ($prize instanceof Cosmetic) {
						$opening->sendMessage(TextFormat::GI . "You found a " . ($prizename = $prize->getRarityColor() . $prize->getName() . TextFormat::GRAY . " " . $prize->getTypeName()) . "!");
						if ($opening->getSession()->getCosmetics()->hasCosmetic($prize)) {
							$opening->getSession()->getLootBoxes()->addShards($prize->getShardWorth());
							$opening->sendMessage(TextFormat::YI . "You already had this cosmetic unlocked, so you were given " . TextFormat::AQUA . number_format($prize->getShardWorth()) . " shards" . TextFormat::GRAY . " instead!");
						} else {
							$opening->getSession()->getCosmetics()->addCosmetic($prize);
						}
					}

					$opening->getSession()->getLootBoxes()->takeLootBoxes();

					$this->setNametag($prizename);
				}
				break;
			case self::PHASE_OPEN:
				$this->phaseTicks++;
				if ($this->getPhaseTicks() >= $this->getPhaseLength()) {
					$this->setOpening();
					$this->setPhase(self::PHASE_IDLE);
					$this->setNametag("");
				}
				break;
		}

		return true;
	}

	public function open(Player $player): void {
		$this->setOpening($player);
		$this->setPhase(self::PHASE_OPENING);
		$this->doAnimation("opening");
	}

	public function getOpening(): ?Player {
		return $this->opening;
	}

	public function setOpening(?Player $player = null): void {
		$this->opening = $player;
	}

	public function isOccupied(): bool {
		return $this->getOpening() !== null && $this->getOpening()->isConnected();
	}

	public function getPhase(): int {
		return $this->phase;
	}

	public function setPhase(int $phase): void {
		$this->phase = $phase;
		$this->resetPhaseTicks();
	}

	public function getPhaseTicks(): int {
		return $this->phaseTicks;
	}

	public function resetPhaseTicks(): void {
		$this->phaseTicks = 0;
	}

	public function getPhaseLength(): int {
		return match ($this->getPhase()) {
			self::PHASE_IDLE => -1,
			self::PHASE_OPENING => 60,
			self::PHASE_OPEN => 40,
		};
	}


	public function doAnimation(string $anim): void {
		$controller = "controller.animation.lootbox.general";
		$packet = AnimateEntityPacket::create($anim, "", "", 0, $controller, 0, [$this->getId()]);
		$this->getWorld()->broadcastPacketToViewers($this->getPosition(), $packet);
	}

	protected function getInitialSizeInfo(): EntitySizeInfo {
		return new EntitySizeInfo(1, 0.6, 1);
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
