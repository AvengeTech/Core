<?php

namespace core\tutorial;

use pocketmine\event\Listener;
use pocketmine\event\player\{
	PlayerInteractEvent,
};

class MovementListener implements Listener {

	public function __construct(public Tutorials $tutorials) {
	}

	public function onInteract(PlayerInteractEvent $e) {
		/*if($this->tutorials->inMovementMode($e->getPlayer())){
			
		}*/
	}
}
