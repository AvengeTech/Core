<?php

namespace core\staff\uis\actions\ban;

use core\AtPlayer;
use core\Core;
use core\session\CoreSession;
use core\staff\entry\BanEntry;
use core\ui\elements\simpleForm\Button;
use core\ui\windows\SimpleForm;
use core\user\User;

class ConfirmRevokeUi extends SimpleForm {

	/** @param BanEntry[] $bans */
	public function __construct(public BanEntry $entry, public array $bans, public User $user) {
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
				$banManager = $session->getStaff()->getBanManager();

				$banManager->revokeBan($this->entry, $player->getUser());
				$player->showModal(new BansListUi($player, $banManager->getBans(), $this->user, "Ban revoked successfully!"));
			});
		} else {
			$player->showModal(new ViewBanUi($player, $this->entry, $this->bans, $this->user));
		}
	}
}
