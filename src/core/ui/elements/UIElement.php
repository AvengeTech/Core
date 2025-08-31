<?php

namespace core\ui\elements;

use core\AtPlayer as Player;

abstract class UIElement {

	public $text = "";

	/**
	 * @return array
	 */
	abstract public function getDataToJson(): array;

	/**
	 * @param $value
	 * @param Player $player
	 */
	abstract public function handle($value, Player $player);
}
