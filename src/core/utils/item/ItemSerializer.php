<?php

namespace core\utils\item;

use Closure;
use InvalidArgumentException;
use pocketmine\block\Block;
use pocketmine\data\bedrock\item\SavedItemData;
use pocketmine\item\Item;
use pocketmine\item\ItemBlock;
use pocketmine\world\format\io\GlobalItemDataHandlers;
use ReflectionProperty;

class ItemSerializer{

	public static function map(Item $item, Closure $serializer): void {
		$instance = GlobalItemDataHandlers::getSerializer();

		try {
			$instance->map($item, $serializer);
		} catch (InvalidArgumentException) {
			$serializerProperty = new ReflectionProperty($instance, "itemSerializers");
			$serializerProperty->setAccessible(true);
			$value = $serializerProperty->getValue($instance);
			$value[$item->getTypeId()] = $serializer;
			$serializerProperty->setValue($instance, $value);
		}
	}



	public static function mapWithMeta(string $identifier, Item $item, Closure $serializer): void {
		$instance = GlobalItemDataHandlers::getSerializer();

		try {
			$instance->map($item, function (Item $item) use ($identifier, $serializer) : SavedItemData {
				$meta = $serializer($item);

				return new SavedItemData($identifier, $meta);
			});
		} catch (InvalidArgumentException) {
			$serializerProperty = new ReflectionProperty($instance, "itemSerializers");
			$serializerProperty->setAccessible(true);
			$value = $serializerProperty->getValue($instance);
			$value[$item->getTypeId()] = function (Item $item) use ($identifier, $serializer): SavedItemData {
				$meta = $serializer($item);

				return new SavedItemData($identifier, $meta);
			};
			$serializerProperty->setValue($instance, $value);
		}
	}


	public static function mapBlock(Block $block, Closure $serializer): void {
		$instance = GlobalItemDataHandlers::getSerializer();

		try {
			$instance->mapBlock($block, $serializer);
		} catch (InvalidArgumentException) {
			$serializerProperty = new ReflectionProperty($instance, "blockItemSerializers");
			$serializerProperty->setAccessible(true);
			$value = $serializerProperty->getValue($instance);
			$value[$block->getTypeId()] = $serializer;
			$serializerProperty->setValue($instance, $value);
		}
	}
}