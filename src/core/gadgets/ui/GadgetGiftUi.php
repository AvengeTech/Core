<?php

namespace core\gadgets\ui;

use pocketmine\Server;

use core\{
	Core,
	AtPlayer as Player
};
use core\ui\elements\customForm\{
	Label,
	Input,
	Dropdown
};
use core\ui\windows\CustomForm;
use core\utils\TextFormat;

class GadgetGiftUi extends CustomForm {

	public array $players = [];

	public function __construct(Player $player, string $message = "", bool $error = true) {
		parent::__construct("Gadget gifting");

		$this->addElement(new Label(
			($message !== "" ? ($error ? TextFormat::RED : TextFormat::GREEN) . $message . PHP_EOL . PHP_EOL . TextFormat::WHITE : "") .
				"Select a player to gift gadgets to"
		));

		$players = [];
		foreach (Server::getInstance()->getOnlinePlayers() as $pl) {
			if ($pl !== $player) $players[] = $pl->getName();
		}
		$this->players = $players;
		$this->addElement(new Dropdown("Online players", $players));

		$this->addElement(new Label("Select which gadget you'd like to gift!"));

		$gadgets = Core::getInstance()->getGadgets()->getGadgets();
		$options = [];
		foreach ($gadgets as $gadget) {
			$options[] = $gadget->getName() . " (" . number_format($player->getSession()->getGadgets()->getTotal($gadget)) . " available)";
		}
		$this->addElement(new Dropdown("Gadget", $options));
		$this->addElement(new Input("Amount", 1));
	}

	public function close(Player $player) {
		$player->showModal(new GadgetUi($player));
	}

	public function handle($response, Player $player) {
		$pl = Server::getInstance()->getPlayerExact($this->players[$response[1]] ?? "");
		if (!$pl instanceof Player || !$pl->isLoaded()) {
			$player->showModal(new GadgetGiftUi($player, "The player you selected is no longer online!"));
			return;
		}
		$gadget = Core::getInstance()->getGadgets()->getGadgets()[$response[3]];
		$amount = (int) $response[4];

		if ($amount <= 0) {
			$player->showModal(new GadgetGiftUi($player, "Enter an amount higher than 0!"));
			return;
		}
		if ($amount > $player->getSession()->getGadgets()->getTotal($gadget)) {
			$player->showModal(new GadgetGiftUi($player, "You don't have enough of this gadget!"));
			return;
		}

		$pl->getSession()->getGadgets()->addTotal($gadget, $amount);
		$player->getSession()->getGadgets()->takeTotal($gadget, $amount);

		$pl->sendMessage(TextFormat::GI . "You were gifted " . $gadget->getRarityColor() . "x" . number_format($amount) . " " . $gadget->getName() . ($amount > 1 ? "s" : "") . TextFormat::GRAY . " by " . TextFormat::AQUA . $player->getName());
		$player->showModal(new GadgetGiftUi($player, "Successfully gifted x" . number_format($amount) . " " . $gadget->getName() . " to " . $pl->getName() . "!", false));
	}
}
