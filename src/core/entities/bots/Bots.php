<?php

namespace core\entities\bots;

use pocketmine\item\VanillaItems;

use core\{
	Core,
	AtPlayer as Player
};
use core\utils\ItemRegistry;

class Bots {

	public $plugin;

	public $bots = [];
	public $lc = [];

	public function __construct(Core $plugin) {
		$this->plugin = $plugin;

		$botarr = BotData::BOT_DATA[$this->plugin->getNetwork()->getServerType()] ?? [];
		foreach ($botarr as $name => $data) {
			$armor = $data["armor"] ?? [];
			foreach ($armor as $key => $item) {
				$armor[$key] = ItemRegistry::getItemById($item);
			}
			$this->bots[$name] = new Bot($name, (string)$data["nametag"], $data["x"], $data["y"], $data["z"], $data["pitch"], $data["yaw"], $data["level"], ItemRegistry::getItemById($data["item"][0], $data["item"][1], 1) ?? VanillaItems::AIR(), $armor, $data["sitting"] ?? false, $data["turn"], $data["skin"]["enabled"], $data["skin"]["name"], $data["scale"] ?? 1, $data["config"]);
		}

		$botarr = BotData::BOT_DATA[$this->plugin->getNetwork()->getIdentifier()] ?? [];
		foreach ($botarr as $name => $data) {
			$armor = $data["armor"] ?? [];
			foreach ($armor as $key => $item) {
				$armor[$key] = ItemRegistry::getItemById($item);
			}
			$this->bots[$name] = new Bot($name, (string)$data["nametag"], $data["x"], $data["y"], $data["z"], $data["pitch"], $data["yaw"], $data["level"], ItemRegistry::getItemById($data["item"][0], $data["item"][1], 1) ?? VanillaItems::AIR(), $armor, $data["sitting"] ?? false, $data["turn"], $data["skin"]["enabled"], $data["skin"]["name"], $data["scale"] ?? 1, $data["config"]);
		}
	}

	public function tick(): void {
		foreach ($this->lc as $name => $lvl) {
			$player = $this->plugin->getServer()->getPlayerExact($name);
			unset($this->lc[$name]);
			if ($player instanceof Player) {
				$bots = $this->getBots();
				foreach ($bots as $bot) {
					if ($bot->getWorldName() == $lvl) {
						$bot->spawn($player);
					} else {
						$bot->despawn($player);
					}
				}
			}
		}
	}

	public function onJoin(Player $player): void {
		$bots = $this->getBots();
		foreach ($bots as $name => $bot) {
			if ($bot->getWorldName() == $player->getWorld()->getDisplayName()) {
				$bot->spawn($player);
			} else {
				$bot->despawn($player);
			}
		}
	}

	public function onQuit(Player $player): void {
		$bots = $this->getBots();
		foreach ($bots as $name => $bot) {
			if (isset($bot->getSpawnedTo()[$player->getName()])) {
				$bot->despawn($player);
			}
		}
	}

	public function changeLevel(Player $player, string $newlevel): void {
		$this->lc[$player->getName()] = $newlevel;
	}

	public function getBots(): array {
		return $this->bots;
	}

	public function getBot(string $name): ?Bot {
		return $this->getBots()[$name] ?? null;
	}
}
