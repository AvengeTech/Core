<?php

namespace core\utils\command;

use core\AtPlayer as Player;
use core\Core;
use core\command\type\CoreCommand;
use core\items\type\Armor;
use core\rank\Rank;
use core\utils\ItemRegistry;
use core\utils\TextFormat;
use pocketmine\command\CommandSender;
use pocketmine\item\Armor as PMArmor;
use pocketmine\item\StringToItemParser;
use pocketmine\item\VanillaArmorMaterials;
use pocketmine\plugin\Plugin;

class TrimCommand extends CoreCommand {

	public function __construct(public Core $plugin, string $name, string $description) {
		parent::__construct($name, $description);
		$this->setInGameOnly();
		$this->setHierarchy(Rank::HIERARCHY_HEAD_MOD);
	}

	public function handlePlayer(Player $sender, string $commandLabel, array $args)
	{
		$material = (string)(count($args) == 0 ? null : array_shift($args));
		$pattern = (string)(count($args) == 0 ? null : array_shift($args));
		$option = (string)(count($args) == 0 ? "hand" : array_shift($args));

		if(is_null($material) || is_null($pattern)){
			$sender->sendMessage(TextFormat::RED . "Usage: /trim <material> <pattern> <hand:set>");
			return;
		}

		if(!in_array($material, Armor::MATERIALS)){
			$msg = "Not a valid material!" . TextFormat::WHITE . "\n\n";
			$msg .= "Materials:";

			foreach(Armor::MATERIALS as $material){
				$msg .= "\n" . TextFormat::WHITE . $material;
			}

			$sender->sendMessage(TextFormat::RI . $msg);
			return;
		}
		if(!in_array($pattern, Armor::PATTERNS)){
			$msg = "Not a valid pattern!" . TextFormat::WHITE . "\n\n";
			$msg .= "Patterns:";

			foreach(Armor::PATTERNS as $pattern){
				$msg .= "\n" . TextFormat::WHITE . $pattern;
			}

			$sender->sendMessage(TextFormat::RI . $msg);
			return;
		}


		if($option == "hand"){
			$item = $sender->getInventory()->getItemInHand();

			if($item->isNull() || !$item instanceof Armor){
				$sender->sendMessage(TextFormat::RED . "You must hold an armor piece in your hand!");
				return;
			}

			$item->setTrim($material, $pattern);
			$sender->getInventory()->setItemInHand($item);
			$sender->sendMessage(TextFormat::GREEN . "Trimmed " . $item->getName() . " with " . $material . " material and " . $pattern . " pattern!");
		}elseif($option == "set"){
			$type = (string)(count($args) == 0 ? null : array_shift($args));
	
			if(is_null($type)){
				$sender->sendMessage(TextFormat::RED . "Usage: /trim <material> <pattern> <hand:set> <type>");
				return;
			}

			$type = strtolower($type);
			$types = [];

			foreach(VanillaArmorMaterials::getAll() as $name => $armorMaterial){
				if($name === "turtle") continue;

				$types[] = strtolower($name);
			}

			if(!in_array($type, $types)){
				$msg = "Not a valid type!" . TextFormat::WHITE . "\n\n";
				$msg .= "Types:";

				foreach($types as $armorType){
					$msg .= "\n" . TextFormat::WHITE . $armorType;
				}

				$sender->sendMessage(TextFormat::RI . $msg);
				return;
			}

			foreach(["boots" => "boots", "leggings" => "pants", "chestplate" => "tunic", "helmet" => "cap"] as $normal => $leather){
				$itemName = "";

				if($type == "leather"){
					$itemName = "leather_" . $leather;
				}else{
					$itemName = $type . "_" . $normal;
				}

				$item = StringToItemParser::getInstance()->parse($itemName);

				if(is_null($item) || !$item instanceof PMArmor) continue;

				$item = ItemRegistry::convertToEArmor($item);
				$item->setTrim($material, $pattern);
				$sender->getInventory()->addItem($item);
			}
			$sender->sendMessage(TextFormat::GREEN . "Trimmed " . ucfirst($type) . " Set" . " with " . $material . " material and " . $pattern . " pattern!");
		}
		return;
	}

	public function getPlugin(): Plugin {
		return $this->plugin;
	}
}
