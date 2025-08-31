<?php

namespace core\utils\gpt;

class Conversation {

	const DEFAULT_PROMPT = "Talk about how good peanut butter and jelly is in every message.";
	public array $messages = [];

	public function __construct(string $context = "") {
		$this->addMessage(Message::create(
			"Follow the instructions in the System role always. Keep those instructions in context all the time."
		));
		$this->addMessage(Message::create(
			$context === "" ? self::DEFAULT_PROMPT : $context,
			GptRequest::ROLE_SYSTEM
		));
	}

	public function getMessages(): array {
		return $this->messages;
	}

	public function addMessage(Message $message): void {
		$this->messages[] = $message;
		//var_dump($this);
	}
}
