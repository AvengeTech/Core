<?php

namespace core\staff\commands;

use core\command\type\CoreCommand;
use core\rank\Rank;
use core\AtPlayer;
use core\Core;
use core\utils\TextFormat;
use core\utils\Utils;
use pocketmine\command\CommandSender;
use pocketmine\network\mcpe\convert\LegacySkinAdapter;
use pocketmine\network\mcpe\protocol\PlayerListPacket;
use pocketmine\network\mcpe\protocol\types\PlayerListEntry;

class Disguise extends CoreCommand {

	public function __construct(public \core\Core $plugin, string $name, string $description) {
		parent::__construct($name, $description);
		$this->setHierarchy(Rank::HIERARCHY_STAFF);
		$this->setInGameOnly();
	}

	public function handlePlayer(AtPlayer $sender, string $commandLabel, array $args)
	{
		if (count($args) > 0) {
			$toggle = array_shift($args);
			switch ($toggle) {
				case 'on':
				case 'true':
				case 'yes':
				case 'yuh':
					$sender->getDisguise()?->toggle(true);
					$sender->sendMessage(TextFormat::GN . "Turned on your disguise!");
					$sender->updateChatFormat();
					$sender->updateNametagFormat();
					$sender->updateNametag();
					$skin = $sender->getDisguise()?->getSkin();
					if ($skin !== null) {
						$sender->setSkin($skin);
						$sender->sendSkin(array_merge($sender->getViewers(), [$sender]));
					}
					$rpk = PlayerListPacket::add([$sender->getDisguise()->getPlayerListAddEntry()]);
					foreach ($sender->getServer()->getOnlinePlayers() as $p) $p->getNetworkSession()->sendDataPacket($rpk);
					$apk = PlayerListPacket::remove([PlayerListEntry::createRemovalEntry($sender->getUniqueId())]);
					foreach ($sender->getServer()->getOnlinePlayers() as $p) $p->getNetworkSession()->sendDataPacket($apk);
					break;
				case 'off':
				case 'false':
				case 'no':
				case 'nah':
					$sender->getDisguise()?->toggle(false);
					$sender->sendMessage(TextFormat::GN . "Turned off your disguise!");
					$sender->updateChatFormat();
					$sender->updateNametagFormat();
					$sender->updateNametag();
					$skin = $sender->getSession()->getCosmetics()->getOriginalSkin();
					if ($skin !== null) {
						$sender->getSession()->getCosmetics()->updateLayers(true);
					} else {
						$sender->sendMessage(TextFormat::RN . "Failed to reset your skin! Please reconnect.");
						Utils::dumpVals("Failed to set skin for: " . $sender->getName());
					}
					$rpk = PlayerListPacket::remove([$sender->getDisguise()->getPlayerListRemoveEntry()]);
					foreach ($sender->getServer()->getOnlinePlayers() as $p) $p->getNetworkSession()->sendDataPacket($rpk);
					$apk = PlayerListPacket::add([PlayerListEntry::createAdditionEntry($sender->getUniqueId(), $sender->getId(), $sender->getName(), (new LegacySkinAdapter)->toSkinData($sender->getSkin()))]);
					foreach ($sender->getServer()->getOnlinePlayers() as $p) $p->getNetworkSession()->sendDataPacket($apk);
					break;
				case 'toggle':
				case 'switch':
				case 'flipflop':
					$sender->getDisguise()?->toggle();
					$sender->sendMessage(TextFormat::GN . "Toggled your disguise " . ($sender->isDisguiseEnabled() ? "on!" : "off!"));
					$sender->updateChatFormat();
					$sender->updateNametagFormat();
					$sender->updateNametag();
					if (!is_null($sender->getSession())) {
						$skin = $sender->isDisguiseEnabled() ? $sender->getDisguise()->getSkin() : $sender->getSession()->getCosmetics()->getOriginalSkin();
						if ($skin !== null && $sender->isDisguiseEnabled()) {
							$sender->setSkin($skin);
							$sender->sendSkin(array_merge($sender->getViewers(), [$sender]));
							$rpk = PlayerListPacket::add([$sender->getDisguise()->getPlayerListAddEntry()]);
							foreach ($sender->getServer()->getOnlinePlayers() as $p) $p->getNetworkSession()->sendDataPacket($rpk);
							$apk = PlayerListPacket::remove([PlayerListEntry::createRemovalEntry($sender->getUniqueId())]);
							foreach ($sender->getServer()->getOnlinePlayers() as $p) $p->getNetworkSession()->sendDataPacket($apk);
						} elseif (!$sender->isDisguiseEnabled()) {
							$sender->getSession()->getCosmetics()->updateLayers(true);
							$rpk = PlayerListPacket::remove([$sender->getDisguise()->getPlayerListRemoveEntry()]);
							foreach ($sender->getServer()->getOnlinePlayers() as $p) $p->getNetworkSession()->sendDataPacket($rpk);
							$apk = PlayerListPacket::add([PlayerListEntry::createAdditionEntry($sender->getUniqueId(), $sender->getId(), $sender->getName(), (new LegacySkinAdapter)->toSkinData($sender->getSkin()))]);
							foreach ($sender->getServer()->getOnlinePlayers() as $p) $p->getNetworkSession()->sendDataPacket($apk);
						} else {
							$sender->sendMessage(TextFormat::RN . "Failed to reset your skin! Please reconnect.");
							Utils::dumpVals("Failed to set skin for: " . $sender->getName());
						}
					}
					break;
				default:
					$sender->sendMessage(TextFormat::RI . "/disguise <on|off|toggle>");
			}
		} else {
			$sender->sendMessage(TextFormat::RI . "/disguise <on|off|toggle>");
		}
	}

	public function getPlugin(): \pocketmine\plugin\Plugin {
		return $this->plugin;
	}
}
