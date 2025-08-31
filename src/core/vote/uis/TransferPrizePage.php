<?php

namespace core\vote\uis;

use core\AtPlayer as Player;
use core\ui\windows\CustomForm;
use core\ui\elements\customForm\{
	Label,
	Input,
	Toggle
};

use core\Core;
use core\user\User;
use core\utils\TextFormat;
use core\vote\utils\WinnerEntry;

class TransferPrizePage extends CustomForm {

	public $entry;

	public function __construct(Player $player, WinnerEntry $entry, string $error = "") {
		parent::__construct("Transfer Vote Prize");

		$this->entry = $entry;

		$this->addElement(new Label(($error != "" ? TextFormat::RED . $error . "\n" . "\n" : "") . TextFormat::GRAY . "Enter the username of the player you are transferring your vote prize to."));
		$this->addElement(new Input("Username", "E.g. sn3akrr"));
		$this->addElement(new Toggle("By checking this box, you agree that the username above is correct, and that you are okay with transferring your vote prize to them."));
		$this->addElement(new Label(TextFormat::GRAY . "After checking the box above, press 'Submit' to confirm your prize transfer  (" . TextFormat::YELLOW . "NOTE: If this player has a rank, the prize will be converted to a rank upgrade! Otherwise they will receive your original prize" . TextFormat::GRAY . ")"));
	}

	public function handle($response, Player $player) {
		$entry = $this->entry;
		$username = $response[1];
		$agree = $response[2];

		if (!$agree) {
			$player->showModal(new TransferPrizePage($player, $entry, "Please check the option below."));
			return;
		}
		Core::getInstance()->getUserPool()->useUser($username, function (User $user) use ($player, $entry): void {
			if (!$player->isConnected()) return;
			if (!$user->validXuid()) {
				$player->showModal(new TransferPrizePage($player, $entry, "An invalid username was provided. Please try again!"));
				return;
			}
			$entry->verify(function (User $newUser, bool $claimed) use ($player, $entry, $user): void {
				if (!$player->isConnected()) return;
				if (!$newUser->belongsTo($player)) {
					$player->sendMessage(TextFormat::RI . "This vote prize has already been transferred.");
					return;
				}
				if ($claimed) {
					$player->sendMessage(TextFormat::RI . "This vote prize has already been claimed.");
					return;
				}
				$entry->transfer($user);
				$player->sendMessage(TextFormat::GI . "Your vote prize has been successfully transferred to " . TextFormat::YELLOW . $user->getGamertag());
			});
		});
	}
}
