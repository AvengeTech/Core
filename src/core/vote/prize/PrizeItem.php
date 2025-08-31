<?php

namespace core\vote\prize;

use pocketmine\item\Item;
use pocketmine\{
	lang\Language,
	Server
};
use pocketmine\console\ConsoleCommandSender;

use prison\Prison;
use prison\enchantments\book\RedeemedBook;
use prison\enchantments\effects\items\EffectItem;

use skyblock\SkyBlock;
use skyblock\enchantments\item\EnchantmentBook;

use core\Core;
use core\AtPlayer as Player;
use core\AtPlayer;
use core\utils\TextFormat;
use prison\enchantments\EnchantmentRegistry;
use prison\PrisonPlayer;
use skyblock\enchantments\EnchantmentRegistry as SBER;
use skyblock\enchantments\item\MaxBook;
use skyblock\SkyBlockPlayer;

class PrizeItem{

	const RARITY_NAMES = [
		1 => "Common",
		2 => "Uncommon",
		3 => "Rare",
		4 => "Legendary",
		5 => "Divine"
	];

	public function __construct(
		public $item,
		public int $count = 1,
		public bool $needsRefresh = false,
		public $extra = -1,
		public string $customname = ""
	) {
	}

	public function getName(): string {
		$item = $this->getItem();
		$count = $this->getCount();
		$extra = $this->getExtra();
		$servertype = Core::getInstance()->getNetwork()->getServerType();

		if ($this->hasCustomName()) {
			return $count . " " . $this->getCustomName();
		}

		if ($item instanceof Item) {
			if ($servertype == "prison") {
				if ($item instanceof RedeemedBook) {
					if ($this->needsRefresh()) {
						$rarity = self::RARITY_NAMES[$extra];
						return $count . " Random " . $rarity . " Enchantment" . ($count !== 1 ? "s" : "");
					} else {
						$ench = $item->getEnchant();
						return $count . " " . $ench->getName() . " " . $ench->getStoredLevel() . " book" . ($count !== 1 ? "s" : "");
					}
				}
				if ($item instanceof EffectItem) {
					if ($this->needsRefresh()) {
						$rarity = self::RARITY_NAMES[$extra];
						return $count . " Random " . $rarity . " Animator" . ($count !== 1 ? "s" : "");
					} else {
						$effect = $item->getEffect();
						return $count . " " . $effect->getName() . " " . $effect->getTypeName() . " Animator" . ($count !== 1 ? "s" : "");
					}
				}
			}
			if ($servertype == "skyblock") {
				if ($item instanceof EnchantmentBook) {
					$rarity = self::RARITY_NAMES[$extra];
					if ($this->needsRefresh()) {
						return $count . " Random " . $rarity . " Enchantment" . ($count !== 1 ? "s" : "");
					} else {
						$ench = $item->getEnchant();

						if(is_null($ench)) return $count . " " . TextFormat::clean($item->getName());

						return $count . " " . $ench->getName() . " " . $ench->getStoredLevel() . " book" . ($count !== 1 ? "s" : "");
					}
				}
				if ($item instanceof MaxBook) {
					$rarity = self::RARITY_NAMES[$extra];
					return $count . " " . $rarity . " Max Book" . ($count !== 1 ? "s" : "");
				}
			}
			return $count . " " . TextFormat::clean($item->getName());
		} else {
			$data = explode(":", $item);
			switch (array_shift($data)) {
				case "key":
					$type = array_shift($data);
					if ($type == "all") {
						return $count . " of each key";
					} else {
						return $count . " " . ucfirst($type) . " keys";
					}

				case "tag":
					$type = array_shift($data);
					if ($type == "random") {
						return $count . " random tags";
					} else {
						return $count . " " . $extra . " tag";
					}

				case "techits":
					return number_format($count) . " techits";

				case "shards":
					return number_format($count) . " shards";

				case "lootbox":
					return number_format($count) . " loot box" . ($count > 1 ? "es" : "");

				case "xp":
					return $count . " XP levels";

				case "kit":
					$type = array_shift($data);
					return $count . " " . ucfirst($type) . " kit";

				case "command":
					$type = array_shift($data);
					return "/" . $type;
			}
		}
		return "unknown";
	}

	public function getItem() {
		return $this->item;
	}

	public function refreshItem(Player $player): void {
		$item = $this->getItem();
		$extra = $this->getExtra();
		$servertype = Core::getInstance()->getNetwork()->getServerType();

		if ($item instanceof Item) {
			if ($servertype == "prison") {
				if ($item instanceof RedeemedBook) {
					$ench = EnchantmentRegistry::getRandomEnchantment($extra);
					$item->setup($ench);
					$this->item = $item;
					return;
				}
				if ($item instanceof EffectItem) {
					$eff = Prison::getInstance()->getEnchantments()->getEffects()->getRandomEffect($extra);
					$item->setup($extra, $eff);
					$this->item = $item;
					return;
				}
			}
			if ($servertype == "skyblock") {
				if ($item instanceof EnchantmentBook) {
					($ench = SBER::getRandomEnchantment($extra))->setStoredLevel(mt_rand(1, $ench->getMaxLevel()));
					$item->setup($ench);
					$this->item = $item;
					return;
				}
			}
		} else {
			$data = explode(":", $item);
			switch (array_shift($data)) {
				case "tag":
					if ($servertype == "prison") {
						$tags = Prison::getInstance()->getTags();
						/** @var PrisonPlayer $player */
						$session = $player->getGameSession()->getTags();
						try {
							$nh = $session->getTagsNoHave();
							$random = $nh[array_rand($nh)];
							$this->setExtra($random->getName());
						} catch (\Error $e) {
							$this->setExtra($tags->getRandomTag()->getName());
						}
						return;
					}
					if ($servertype == "skyblock") {
						/** @var SkyBlockPlayer $player */
						$tags = SkyBlock::getInstance()->getTags();
						$session = $player->getGameSession()->getTags();
						try {
							$nh = $session->getTagsNoHave();
							$random = $nh[array_rand($nh)];
							$this->setExtra($random->getName());
						} catch (\Error $e) {
							$this->setExtra($tags->getRandomTag()->getName());
						}
						return;
					}
					return;
			}
		}
	}

	public function needsRefresh(): bool {
		return $this->needsRefresh;
	}

	public function getCount(): int {
		return $this->count;
	}

	public function getExtra() {
		return $this->extra;
	}

	public function setExtra($extra): void {
		$this->extra = $extra;
	}

	public function hasCustomName(): bool {
		return $this->customname != "";
	}

	public function getCustomName(): string {
		return $this->customname;
	}

	public function give(Player $player): void {
		$item = $this->getItem();
		$count = $this->getCount();
		$extra = $this->getExtra();
		$servertype = Core::getInstance()->getNetwork()->getServerType();

		if ($item instanceof Item) {
			while ($count > 0) {
				if ($this->needsRefresh()) $this->refreshItem($player);
				$player->getInventory()->addItem($this->getItem());

				$count--;
			}
		} else {
			$data = explode(":", $item);
			switch (array_shift($data)) {
				case "key":
					$type = array_shift($data);
					if ($servertype == "prison") {
						/** @var PrisonPlayer $player */
						$session = $player->getGameSession()->getMysteryBoxes();
						if ($type == "all") {
							foreach ([
								"iron",
								"gold",
								"diamond",
								"emerald",
								"vote",
							] as $t) $session->addKeys($t, $count);
						} else {
							$session->addKeys($type, $count);
						}
						return;
					}
					if ($servertype == "skyblock") {
						/** @var SkyBlockPlayer $player */
						$session = $player->getGameSession()->getCrates();
						if ($type == "all") {
							foreach ([
								"iron",
								"gold",
								"diamond",
								"emerald",
								"vote",
							] as $t) $session->addKeys($t, $count);
						} else {
							$session->addKeys($type, $count);
						}
						return;
					}
					return;
				case "tag":
					while ($count > 0) {
						if ($this->needsRefresh()) $this->refreshItem($player);

						if ($servertype == "prison") {
							/** @var PrisonPlayer $player */
							$tags = Prison::getInstance()->getTags();
							$session = $player->getGameSession()->getTags();
							$session->addTag($tags->getTag($this->getExtra()));
						}
						if ($servertype == "skyblock") {
							/** @var SkyBlockPlayer $player */
							$tags = SkyBlock::getInstance()->getTags();
							$session = $player->getGameSession()->getTags();
							$session->addTag($tags->getTag($this->getExtra()));
						}
						$count--;
					}
					return;
				case "techits":
					/** @var PrisonPlayer|SkyBlockPlayer $player */
					$player->addTechits($count);
					return;
				case "shards":
					$player->getSession()->getLootBoxes()->addShards($count);
					return;
				case "lootbox":
					$player->getSession()->getLootBoxes()->addLootBoxes($count);
					return;
				case "xp":
					$player->getXpManager()->addXpLevels($count);
					return;
				case "kit":
					$name = array_shift($data);
					if ($servertype == "prison") {
						$kit = Prison::getInstance()->getKits()->getKit($name);
						$kit->equip($player, false);
					}
					if ($servertype == "skyblock") {
						$kit = SkyBlock::getInstance()->getKits()->getKitByName($name);
						$kit->equip($player, false);
					}
					return;
				case "command":
					$command = array_shift($data);
					$sender = new ConsoleCommandSender($player->getServer(), new Language("eng"));
					foreach ([
						"core.staff", "prison.staff", "skyblock.staff",
						"core.tier3", "prison.tier3", "skyblock.tier3",
					] as $permission) {
						$sender->addAttachment(Core::getInstance(), $permission, true);
					}
					Server::getInstance()->dispatchCommand($sender, str_replace("{player}", $player->getName(), $command));
					return;
			}
		}
	}
}
