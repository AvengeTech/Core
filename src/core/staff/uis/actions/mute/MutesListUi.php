<?php

namespace core\staff\uis\actions\mute;

use core\AtPlayer;
use core\rank\Rank;
use core\staff\entry\MuteEntry;
use core\ui\elements\simpleForm\Button;
use core\ui\windows\SimpleForm;
use core\user\User;
use core\utils\TextFormat;

class MutesListUi extends SimpleForm {

	public array $visibleMutes = [];

	/** @param MuteEntry[] $mutes */
	public function __construct(AtPlayer $player, public array $mutes, public User $user, string $message = "") {
		if (empty($this->mutes)) {
			$message .= ($message != "" ? PHP_EOL : "") . "No mutes found for " . $this->user->getGamertag() . ".";
		}
		parent::__construct("Mutes for " . $this->user->getGamertag(), TextFormat::RED . $message . TextFormat::RESET);

		foreach ($this->mutes as $mute) {
			if ($mute->isRevoked() && $player->getRankHierarchy() < Rank::HIERARCHY_SR_MOD) continue;
			$this->visibleMutes[] = $mute;
			$this->addButton(new Button(TextFormat::YELLOW . $mute->getReason() . PHP_EOL . ($mute->isRevoked() ? TextFormat::RED . TextFormat::BOLD . "REVOKED" : TextFormat::DARK_GRAY . $mute->getFormattedWhen(true))));
		}
	}

	public function handle($response, AtPlayer $player) {
		$selectedMute = $this->visibleMutes[$response] ?? null;
		if ($selectedMute instanceof MuteEntry) {
			$player->showModal(new ViewMuteUi($player, $selectedMute, $this->mutes, $this->user));
		} else {
			$player->showModal(new MutesListUi($player, $this->mutes, $this->user, "Invalid selection! (Key: $response)"));
		}
	}
}
