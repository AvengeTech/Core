<?php

namespace core\settings\ui;

use core\{
	Core,
	AtPlayer as Player
};
use core\ui\elements\simpleForm\Button;
use core\ui\windows\SimpleForm;
use core\utils\TextFormat;

use lobby\settings\ui\LobbySettingsUi;
use prison\settings\ui\PrisonSettingsUi;

use skyblock\SkyBlock;
use skyblock\SkyBlockPlayer;
use skyblock\settings\ui\SkyBlockSettingsUi;

use pvp\settings\ui\PvPSettingsUi;

class SettingsUi extends SimpleForm {

	public function __construct(string $message = "") {
		parent::__construct("Settings", ($message !== "" ? $message . TextFormat::RESET . TextFormat::WHITE . PHP_EOL . PHP_EOL : "") . "Which type of settings are you trying to access?");
		$this->addButton(new Button("Global settings"));
		$this->addButton(new Button(Core::getInstance()->getNetwork()->getThisServer()->getTypeCase() . " settings"));
	}

	public function handle($response, Player $player) {
		if ($response == 0) {
			$player->showModal(new GlobalSettingsUi($player));
			return;
		}
		switch (Core::getInstance()->getNetwork()->getThisServer()->getType()) {
			case "lobby":
				$player->showModal(new LobbySettingsUi($player));
				break;
			case "prison":
				$player->showModal(new PrisonSettingsUi($player));
				break;
			case "skyblock":
				/** @var SkyBlockPlayer $player */
				$worlds = [];
				$session = $player->getGameSession()->getIslands();
				foreach ($session->getPermissions() as $permission) {
					$worlds[] = $permission->getIslandWorld();
				}
				SkyBlock::getInstance()->getIslands()->getIslandManager()->loadIslands($worlds, function (array $islands) use ($player): void {
					if (!$player->isConnected()) return;
					$player->showModal(new SkyBlockSettingsUi($player, $islands));
				});
				break;
			case "pvp":
				$player->showModal(new SettingsUi(TextFormat::EMOJI_X . TextFormat::RED . " No PvP settings yet"));
				#$player->showModal(new PvPSettingsUi($player));
				break;
		}
	}
}
