<?php

namespace core\gadgets\entity;

use pocketmine\Server;
use pocketmine\entity\{
	Entity,
	EntitySizeInfo,
	Location
};
use pocketmine\event\entity\{
	EntityDamageEvent,
};
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\SetActorLinkPacket;
use pocketmine\network\mcpe\protocol\types\entity\{
	EntityLink,
	EntityMetadataFlags,
	EntityMetadataProperties
};
use pocketmine\scheduler\ClosureTask;
use pocketmine\world\{
	particle\HugeExplodeParticle,
	sound\ExplodeSound
};

use core\{
	Core,
	AtPlayer as Player
};
use core\utils\{
	PlaySound,
	Utils
};
use pocketmine\network\mcpe\NetworkBroadcastUtils;

class SailingSub extends Entity {

	public int $aliveTicks = 0;

	public ?Player $player;

	public bool $scheduledBoom = false;

	public static function getNetworkTypeId(): string {
		return "core:gadget.sailing_sub";
	}

	protected function getInitialDragMultiplier(): float {
		return 0.0;
	}

	protected function getInitialGravity(): float {
		return 0.0;
	}

	public function __construct(Location $location, ?CompoundTag $nbt = null) {
		parent::__construct($location, $nbt);
		$location->getWorld()->addSound($location, new PlaySound($location, "missile.launch"));
	}

	public function getPlayer(): ?Player {
		return $this->player;
	}

	public function setPlayer(?Player $player): void {
		$this->player = $player;
		if ($player !== null) {
			$player->setSailingSub($this);
		}
	}

	public function sitDown(Player $player): void {
		$player->getNetworkProperties()->setGenericFlag(EntityMetadataFlags::RIDING, true);
		$player->getNetworkProperties()->setVector3(EntityMetadataProperties::RIDER_SEAT_POSITION, new Vector3(0, 1.85, -0.6));

		$link = new SetActorLinkPacket();
		$link->link = new EntityLink($this->getId(), $player->getId(), EntityLink::TYPE_RIDER, true, true, 0);
		NetworkBroadcastUtils::broadcastPackets($this->getViewers(), [$link]);

		$this->setPlayer($player);

		$this->setOwningEntity($player);
	}

	public function getUp(Player $player): void {
		$link = new SetActorLinkPacket();
		$link->link = new EntityLink($this->getId(), $player->getId(), EntityLink::TYPE_REMOVE, true, true, 0);
		NetworkBroadcastUtils::broadcastPackets(Server::getInstance()->getOnlinePlayers(), [$link]);

		$player->getNetworkProperties()->setGenericFlag(EntityMetadataFlags::RIDING, false);

		$player->setSailingSub();
	}

	public function canSaveWithChunk(): bool {
		return false;
	}

	public function attack(EntityDamageEvent $source): void {
		$source->cancel();
	}

	public function entityBaseTick(int $tickDiff = 1): bool {
		$hasUpdate = parent::entityBaseTick($tickDiff);
		$this->aliveTicks++;

		if (($pl = $this->getPlayer()) === null || !$pl->isConnected()) {
			$this->flagForDespawn(); //explode
			$this->getPosition()->getWorld()->addParticle($this->getPosition(), new HugeExplodeParticle());
			return false;
		}

		if ($this->aliveTicks >= 60 && $this->gravity <= 0.3) {
			$this->gravity += 0.03;
		}

		if (($this->getPosition()->getY() <= 40 || $this->aliveTicks >= 250 || $this->onGround || $this->isCollidedHorizontally) && !$this->scheduledBoom) {
			$this->scheduledBoom = true;
			Core::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function (): void {
				if (!$this->isFlaggedForDespawn()) {
					if ($this->getPlayer() !== null && $this->getPlayer()->isConnected()) {
						$this->getUp($this->getPlayer());
					}
					$this->flagForDespawn(); //explode too
					$this->getPosition()->getWorld()->addParticle($this->getPosition(), new HugeExplodeParticle());
					$this->getPosition()->getWorld()->addSound($this->getPosition(), new ExplodeSound());
					Utils::blockFlyBoom($this->getPosition());
				}
			}), 5);
		}
		return true;
	}

	protected function getInitialSizeInfo(): EntitySizeInfo {
		return new EntitySizeInfo(0.1, 0.1, 0.1);
	}
}
