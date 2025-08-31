<?php

namespace core\discord\task;

use pocketmine\scheduler\AsyncTask;

use core\Core;

class UserFromSnowflakeTask extends AsyncTask {

	const TOKEN = "[REDACTED]";

	public function __construct(public int $taskId, public int $snowflake) {
	}

	public function onRun(): void {
		$ch = curl_init("https://discord.com/api/v9/users/" . $this->snowflake);
		curl_setopt($ch, CURLOPT_HTTPHEADER, [
			"Content-type: application/json",
			"Authorization: Bot " . self::TOKEN
		]);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$this->setResult(curl_exec($ch));
		curl_close($ch);
	}

	public function onCompletion(): void {
		Core::getInstance()->getDiscord()->returnUserData($this->taskId, $this->snowflake, json_decode($this->getResult(), true));
	}
}
