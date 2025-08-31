<?php

namespace core\staff\uis\actions\reports;

use core\AtPlayer as Player;
use core\ui\windows\CustomForm;

class OpenReportUi extends CustomForm {

	public function __construct(Player $player) {
		parent::__construct("Open Report");
	}

	public function handle($response, Player $player) {
	}
}
