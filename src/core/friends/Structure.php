<?php

namespace core\friends;

/**
 * @deprecated
 */
class Structure {

	const TELEPORT = 0;
	const MESSAGE = 1;

	const SETTING_VERSION = "1.0.0";
	const SETTINGS = [
		self::TELEPORT => 0, //-1=no, 0=request, 1=yes
		self::MESSAGE => 1,
	];
	const SETTING_UPDATES = [];
}
