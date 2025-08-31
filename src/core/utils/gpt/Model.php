<?php

namespace core\utils\gpt;

class Model {

	const MODEL_35_TURBO = "gpt-3.5-turbo";
	const MODEL_4 = "gpt-4";

	const MODEL_DAVINCI_001 = "text-davinci-001";
	const MODEL_DAVINCI_002 = "text-davinci-002";
	const MODEL_DAVINCI_003 = "text-davinci-003";

	const ENDPOINT_CHAT = "https://api.openai.com/v1/chat/completions";
	const ENDPOINT_OTHER = "https://api.openai.com/v1/completions";

	public function __construct(
		public string $name,
		public string $endpoint
	) {
	}

	public function getName(): string {
		return $this->name;
	}

	public function getEndpoint(): string {
		return $this->endpoint;
	}
}
