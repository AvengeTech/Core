<?php

namespace core\staff\uis\actions\ban;

use core\AtPlayer;
use core\rank\Rank;
use core\staff\entry\BanEntry;
use core\ui\elements\customForm\Input;
use core\ui\elements\customForm\Label;
use core\ui\elements\customForm\Toggle;
use core\ui\windows\CustomForm;
use core\user\User;

class ModifyBanUi extends CustomForm {

	public bool $allowed = true;

	/** @param BanEntry[] $bans */
	public function __construct(AtPlayer $player, public BanEntry $entry, public array $bans, public User $user) {
		parent::__construct("Modify Ban");

		if ($player->getRankHierarchy() < Rank::HIERARCHY_SR_MOD) {
			$this->addElement(new Label("You do not have permission to modify bans!"));
			$this->allowed = false;
			return;
		}

		$this->addElement(new Input("Duration (in days)", "3", (string)floor(($this->entry->getUntil() - $this->entry->getWhen()) / 86400)));
		$this->addElement(new Input("Reason", "Cheating", $this->entry->getReason()));
		$this->addElement(new Toggle("Confirm Changes", false));
	}

	public function handle($response, AtPlayer $player) {
		if (!$this->allowed) {
			$player->showModal(new ViewBanUi($player, $this->entry, $this->bans, $this->user));
			return;
		}
		if (is_numeric($response[0])) $duration = floor(floatval($response[0]) * 86400);
		else {
			$player->showModal(new ModifyBanUi($player, $this->entry, $this->bans, $this->user));
			return;
		}
		$reason = $response[1] ?? "";
		if ($reason == "") {
			$player->showModal(new ModifyBanUi($player, $this->entry, $this->bans, $this->user));
			return;
		}
		if (!$response[2]) {
			$player->showModal(new ViewBanUi($player, $this->entry, $this->bans, $this->user));
			return;
		}
		if ($duration > 0) $until = $this->entry->getWhen() + $duration;
		else $until = -1;
		$this->entry->setUntil($until);
		$this->entry->setReason($reason);
		$player->showModal(new ViewBanUi($player, $this->entry, $this->bans, $this->user));
	}
}
