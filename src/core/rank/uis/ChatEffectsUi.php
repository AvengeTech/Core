<?php

namespace core\rank\uis;

use core\{
	Core,
	AtPlayer as Player
};
use core\cosmetics\ui\CosmeticsUi;
use core\discord\objects\{
	Post,
	Footer,
	Embed,
	Field,
	Webhook
};
use core\ui\windows\CustomForm;
use core\ui\elements\customForm\{
	Label,
	Input,
	Dropdown
};
use core\user\User;
use core\utils\TextFormat;

class ChatEffectsUi extends CustomForm {

	const BLACKLISTED_EMOJIS = [
		":owner:", ":crown:",
		":mod:", ":trainee:",
		":yt:", ":youtube:",
		":dev:", ":developer:",
		":builder:", ":artist:"
	];

	const BLACKLISTED_NICKNAMES = [
		"owner", "staff",
		"mod", "trainee",
		"sn3ak", "kally",
		"pringz", "miguel"
	];

	const RANKED_EMOJIS = [
		":ed:" => 6,
		":enderdragon:" => 6,
		":wither:" => 5,
		":enderman:" => 4,
		":ghast:" => 3,
		":blaze:" => 2,
		":endermite:" => 1,
	];

	const RANKED_EMOJIS_NAMES = [
		":ed:" => "Enderdragon",
		":enderdragon:" => "Enderdragon",
		":wither:" => "Wither",
		":enderman:" => "Enderman",
		":ghast:" => "Ghast",
		":blaze:" => "Blaze",
		":endermite:" => "Endermite",
	];

	const NICKNAME_LIMIT = 15;

	public function __construct(Player $player, string $error = "", public bool $fromMenu = false) {
		parent::__construct(TextFormat::ICON_WARDEN . " Chat Effects");
		$rs = $player->getSession()->getRank();
		$this->addElement(new Label(($error == "" ? "" : TextFormat::RED . "Error: " . $error . PHP_EOL . PHP_EOL . TextFormat::WHITE) . "Use this menu to edit your chat appearance!"));
		$this->addElement(new Input("Nickname", "poop pee", $rs->getNick()));
		$this->addElement(new Input("Rank Emoji (leave blank for default)", ":burger:", $rs->getCustomIcon()));
		$dd = new Dropdown("Chat color", [
			TextFormat::YELLOW . "Yellow (Default)",
			TextFormat::RED . "Red",
			TextFormat::GOLD . "Gold",
			TextFormat::GREEN . "Green",
			TextFormat::AQUA . "Light blue",
			TextFormat::LIGHT_PURPLE . "Light purple",
			TextFormat::DARK_PURPLE . "Dark purple",
			TextFormat::WHITE . "White",
		]);
		$dd->setIndexAsDefault($rs->getNameColor() + 1);
		$this->addElement($dd);
	}

	public function close(Player $player) {
		if ($this->fromMenu) {
			$player->showModal(new CosmeticsUi($player));
		}
	}

	public function handle($response, Player $player) {
		if (!$player->getSession()->getRank()->hasSub()) {
			$player->sendMessage(TextFormat::RI . "You must have the " . TextFormat::DARK_AQUA . "Warden " . TextFormat::ICON_WARDEN . TextFormat::GRAY  . "subscription to modify your chat settings! Purchase a subscription at " . TextFormat::YELLOW . "store.avengetech.net");
			return;
		}
		$nickname = $response[1];
		$emoji = $response[2];
		$color = $response[3] - 1;

		$func = function () use ($player, $nickname, $emoji, $color): void {
			if (!$player->isConnected()) return;
			if (!$player->isTier3() && in_array($emoji, self::BLACKLISTED_EMOJIS)) {
				$player->showModal(new ChatEffectsUi($player, "You tried using a blacklisted emoji!"));
				return;
			}
			if ($emoji !== "" && Core::getInstance()->getChat()->getEmojiLibrary()->getEmoji($emoji) === "") {
				$player->showModal(new ChatEffectsUi($player, "Invalid emoji selected! View a list of emojis with " . TextFormat::YELLOW . "/emojis"));
				return;
			}
			$rh = $player->getRankHierarchy();
			if (isset(self::RANKED_EMOJIS[$emoji]) && self::RANKED_EMOJIS[$emoji] > $rh) {
				$player->showModal(new ChatEffectsUi($player, "You must be at least " . Core::getInstance()->getChat()->getEmojiLibrary()->getEmoji($emoji) . self::RANKED_EMOJIS_NAMES[$emoji] . " rank to use this emoji!"));
				return;
			}
			$rs = $player->getSession()->getRank();
			$rs->setCustomIcon($emoji);
			$rs->setNameColor($color);
			if ($nickname !== $rs->getNick()) {
				$oldnick = $rs->getNick();
				$rs->setNick($nickname);

				$post = new Post("", "Nick log - " . $player->getName(), "[REDACTED]", false, "", [
					new Embed("", "rich", "**" . $player->getName() . "** has changed their nickname!", "", "ffb106", new Footer("WARDENNNNN"), "", "[REDACTED]", null, [
						new Field("Old nickname", $oldnick, true),
						new Field("New nickname", $nickname, true)
					])
				]);
				$post->setWebhook(Webhook::getWebhookByName("nick-log"));
				$post->send();
			}

			$player->updateChatFormat();
			$player->updateNametag();

			if ($this->fromMenu) {
				$player->showModal(new CosmeticsUi($player, "Successfully updated your chat settings!", false));
			} else {
				$player->sendMessage(TextFormat::GI . "Successfully updated your chat effects!");
			}
		};
		if ($nickname !== "" && $nickname !== $player->getSession()->getRank()->getNick()) {
			if (!$player->isTier3() && in_array($nickname, self::BLACKLISTED_NICKNAMES)) {
				$player->showModal(new ChatEffectsUi($player, "You tried using a blacklisted nickname!", $this->fromMenu));
				return;
			}
			if (!ctype_alnum($nickname) || strlen($nickname) >= self::NICKNAME_LIMIT) {
				$player->showModal(new ChatEffectsUi($player, "Nickname must be alphanumeric and under 16 characters!", $this->fromMenu));
				return;
			}
			Core::getInstance()->getUserPool()->useUser($nickname, function (User $user) use ($player, $nickname, $func): void {
				if (!$player->isConnected()) return;
				if ($user->valid()) {
					$player->showModal(new ChatEffectsUi($player, "This username belongs to another player!", $this->fromMenu));
					return;
				}

				$player->getSession()->getRank()->nickExists($nickname, function (bool $exists) use ($func, $nickname, $player): void {
					if (!$player->isConnected()) return;
					if ($exists) {
						$player->showModal(new ChatEffectsUi($player, "Nickname provided is already in use!", $this->fromMenu));
						return;
					}
					$player->getSession()->getRank()->trySaveNick($nickname, function (bool $saved) use ($func, $player): void {
						if (!$player->isConnected()) return;
						if (!$saved) {
							$player->showModal(new ChatEffectsUi($player, "Nickname provided is already in use!", $this->fromMenu));
							return;
						}
						$func();
					});
				});
			});
		} else {
			$func();
		}
	}
}
