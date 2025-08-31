<?php

namespace core\staff\uis\actions\mute;

use core\AtPlayer;
use core\Core;
use core\session\CoreSession;
use core\staff\entry\MuteEntry;
use core\ui\elements\simpleForm\Button;
use core\ui\windows\SimpleForm;
use core\user\User;

class ConfirmRevokeUi extends SimpleForm {

	/** @param MuteEntry[] $mutes */
	public function __construct(public MuteEntry $entry, public array $mutes, public User $user) {
		parent::__construct(
			"Confirm Revocation",
			"Are you sure you want to revoke this mute?"
		);

		$this->addButton(new Button("Yes"));
		$this->addButton(new Button("No"));
	}

	public function handle($response, AtPlayer $player) {
		if ($response === 0) {
			Core::getInstance()->getSessionManager()->useSession($this->user, function (CoreSession $session) use ($player) {
				$muteManager = $session->getStaff()->getMuteManager();

				$muteManager->removeMute($this->entry, $player->getUser());
				$player->showModal(new MutesListUi($player, $muteManager->getMutes(), $this->user, "Mute revoked successfully!"));
			});
		} else {
			$player->showModal(new ViewMuteUi($player, $this->entry, $this->mutes, $this->user));
		}
	}
}
