<?php

namespace core\vote\uis;

use core\AtPlayer as Player;
use core\Core;
use core\vote\Structure;
use core\ui\windows\SimpleForm;
use core\ui\elements\simpleForm\Button;
use core\utils\TextFormat;

class VoteRewardsUi extends SimpleForm {

	public function __construct(Player $player) {
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

		$session = $player->getSession()->getVote();

		$this->addButton(new Button(TextFormat::RED . "Tap to claim rewards!" . PHP_EOL . TextFormat::YELLOW . "avengetech.net/vote"));
		$this->addButton(new Button(TextFormat::YELLOW . "Daily Reward"));

		$ms = $session->getMonthlyStreak();
		for ($i = 1; $i < $session->getLastDayOfMonth(); $i++) {
			$this->addButton(new Button(
				"Day " . $i . " Reward" . PHP_EOL . TextFormat::BOLD .
					($i <= $ms ? TextFormat::GREEN . "CLAIMED" : TextFormat::RED . "NOT CLAIMED")
			));
		}
		$this->addButton(new Button(TextFormat::YELLOW . TextFormat::OBFUSCATED . "|||" . TextFormat::RESET . TextFormat::AQUA . " Last Day Reward " . TextFormat::YELLOW . TextFormat::OBFUSCATED . "|||"));

		parent::__construct(
			"Vote Rewards",
			$extra . TextFormat::WHITE . "Increase your vote streak by voting multiple days in a row! The " . TextFormat::YELLOW . "higher" . TextFormat::WHITE . " your monthly streak is, the " . TextFormat::AQUA . "BETTER" . TextFormat::WHITE . " your vote prizes become!" . PHP_EOL . PHP_EOL .
				"(NOTE: Make sure you vote before " . TextFormat::AQUA . "12:00am EST" . TextFormat::WHITE . " every day to maintain your streak!)" . PHP_EOL . PHP_EOL .

				"Monthly Vote Streak: " . TextFormat::YELLOW . $ms . TextFormat::WHITE . PHP_EOL .
				"Total Vote Streak: " . TextFormat::YELLOW . $session->getTotalStreak() . TextFormat::WHITE . PHP_EOL .
				"Highest Streak: " . TextFormat::YELLOW . $session->getHighestStreak() . TextFormat::WHITE . PHP_EOL . PHP_EOL .

				"Tap a button below to get started!"
		);
	}

	public function handle($response, Player $player) {
		$vote = Core::getInstance()->getVote();
		if ($response == 0) {
			$site = $vote->getVoteSite(($response + 1));
			if ($site->hasVoteCheck($player)) {
				$player->sendMessage(TextFormat::RI . "You already have a vote request being sent, please wait...");
				return;
			}
			if ($vote->hasVotedToday($player)) {
				$player->showModal(new VoteErrorUi(1));
				return;
			}
			$site->sendVoteCheck($player);
			return;
		}
		if ($response == 1) {
			$prize = Core::getInstance()->getVote()->getPrizeVendor()->getDailyPrize();
			$player->showModal(new ViewPrizeUi($player, $prize));
			return;
		}
		$session = $player->getSession()->getVote();

		if ($response == $session->getLastDayOfMonth() + 1) {
			$prize = Core::getInstance()->getVote()->getPrizeVendor()->getLastDayPrize();
		} else {
			$prize = Core::getInstance()->getVote()->getPrizeVendor()->getPrizeFor($response - 1);
		}
		$player->showModal(new ViewPrizeUi($player, $prize));
	}
}
