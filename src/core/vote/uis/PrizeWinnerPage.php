<?php

namespace core\vote\uis;


use core\ui\windows\SimpleForm;
use core\ui\elements\simpleForm\Button;

use core\Core;
use core\AtPlayer as Player;
use core\user\User;
use core\utils\TextFormat;
use core\vote\utils\WinnerEntry;

class PrizeWinnerPage extends SimpleForm {

	public $entry;

	public function __construct(Player $player, WinnerEntry $entry) {
		$this->entry = $entry;

		$chat = Core::getInstance()->getChat();

		$prizename = (
			(!$player->isStaff() && (($rank = $player->getRank()) == "default" || !isset(WinnerEntry::RANK_ORDER[$rank]))) ?
			TextFormat::GRAY . "Rank up to " . $chat->getFormattedRank($entry->getNewRank($player)) . TextFormat::GRAY : ($player->getRankHierarchy() >= $player->getRankHierarchy("enderdragon") ?
			TextFormat::YELLOW . "1 month of " . TextFormat::ICON_WARDEN . " Warden subscription" :
				$entry->getNewRank($player)
			) . TextFormat::GRAY . " rank"
		) . TextFormat::RESET;

		parent::__construct(
			"Vote Prize Winner!",
			TextFormat::GRAY . "Congratulations, " . TextFormat::YELLOW . $player->getName() . TextFormat::GRAY . "!" . "\n" . "\n" .
				"Your daily voting has paid off, and you won a prize:" . "\n" .
				" - " . $prizename . "\n" . "\n" .
				TextFormat::GRAY . "Tap one of the following options below to redeem your prize!"
		);

		$this->addButton(new Button("Verify and Redeem"));
		$this->addButton(new Button("Transfer Prize"));
	}

	public function handle($response, Player $player) {
		$entry = $this->entry;
		if ($response == 0) {
			$entry->verify(function (User $user, bool $claimed) use ($player, $entry): void {
				if (!$player->isConnected()) return;
				if (!$user->belongsTo($player)) {
					$player->sendMessage(TextFormat::RI . "This vote prize has already been transferred.");
					return;
				}
				if ($claimed) {
					$player->sendMessage(TextFormat::RI . "This vote prize has already been claimed.");
					return;
				}
				$entry->claim($player);
				$player->sendMessage(TextFormat::GI . "Your vote prize has successfully been redeemed!");
			});
			return;
		}
		if ($response == 1) {
			$entry->verify(function (User $user, bool $claimed) use ($player, $entry): void {
				if (!$player->isConnected()) return;
				if (!$user->belongsTo($player)) {
					$player->sendMessage(TextFormat::RI . "This vote prize has already been transferred.");
					return;
				}
				if ($claimed) {
					$player->sendMessage(TextFormat::RI . "This vote prize has already been claimed.");
					return;
				}
				$player->showModal(new TransferPrizePage($player, $entry));
			});
		}
	}
}
