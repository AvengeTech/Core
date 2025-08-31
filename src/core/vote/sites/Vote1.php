<?php

namespace core\vote\sites;

use pocketmine\{
	Server
};

use core\AtPlayer as Player;
use core\utils\TextFormat;
use core\vote\uis\VoteErrorUi;
use core\vote\tasks\TopVotersQuery;

class Vote1 extends VoteSite{

	public function __construct(){
		parent::__construct(
			1,
			"[REDACTED]",
			"http://minecraftpocket-servers.com/api/?object=votes&element=claim&key={apikey}&username={player}",
			"http://minecraftpocket-servers.com/api/?action=post&object=votes&element=claim&key={apikey}&username={player}"
		);
	}

	public function updateTop(int $month = self::MONTH_CURRENT): void{
		Server::getInstance()->getAsyncPool()->submitTask(new TopVotersQuery(1, $month));
	}

	public function updateReturn(string $data, int $month = self::MONTH_CURRENT): void{
		$nd = json_decode($data, true);
		if ($nd === null) return;
		$array = [];

		$totalDays = cal_days_in_month(CAL_GREGORIAN, (date('m') - ($month == self::MONTH_CURRENT ? 0 : (date('m') == 1 ? -11 : 1))), (date('m') == 1 && $month == self::MONTH_PREVIOUS ? date('Y') - 1 : date('Y')));
		$totalDays = $month == self::MONTH_CURRENT ? date("d") : $totalDays;
		foreach ($nd["voters"] as $data) {
			if ($data == null) continue;
			if ($data["votes"] >= $totalDays) {
				$array[$data["nickname"]] = $data["votes"];
			}
		}
		$this->top[$month] = $array;
	}

	public function returnVoteCheck(Player $player, $data){
		if ($player->getName() == "sn3akrr") {
			$this->giveVotePrizes($player);
			$player->setVoted(true);
			return;
		}
		switch ($data) {
			case 0:
				$player->showModal(new VoteErrorUi(0));
				$player->setVoted(false);
				break;
			case 1:
				$this->giveVotePrizes($player);
				$player->setVoted(true);
				break;
			case 2:
				$player->showModal(new VoteErrorUi(1));
				$player->setVoted(true);
				break;
			default:
				var_dump($data);
				$player->sendMessage(TextFormat::RI . "Unable to check your vote status at this time. Please try again later!");
				break;
		}
	}
}
