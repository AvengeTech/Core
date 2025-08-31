<?php

namespace core\utils\gpt;

class GptRequest {

	const ROLE_USER = "user";
	const ROLE_SYSTEM = "system";

	public ?GptResponse $response = null;

	public function __construct(
		public array $messages,
		public \Closure $closure,
		public ?Conversation $conversation = null,
		public float $temperature = 1.0
	) {
	}

	public static function create(array $messages, \Closure $closure): GptRequest {
		return new GptRequest($messages, $closure);
	}

	public function getMessages(): array {
		return $this->messages;
	}

	public function addContent(Message $message): void {
		$this->messages[] = $message;
	}

	public function getResponseClosure(): \Closure {
		return $this->closure;
	}

	public function getConversation(): ?Conversation {
		return $this->conversation;
	}

	public function hasResponse(): bool {
		return $this->response !== null;
	}

	public function getResponse(): ?GptResponse {
		return $this->response;
	}

	public function setResponse(GptResponse $response): void {
		$this->response = $response;
		var_dump($response->getUsage());

		foreach ($response->getChoices() as $choice) {
			$this->getConversation()?->addMessage($choice->getMessage());
		}
	}

	public function getTemperature(): float {
		return $this->temperature;
	}

	public function toJson(Model $model): string {
		switch ($model->getName()) {
			case Model::MODEL_DAVINCI_001:
			case Model::MODEL_DAVINCI_002:
			case Model::MODEL_DAVINCI_003:
				$array = [
					"model" => $model->getName(),
					"prompt" => $this->getMessages()[0]?->getContent() ?? ""
				];
				break;
			case Model::MODEL_35_TURBO:
			case Model::MODEL_4:
				$array = [
					"model" => $model->getName(),
					"messages" => []
				];
				foreach ($this->getMessages() as $message) {
					$array["messages"][] = [
						"role" => $message->getRole(),
						"content" => $message->getContent()
					];
				}
				break;
		}
		return json_encode($array);
	}
}
