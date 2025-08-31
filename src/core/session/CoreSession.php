<?php

namespace core\session;

use core\{
	AtPlayer as Player,
	cosmetics\CosmeticsComponent,
	discord\DiscordComponent,
	gadgets\GadgetsComponent,
	inbox\InboxComponent,
	lootboxes\LootBoxComponent,
	network\NetworkComponent,
	rank\RankComponent,
	staff\StaffComponent,
	utils\LoginCommandsComponent,
	utils\PlaytimeComponent,
	vote\VoteComponent
};
use core\settings\{
	GlobalSettings,
	SettingsComponent
};

use core\user\User;
use core\utils\Version;

class CoreSession extends PlayerSession {

	public function __construct(SessionManager $sessionManager, Player|User $user) {
		parent::__construct($sessionManager, $user);

		$this->addComponent(new CosmeticsComponent($this));
		$this->addComponent(new DiscordComponent($this));
		$this->addComponent(new GadgetsComponent($this));
		$this->addComponent(new InboxComponent($this));
		$this->addComponent(new LootBoxComponent($this));
		$this->addComponent(new NetworkComponent($this));
		$this->addComponent(new RankComponent($this));
		$this->addComponent(new StaffComponent($this));
		$this->addComponent(new LoginCommandsComponent($this));
		$this->addComponent(new PlaytimeComponent($this));
		$this->addComponent(new VoteComponent($this));

		$this->addComponent(new SettingsComponent(
			$this,
			Version::fromString(GlobalSettings::VERSION),
			GlobalSettings::DEFAULT_SETTINGS,
			GlobalSettings::SETTING_UPDATES
		));
	}

	public function getCosmetics(): CosmeticsComponent {
		return $this->getComponent("cosmetics");
	}

	public function getDiscord(): DiscordComponent {
		return $this->getComponent("discord");
	}

	public function getGadgets(): GadgetsComponent {
		return $this->getComponent("gadgets");
	}

	public function getInbox(): InboxComponent {
		return $this->getComponent("inbox");
	}

	public function getLootBoxes(): LootBoxComponent {
		return $this->getComponent("lootboxes");
	}

	public function getNetwork(): NetworkComponent {
		return $this->getComponent("network");
	}

	public function getRank(): RankComponent {
		return $this->getComponent("rank");
	}

	public function getStaff(): StaffComponent {
		return $this->getComponent("staff");
	}

	public function getLoginCommands(): LoginCommandsComponent {
		return $this->getComponent("login_commands");
	}

	public function getPlaytime(): PlaytimeComponent {
		return $this->getComponent("playtime");
	}

	public function getVote(): VoteComponent {
		return $this->getComponent("vote");
	}

	public function getSettings(): SettingsComponent {
		return $this->getComponent("settings");
	}
}
