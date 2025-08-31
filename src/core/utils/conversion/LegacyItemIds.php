<?php

namespace core\utils\conversion;

use core\utils\BlockRegistry;
use pocketmine\data\bedrock\item\upgrade\LegacyItemIdToStringIdMap;

use core\utils\ItemRegistry;
use Exception;
use pocketmine\block\VanillaBlocks;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\world\format\io\GlobalItemDataHandlers;

final class LegacyItemIds {

	private function __construct() {
		// NOOP
	}

	public const PM_CONVERT = [
		'minecraft:leather_helmet' => [['minecraft:leather_cap', false, false]],
		'minecraft:leather_chestplate' => [['minecraft:leather_tunic', false, false]],
		'minecraft:leather_leggings' => [['minecraft:leather_pants', false, false]],
		'minecraft:frame' => [['minecraft:item_frame', false, false]],
		'minecraft:chicken' => [['minecraft:raw_chicken', false, false]],
		'minecraft:beef' => [["minecraft:raw_beef", false, false]],
		'minecraft:porkchop' => [['minecraft:raw_porkchop', false, false]],
		'minecraft:fish' => [['minecraft:raw_fish', false, false]],
		'minecraft:rabbit' => [['minecraft:raw_rabbit', false, false]],
		'minecraft:mutton' => [["minecraft:raw_mutton", false, false]],
		'minecraft:salmon' => [["minecraft:raw_salmon", false, false]],
		'minecraft:cooked_beef' => [['minecraft:steak', false, false]],
		'minecraft:muttoncooked' => [['minecraft:cooked_mutton', false, false]],
		'minecraft:muttonraw' => [['minecraft:raw_mutton', false, false]],
		'minecraft:coal' => [['minecraft:coal', false, false], ['minecraft:charcoal', true]],
		'minecraft:enchanted_book' => [['minecraft:redeemed_book', true, true]],
		'minecraft:name_tag' => [['minecraft:nametag', true, true]],
		'minecraft:shulker_shell' => [['minecraft:shulker_shell', false, false], ['minecraft:custom_death_tag', true]],
		'minecraft:turtle_shell_piece' => [['minecraft:scute', false, false], ['minecraft:gen_booster', true]],
		'minecraft:fermented_spider_eye' => [['minecraft:fermented_spider_eye', false, false], ['minecraft:enchantment_remover', true]],
		'minecraft:nether_star' => [['minecraft:nether_star', false, false], ['minecraft:animator', true]],
		'minecraft:empty_map' => [['minecraft:empty_map', false, false], ['minecraft:techit_note', true]],
		'minecraft:ghast_tear' => [['minecraft:ghast_tear', false, false], ['minecraft:sale_booster', true]],
		'minecraft:paper' => [['minecraft:paper', false, false], ['minecraft:key_note', true]],
		'minecraft:book' => [['minecraft:book', false, false], ['minecraft:redeemable_book', true], ['minecraft:redeemable_book', true], ['minecraft:redeemable_book', true], ['minecraft:redeemable_book', true], ['minecraft:redeemable_book', true]],
		'minecraft:magma_cream' => [['minecraft:magma_cream', false, false], ['minecraft:mine_nuke', true]],
		'minecraft:firework_star' => [['minecraft:haste_bomb', true, true]],
		'minecraft:blaze_rod' => [['minecraft:blaze_rod', false, false], ['minecraft:sell_wand', true]],
		'minecraft:paper' => [['minecraft:paper', false, false], ['minecraft:techit_note', true]],
		'minecraft:snowball' => [['minecraft:snowball', false, false], ['minecraft:fling_ball', true]],
		'minecraft:bucket' => [['minecraft:bucket', false, true], 8 => ['minecraft:water_bucket', true], 10 => ['minecraft:lava_bucket', true]],
		'minecraft:log' => [['minecraft:oak_log', true], ['minecraft:spruce_log', true], ['minecraft:birch_log', true], ['minecraft:jungle_log', true], ['minecraft:jungle_log', true]],
		'minecraft:log2' => [['minecraft:acacia_log', true], ['minecraft:dark_oak_log', true]],
		'minecraft:stained_hardened_clay' => [['minecraft:stained_clay', false, true]],
		'minecraft:reeds' => [['minecraft:sugarcane', true, true]],
		'minecraft:lapis_block' => [['minecraft:[block]lapis_lazuli', true, true]],
		"minecraft:sealantern" => [['minecraft:sea_lantern', true, true]],
		'minecraft:ender_chest' => [['minecraft:ender_chest', true, true]]
		//'minecraft:' => [['minecraft:', false, false], ['minecraft:', true]],
	];

	public static function legacyIdToTypeId(int $id, int $meta = 0, ?bool $fromBlockMap = false): ?int {
		$newString = LegacyItemIdToStringIdMap::getInstance()->legacyToString($id);
		if (isset(self::PM_CONVERT[$newString]) && (isset(self::PM_CONVERT[$newString][$meta]) || (isset(self::PM_CONVERT[$newString][0]) && self::PM_CONVERT[$newString][0][2]))) {
			$k = $meta;
			if (!isset(self::PM_CONVERT[$newString][$meta]) && self::PM_CONVERT[$newString][0][2]) $k = 0;
			$s = $newString;
			$newString = self::PM_CONVERT[$newString][$k][0];
			if (self::PM_CONVERT[$s][$k][1]) $meta = -1;
		}
		if (is_null($newString)) {
			if ($fromBlockMap) return null;
			return -LegacyBlockIds::legacyIdToTypeId($id, $meta, true);
		}
		//if (($s ??= null) == 'minecraft:log') var_dump("LOG NEW ID => " . $newString);
		return ItemRegistry::getItem($newString, $meta)?->getTypeId() ?? -BlockRegistry::getBlock($newString, $meta)?->getTypeId() ?? ItemRegistry::getItem($newString, 0)?->getTypeId() ?? -BlockRegistry::getBlock($newString, 0)?->getTypeId() ?? $id;
	}

	public static function typeIdToLegacyId(int $typeId, ?bool $fromBlockMap = false): ?int {
		$item = ItemRegistry::getItemById($typeId, -1);
		/** @var array<int,string> */
		$frontmap = LegacyItemIdToStringIdMap::getInstance()->getLegacyToStringMap();
		/** @var array<string,int> */
		$map = [];
		$id = 'air';
		foreach (array_merge(VanillaItems::getAll(), ItemRegistry::getAll()) as $iid => $item) {
			if ($item->getTypeId() == $typeId) $id = $iid;
		}
		$name = "minecraft:" . strtolower(str_replace(' ', '_', $id));
		foreach ($frontmap as $k => $v) $map[$v] = $k;
		return isset($map[$name]) ? $map[$name] : ($fromBlockMap ? null : LegacyBlockIds::typeIdToLegacyId($typeId, true)) ?? $typeId;
	}

	public static function stateIdToMeta(Item $item): int {
		try {
			$data = GlobalItemDataHandlers::getSerializer()->serializeType($item);
		} catch (Exception) {
			$data = null;
		}
		return $data?->getMeta() ?? 0;
	}
}
