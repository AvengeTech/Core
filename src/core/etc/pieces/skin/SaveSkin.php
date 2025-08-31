<?php

namespace core\etc\pieces\skin;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\plugin\Plugin;
use pocketmine\Server;

use core\{
	Core,
	AtPlayer as Player
};
use core\utils\{
	CapeData,
	TextFormat
};

class SaveSkin extends Command {

	public $plugin;

	public function __construct(Core $plugin, $name, $description) {
		$this->plugin = $plugin;
		parent::__construct($name, $description);
		$this->setPermission("core.tier3");
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args) {
		$skin = $this->plugin->getEtc()->getPiece("skin");
		$name = array_shift($args);
		if ($sender instanceof Player && !$sender->isTier3() && $sender->getName() !== "PringelzDaddy48") {
			$sender->sendMessage(TextFormat::RI . "Invalid permissions");
			return false;
		}
		if (count($args) === 0) {
			if (!$sender instanceof Player) return;
			if ($skin->skinExists(strtolower($name))) {
				$sender->sendMessage(TextFormat::RI . "A skin by this name already exists!");
				return false;
			}
			$skin->saveSkin($sender, strtolower($name));
			$sender->sendMessage(TextFormat::GI . "Skin saved as " . strtolower($name) . ".dat");
			return true;
		}
		$cd = new CapeData();
		$arg = array_shift($args);
		switch ($arg) {
			case "png":
				if (!$sender instanceof Player) return;
				$png = $cd->skinToPng($sender->getSkin()->getSkinData());
				imagepng($png, $skin::$dir . "png/" . $name . ".png");
				$sender->sendMessage(TextFormat::GI . "Skin saved as " . strtolower($name) . ".png");
				break;
			case "png128":
				if (!$sender instanceof Player) return;
				$png = $cd->skinToPng128($cd->skinToPng($sender->getSkin()->getSkinData()));
				imagepng($png, $skin::$dir . "png128/" . $name . ".png");
				$sender->sendMessage(TextFormat::GI . "Skin saved as " . strtolower($name) . ".png");
				break;
			case "convert":
				if (!$skin->skinExists(strtolower($name))) {
					$sender->sendMessage(TextFormat::RI . "A skin by this name doesn't exist!");
					return false;
				}
				$png = $cd->skinToPng(Skin::getSavedSkinData(strtolower($name)));
				imagepng($png, $skin::$dir . "png/" . $name . ".png");
				$sender->sendMessage(TextFormat::GI . "Skin saved as " . strtolower($name) . ".png");
				break;
			default:
				$player = Server::getInstance()->getPlayerByPrefix($arg);
				if($player instanceof Player){
					if ($skin->skinExists(strtolower($name))) {
						$sender->sendMessage(TextFormat::RI . "A skin by this name already exists!");
						return false;
					}
					$skin->saveSkin($player, strtolower($name));
					$sender->sendMessage(TextFormat::GI . $player->getName() . "'s skin saved as " . strtolower($name) . ".dat");
					return true;
				}else{
					$sender->sendMessage(TextFormat::RI . "Player not found!");
				}
				break;
		}
	}

	public function getPlugin(): Plugin {
		return $this->plugin;
	}
}
