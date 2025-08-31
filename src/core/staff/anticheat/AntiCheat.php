<?php

declare(strict_types=1);

namespace core\staff\anticheat;

use pocketmine\Server;

use core\staff\anticheat\session\SessionManager;
use core\staff\anticheat\utils\Devices;

use core\AtPlayer as Player;
use core\AtPlayer;
use core\Core;
use core\discord\objects\Embed;
use core\discord\objects\Field;
use core\discord\objects\Footer;
use core\discord\objects\Post;
use core\discord\objects\Webhook;
use core\user\User;
use core\utils\TextFormat;

use pocketmine\event\Listener;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerItemUseEvent;
use pocketmine\item\FishingRod as ItemFishingRod;
use pocketmine\network\mcpe\protocol\ContainerClosePacket;
use pocketmine\network\mcpe\protocol\InteractPacket;
use pocketmine\network\mcpe\protocol\PlayerAuthInputPacket;
use pocketmine\network\mcpe\protocol\types\PlayerAuthInputFlags;
use prison\PrisonPlayer;
use skyblock\fishing\item\FishingRod;
use skyblock\SkyBlockPlayer;

class AntiCheat implements Listener {
	private static self $i;

	public static array $otherHitReg = [];

	const USER_NAME = TextFormat::BOLD . TextFormat::AQUA . "Hy" . TextFormat::GOLD . "Tech" . TextFormat::AQUA . " AntiCheat" . TextFormat::RESET;

	public function __construct() {
		$thisServer = Core::thisServer();
		self::$i = $this;
		new SessionManager;
		if (in_array($thisServer->getType(), ["idle", "core", "lobby"])) return;
		Server::getInstance()->getPluginManager()->registerEvents($this, Core::getInstance());
	}

	public function onItemUse(PlayerItemUseEvent $event) {
		/** @var AtPlayer */
		$player = $event->getPlayer();
		$session = $player->getAntiCheatSession();
		$item = $event->getItem();

		if ($item instanceof FishingRod || $item instanceof ItemFishingRod) {
			if ($session->isInvOpen() && !$session->justOpenedInv()) {
				$session->fishflag += 1;
				if ($session->fishflag >= 5.01) {
					$dur = $player->getSession()?->getStaff()->getBanManager()->getNextDuration("Cheating") ?? (7 * 86400);
					Core::getInstance()->getStaff()->ban($player, new User(-100, AntiCheat::USER_NAME), "Fishing while in inventory (AutoFish)", $dur);
				}
				$session->flag("AutoFish", [
						"Inventory Open" => "YES"
				]);
			} else {
				$session->fishflag = max(0, $session->fishflag - 1.03);
			}
		}
	}

	public function onPacket(DataPacketReceiveEvent $event) {
		$packet = $event->getPacket();
		/** @var null|AtPlayer $player */
		$player = $event->getOrigin()->getPlayer();
		if ($packet instanceof PlayerAuthInputPacket) {
			$session = $player->getAntiCheatSession();
			if (!is_null($session)) {
				$session->inputMode = $packet->getInputMode();
				$session->time = microtime(true);
				if ($packet->getInputFlags()->get(PlayerAuthInputFlags::SNEAK_DOWN)) $session->setSneaking();
				else $session->setSneaking(false);
			}
		} elseif ($packet instanceof InteractPacket && $packet->action == InteractPacket::ACTION_OPEN_INVENTORY) {
			SessionManager::fetch()->getSessionFor($player)?->openedInv();
		} elseif ($packet instanceof ContainerClosePacket) {
			SessionManager::fetch()->getSessionFor($player)?->closedInv();
		}
	}

	/**
	 * @priority LOWEST
	 */
	public function hitRegistry(EntityDamageEvent $event) {
		if ($event->isCancelled()) return;
		if (in_array($event->getCause(), [
			EntityDamageEvent::CAUSE_FALL,
			EntityDamageEvent::CAUSE_SUFFOCATION,
			EntityDamageEvent::CAUSE_DROWNING
		])) {
			$event->cancel();
			return;
		}
		$entity = $event->getEntity();
		self::$otherHitReg[$entity->getId()] ??= 0;
		$event->setAttackCooldown(0);
		if (!in_array($event->getCause(), [EntityDamageEvent::CAUSE_ENTITY_ATTACK, EntityDamageEvent::CAUSE_PROJECTILE]) && Server::getInstance()->getTick() < self::$otherHitReg[$entity->getId()]) {
			$event->cancel();
			return;
		} elseif (!in_array($event->getCause(), [EntityDamageEvent::CAUSE_ENTITY_ATTACK, EntityDamageEvent::CAUSE_PROJECTILE])) {
			self::$otherHitReg[$entity->getId()] = Server::getInstance()->getTick() + 10;
			/*if ($entity instanceof AtPlayer) {
				$event->cancel();
				$finalDamage = $event->getFinalDamage();
				$entity->processDamageSilent($finalDamage);
			}*/
		}
	}

	/**
	 * @priority LOW
	 */
	public function onDamage(EntityDamageByEntityEvent $event) {
		if ($event->isCancelled()) return;
		$victim = $event->getEntity();
		$attacker = $event->getDamager();
		$attackerSession = null;
		$victimSession = null;

		/* CHECKING SESSIONS AND HIT REGISTRY TIMINGS */
		if ($victim instanceof Player) {
			$victimSession = $victim->getAntiCheatSession();
			if (!$victimSession?->canBeHit()) $event->cancel();
		} elseif ($attacker instanceof Player) {
			$attackerSession = $attacker->getAntiCheatSession();
			if (!$attackerSession?->canHit($victim)) $event->cancel();
		}
		/* */

		/* EXECUTE SESSIONS REGISTERING HITS */
		if ($victim instanceof Player && !$event->isCancelled()) {
			$victimSession?->attacked($attacker);
		} elseif ($attacker instanceof Player && !$event->isCancelled()) {
			$attackerSession?->hit($victim);
		}
		/* */
	}

	public function onJoin(PlayerJoinEvent $event) {
		SessionManager::fetch()->registerSessionFor($event->getPlayer());
	}

	public function onPlayerQuit(PlayerQuitEvent $event): void {
		/** @var AtPlayer */
		$player = $event->getPlayer();
		$player->getAntiCheatSession()?->disconnected();
	}

	/**
	 * @priority LOWEST
	 */
	public function onPreLogin(\pocketmine\event\player\PlayerPreLoginEvent $event) {
		if (in_array($device = $event->getPlayerInfo()->getExtraData()["DeviceOS"], [-1, 5, 6, 8, 9, 10])) { // str_replace("_", " ", Devices::TRANSLATE_FROM[$device])
			$event->setKickFlag(\pocketmine\event\player\PlayerPreLoginEvent::KICK_FLAG_PLUGIN, TextFormat::RED . "You were banned by: " . TextFormat::BOLD . TextFormat::AQUA . "Hy" . TextFormat::GOLD . "Tech " . TextFormat::AQUA . "AntiCheat" . TextFormat::RESET . PHP_EOL . TextFormat::RED . "Reason: DeviceID spoofing attempt caught" . PHP_EOL . "Length: " . TextFormat::YELLOW . date("m-d-Y", time() + (7 * (60 * 60 * 24))));
			$givenGamertag = $event->getPlayerInfo()->getUsername();
			Core::getInstance()->getUserPool()->useUser($givenGamertag, function (User $user) use ($givenGamertag, $device): void {
				if (!$user->valid()) return;
				Core::getInstance()->getStaff()->ban($user, new User(-100, AntiCheat::USER_NAME), "Logged in with fake DeviceID (" . str_replace("_", " ", Devices::TRANSLATE_FROM[$device]) . ")");
			});
			$type = Core::thisServer()->getType();
			$post = new Post("", "AntiCheat - " . Core::getInstance()->getNetwork()->getIdentifier(), "[REDACTED]", false, "", [
				new Embed("", "rich", "**" . $givenGamertag . "** logged in with invalid DeviceID!", "", Core::thisServer()->isTestServer() ? "902CAD" : "FF2900", new Footer("OMG CHEATER 😡"), "", "[REDACTED]", null, [
					new Field("Device", str_replace("_", " ", Devices::TRANSLATE_FROM[$device]), true)
				])
			]);
			$post->setWebhook(Webhook::getWebhookByName("anticheat-" . $type));
			$post->send();
		}
	}

	public static function fetch(): self {
		return self::$i;
	}
}
