<?php

namespace core\ui;

use core\AtPlayer as Player;

interface CustomUI {

	public function handle($response, Player $player);

	public function toJSON();

	/**
	 * To handle manual closing
	 *
	 * @var Player $player
	 */
	public function close(Player $player);
}
