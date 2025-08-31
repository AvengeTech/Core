<?php

namespace core\gadgets\ui;

use core\{
	Core,
	AtPlayer as Player
};
use core\cosmetics\ui\CosmeticsUi;
use core\ui\elements\simpleForm\Button;
use core\ui\windows\SimpleForm;

class GadgetUi extends SimpleForm {

	public function __construct(Player $player) {
		parent::__construct("Gadgets", "Select a gadget to equip it!");
		$this->addButton(new Button("Gift gadgets"));
		$this->addButton(new Button("Unequip gadget"));
		foreach (Core::getInstance()->getGadgets()->getGadgets() as $gadget) {
			$this->addButton(new Button($gadget->getName() . PHP_EOL . "(" . number_format($player->getSession()->getGadgets()->getTotal($gadget)) . " available)"));
		}
		$this->addButton(new Button("Go back"));
	}

	public function handle($response, Player $player) {
		if ($response === 0) {
			$player->showModal(new GadgetGiftUi($player));
			return;
		}
		if ($response === 1) {
			$player->getSession()->getGadgets()->setDefaultGadget(-1);
			$player->showModal(new CosmeticsUi($player, "Gadget has been unequipped", false));
			return;
		}
		$response -= 2;
		$gadget = Core::getInstance()->getGadgets()->getGadgets()[$response] ?? null;
		if ($gadget !== null) {
			$player->getSession()->getGadgets()->setDefaultGadget($gadget);
			$player->showModal(new CosmeticsUi($player, "Equipped the " . $gadget->getName() . " gadget", false));
			return;
		}
		$player->showModal(new CosmeticsUi($player));
	}
}
