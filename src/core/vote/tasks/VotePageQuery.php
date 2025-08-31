<?php

namespace core\vote\tasks;

use pocketmine\Server;
use pocketmine\scheduler\AsyncTask;

use core\Core;
use core\AtPlayer as Player;

class VotePageQuery extends AsyncTask {

	public function __construct(
		public string $url,
		public string $player,
		public bool $return,
		public int $voteid
	) {
	}

	public function onRun(): void {
		$query = curl_init($this->url);
		curl_setopt($query, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($query, CURLOPT_SSL_VERIFYHOST, 2);
		curl_setopt($query, CURLOPT_FORBID_REUSE, 1);
		curl_setopt($query, CURLOPT_FRESH_CONNECT, 1);
		curl_setopt($query, CURLOPT_AUTOREFERER, true);
		curl_setopt($query, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($query, CURLOPT_HTTPHEADER, array("User-Agent: AvengeTech"));
		curl_setopt($query, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($query, CURLOPT_TIMEOUT, 5);
		$return = curl_exec($query);
		curl_close($query);
		$this->setResult($return);
	}

	public function onCompletion(): void {
		$player = Server::getInstance()->getPlayerExact($this->player);

		$site = Core::getInstance()->getVote()->getVoteSite($this->voteid);
		if ($player instanceof Player && $this->return) {
			$site->returnVoteCheck($player, $this->getResult());
		}
		unset($site->checks[$this->player]);
	}
}
