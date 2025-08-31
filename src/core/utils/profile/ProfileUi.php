<?php

namespace core\utils\profile;

use core\AtPlayer as Player;
use core\session\CoreSession;
use core\ui\elements\simpleForm\Button;
use core\ui\windows\SimpleForm;
use core\utils\TextFormat;

class ProfileUi extends SimpleForm {

	public function __construct(CoreSession $session) {
		$rs = $session->getRank();
		$cs = $session->getCosmetics();
		$ls = $session->getLootBoxes();
		$ns = $session->getNetwork();
		$vs = $session->getVote();
		$sub = $rs->hasSub();
		parent::__construct(
			$session->getUser()->getGamertag() . "'s profile",
			"Rank: " . $rs->getRank() . PHP_EOL .
				"Warden subscription: " . (
					!$sub ? TextFormat::RED . "Not active" . TextFormat::WHITE . PHP_EOL : (
						TextFormat::GREEN . "Active" . PHP_EOL .
						TextFormat::WHITE . "Subscribed since: " . TextFormat::YELLOW . $rs->getSubSince(true) . PHP_EOL .
						TextFormat::WHITE . "Expiration: " . TextFormat::RED . $rs->getSubExpiration(true) . PHP_EOL .
						TextFormat::WHITE . "Nickname: " . $rs->getNick() . PHP_EOL .
						TextFormat::WHITE . "Rank icon: " . $rs->getRankIcon() . PHP_EOL
				)
			) . PHP_EOL .

				"Cosmetics:" . PHP_EOL .
				"- Cape: " . ($cs->hasEquippedCape() ? ($cape = $cs->getEquippedCape())->getRarityColor() . $cape->getName() . TextFormat::WHITE : "None") . PHP_EOL .

				//"- Hat: " . ($cs->hasEquippedHat() ? ($hat = $cs->getEquippedHat())->getRarityColor() . $hat->getName() . TextFormat::WHITE : "None") . PHP_EOL .
				//"- Back: " . ($cs->hasEquippedBack() ? ($back = $cs->getEquippedBack())->getRarityColor() . $back->getName() . TextFormat::WHITE : "None") . PHP_EOL .
				//"- Shoes: " . ($cs->hasEquippedShoes() ? ($shoes = $cs->getEquippedShoes())->getRarityColor() . $shoes->getName() . TextFormat::WHITE : "None") . PHP_EOL .
				//"- Suit: " . ($cs->hasEquippedSuit() ? ($suit = $cs->getEquippedSuit())->getRarityColor() . $suit->getName() . TextFormat::WHITE : "None") . PHP_EOL .

				"- Trail effect: " . ($cs->hasEquippedTrail() ? ($trail = $cs->getEquippedTrail())->getRarityColor() . $trail->getName() . TextFormat::WHITE : "None") . PHP_EOL .
				"- Idle effect: " . ($cs->hasEquippedIdle() ? ($idle = $cs->getEquippedIdle())->getRarityColor() . $idle->getName() . TextFormat::WHITE : "None") . PHP_EOL .
				"- Double jump effect: " . ($cs->hasEquippedDoubleJump() ? ($dj = $cs->getEquippedDoubleJump())->getRarityColor() . $dj->getName() . TextFormat::WHITE : "None") . PHP_EOL .
				"- Arrow effect: " . ($cs->hasEquippedArrow() ? ($arrow = $cs->getEquippedArrow())->getRarityColor() . $arrow->getName() . TextFormat::WHITE : "None") . PHP_EOL .
				"- Snowball effect: " . ($cs->hasEquippedSnowball() ? ($snowball = $cs->getEquippedSnowball())->getRarityColor() . $snowball->getName() . TextFormat::WHITE : "None") . PHP_EOL . PHP_EOL .

				"Loot boxes: " . number_format($ls->getLootBoxes()) . PHP_EOL .
				"Shards: " . number_format($ls->getShards()) . PHP_EOL . PHP_EOL .

				"Vote streak: " . number_format($vs->getTotalStreak()) . PHP_EOL .
				"Highest vote streak: " . number_format($vs->getHighestStreak()) . PHP_EOL .
				"Monthly vote streak: " . number_format($vs->getMonthlyStreak()) . PHP_EOL . PHP_EOL .

				"Last seen: " . date("m/d/y", $ns->getLastLogin()) . " (" . $ns->getLastServer() . ")" . PHP_EOL .
				"Total warnings: " . count($session->getStaff()->getWarnManager()->getWarns()) . PHP_EOL .
				"Discord verified: " . ($session->getDiscord()->isVerified() ? TextFormat::GREEN . "YES" : TextFormat::RED . "NO") . TextFormat::WHITE . PHP_EOL
		);
		$this->addButton(new Button("Search"));
	}

	public function handle($response, Player $player) {
		$player->showModal(new ProfileSearchUi());
	}
}
