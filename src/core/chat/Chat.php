<?php

namespace core\chat;

use pocketmine\utils\TextFormat;

use core\{
	Core,
	AtPlayer as Player,
	AtPlayer
};
use core\chat\antispam\AntiSpam;
use core\chat\command\Emojis;
use core\chat\emoji\EmojiLibrary;
use core\chat\filter\Filter;
use core\rank\Rank;
use faction\Faction;
use faction\player\FactionPlayer;
use skyblock\{SkyBlock, SkyBlockPlayer};

use prison\{Prison, PrisonPlayer};

use pvp\{PvP, PvPPlayer};

class Chat {

	const MAX_WARNINGS = 3;
	const MAX_KICKS = 3;

	public Core $plugin;
	public Data $data;

	public AntiSpam $antispam;
	public Filter $filter;
	public EmojiLibrary $emojiLibrary;

	public array $cfc = [];
	public array $ntf = [];
	public array $cntf = [];

	public array $warnings = [];
	public array $kicks = [];

	public function __construct(Core $plugin) {
		$this->plugin = $plugin;
		$this->data = new Data();

		$this->antispam = new AntiSpam($plugin);
		$this->filter = new Filter($plugin);
		$this->emojiLibrary = new EmojiLibrary();

		$cmdMap = $plugin->getServer()->getCommandMap();
		$cmdMap->registerAll("chat", [
			new Emojis($plugin, "emojis", "Displays a list of emoji codes"),
		]);
	}

	public function close(): void {
		unset($this->warnings);
		unset($this->kicks);
	}

	public function getAntiSpam(): AntiSpam {
		return $this->antispam;
	}

	public function getFilter(): Filter {
		return $this->filter;
	}

	public function getEmojiLibrary(): EmojiLibrary {
		return $this->emojiLibrary;
	}

	public function getWarnings(Player $player): int {
		$name = $player->getName();
		if (!isset($this->warnings[$name])) return 0;
		return $this->warnings[$name];
	}

	public function addWarning(Player $player): void {
		$name = $player->getName();
		if (!isset($this->warnings[$name])) {
			$this->warnings[$name] = 1;
		} else {
			$this->warnings[$name] += 1;
		}

		if ($this->getWarnings($player) >= self::MAX_WARNINGS) {
			unset($this->warnings[$name]);
			$player->kick(TextFormat::RED . "Kicked for " . TextFormat::YELLOW . self::MAX_WARNINGS . TextFormat::RED . " chat offenses.", false);
			$this->addKick($player);
		}
	}

	public function getKicks(Player $player): int {
		$name = $player->getName();
		if (!isset($this->kicks[$name])) return 0;
		return $this->kicks[$name];
	}

	public function addKick(Player $player): void {
		$name = $player->getName();
		if (!isset($this->kicks[$name])) {
			$this->kicks[$name] = 1;
		} else {
			$this->kicks[$name] += 1;
		}

		if ($this->getKicks($player) >= self::MAX_KICKS) {
			unset($this->kicks[$name]);
			$this->plugin->getStaff()->ban($player, "sn3akrr", "Constantly attempting to bypass the chat filter.");
		}
	}

	public function getChatText(): string {
		return (isset($this->data->chat_formats[$this->plugin->getNetwork()->getServerType()]) ?
			$this->data->chat_formats[$this->plugin->getNetwork()->getServerType()]["chat"] :
			$this->data->chat_formats["default"]["chat"]
		);
	}

	public function getChatRankedText(): string {
		return (isset($this->data->chat_formats[$this->plugin->getNetwork()->getServerType()]) ?
			$this->data->chat_formats[$this->plugin->getNetwork()->getServerType()]["chat_ranked"] :
			$this->data->chat_formats["default"]["chat_ranked"]
		);
	}

	public function getNametagText(): string {
		return (isset($this->data->chat_formats[$this->plugin->getNetwork()->getServerType()]) ?
			$this->data->chat_formats[$this->plugin->getNetwork()->getServerType()]["nametag"] :
			$this->data->chat_formats["default"]["nametag"]
		);
	}

	public function getNametagRankedText(): string {
		return (isset($this->data->chat_formats[$this->plugin->getNetwork()->getServerType()]) ?
			$this->data->chat_formats[$this->plugin->getNetwork()->getServerType()]["nametag_ranked"] :
			$this->data->chat_formats["default"]["nametag_ranked"]
		);
	}

	/**
	 * Get a rank formatted in icon form
	 * @param string $rank | Rank name
	 */
	public function getFormattedRank(string $rank): string {
		return TextFormat::BOLD . Structure::RANK_FORMATS[strtolower($rank)] . TextFormat::RESET;
	}

	/**
	 * Recalculates chat formatting for a player
	 * @param Player $player
	 */
	public function updateChatFormat(Player $player): void {
		if (!$player->isLoaded()) return;
		$rank = $player->getRank();

		$format = ($rank == "default" ? $this->getChatText() : $this->getChatRankedText());

		$rs = $player->getSession()->getRank();
		$nameToUse = (
		$player->isDisguiseEnabled() ?
			$player->getDisguise()->getName() : (
				($rs->hasSub() && $rs->hasNick()) ?
				("*" . $rs->getNick()) : $player->getDisplayName()
			)
		);
		$format = str_replace("{NAME}", $nameToUse, $format);
		$format = str_replace("{NAMECOLOR}", ($player->isDisguiseEnabled() ? Rank::NAME_COLORS[-1] : Rank::NAME_COLORS[$rs->hasSub() ? $rs->getNameColor() : -1]), $format);

		/** @var Prison $prison */
		$prison = $this->plugin->getServer()->getPluginManager()->getPlugin("Prison");
		if ($prison !== null) {
			/** @var PrisonPlayer $player */
			$session = $player->getGameSession()->getRankUp();
			$rank = $session->getRank();
			$prestige = $session->getPrestige();
			$format = str_replace("{PRISON_RANK}", $prison->getRankUp()->getFormattedRank($rank, $prestige), $format);

			$tag = $player->getGameSession()->getTags()->getActiveTag();
			if ($tag !== null) {
				$tag = $tag->getFormat();
			} else {
				$tag = "";
			}
			$format = str_replace("{TAG}", $tag, $format);
		}

		/** @var SkyBlock $skyblock */
		$skyblock = $this->plugin->getServer()->getPluginManager()->getPlugin("SkyBlock");
		if ($skyblock !== null) {
			/** @var SkyBlockPlayer $player */
			$tag = $player->getGameSession()->getTags()->getActiveTag();
			if ($tag !== null) {
				$tag = $tag->getFormat();
			} else {
				$tag = "";
			}
			$format = str_replace("{TAG}", $tag, $format);
		}

		/** @var PvP $pvp */
		$pvp = $this->plugin->getServer()->getPluginManager()->getPlugin("PvP");
		if ($pvp !== null) {
			/** @var PvPPlayer $player */
			$session = $player->getGameSession()->getLevels();
			$level = TextFormat::AQUA . "[" . TextFormat::BOLD . TextFormat::WHITE . $session->getLevel() . TextFormat::RESET . TextFormat::AQUA . "] ";
			$format = str_replace("{LEVEL}", $level, $format);

			$tag = $player->getGameSession()->getTags()->getActiveTag();
			if ($tag !== null) {
				$tag = $tag->getFormat();
			} else {
				$tag = "";
			}
			$format = str_replace("{TAG}", $tag, $format);
		}

		/** @var Faction $faction */
		$faction = $this->plugin->getServer()->getPluginManager()->getPlugin("Faction");
		if($faction !== null) {
			/** @var FactionPlayer $player */
			$tag = $player->getGameSession()->getTags()->getActiveTag();
			$tag = (!is_null($tag) ? $tag->getFormat() : "");
			$format = str_replace("{TAG}", $tag, $format);
		}

		$this->cfc[$player->getName()] = $format;
	}

	/**
	 * Get a formatted chat string for a player
	 * @param Player $player
	 * @param string $message
	 */
	public function getChatFormat(Player $player, string $message): string {
		if (!$player->isLoaded()) return $player->getName();
		if (!isset($this->cfc[$player->getName()])) $this->updateChatFormat($player);

		$format = $this->cfc[$player->getName()];
		$format = str_replace(["{RANK}", "{MESSAGE}"], [$player->getSession()->getRank()->getRankIcon(), ($player->getRank() !== "default" ? self::convertWithEmojis($message) : $message)], $format);

		return $format;
	}

	/**
	 * Recalculates nametag formatting for a player
	 * @param Player $player
	 */
	public function updateNametagFormat(Player $player) {
		if (!$player->isLoaded() || is_null($player->getSession()) || (($player instanceof PrisonPlayer || $player instanceof SkyBlockPlayer || $player instanceof PvPPlayer) && is_null($player->getGameSession()))) return $player->getName();
		$rank = $player->getRank();
		$format = ($rank == "default" ? $this->getNametagText() : $this->getNametagRankedText());

		$rs = $player->getSession()->getRank();
		$nameToUse = (
		$player->isDisguiseEnabled() ?
			$player->getDisguise()->getName() : (
			($rs->hasSub() && $rs->hasNick()) ?
			("*" . $rs->getNick()) : $player->getDisplayName()
			)
		);
		$format = str_replace("{NAME}", $nameToUse, $format);
		$format = str_replace("{NAMECOLOR}", $player->isVanished() ? TextFormat::GRAY : ($player->isDisguiseEnabled() ? Rank::NAME_COLORS[-1] : Rank::NAME_COLORS[$rs->hasSub() ? $rs->getNameColor() : -1]), $format);
		$combatFormat = $format;

		/** @var Prison $prison */
		$prison = $this->plugin->getServer()->getPluginManager()->getPlugin("Prison");
		if ($prison != null) {
			/** @var PrisonPlayer $player */
			$session = $player->getGameSession()->getRankUp();
			$rank = $session->getRank();
			$prestige = $session->getPrestige();
			$format = str_replace("{PRISON_RANK}", $prison->getRankUp()->getFormattedRank($rank, $prestige), $format);

			$tag = $player->getGameSession()->getTags()->getActiveTag();
			if ($tag !== null) {
				$tag = $tag->getFormat();
			} else {
				$tag = "";
			}
			$format = str_replace("{TAG}", $tag, $format);

			$format = str_replace("{PVP}", (($combat = $player->getGameSession()->getCombat())->inPvPMode() ? " " . TextFormat::YELLOW . TextFormat::BOLD . "PVP" : ""), $format);
			$combatFormat = str_replace("{PVP}", (($combat = $player->getGameSession()->getCombat())->inPvPMode() ? " " . TextFormat::YELLOW . TextFormat::BOLD . "PVP" : ""), $combatFormat);

			if ($combat->hasBounty()) {
				$value = $combat->getBountyValue();
				$format = str_replace("{BOUNTY}", " " . TextFormat::RESET . TextFormat::AQUA . "B:(" . number_format($value) . ")", $format);
			} else {
				$format = str_replace("{BOUNTY}", "", $format);
			}
			$combatFormat = str_replace(["{PRISON_RANK} ", "{TAG}", "{BOUNTY}"], "", $combatFormat);
		}

		/** @var SkyBlock $skyblock */
		$skyblock = $this->plugin->getServer()->getPluginManager()->getPlugin("SkyBlock");
		if ($skyblock != null) {
			/** @var SkyBlockPlayer $player */
			$tag = $player->getGameSession()->getTags()->getActiveTag();
			if ($tag !== null) {
				$tag = $tag->getFormat();
			} else {
				$tag = "";
			}
			$format = str_replace("{TAG}", $tag, $format);

			$format = str_replace("{PVP}", (($combat = $player->getGameSession()->getCombat())->inPvPMode() ? " " . TextFormat::YELLOW . TextFormat::BOLD . "PVP" : ""), $format);
			$combatFormat = str_replace("{PVP}", (($combat = $player->getGameSession()?->getCombat())?->inPvPMode() ? " " . TextFormat::YELLOW . TextFormat::BOLD . "PVP" : ""), $combatFormat);
			$combatFormat = str_replace(["{TAG}"], "", $combatFormat);
		}

		/** @var PvP $pvp */
		$pvp = $this->plugin->getServer()->getPluginManager()->getPlugin("PvP");
		if ($pvp !== null) {
			/** @var PvPPlayer $player */
			$session = $player->getGameSession()->getLevels();
			$level = TextFormat::AQUA . "[" . TextFormat::BOLD . TextFormat::WHITE . $session->getLevel() . TextFormat::RESET . TextFormat::AQUA . "] ";
			$format = str_replace("{LEVEL}", $level, $format);

			$tag = $player->getGameSession()->getTags()->getActiveTag();
			if ($tag !== null) {
				$tag = $tag->getFormat();
			} else {
				$tag = "";
			}
			$format = str_replace("{TAG}", $tag, $format);
			$combatFormat = str_replace(["{LEVEL}", "{TAG}"], "", $combatFormat);
		}

		/** @var Faction $faction */
		$faction = $this->plugin->getServer()->getPluginManager()->getPlugin("Faction");
		if($faction !== null){
			/** @var FactionPlayer $player */
			$session = $player->getGameSession();
			$tag = $session->getTags()->getActiveTag();
			$tag = (!is_null($tag) ? $tag->getFormat() : "");

			$format = str_replace("{TAG}", $tag, $format);
			$combatFormat = str_replace(["{TAG}"], "", $combatFormat);
		}

		$this->cntf[$player->getName()] = $combatFormat;
		$this->ntf[$player->getName()] = $format;
	}

	/**
	 * Get a formatted nametag string for a player
	 * @param Player $player
	 * @param false|bool $combat
	 */
	public function getNametagFormat(Player $player, bool $combat = false): string {
		if (!$player->isLoaded()) return $player->getName();
		if (!isset($this->ntf[$player->getName()])) $this->updateNametagFormat($player);

		$format = $combat ? $this->cntf[$player->getName()] : $this->ntf[$player->getName()];
		$format = str_replace("{RANK}", $player->getSession()?->getRank()->getRankIcon() ?? "", $format);

		if ($player->isAFK()) {
			$format = TextFormat::DARK_GRAY . TextFormat::BOLD . "[AFK]" . TextFormat::RESET . " " . $format;
		}

		return $format;
	}

	public static function convertWithEmojis(string $text): string {
		return EmojiLibrary::convertWithEmojis($text); //todo: remove and use EmojiData directly prob
	}
}
