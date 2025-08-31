<?php

namespace core\settings\ui;

use core\AtPlayer as Player;
use core\settings\GlobalSettings;
use core\ui\windows\CustomForm;
use core\ui\elements\customForm\{
	Label,
	Toggle
};
use core\utils\TextFormat;

class GlobalSettingsUi extends CustomForm {

	public function __construct(Player $player) {
		parent::__construct("Global settings");

		$settings = $player->getSession()->getSettings()->getSettings();
		$this->addElement(new Label("Free settings"));
		$this->addElement(new Toggle("Open DMs", $settings[GlobalSettings::OPEN_DMS]));
		$this->addElement(new Toggle("Display others cosmetic effects", $settings[GlobalSettings::DISPLAY_COSMETIC_EFFECTS] ?? true));
		$this->addElement(new Toggle("CPS/Ping display when hitting entities", $settings[GlobalSettings::CPS_PING_COUNTER] ?? false));
		$this->addElement(new Toggle("Enable Particles", $settings[GlobalSettings::PARTICLES] ?? true));
		$this->addElement(new Toggle("Enable Enchantment Sounds", $settings[GlobalSettings::ENCHANTMENT_SOUNDS] ?? true));
		$this->addElement(new Toggle("Legacy Rank Icons", $settings[GlobalSettings::LEGACY_RANK_ICONS]));

		$this->addElement(new Label("Premium settings"));
		$this->addElement(new Label("(Each premium setting is marked with the lowest rank it's compatible with)"));
		$this->addElement(new Toggle(TextFormat::ICON_ENDERMITE . " Join message", $settings[GlobalSettings::JOIN_MESSAGE]));
		$this->addElement(new Toggle(TextFormat::ICON_ENDERDRAGON . " Announcement board", $settings[GlobalSettings::ANNOUNCEMENT_BOARD]));

		if ($player->isStaff()) {
			$this->addElement(new Label("Staff settings"));
			$this->addElement(new Toggle(TextFormat::ICON_MOD . " Vanish on join", $settings[GlobalSettings::VANISHED]));
			$this->addElement(new Toggle(TextFormat::ICON_MOD . " Show anticheat messages", $settings[GlobalSettings::ANTICHEAT_MESSAGES]));
			$this->addElement(new Toggle(TextFormat::ICON_MOD . " Command see on join", $settings[GlobalSettings::COMMAND_SEE]));
			$this->addElement(new Toggle(TextFormat::ICON_MOD . " /tell see on join", $settings[GlobalSettings::TELL_SEE]));
			$this->addElement(new Toggle(TextFormat::ICON_MOD . " Enable staffchat on join", $settings[GlobalSettings::STAFFCHAT_JOIN]));
		}
	}

	public function handle($response, Player $player) {
		$session = $player->getSession()->getSettings();

		$session->setSetting(GlobalSettings::OPEN_DMS, $response[1]);
		$session->setSetting(GlobalSettings::DISPLAY_COSMETIC_EFFECTS, $response[2]);
		$session->setSetting(GlobalSettings::CPS_PING_COUNTER, $response[3]);
		$session->setSetting(GlobalSettings::PARTICLES, $response[4]);
		$session->setSetting(GlobalSettings::ENCHANTMENT_SOUNDS, $response[5]);
		$session->setSetting(GlobalSettings::LEGACY_RANK_ICONS, $response[6]);

		if ($player->hasRank()) $session->setSetting(GlobalSettings::JOIN_MESSAGE, $response[9]);
		if ($player->rankAtLeast("enderdragon")) $session->setSetting(GlobalSettings::ANNOUNCEMENT_BOARD, $response[10]);

		if ($player->isStaff()) {
			$session->setSetting(GlobalSettings::VANISHED, $response[12]);
			$session->setSetting(GlobalSettings::ANTICHEAT_MESSAGES, $response[13]);
			$session->setSetting(GlobalSettings::COMMAND_SEE, $response[14]);
			$session->setSetting(GlobalSettings::TELL_SEE, $response[15]);
			$session->setSetting(GlobalSettings::STAFFCHAT_JOIN, $response[16]);
		}

		$player->showModal(new SettingsUi(TextFormat::EMOJI_CHECKMARK . TextFormat::GREEN . " Global settings have been updated!"));
	}
}
