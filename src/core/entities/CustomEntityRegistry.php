<?php

namespace core\entities;

use pocketmine\nbt\tag\{
	CompoundTag,
	ListTag
};
use pocketmine\network\mcpe\protocol\types\CacheableNbt;

/**
 * The CustomEntityRegistry's purpose is to register entities that are controlled and manipulated through packets
 */
class CustomEntityRegistry {

	public array $entities = [];

	public function getRegisteredEntities(): array {
		return $this->entities;
	}

	/**
	 * Registers an entity to the Core's registry
	 * @param string $identifier
	 */
	public function registerEntity(string $identifier): void {
		if (in_array($identifier, $this->entities)) return;
		$this->entities[] = $identifier;
	}

	/**
	 * Registers entities to the Core's registry
	 * @param string[] $identifiers
	 */
	public function registerEntities(array $identifiers): void {
		foreach ($identifiers as $identifier) {
			$this->registerEntity($identifier);
		}
	}

	public function nbt(CacheableNbt $nbt): CacheableNbt {
		$idList = $nbt->getRoot()->getListTag("idlist");
		$newArray = $idList->getValue();
		$newTag = CompoundTag::create();
		$entityID = 100;
		foreach ($this->getRegisteredEntities() as $entity) {
			$entityID++;
			$tag = CompoundTag::create();
			$tag->setString("bid", "");
			$tag->setByte("hasspawnegg", 0);
			$tag->setString("id", $entity);
			$tag->setByte("rid", $entityID);
			$tag->setByte("summonable", 1);
			$newArray[] = $tag;
		}
		$newTag->setTag("idlist", new ListTag($newArray));
		return new CacheableNbt($newTag);
	}
}
