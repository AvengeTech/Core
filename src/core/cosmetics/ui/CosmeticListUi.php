<?php

namespace core\cosmetics\ui;

use core\AtPlayer as Player;
use core\cosmetics\CosmeticData;
use core\ui\elements\simpleForm\Button;
use core\ui\windows\SimpleForm;
use core\utils\TextFormat;

class CosmeticListUi extends SimpleForm {

	const PER_PAGE = 8;

	public array $cosmetics = [];

	public int $totalPages;

	public array $thisPage = [];

	public function __construct(
		Player $player,
		public int $listType,
		public int $page = 1,
		public array $cachedCosmetics = [],
		public bool $fromMenu = true
	) {
		parent::__construct("Cosmetics", "Select which cosmetic you'd like to view below!");

		if (count($this->cachedCosmetics) == 0) {
			$this->cosmetics = $this->cachedCosmetics = $player->getSession()->getCosmetics()->getAvailableCosmetics($listType);
		} else {
			$this->cosmetics = $this->cachedCosmetics;
		}

		$name = match ($listType) {
			CosmeticData::TYPE_CAPE => "Cape",
			CosmeticData::TYPE_TRAIL_EFFECT => "Trail Effect",
			CosmeticData::TYPE_IDLE_EFFECT => "Idle Effect",
			CosmeticData::TYPE_DOUBLE_JUMP_EFFECT => "Double Jump Effect",
			CosmeticData::TYPE_ARROW_EFFECT => "Arrow Effect",
			CosmeticData::TYPE_SNOWBALL_EFFECT => "Snowball Effect",
			CosmeticData::TYPE_HAT => "Hat",
			CosmeticData::TYPE_BACK => "Back",
			CosmeticData::TYPE_SHOES => "Shoes",
			CosmeticData::TYPE_SUIT => "Suit",
			CosmeticData::TYPE_MORPH => "Morph",
			CosmeticData::TYPE_PET => "Pet",
		};

		$pages = array_chunk($this->cosmetics, self::PER_PAGE);
		parent::__construct($name . "s (" . $page . "/" . ($this->totalPages = count($pages)) . ")", "Select a " . $name . " to equip it!");

		$this->addButton(new Button("Search"));

		if ($page > 1) {
			$this->addButton(new Button("Previous page (" . ($page - 1) . "/" . count($pages) . ")"));
		}

		$this->addButton(new Button("Unequip " . $name));

		$display = $this->thisPage = $pages[$page - 1] ?? [];
		foreach ($display as $cosmetic) {
			$this->addButton(new Button($cosmetic->getRarityColor() . $cosmetic->getName()));
		}

		if ($page < count($pages)) {
			$this->addButton(new Button("Next page (" . ($page + 1) . "/" . count($pages) . ")"));
		}

		if ($this->fromMenu) $this->addButton(new Button("Go back"));
	}

	public function handle($response, Player $player) {
		if ($response == 0) {
			if (!$player->isStaff()) {
				$player->showModal(new CosmeticListUi($player, $this->listType, $this->page, $this->cachedCosmetics, $this->fromMenu));
				return;
			}
			$player->showModal(new SearchCosmeticsUi($player, $this->listType, $this->fromMenu));
			return;
		}
		$response--;
		if ($this->page > 1) {
			if ($response == 0) {
				$player->showModal(new CosmeticListUi($player, $this->listType, $this->page - 1, $this->cachedCosmetics, $this->fromMenu));
				return;
			}
			$response--;
		}

		$name = match ($this->listType) {
			CosmeticData::TYPE_CAPE => "Cape",
			CosmeticData::TYPE_TRAIL_EFFECT => "Trail Effect",
			CosmeticData::TYPE_IDLE_EFFECT => "Idle Effect",
			CosmeticData::TYPE_DOUBLE_JUMP_EFFECT => "Double Jump Effect",
			CosmeticData::TYPE_ARROW_EFFECT => "Arrow Effect",
			CosmeticData::TYPE_SNOWBALL_EFFECT => "Snowball Effect",
			CosmeticData::TYPE_HAT => "Hat",
			CosmeticData::TYPE_BACK => "Back",
			CosmeticData::TYPE_SHOES => "Shoes",
			CosmeticData::TYPE_SUIT => "Suit",
			CosmeticData::TYPE_MORPH => "Morph",
			CosmeticData::TYPE_PET => "Pet",
		};

		if ($response == 0) {
			switch ($this->listType) {
				case CosmeticData::TYPE_CAPE:
					$player->getSession()->getCosmetics()->equipCape();
					break;
				case CosmeticData::TYPE_TRAIL_EFFECT:
					$player->getSession()->getCosmetics()->equipTrail();
					break;
				case CosmeticData::TYPE_IDLE_EFFECT:
					$player->getSession()->getCosmetics()->equipIdle();
					break;
				case CosmeticData::TYPE_DOUBLE_JUMP_EFFECT:
					$player->getSession()->getCosmetics()->equipDoubleJump();
					break;
				case CosmeticData::TYPE_ARROW_EFFECT:
					$player->getSession()->getCosmetics()->equipArrow();
					break;
				case CosmeticData::TYPE_SNOWBALL_EFFECT:
					$player->getSession()->getCosmetics()->equipSnowball();
					break;
				case CosmeticData::TYPE_HAT:
					$player->getSession()->getCosmetics()->equipHat();
					break;
				case CosmeticData::TYPE_BACK:
					$player->getSession()->getCosmetics()->equipBack();
					break;
				case CosmeticData::TYPE_SHOES:
					$player->getSession()->getCosmetics()->equipShoes();
					break;
				case CosmeticData::TYPE_SUIT:
					$player->getSession()->getCosmetics()->equipSuit();
					break;
			}
			if ($this->fromMenu) {
				switch ($this->listType) {
					case CosmeticData::TYPE_CAPE:
						$player->showModal(new CosmeticsUi($player, "Your " . $name . " has been unequipped!", false));
						break;
					case CosmeticData::TYPE_TRAIL_EFFECT:
					case CosmeticData::TYPE_IDLE_EFFECT:
					case CosmeticData::TYPE_DOUBLE_JUMP_EFFECT:
					case CosmeticData::TYPE_ARROW_EFFECT:
					case CosmeticData::TYPE_SNOWBALL_EFFECT:
						$player->showModal(new EffectCosmeticsUi($player, "Your " . $name . " has been unequipped!", false));
						break;
					case CosmeticData::TYPE_HAT:
					case CosmeticData::TYPE_BACK:
					case CosmeticData::TYPE_SHOES:
					case CosmeticData::TYPE_SUIT:
						$player->showModal(new ClothingCosmeticsUi($player, "Your " . $name . " has been unequipped!", false));
						break;
				}
			} else {
				$player->sendMessage(TextFormat::GI . "Your " . $name . " has been unequipped!");
			}
			return;
		}
		$response--;

		$cosmetic = $this->thisPage[$response] ?? null;
		if ($cosmetic !== null) {
			switch ($this->listType) {
				case CosmeticData::TYPE_CAPE:
					$player->getSession()->getCosmetics()->equipCape($cosmetic);
					break;
				case CosmeticData::TYPE_TRAIL_EFFECT:
					$player->getSession()->getCosmetics()->equipTrail($cosmetic);
					break;
				case CosmeticData::TYPE_IDLE_EFFECT:
					$player->getSession()->getCosmetics()->equipIdle($cosmetic);
					break;
				case CosmeticData::TYPE_DOUBLE_JUMP_EFFECT:
					$player->getSession()->getCosmetics()->equipDoubleJump($cosmetic);
					break;
				case CosmeticData::TYPE_ARROW_EFFECT:
					$player->getSession()->getCosmetics()->equipArrow($cosmetic);
					break;
				case CosmeticData::TYPE_SNOWBALL_EFFECT:
					$player->getSession()->getCosmetics()->equipSnowball($cosmetic);
					break;
				case CosmeticData::TYPE_HAT:
					$player->getSession()->getCosmetics()->equipHat($cosmetic);
					break;
				case CosmeticData::TYPE_BACK:
					$player->getSession()->getCosmetics()->equipBack($cosmetic);
					break;
				case CosmeticData::TYPE_SHOES:
					$player->getSession()->getCosmetics()->equipShoes($cosmetic);
					break;
				case CosmeticData::TYPE_SUIT:
					$player->getSession()->getCosmetics()->equipSuit($cosmetic);
					break;
			}
			if ($this->fromMenu) {
				switch ($this->listType) {
					case CosmeticData::TYPE_CAPE:
						$player->showModal(new CosmeticsUi($player, $cosmetic->getName() . " " . $name . " has been equipped!", false));
						break;
					case CosmeticData::TYPE_TRAIL_EFFECT:
					case CosmeticData::TYPE_IDLE_EFFECT:
					case CosmeticData::TYPE_DOUBLE_JUMP_EFFECT:
					case CosmeticData::TYPE_ARROW_EFFECT:
					case CosmeticData::TYPE_SNOWBALL_EFFECT:
						$player->showModal(new EffectCosmeticsUi($player, $cosmetic->getName() . " " . $name . " has been equipped!", false));
						break;
					case CosmeticData::TYPE_HAT:
					case CosmeticData::TYPE_BACK:
					case CosmeticData::TYPE_SHOES:
					case CosmeticData::TYPE_SUIT:
						$player->showModal(new ClothingCosmeticsUi($player, $cosmetic->getName() . " " . $name . " has been equipped!", false));
						break;
				}
			} else {
				$player->sendMessage(TextFormat::GI . $cosmetic->getName() . " " . $name . " has been equipped!");
			}
			return;
		}

		if ($response == count($this->thisPage)) {
			if ($this->page < $this->totalPages) {
				$player->showModal(new CosmeticListUi($player, $this->listType, $this->page + 1, $this->cachedCosmetics, $this->fromMenu));
				return;
			}
			if ($this->fromMenu) {
				switch ($this->listType) {
					case CosmeticData::TYPE_CAPE:
						$player->showModal(new CosmeticsUi($player));
						break;
					case CosmeticData::TYPE_TRAIL_EFFECT:
					case CosmeticData::TYPE_IDLE_EFFECT:
					case CosmeticData::TYPE_DOUBLE_JUMP_EFFECT:
					case CosmeticData::TYPE_ARROW_EFFECT:
					case CosmeticData::TYPE_SNOWBALL_EFFECT:
						$player->showModal(new EffectCosmeticsUi($player));
						break;
					case CosmeticData::TYPE_HAT:
					case CosmeticData::TYPE_BACK:
					case CosmeticData::TYPE_SHOES:
					case CosmeticData::TYPE_SUIT:
						$player->showModal(new ClothingCosmeticsUi($player));
						break;
				}
			}
			return;
		}
		if ($this->fromMenu) {
			switch ($this->listType) {
				case CosmeticData::TYPE_CAPE:
					$player->showModal(new CosmeticsUi($player));
					break;
				case CosmeticData::TYPE_TRAIL_EFFECT:
				case CosmeticData::TYPE_IDLE_EFFECT:
				case CosmeticData::TYPE_DOUBLE_JUMP_EFFECT:
				case CosmeticData::TYPE_ARROW_EFFECT:
				case CosmeticData::TYPE_SNOWBALL_EFFECT:
					$player->showModal(new EffectCosmeticsUi($player));
					break;
				case CosmeticData::TYPE_HAT:
				case CosmeticData::TYPE_BACK:
				case CosmeticData::TYPE_SHOES:
				case CosmeticData::TYPE_SUIT:
					$player->showModal(new ClothingCosmeticsUi($player));
					break;
			}
		}
	}
}
