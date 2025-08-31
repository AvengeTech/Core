<?php

namespace core\staff\uis\actions\warn;

use core\AtPlayer;
use core\Core;
use core\session\CoreSession;
use core\staff\entry\WarnEntry;
use core\ui\elements\simpleForm\Button;
use core\ui\windows\SimpleForm;
use core\user\User;

class ConfirmRevokeUi extends SimpleForm {

	/** @param WarnEntry[] $warns */
	public function __construct(public WarnEntry $entry, public array $warns, public User $user) {
		parent::__construct(
			"Confirm Revocation",
			"Are you sure you want to revoke this warning?"
		);

		$this->addButton(new Button("Yes"));
		$this->addButton(new Button("No"));
	}

	public function handle($response, AtPlayer $player) {
		if ($response === 0) {
			Core::getInstance()->getSessionManager()->useSession($this->user, function (CoreSession $session) use ($player) {
				$warnManager = $session->getStaff()->getWarnManager();

				$warnManager->removeWarn($this->entry, $player->getUser());
				$player->showModal(new WarnsListUi($player, $warnManager->getWarns(), $this->user, "Mute revoked successfully!"));
			});
		} else {
			$player->showModal(new ViewWarnUi($player, $this->entry, $this->warns, $this->user));
		}
	}
}
