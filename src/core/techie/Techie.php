<?php

namespace core\techie;

use pocketmine\entity\{
	EntityDataHelper,
	EntityFactory,
	Location,
	Skin,
	Human
};
use pocketmine\world\World;
use pocketmine\nbt\tag\CompoundTag;

use core\Core;

class Techie {

	public ?TechieBot $techie = null;

	public function __construct(public Core $plugin) {

		$data = Structure::TECHIE_DATA[$plugin->getNetwork()->getServerType()]["pos"] ?? null;
		if ($data == null) return;

		$world = $plugin->getServer()->getWorldManager()->getWorldByName($data["level"]);
		if ($world == null) {
			$plugin->getServer()->getWorldManager()->loadWorld($data["level"], true);
			$world = $plugin->getServer()->getWorldManager()->getWorldByName($data["level"]);
		}

		//return;
		if ($world !== null) { //double check
			$chunk = $world->getChunk((int) $data["x"] >> 4, (int) $data["z"] >> 4);
			if ($chunk === null) {
				$world->loadChunk((int) $data["x"] >> 4, (int) $data["z"] >> 4);
			}

			$techie = new TechieBot(new Location($data["x"], $data["y"], $data["z"], $world, 0, 0), new Skin("Standard_Custom", file_get_contents("/[REDACTED]/skins/techie.dat"), "", "geometry.humanoid.custom"), $data["sitting"] ?? false);
			$techie->spawnToAll();

			$this->techie = $techie;
		}
	}

	public function getTechie(): ?TechieBot {
		return $this->techie;
	}
}
