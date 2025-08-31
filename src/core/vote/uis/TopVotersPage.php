<?php

namespace core\vote\uis;

use core\AtPlayer as Player;
use core\Core;
use core\ui\windows\SimpleForm;
use core\ui\elements\simpleForm\Button;
use core\utils\TextFormat;

class TopVotersPage extends SimpleForm {

	public $site;
	public $page;

	public $hasBack = false;
	public $backButton = -1;
	public $hasNext = false;
	public $nextButton = -1;

	public function __construct(int $site = 1, int $page = 1) {
		$this->site = $site;
		$this->page = $page;

		$ss = Core::getInstance()->getVote()->getVoteSite($site);
		$wa = $ss->winCache[1] ?? [];
		$winners = "";
		foreach ($wa as $winner) {
			$winners .= Core::getInstance()->getChat()->getFormattedRank($winner->getRankName()) . " " . TextFormat::GRAY . "-> " . TextFormat::YELLOW . $winner->getUser()->getGamertag() . "\n";
		}

		$tm = date("n");
		parent::__construct(
			"Top Voters (" . $page . "/" . $ss->getTotalTopCachePages() . ")",
			TextFormat::GRAY . "Winners for the month of " . TextFormat::AQUA . date("F") . ":" . "\n" . $winners . "\n" .

				TextFormat::GRAY . "To see if you won last month, type " . TextFormat::YELLOW . "/winner" . "\n" . "\n" .

				TextFormat::YELLOW . "3" . TextFormat::GRAY . " new winners will be randomly picked from the list below on " . TextFormat::RED . ($tm + 1) . "/1" .
				TextFormat::GRAY . ". Vote everyday of each month at " . TextFormat::YELLOW . "avengetech.net/vote" . TextFormat::GRAY . " for a chance to win one of the prizes below!" . "\n" . "\n" .

				TextFormat::GRAY . "Prizes:" . "\n" .
				"x1 " . TextFormat::BOLD . TextFormat::DARK_RED . "WITHER" . TextFormat::RESET . TextFormat::GRAY . " rank" . "\n" .
				"x1 " . TextFormat::BOLD . TextFormat::DARK_PURPLE . "ENDERMAN" . TextFormat::RESET . TextFormat::GRAY . " rank" . "\n" .
				"x1 " . TextFormat::BOLD . TextFormat::WHITE . "GHAST" . TextFormat::RESET . TextFormat::GRAY . " rank" . "\n" . "\n" .

				TextFormat::RESET . TextFormat::GRAY . "(NOTE: All prizes will be swapped out with a " . TextFormat::YELLOW . "rank upgrade" . TextFormat::GRAY . " if you already have a rank. If you already have the highest rank, you will be given " . TextFormat::YELLOW . "31 days of our Warden " . TextFormat::ICON_WARDEN . " subscription" . TextFormat::GRAY . ")"
		);

		$voters = $ss->getTopCache(0, $page);

		$this->addButton(new Button("Refresh"));
		$buttons = 1;
		foreach ($voters as $name => $votes) {
			$this->addButton(new Button($name . "\n" . "Votes: " . $votes));
			$buttons++;
		}
		if ($page != 1) {
			$this->hasBack = true;
			$this->backButton = $buttons;
			$buttons++;
			$this->addButton(new Button("Last page (" . ($this->page - 1) . "/" . $ss->getTotalTopCachePages() . ")"));
		}
		if ($ss->hasNextTopCachePage(0, $page)) {
			$this->hasNext = true;
			$this->nextButton = $buttons;
			$this->addButton(new Button("Next page (" . ($this->page + 1) . "/" . $ss->getTotalTopCachePages() . ")"));
		}
	}

	public function handle($response, Player $player) {
		if ($response == 0) {
			$player->showModal(new TopVotersPage($this->site, $this->page));
			return;
		}
		if ($response == $this->backButton) {
			$player->showModal(new TopVotersPage($this->site, $this->page - 1));
			return;
		}
		if ($response == $this->nextButton) {
			$player->showModal(new TopVotersPage($this->site, $this->page + 1));
			return;
		}
	}
}
