<?php

namespace core\staff\uis\actions\ban;

use core\AtPlayer;
use core\rank\Rank;
use core\staff\entry\BanEntry;
use core\ui\elements\simpleForm\Button;
use core\ui\windows\SimpleForm;
use core\user\User;
use core\utils\TextFormat;

class BansListUi extends SimpleForm {

	public array $visibleBans = [];

	/** @param BanEntry[] $bans */
	public function __construct(AtPlayer $player, public array $bans, public User $user, string $message = "") {
		if (empty($this->bans)) {
			$message .= ($message != "" ? PHP_EOL : "") . "No bans found for " . $this->user->getGamertag() . ".";
		}
		parent::__construct("Bans for " . $this->user->getGamertag(), TextFormat::RED . $message . TextFormat::RESET);

		foreach ($this->bans as $ban) {
			if ($ban->isRevoked() && $player->getRankHierarchy() < Rank::HIERARCHY_SR_MOD) continue;
			$this->visibleBans[] = $ban;
			$this->addButton(new Button(TextFormat::YELLOW . $ban->getReason() . PHP_EOL . ($ban->isRevoked() ? TextFormat::RED . TextFormat::BOLD . "REVOKED" : TextFormat::DARK_GRAY . $ban->getFormattedWhen(true))));
		}
	}

	public function handle($response, AtPlayer $player) {
		$selectedBan = $this->visibleBans[$response] ?? null;
		if ($selectedBan instanceof BanEntry) {
			match ($selectedBan->getType()) {
				BanEntry::TYPE_REGULAR => $player->showModal(new ViewBanUi($player, $selectedBan, $this->bans, $this->user)),
				default => $player->showModal(new ViewBanUi($player, $selectedBan, $this->bans, $this->user))
			};
		} else {
			$player->showModal(new BansListUi($player, $this->bans, $this->user, "Invalid selection! (Key: $response)"));
		}
	}
}
