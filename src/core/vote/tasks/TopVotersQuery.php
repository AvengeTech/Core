<?php

namespace core\vote\tasks;

use pocketmine\scheduler\AsyncTask;

use core\Core;

class TopVotersQuery extends AsyncTask {

	public function __construct(public int $site = 1, public int $month = 0) {
	}

	public function onRun(): void {
		$month = ($this->month == 0 ? "current" : "previous");

		$query = curl_init("http://minecraftpocket-servers.com/api/?object=servers&element=voters&key=[REDACTED]&month=" . $month . "&format=json&limit=2000");
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
		Core::getInstance()->getVote()->getVoteSite($this->site)->updateReturn($this->getResult(), $this->month);
	}
}
