<?php

namespace core\staff\uis\actions\warn;

use core\AtPlayer;
use core\rank\Rank;
use core\staff\entry\WarnEntry;
use core\ui\elements\customForm\Input;
use core\ui\elements\customForm\Label;
use core\ui\elements\customForm\Toggle;
use core\ui\windows\CustomForm;
use core\user\User;

class ModifyWarnUi extends CustomForm {

	public bool $allowed = true;

	/** @param WarnEntry[] $warns */
	public function __construct(AtPlayer $player, public WarnEntry $entry, public array $warns, public User $user) {
		parent::__construct("Modify Mute");

		if ($player->getRankHierarchy() < Rank::HIERARCHY_SR_MOD) {
			$this->addElement(new Label("You do not have permission to modify mutes!"));
			$this->allowed = false;
			return;
		}

		$this->addElement(new Input("Reason", "Warnings", $this->entry->getReason()));
		$this->addElement(new Toggle("Severe", $this->entry->isSevere()));
		$this->addElement(new Toggle("Confirm Changes", false));
	}

	public function handle($response, AtPlayer $player) {
		if (!$this->allowed) {
			$player->showModal(new ViewWarnUi($player, $this->entry, $this->warns, $this->user));
			return;
		}
		$reason = $response[0] ?? "";
		$severe = $response[1] ?? $this->entry->isSevere();
		if ($reason == "") {
			$player->showModal(new ModifyWarnUi($player, $this->entry, $this->warns, $this->user));
			return;
		}
		if (!$response[2]) {
			$player->showModal(new ViewWarnUi($player, $this->entry, $this->warns, $this->user));
			return;
		}
		$this->entry->setReason($reason);
		$this->entry->setSevere($severe);
		$player->showModal(new ViewWarnUi($player, $this->entry, $this->warns, $this->user));
	}
}
