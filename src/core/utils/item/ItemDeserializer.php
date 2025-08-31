<?php

namespace core\utils\item;

use Closure;
use InvalidArgumentException;
use pocketmine\data\bedrock\item\SavedItemData as Data;
use pocketmine\item\Item;
use pocketmine\world\format\io\GlobalItemDataHandlers;
use ReflectionProperty;

class ItemDeserializer{

	public static function map(string $identifier, Closure $deserializer) : void{
		$instance = GlobalItemDataHandlers::getDeserializer();

		try {
			$instance->map($identifier, $deserializer);
		} catch (InvalidArgumentException) {
			$deserializerProperty = new ReflectionProperty($instance, "deserializers");
			$deserializerProperty->setAccessible(true);
			$value = $deserializerProperty->getValue($instance);
			$value[$identifier] = $deserializer;
			$deserializerProperty->setValue($instance, $value);
		}
	}

	public static function mapWithMeta(string $identifier, Item $item, Closure $deserializer) : void{
		$instance = GlobalItemDataHandlers::getDeserializer();

		try{
			$instance->map($identifier, function(Data $data) use($item, $deserializer) : Item {
				$result = clone $item;
				$deserializer($result, $data->getMeta());

				return $result;
			});
		}catch(InvalidArgumentException){
			$serializerProperty = new ReflectionProperty($instance, "deserializers");
			$serializerProperty->setAccessible(true);
			$value = $serializerProperty->getValue($instance);
			$value[$identifier] = function (Data $data) use ($item, $deserializer) : Item{
				$result = clone $item;
				$deserializer($result, $data->getMeta());

				return $result;
			};
			$serializerProperty->setValue($instance, $value);
		}
	}

	public static function mapBlock(string $identifier, Closure $deserializer) : void{
		self::map($identifier, fn(Data $data) => $deserializer($data)->asItem());
	}
}