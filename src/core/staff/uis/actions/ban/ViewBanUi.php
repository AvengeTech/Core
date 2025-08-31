<?php

namespace core\staff\uis\actions\ban;

use core\AtPlayer;
use core\rank\Rank;
use core\staff\entry\BanEntry;
use core\ui\elements\simpleForm\Button;
use core\ui\windows\SimpleForm;
use core\user\User;
use pocketmine\promise\Promise;

class ViewBanUi extends SimpleForm {

	public int $offset = 0;

	/** @param BanEntry[] $bans */
	public function __construct(AtPlayer $player, public BanEntry $entry, public array $bans, public User $user) {
		$byUser = $this->entry->getByUser();
		if ($byUser instanceof Promise) $byUser = new User(0, "Loading...");
		parent::__construct(
			"Ban Details",
			"Moderator: " . $byUser->getGamertag() . PHP_EOL .
				PHP_EOL .
				"Duration: " . ($this->entry->getUntil() > 0 ? floor(($this->entry->getUntil() - $this->entry->getWhen()) / 86400) . " days" : "ETERNITY") . PHP_EOL .
				PHP_EOL .
				"Status: " . ($this->entry->isRevoked() ? "Revoked" : ($this->entry->isBanned() ? "Active" : "Expired")) . PHP_EOL . PHP_EOL .
				"Reason: " . $this->entry->getReason() . PHP_EOL
		);

		if ($player->getRankHierarchy() >= Rank::HIERARCHY_SR_MOD && !$this->entry->isRevoked()) {
			$this->addButton(new Button("Modify Ban"));
			$this->offset = 1;
		}
		if (!$this->entry->isRevoked() && $player->getRankHierarchy() >= Rank::HIERARCHY_MOD) $this->addButton(new Button("Revoke"));
		$this->addButton(new Button("Back"));
	}

	public function handle($response, AtPlayer $player) {
		if ($response == -1 + $this->offset) return $player->showModal(new ModifyBanUi($player, $this->entry, $this->bans, $this->user));
		elseif ($response == 0 + $this->offset) {
			if ($this->entry->isRevoked()) return $player->showModal(new BansListUi($player, $this->bans, $this->user));
			elseif ($player->getRankHierarchy() >= Rank::HIERARCHY_MOD) return $player->showModal(new ConfirmRevokeUi($this->entry, $this->bans, $this->user));
		}
		$player->showModal(new BansListUi($player, $this->bans, $this->user));
	}
}
