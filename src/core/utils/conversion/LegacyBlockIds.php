<?php

namespace core\utils\conversion;

use pocketmine\data\bedrock\block\upgrade\LegacyBlockIdToStringIdMap;
use pocketmine\network\mcpe\convert\TypeConverter;
use pocketmine\block\VanillaBlocks;

use core\utils\BlockRegistry;
use core\utils\ItemRegistry;

final class LegacyBlockIds {

	private function __construct() {
		// NOOP
	}

	public const PM_CONVERT = [
		
	];

	public static function legacyIdToTypeId(int $id, int $meta = 0, ?bool $fromItemMap = false): ?int {
		$newString = LegacyBlockIdToStringIdMap::getInstance()->legacyToString($id);
		if (isset(self::PM_CONVERT[$newString]) && (isset(self::PM_CONVERT[$newString][$meta]) || (isset(self::PM_CONVERT[$newString][0]) && self::PM_CONVERT[$newString][0][2]))) {
			$k = $meta;
			if (!isset(self::PM_CONVERT[$newString][$meta]) && self::PM_CONVERT[$newString][0][2]) $k = 0;
			$s = $newString;
			$newString = self::PM_CONVERT[$newString][$k][0];
			if (self::PM_CONVERT[$s][$k][1]) $meta = 0;
		}
		if (is_null($newString)) {
			if ($fromItemMap) return null;
			return -LegacyItemIds::legacyIdToTypeId($id, $meta, true);
		}
		return BlockRegistry::getBlock($newString, $meta)?->getTypeId() ?? -ItemRegistry::getItem($newString, $meta)?->getTypeId() ?? BlockRegistry::getBlock($newString, 0)?->getTypeId() ?? -ItemRegistry::getItem($newString, 0)?->getTypeId() ?? $id;
	}

	public static function typeIdToLegacyId(int $typeId, ?bool $fromItemMap = false): ?int {
		$item = BlockRegistry::getBlockById($typeId, -1);
		/** @var array<int,string> */
		$frontmap = LegacyBlockIdToStringIdMap::getInstance()->getLegacyToStringMap();
		/** @var array<string,int> */
		$map = [];
		$id = 'air';
		foreach (array_merge(VanillaBlocks::getAll(), BlockRegistry::getAll()) as $iid => $item) {
			if ($item->getTypeId() == $typeId) $id = $iid;
		}
		$name = "minecraft:" . strtolower(str_replace(' ', '_', $id));
		foreach ($frontmap as $k => $v) $map[$v] = $k;
		return isset($map[$name]) ? $map[$name] : ($fromItemMap ? null : LegacyItemIds::typeIdToLegacyId($typeId, true)) ?? $typeId;
	}

	public static function stateIdToMeta(int $stateId): int {
		return TypeConverter::getInstance()->getBlockTranslator()->getBlockStateDictionary()->getMetaFromStateId(TypeConverter::getInstance()->getBlockTranslator()->internalIdToNetworkId($stateId)) ?? 0;
	}
}
