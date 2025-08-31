<?php

namespace core\ui\elements\customForm;

use core\ui\elements\UIElement;
use core\AtPlayer as Player;

class Dropdown extends UIElement {

	/** @var string[] */
	protected $options = [];
	/** @var int */
	protected $defaultOptionIndex = 0;

	/**
	 *
	 * @param string $text
	 * @param string[] $options
	 */
	public function __construct(string $text, array $options = []) {
		$this->text = $text;
		$this->options = $options;
	}

	/**
	 *
	 * @param string $optionText
	 * @param boolean $isDefault
	 */
	public function addOption(string $optionText, bool $isDefault = false) {
		if ($isDefault) {
			$this->defaultOptionIndex = count($this->options);
		}
		$this->options[] = $optionText;
	}

	/**
	 *
	 * @param string $optionText
	 * @return boolean
	 */
	public function setOptionAsDefault(string $optionText): bool {
		$index = array_search($optionText, $this->options);
		if ($index === false) {
			return false;
		}
		$this->defaultOptionIndex = $index;
		return true;
	}

	public function setIndexAsDefault(int $index): bool {
		if (!isset($this->options[$index])) {
			return false;
		}
		$this->defaultOptionIndex = $index;
		return true;
	}

	/**
	 * Replace all options
	 *
	 * @param string[] $options
	 */
	public function setOptions(array $options) {
		$this->options = $options;
	}

	/**
	 *
	 * @return array
	 */
	final public function getDataToJson(): array {
		return [
			'type' => 'dropdown',
			'text' => $this->text,
			'options' => $this->options,
			'default' => $this->defaultOptionIndex,
		];
	}

	public function handle($value, Player $player) {
	}
}
