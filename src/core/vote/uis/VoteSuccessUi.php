<?php

namespace core\vote\uis;

use core\AtPlayer as Player;
use core\Core;
use core\vote\Structure;
use core\vote\prize\PrizeCluster;
use core\ui\windows\SimpleForm;
use core\ui\elements\simpleForm\Button;
use core\utils\TextFormat;

class VoteSuccessUi extends SimpleForm {

	public function __construct(Player $player, PrizeCluster $prize) {
		$session = $player->getSession()->getVote();

		$extra = "";
		switch (Structure::getDayType()) {
			case Structure::DAY_WEEKEND:
				$extra = TextFormat::YELLOW . "(It's double vote weekend! Daily vote reward is DOUBLED!)" . PHP_EOL . PHP_EOL;
				break;
			case Structure::DAY_FIRST:
				$extra = TextFormat::YELLOW . "(It's the first of the month! Daily vote reward is QUADRUPLED!!)" . PHP_EOL . PHP_EOL;
				break;
			case Structure::DAY_HOLIDAY:
				$h = Structure::getHoliday();
				$extra = TextFormat::YELLOW . "(It's " . $h . ", so the daily vote reward is TRIPLED!!)" . PHP_EOL . PHP_EOL;
				break;
			case Structure::DAY_NORMAL:
				break;
		}

		$daily = Core::getInstance()->getVote()->getPrizeVendor()->getDailyPrize();
		parent::__construct(
			"Vote Success",
			$extra . TextFormat::WHITE .
				"Thanks for voting! You received the following daily vote rewards:" . PHP_EOL . PHP_EOL .
				$daily->getItemList() . PHP_EOL .
				"Along with the following vote streak rewards:" . PHP_EOL . PHP_EOL .
				$prize->getItemList() . PHP_EOL .
				"Vote again tomorrow to get different prizes!"
		);

		$this->addButton(new Button("Go back"));
	}

	public function handle($response, Player $player) {
		$player->showModal(new VoteRewardsUi($player));
	}
}
