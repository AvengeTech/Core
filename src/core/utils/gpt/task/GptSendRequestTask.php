<?php

namespace core\utils\gpt\task;

use pocketmine\scheduler\AsyncTask;

use core\Core;
use core\utils\gpt\{
	Model,
	GptResponse
};

class GptSendRequestTask extends AsyncTask {

	const KEY = "[REDACTED]";

	public string $endpoint;

	public function __construct(
		Model $model,
		public int $id,
		public string $requestJson
	) {
		$this->endpoint = $model->getEndpoint();
	}

	public function onRun(): void {
		$endpoint = $this->endpoint;
		$key = self::KEY;
		$json = $this->requestJson;

		$ch = curl_init();
		curl_setopt_array($ch, [
			CURLOPT_URL => $this->endpoint,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_HTTPHEADER => [
				"Authorization: Bearer $key",
				"Content-Type: application/json"
			],
			CURLOPT_POSTFIELDS => $json
		]);
		$this->setResult(curl_exec($ch));
		curl_close($ch);
	}

	public function onCompletion(): void {
		Core::getInstance()->getGptQueue()->getRequest($this->id)?->setResponse(GptResponse::fromJson($this->getResult()));
	}
}
