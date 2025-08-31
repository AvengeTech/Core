<?php

namespace core\vote\uis;

use core\AtPlayer as Player;
use core\Core;
use core\vote\Structure;
use core\vote\prize\PrizeCluster;

use core\ui\windows\SimpleForm;
use core\ui\elements\simpleForm\Button;

use core\utils\TextFormat;

class ViewPrizeUi extends SimpleForm {

	public function __construct(Player $player, PrizeCluster $prize) {
		$vote = Core::getInstance()->getVote();
		$session = $player->getSession()->getVote();
		if ($prize->isDaily()) {
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

			parent::__construct(
				"Daily Vote Reward",
				$extra .
					"This is the daily vote reward! Everytime you vote, you will receive this prize along with the vote streak rewards.." . PHP_EOL . PHP_EOL .
					$prize->getItemList()
			);
		} elseif ($prize->isLastDay()) {
			parent::__construct(
				"Last Day Vote Reward",
				"This is the last day vote reward! If you vote everyday this month, you will receive the following items:" . PHP_EOL . PHP_EOL .
					$prize->getItemList()
			);
		} else {
			parent::__construct(
				"Day " . ($day = $prize->getDay()) . " Vote Reward",
				"This is the day " . $day . " vote reward! If you vote for " . $day . " days straight this month, you will receive the following items:" . PHP_EOL . PHP_EOL .
					$prize->getItemList()
			);
		}

		$this->addButton(new Button("Go back"));
	}

	public function handle($response, Player $player) {
		$player->showModal(new VoteRewardsUi($player));
	}
}
