<?php

namespace core\staff\uis\actions\ban;

use core\AtPlayer;
use core\rank\Rank;
use core\ui\elements\simpleForm\Button;
use core\ui\windows\SimpleForm;
use core\utils\TextFormat;

class BanUi extends SimpleForm {

	public function __construct(AtPlayer $player)
	{
		parent::__construct(
			"Ban Menu",
			"What would you like to do?"
		);

		$this->addButton(new Button("Ban Player"));
		$this->addButton(new Button("View Player Bans"));
		if ($player->getRankHierarchy() >= Rank::HIERARCHY_MOD) {
			$this->addButton(new Button("Ban Device"));
			$this->addButton(new Button("View Device Bans"));
		}
		if ($player->getRankHierarchy() >= Rank::HIERARCHY_SR_MOD) {
			$this->addButton(new Button("Ban IP"));
			$this->addButton(new Button("View IP Bans"));
		}
	}

	public function handle($response, AtPlayer $player)
	{
		switch ($response) {
			case 0:
				$player->showModal(new AddPlayerBanUi());
				break;
			case 1:
				$player->showModal(new ViewPlayerBansUi());
				break;
			default:
				$player->sendMessage(TextFormat::RI . "Panel option coming soon!");
				break;
		}
	}

}