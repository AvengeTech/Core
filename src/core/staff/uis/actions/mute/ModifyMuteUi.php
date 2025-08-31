<?php

namespace core\staff\uis\actions\mute;

use core\AtPlayer;
use core\rank\Rank;
use core\staff\entry\MuteEntry;
use core\ui\elements\customForm\Input;
use core\ui\elements\customForm\Label;
use core\ui\elements\customForm\Toggle;
use core\ui\windows\CustomForm;
use core\user\User;

class ModifyMuteUi extends CustomForm {

	public bool $allowed = true;

	/** @param MuteEntry[] $mutes */
	public function __construct(AtPlayer $player, public MuteEntry $entry, public array $mutes, public User $user) {
		parent::__construct("Modify Mute");

		if ($player->getRankHierarchy() < Rank::HIERARCHY_SR_MOD) {
			$this->addElement(new Label("You do not have permission to modify mutes!"));
			$this->allowed = false;
			return;
		}

		$this->addElement(new Input("Duration (in days)", "3", (string)floor(($this->entry->getUntil() - $this->entry->getWhen()) / 86400)));
		$this->addElement(new Input("Reason", "Warnings", $this->entry->getReason()));
		$this->addElement(new Toggle("Confirm Changes", false));
	}

	public function handle($response, AtPlayer $player) {
		if (!$this->allowed) {
			$player->showModal(new ViewMuteUi($player, $this->entry, $this->mutes, $this->user));
			return;
		}
		if (is_numeric($response[0])) $duration = floor(floatval($response[0]) * 86400);
		else {
			$player->showModal(new ModifyMuteUi($player, $this->entry, $this->mutes, $this->user));
			return;
		}
		$reason = $response[1] ?? "";
		if ($reason == "") {
			$player->showModal(new ModifyMuteUi($player, $this->entry, $this->mutes, $this->user));
			return;
		}
		if (!$response[2]) {
			$player->showModal(new ViewMuteUi($player, $this->entry, $this->mutes, $this->user));
			return;
		}
		if ($duration > 0) $until = $this->entry->getWhen() + $duration;
		else $until = -1;
		$this->entry->setUntil($until);
		$this->entry->setReason($reason);
		$player->showModal(new ViewMuteUi($player, $this->entry, $this->mutes, $this->user));
	}
}
