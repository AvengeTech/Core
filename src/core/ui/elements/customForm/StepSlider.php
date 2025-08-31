<?php

namespace core\ui\elements\customForm;

use core\ui\elements\UIElement;
use core\AtPlayer as Player;

class StepSlider extends UIElement {

	/** @var string[] */
	protected $steps = [];
	/** @var integer Step index */
	protected $defaultStepIndex = 0;

	/**
	 *
	 * @param string $text
	 * @param string[] $steps
	 */
	public function __construct(string $text, array $steps = []) {
		$this->text = $text;
		$this->steps = $steps;
	}

	/**
	 *
	 * @param string $stepText
	 * @param boolean $isDefault
	 */
	public function addStep(string $stepText, bool $isDefault = false) {
		if ($isDefault) {
			$this->defaultStepIndex = count($this->steps);
		}
		$this->steps[] = $stepText;
	}

	/**
	 *
	 * @param string $stepText
	 * @return boolean
	 */
	public function setStepAsDefault(string $stepText): bool {
		$index = array_search($stepText, $this->steps);
		if ($index === false) {
			return false;
		}
		$this->defaultStepIndex = $index;
		return true;
	}

	/**
	 * Replace all steps
	 *
	 * @param string[] $steps
	 */
	public function setSteps(array $steps) {
		$this->steps = $steps;
	}

	/**
	 *
	 * @return array
	 */
	final public function getDataToJson(): array {
		return [
			'type' => 'step_slider',
			'text' => $this->text,
			'steps' => $this->steps,
			'default' => $this->defaultStepIndex,
		];
	}

	public function handle($value, Player $player) {
	}
}
