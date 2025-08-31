<?php

namespace core\staff\uis\actions\warn;

use core\AtPlayer as Player;
use core\rank\Rank;
use core\staff\entry\{
	WarnManager,
	WarnEntry
};
use core\ui\windows\SimpleForm;
use core\ui\elements\simpleForm\Button;
use core\user\User;
use core\utils\TextFormat;
use pocketmine\promise\Promise;

class ViewWarnUi extends SimpleForm {

	public int $offset = 0;

	public function __construct(Player $player, public WarnEntry $entry, public array $warns = [], public User $user) {
		$byUser = $this->entry->getByUser();
		if ($byUser instanceof Promise) $byUser = new User(0, "Loading...");
		parent::__construct(
			"Warn Details",
			"Moderator: " . $byUser->getGamertag() . PHP_EOL .
				PHP_EOL .
				"Status: " . ($this->entry->isRevoked() ? "Revoked" : "Active") . PHP_EOL .
				"Severe: " . ($this->entry->isSevere() ? "Yes" : "No") . PHP_EOL .
				PHP_EOL .
				"Type: " . $this->entry->getFormattedType() . PHP_EOL .
				"Reason: " . $this->entry->getReason() . PHP_EOL
		);
		if ($player->getRankHierarchy() >= Rank::HIERARCHY_SR_MOD && !$this->entry->isRevoked()) {
			$this->addButton(new Button("Modify Mute"));
			$this->offset = 1;
		}
		if (!$this->entry->isRevoked() && $player->getRankHierarchy() >= Rank::HIERARCHY_MOD) $this->addButton(new Button("Revoke"));
		$this->addButton(new Button("Back"));
	}

	public function handle($response, Player $player) {
		if ($response == -1 + $this->offset) return $player->showModal(new ModifyWarnUi($player, $this->entry, $this->warns, $this->user));
		elseif ($response == 0 + $this->offset) {
			if ($this->entry->isRevoked()) return $player->showModal(new WarnsListUi($player, $this->warns, $this->user));
			elseif ($player->getRankHierarchy() >= Rank::HIERARCHY_MOD) return $player->showModal(new ConfirmRevokeUi($this->entry, $this->warns, $this->user));
		}
		$player->showModal(new WarnsListUi($player, $this->warns, $this->user));
	}
}
