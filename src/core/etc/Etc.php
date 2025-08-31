<?php

namespace core\etc;

use core\etc\pieces\{
	afk\Afk,
	skin\Skin
};

use core\{
	Core
};

class Etc {

	public $plugin;
	public $pieces = [];

	public function __construct(Core $plugin) {
		$this->plugin = $plugin;

		$this->registerPieces();
	}

	public function registerPieces() {
		$plugin = $this->plugin;
		$this->pieces["afk"] = new Afk($plugin);
		$this->pieces["skin"] = new Skin($plugin);
	}

	public function getPiece($name) {
		return $this->pieces[$name] ?? null;
	}
}
