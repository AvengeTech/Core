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

class ShardGifterUi extends CustomForm {

	public array $players = [];

	public function __construct(Player $player, public LootBox $lootBox, string $message = "", bool $error = true) {
		parent::__construct("Shard gifting");

		$this->addElement(new Label(
			($message !== "" ? ($error ? TextFormat::RED : TextFormat::GREEN) . $message . PHP_EOL . PHP_EOL . TextFormat::WHITE : "") .
				"Select a player to gift shards to"
		));

		$players = [];
		foreach (Server::getInstance()->getOnlinePlayers() as $pl) {
			if ($pl !== $player) $players[] = $pl->getName();
		}
		$this->players = $players;
		$this->addElement(new Dropdown("Online players", $players));

		$this->addElement(new Label("Select how many shards you'd like to gift! You have " . TextFormat::AQUA . number_format($player->getSession()->getLootBoxes()->getShards()) . TextFormat::WHITE . " available"));

		$this->addElement(new Input("Amount", 1));
	}

	public function close(Player $player) {
		$player->showModal(new ShardShaperUi($player, $this->lootBox));
	}

	public function handle($response, Player $player) {
		$pl = Server::getInstance()->getPlayerExact($this->players[$response[1]] ?? "");
		if (!$pl instanceof Player || !$pl->isLoaded()) {
			$player->showModal(new ShardGifterUi($player, $this->lootBox, "The player you selected is no longer online!"));
			return;
		}
		$amount = (int) $response[3];

		if ($amount <= 0) {
			$player->showModal(new ShardGifterUi($player, $this->lootBox, "Enter an amount higher than 0!"));
			return;
		}
		if ($amount > $player->getSession()->getLootBoxes()->getShards()) {
			$player->showModal(new ShardGifterUi($player, $this->lootBox, "You don't have enough shards!"));
			return;
		}

		$pl->getSession()->getLootBoxes()->addShards($amount);
		$player->getSession()->getLootBoxes()->takeShards($amount);

		$player->showModal(new ShardGifterUi($player, $this->lootBox, "Successfully gifted x" . $amount . " shards to " . $pl->getName() . "!", false));
	}
}
