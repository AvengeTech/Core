<?php

namespace core\staff\uis;

use core\AtPlayer as Player;
use core\ui\windows\SimpleForm;
use core\ui\elements\simpleForm\Button;
use core\staff\uis\actions\{
	ban\BanUi,
	mute\MuteUi,
	warn\WarnUi,
	KickUi,
	SeeinvUi,
	TeleportUi
};

class PanelUi extends SimpleForm {

	public function __construct() {
		parent::__construct("Staff Panel", "Manage staff actions");

		$this->addButton(new Button("Warns Panel"));
		$this->addButton(new Button("Mutes Panel"));
		$this->addButton(new Button("Bans Panel"));
		$this->addButton(new Button("Kick Player"));
		$this->addButton(new Button("Teleport to Player"));
		$this->addButton(new Button("Seeinv"));
	}

	public function handle($response, Player $player) {
		if ($response == 0) {
			$player->showModal(new WarnUi());
			return;
		}
		if ($response == 1) {
			$player->showModal(new MuteUi());
			return;
		}
		if ($response == 2) {
			$player->showModal(new BanUi($player));
			return;
		}
		if ($response == 3) {
			$player->showModal(new KickUi());
			return;
		}
		if ($response == 4) {
			$player->showModal(new TeleportUi($player));
			return;
		}
		if ($response == 5) {
			$player->showModal(new SeeinvUi());
			return;
		}
	}
}
