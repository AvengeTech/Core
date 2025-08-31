<?php

namespace core\cosmetics\command;

use core\Core;
use core\AtPlayer as Player;
use core\command\type\CoreCommand;
use core\utils\CapeData;
use core\utils\SkinUtils;
use core\utils\TextFormat;
use pocketmine\command\CommandSender;
use pocketmine\plugin\Plugin;
use core\rank\Rank;

class LayerTest extends CoreCommand {

	public function __construct(public Core $plugin, string $name, string $description) {
		parent::__construct($name, $description);
		$this->setInGameOnly();
		$this->setHierarchy(Rank::HIERARCHY_HEAD_MOD);
	}

	public function handlePlayer(Player $sender, string $commandLabel, array $args)
	{
		if (count($args) < 1) {
			$sender->sendMessage(TextFormat::RI . "Usage: /layertest <cosmetic folder>");
			return;
		}
		$cosmetics = $args;
		$name = array_shift($args);
		$cd = new CapeData();
		if ($name == "reset") {
			$sender->setSkin($cd->getResetSkin($sender->getSkin()));
			$sender->sendMessage(TextFormat::GI . "Skin reset!");
			return;
		}
		foreach ($cosmetics as $cosmetic) {
			if (!file_exists("/[REDACTED]/cosmetics/$cosmetic/$cosmetic.png")) {
				$sender->sendMessage(TextFormat::RI . "Cosmetic $cosmetic not found!");
				return;
			}
		}

		$sender->setSkin(SkinUtils::layerSkin($sender->getSkin(), $cosmetics));
		$sender->sendSkin();
		$sender->sendMessage(TextFormat::GI . "Cosmetics applied!");
	}

	public function getPlugin(): Plugin {
		return $this->plugin;
	}
}
