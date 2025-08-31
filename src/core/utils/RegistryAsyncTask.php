<?php

namespace core\utils;

use core\utils\block\BlockPalette;
use core\utils\BlockRegistry;
use pocketmine\scheduler\AsyncTask;

class RegistryAsyncTask extends AsyncTask{

	public function __construct(
		private string $server
	){}

	public function onRun() : void{
		BlockRegistry::setup($this->server);
	}
}
