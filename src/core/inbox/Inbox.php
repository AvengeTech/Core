<?php

namespace core\inbox;

use core\Core;
use core\inbox\command\OpenInbox;

class Inbox {

	const TYPE_GLOBAL = 0;
	const TYPE_HERE = 1;

	public function __construct(public Core $plugin) {
		$plugin->getServer()->getCommandMap()->registerAll("inbox", [
			new OpenInbox($plugin, "openinbox", "Open message inbox"),
		]);
	}
}
