<?php

namespace core\gadgets;

use core\Core;
use core\gadgets\command\AddGadgets;
use core\gadgets\type\{
	Gadget,

	GatlingMelon,
	SailingSub,
	BirddayCaek,
	Fireworks
};

class Gadgets {

	public array $gadgets = [];

	public function __construct(public Core $plugin) {
		$this->gadgets = [
			GadgetData::GATLING_MELON => new GatlingMelon(),
			GadgetData::SAILING_SUB => new SailingSub(),
			GadgetData::BIRDDAY_CAEK => new BirddayCaek(),
			GadgetData::FIREWORKS => new Fireworks(),
		];

		$plugin->getServer()->getCommandMap()->registerAll("gadgets", [
			new AddGadgets($plugin, "addgadgets", "Give gadgets"),
		]);
	}

	public function getGadgets(): array {
		return $this->gadgets;
	}

	public function getGadget(int $id): ?Gadget {
		return $this->gadgets[$id] ?? null;
	}
}
