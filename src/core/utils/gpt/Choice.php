<?php

namespace core\utils\gpt;

class Choice {

	public function __construct(
		public string $finishReason,
		public int $index,
		public Message $message
	) {
	}

	public static function fromArray(array $array): Choice {
		if (isset($array["message"])) {
			$message = Message::create($array["message"]["content"], $array["message"]["role"]);
		} else {
			$message = Message::create(trim($array["text"]), "");
		}
		return new Choice($array["finish_reason"], $array["index"], $message);
	}

	public function getFinishReason(): string {
		return $this->finishReason;
	}

	public function getIndex(): int {
		return $this->index;
	}

	public function getMessage(): Message {
		return $this->message;
	}
}
