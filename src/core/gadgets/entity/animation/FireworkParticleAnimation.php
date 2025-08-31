<?php

namespace core\gadgets\entity\animation;

use pocketmine\entity\animation\Animation;
use pocketmine\network\mcpe\protocol\ActorEventPacket;
use pocketmine\network\mcpe\protocol\types\ActorEvent;

use core\gadgets\entity\Firework;

class FireworkParticleAnimation implements Animation {

	public function __construct(
		private Firework $firework
	) {
	}

	public function encode(): array {
		return [ActorEventPacket::create($this->firework->getId(), ActorEvent::FIREWORK_PARTICLES, 0)];
	}
}
