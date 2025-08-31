<?php

namespace core\ui\windows;

use core\ui\CustomUI;
use core\AtPlayer as Player;

abstract class ModalWindow implements CustomUI {

	/** @var string */
	protected $title = "";
	/** @var string */
	protected $content = "";
	/** @var string */
	protected $trueButtonText = "";
	/** @var string */
	protected $falseButtonText = "";
	/** @var string */
	protected $json = "";

	/**
	 *
	 * @param string $title
	 * @param string $content
	 * @param string $trueButtonText
	 * @param string $falseButtonText
	 */
	public function __construct(string $title, string $content, string $trueButtonText, string $falseButtonText) {
		$this->title = $title;
		$this->content = $content;
		$this->trueButtonText = $trueButtonText;
		$this->falseButtonText = $falseButtonText;
	}

	/**
	 * Convert class to JSON string
	 *
	 * @return string
	 */
	final public function toJSON(): string {
		if ($this->json != "") {
			return $this->json;
		}
		return $this->json = json_encode([
			'type' => 'modal',
			'title' => $this->title,
			'content' => $this->content,
			'button1' => $this->trueButtonText,
			'button2' => $this->falseButtonText,
		]);
	}

	/**
	 * To handle manual closing
	 *
	 * @var Player $player
	 */
	public function close(Player $player) {
	}

	/**
	 * @param boolean $response
	 * @param Player $player
	 */
	abstract public function handle($response, Player $player);
}
