<?php

namespace core\lootboxes\ui;

use pocketmine\Server;

use core\AtPlayer as Player;
use core\lootboxes\entity\LootBox;
use core\ui\elements\customForm\{
	Label,
	Input,
	Dropdown
};
use core\ui\windows\CustomForm;
use core\utils\TextFormat;

class GiftLootBoxesUi extends CustomForm {

	public array $players = [];

	public function __construct(Player $player, public ?LootBox $lootBox = null, string $message = "", bool $error = true) {
		parent::__construct("Loot box gifting");

		$this->addElement(new Label(
			($message !== "" ? ($error ? TextFormat::RED : TextFormat::GREEN) . $message . PHP_EOL . PHP_EOL . TextFormat::WHITE : "") .
				"Select a player to gift lootboxes to"
		));

		$players = [];
		foreach (Server::getInstance()->getOnlinePlayers() as $pl) {
			if ($pl !== $player) $players[] = $pl->getName();
		}
		$this->players = $players;
		$this->addElement(new Dropdown("Online players", $players));

		$this->addElement(new Label("Select how many loot boxes you'd like to gift! You have " . $player->getSession()->getLootBoxes()->getLootBoxes() . " available"));

		$this->addElement(new Input("Amount", 1));
	}

	public function close(Player $player) {
		if ($this->lootBox !== null) $player->showModal(new LootBoxUi($player, $this->lootBox));
	}

	public function handle($response, Player $player) {
		$pl = Server::getInstance()->getPlayerExact($this->players[$response[1]] ?? "");
		if (!$pl instanceof Player || !$pl->isLoaded()) {
			$player->showModal(new GiftLootBoxesUi($player, $this->lootBox, "The player you selected is no longer online!"));
			return;
		}
		$amount = (int) $response[3];

		if ($amount <= 0) {
			$player->showModal(new GiftLootBoxesUi($player, $this->lootBox, "Enter an amount higher than 0!"));
			return;
		}
		if ($amount > $player->getSession()->getLootBoxes()->getLootBoxes()) {
			$player->showModal(new GiftLootBoxesUi($player, $this->lootBox, "You don't have enough loot boxes!"));
			return;
		}

		$pl->getSession()->getLootBoxes()->addLootBoxes($amount);
		$player->getSession()->getLootBoxes()->takeLootBoxes($amount);

		$pl->sendMessage(TextFormat::GI . "You were gifted " . TextFormat::LIGHT_PURPLE . "x" . $amount . " Loot Boxes");
		$player->showModal(new GiftLootBoxesUi($player, $this->lootBox, "Successfully gifted x" . $amount . " loot boxes to " . $pl->getName() . "!", false));
	}
}
