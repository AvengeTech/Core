<?php

namespace core\ui\elements\customForm;

use core\ui\elements\UIElement;
use core\AtPlayer as Player;

class Label extends UIElement {

	/**
	 *
	 * @param string $text
	 */
	public function __construct(string $text) {
		$this->text = $text;
	}

	/**
	 *
	 * @return array
	 */
	final public function getDataToJson(): array {
		return [
			"type" => "label",
			"text" => $this->text,
		];
	}

	/**
	 * @notice Value for Label always null
	 *
	 * @param null $value
	 * @param Player $player
	 */
	final public function handle($value, Player $player) {
	}
}
