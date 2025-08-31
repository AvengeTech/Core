<?php

namespace core\ui\windows;

use core\ui\CustomUI;
use core\ui\elements\UIElement;
use core\ui\elements\customForm\Slider;
use core\Core;
use core\user\User;
use core\AtPlayer as Player;
use core\ui\elements\customForm\Label;
use PDO;
use pocketmine\form\FormValidationException;

class CustomForm implements CustomUI {

	/** @var string */
	protected $title = "";
	/** @var UIElement[] */
	protected $elements = [];
	/** @var string */
	protected $json = "";
	/** @var string Only for server settings */
	protected $iconURL = "";

	public function __construct(string $title) {
		$this->title = $title;
	}

	/**
	 * Add element to form
	 */
	public function addElement(UIElement $element) {
		$this->elements[] = $element;
		$this->json = "";
	}

	public function getElements(): array {
		return $this->elements;
	}

	/**
	 * Only for server settings
	 * @param string $url
	 */
	public function addIconUrl($url) {
		$this->iconURL = $url;
	}

	final public function toJSON(): string {
		if ($this->json != "") {
			return $this->json;
		}
		$data = [
			'type' => 'custom_form',
			'title' => $this->title,
			'content' => [],
		];
		if ($this->iconURL != "") {
			$data['icon'] = [
				"type" => "url",
				"data" => $this->iconURL,
			];
		}
		foreach ($this->elements as $element) {
			$data['content'][] = $element->getDataToJson();
		}
		return $this->json = json_encode($data);
	}

	/**
	 * To handle manual closing
	 *
	 * @var Player $player
	 */
	public function close(Player $player) {
	}

	/**
	 * @notice It not final because some logic may
	 * depends on some elements at the same time
	 *
	 * @param array $response
	 * @param Player $player
	 */
	public function handle($response, Player $player) {
		foreach ($response as $elementKey => $elementValue) {
			if (isset($this->elements[$elementKey])) {
				$this->elements[$elementKey]->handle($elementValue, $player);
			} else {
				error_log(__CLASS__ . '::' . __METHOD__ . " Element with index {$elementKey} doesn't exists.");
			}
		}
	}

	public static function verifyData(CustomForm $ui, array $response, Player $player) : array{
		$actual = count($response);
		$expected = count($ui->elements);

		if($actual > $expected){
			throw new FormValidationException("Too many result elements, expected $expected, got $actual");
		}elseif($actual < $expected){
			//In 1.21.70, the client doesn't send nulls for labels, so we need to polyfill them here to
			//maintain the old behaviour
			$noLabelsIndexMapping = [];

			foreach($ui->elements as $elementKey => $elementValue){
				if(isset($ui->elements[$elementKey])){
					$element = $ui->elements[$elementKey];

					if(!$element instanceof Label){
						$noLabelsIndexMapping[] = $elementKey;
					}
				}else{
					error_log(__CLASS__ . '::' . __METHOD__ . " Element with index {$elementKey} doesn't exists.");
				}
			}

			$expectedWithoutLabels = count($noLabelsIndexMapping);

			if($actual !== $expectedWithoutLabels){
				error_log(__CLASS__ . '::' . __METHOD__ . " Wrong number of result elements, expected either " .
					$expected .
					" (with label values, <1.21.70) or " .
					$expectedWithoutLabels .
					" (without label values, >=1.21.70), got " .
					$actual
				);
			}

			//polyfill the missing nulls
			$mappedResponse = array_fill(0, $expected, null);

			foreach($response as $givenIndex => $value){
				$internalIndex = $noLabelsIndexMapping[$givenIndex] ?? null;

				if(is_null($internalIndex)){
					error_log(__CLASS__ . '::' . __METHOD__ . " Can't map given offset $givenIndex to an internal element offset (while correcting for labels)");
				}

				//set the appropriate values according to the given index
				//this could (?) still leave unexpected nulls, but the validation below will catch that
				$mappedResponse[$internalIndex] = $value;
			}

			if(count($mappedResponse) !== $expected){
				error_log(__CLASS__ . '::' . __METHOD__ . " This should always match.");
			}

			$response = $mappedResponse;
		}

		// idk another way so imma do this.
		$labelMap = [];

		foreach($ui->elements as $elementKey => $elementValue){
			if(isset($ui->elements[$elementKey])){
				$element = $ui->elements[$elementKey];

				if($element instanceof Label){
					$labelMap[] = $element->text;
				}else{
					$labelMap[] = count($labelMap);
				}
			}else{
				error_log(__CLASS__ . '::' . __METHOD__ . " Element with index {$elementKey} doesn't exists.");
			}
		}
		//

		$new = [];

		foreach($response as $index => $value){
			if(isset($ui->elements[$index])){
				error_log(__CLASS__ . '::' . __METHOD__ . " Element with index {$elementKey} doesn't exists.");
			}
			$new[$labelMap[$index]] = $value;
		}
		$response = $new;

		foreach($response as $elementKey => $elementValue){
			if(isset($ui->elements[$elementKey])){
				$element = $ui->elements[$elementKey];

				if($element instanceof Slider){
					if($elementValue < $element->min){
						Core::getInstance()->getStaff()->ban($player, new User("sn3akrr"), "UI Spoofing");
					}
					$response[$elementKey] = max($element->min, min($element->max, $elementValue));
				}
			}else{
				error_log(__CLASS__ . '::' . __METHOD__ . " Element with index {$elementKey} doesn't exists.");
			}
		}

		return $response;
	}
}
