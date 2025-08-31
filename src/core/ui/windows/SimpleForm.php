<?php

namespace core\ui\windows;

use core\ui\CustomUI;
use core\ui\elements\simpleForm\Button;
use core\AtPlayer as Player;

class SimpleForm implements CustomUI {

	/** @var string */
	protected $title = "";
	/** @var string */
	protected $content = "";
	/** @var Button[] */
	protected $buttons = [];
	/** @var string */
	protected $json = "";

	/**
	 *
	 * @param string $title
	 * @param string $content
	 */
	public function __construct(string $title, string $content) {
		$this->title = $title;
		$this->content = $content;
	}

	/**
	 * Add button to form
	 *
	 * @param Button $button
	 */
	public function addButton(Button $button) {
		$this->buttons[] = $button;
		$this->json = "";
	}

	/**
	 * Convert class to JSON string
	 *
	 * @return string
	 */
	public function toJSON(): string {
		if ($this->json != "") {
			return $this->json;
		}
		$data = [
			'type' => 'form',
			'title' => $this->title,
			'content' => $this->content,
			'buttons' => [],
		];
		foreach ($this->buttons as $button) {
			$data['buttons'][] = $button->getDataToJson();
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
	 * @param $response
	 * @param Player $player
	 * @throws \Exception
	 */
	public function handle($response, Player $player) {
		if (isset($this->buttons[$response])) {
			$this->buttons[$response]->handle(true, $player);
		} else {
			error_log(__CLASS__ . '::' . __METHOD__ . " Button with index {$response} doesn't exists.");
		}
	}
}
