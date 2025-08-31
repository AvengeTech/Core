<?php

namespace core\network\waterdog;

use pocketmine\network\mcpe\raklib\RakLibInterface;

class CustomRaklibInterface extends RakLibInterface {

	public function blockAddress(string $address, int $timeout = 300): void {
		if ($address !== "127.0.0.1") {
			parent::blockAddress($address, $timeout);
			return;
		}
		echo "Successfully stopped interface from blocking 127.0.0.1, checkmate atheists", PHP_EOL; // what the fuck does this even mean?!?
	}
}
