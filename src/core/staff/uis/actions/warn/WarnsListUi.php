<?php

namespace core\staff\uis\actions\warn;

use core\AtPlayer as Player;
use core\rank\Rank;
use core\staff\entry\WarnEntry;
use core\staff\entry\WarnManager;
use core\ui\windows\SimpleForm;
use core\ui\elements\simpleForm\Button;
use core\user\User;
use core\utils\TextFormat;

class WarnsListUi extends SimpleForm {

	/** @var WarnEntry[] */
	public array $visibleWarns = [];

	/** @param WarnEntry[] $warns */
	public function __construct(Player $player, public array $warns = [], public User $user) {
		parent::__construct(
			$user->getGamertag() . "'s warnings",
			"This player has a total of " . TextFormat::RED . count($this->warns) . TextFormat::WHITE . " warnings. Select one from the list below to view more details!"
		);

		foreach ($this->warns as $warn) {
			if ($warn->isRevoked() && $player->getRankHierarchy() < Rank::HIERARCHY_SR_MOD) continue;
			$this->visibleWarns[] = $warn;
			$this->addButton(new Button(TextFormat::YELLOW . $warn->getReason() . PHP_EOL . TextFormat::DARK_GRAY . $warn->getFormattedWhen(true)));
		}
		$this->addButton(new Button("Go back"));
	}

	public function handle($response, Player $player) {
		$selectedWarn = $this->visibleWarns[$response] ?? null;
		if ($selectedWarn instanceof WarnEntry) {
			$player->showModal(new ViewWarnUi($player, $selectedWarn, $this->warns, $this->user));
			return;
		}
		$player->showModal(new WarnUi());
	}
}
