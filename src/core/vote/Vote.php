<?php

namespace core\vote;

use pocketmine\{
	entity\Location,
	entity\Skin,
	lang\Language,
	player\Player,
	Server
};
use pocketmine\block\VanillaBlocks;
use pocketmine\item\VanillaItems;
use pocketmine\console\ConsoleCommandSender;
use pocketmine\scheduler\ClosureTask;
use pocketmine\world\Position;

use core\network\protocol\ServerSubUpdatePacket;
use core\vote\sites\{
	VoteSite,
	Vote1,
};
use core\vote\commands\{
	Vote as VoteCommand,
	TopVoters,
	Winner,

	VotePrizes,
	VoteParty,
	VoteBox,
	SetVotes
};
use core\vote\entity\VoteBox as VoteBoxEntity;
use core\vote\prize\PrizeVendor;

use core\vote\utils\WinnerEntry;
use core\utils\{
    BlockRegistry,
    ItemRegistry,
    TextFormat,
	Utils,
};
use core\Core;
use prison\enchantments\book\RedeemableBook;
use prison\enchantments\EnchantmentData as PrisonED;
use prison\item\Essence as PrisonEssence;
use prison\item\HasteBomb;
use prison\item\MineNuke;
use prison\item\PouchOfEssence as PrisonPouchOfEssence;
use prison\techits\item\TechitNote as PrisonTechitNote;
use skyblock\enchantments\EnchantmentData as SkyBlockED;
use skyblock\enchantments\item\MaxBook;
use skyblock\generators\tile\OreGenerator;
use skyblock\item\Essence as SkyBlockEssence;
use skyblock\item\PouchOfEssence as SkyBlockPouchOfEssence;
use skyblock\techits\item\TechitNote as SkyBlockTechitNote;
use skyblock\pets\item\EnergyBooster;
use skyblock\pets\item\GummyOrb;

class Vote {

	public string $skinDir = "/[REDACTED]/skins/techie.dat";

	const VOTE_PARTY_BASE = 50;

	const STATUS_INACTIVE = 0;
	const STATUS_COUNTDOWN = 1;
	const STATUS_START = 2;

	const STATUS_TIMES = [
		self::STATUS_COUNTDOWN => 60,
		self::STATUS_START => 60
	];

	public array $sites = [];
	public PrizeVendor $vendor;

	public ?VoteBoxEntity $voteBox = null;

	public int $voteCount = 0;

	public array $hasVotedToday = [];

	public int $partyStatus = self::STATUS_INACTIVE;
	public int $partyTimer = 0;
	public int $dropCount = 3;

	public Position $partySpawn;
	public Position $partyDrop;

	public array $partyDrops = [
		"ass" => [],
		"good" => [],
	];

	public function __construct(public Core $plugin) {
		$this->sites = [1 => new Vote1()];
		$this->vendor = new PrizeVendor();

		$ts = Core::getInstance()->getNetwork()->getThisServer();
		$type = $ts->getType();
		if (isset(Structure::VOTE_BOX_LOCATIONS[$type])) {
			$data = Structure::VOTE_BOX_LOCATIONS[$type];
			$world = Server::getInstance()->getWorldManager()->getWorldByName($data["level"]);
			if ($world !== null) {
				$chunk = $world->getChunk((int) $data["x"] >> 4, (int) $data["z"] >> 4);
				if ($chunk === null) {
					$world->loadChunk((int) $data["x"] >> 4, (int) $data["z"] >> 4);
				}
				$box = new VoteBoxEntity(new Location($data["x"], $data["y"], $data["z"], $world, 0, 0), new Skin("Standard_Custom", file_get_contents($this->skinDir), "", "geometry.humanoid.custom"));
				$box->spawnToAll();
				$this->voteBox = $box;
			}
		}

		$plugin->getServer()->getCommandMap()->registerAll("vote", [
			new VoteCommand($this->plugin, "vote", "Claim your vote reward!"),
			new TopVoters($this->plugin, "topvoters", "Check all top voters of the month!"),
			new Winner($this->plugin, "winner", "Check if you won a vote prize last month!"),

			new VotePrizes($this->plugin, "voteprizes", "vp"),
			new VoteParty($this->plugin, "voteparty", "Teleport to vote party area"),
			new VoteBox($this->plugin, "votebox", "vb"),
			new SetVotes($this->plugin, "setvotes", "Manually restore votes (staff)")
		]);

		$this->updateTops();

		Core::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function (): void {
			$this->syncVoteCount();
		}), 10);

		if (isset(Structure::VOTE_PARTY_LOCATIONS[$type])) {
			$data = Structure::VOTE_PARTY_LOCATIONS[$type];
			$spawn = $data["spawn"];
			$drops = $data["drops"];
			$world = Server::getInstance()->getWorldManager()->getWorldByName($data["world"]);
			$this->partySpawn = new Position($spawn["x"], $spawn["y"], $spawn["z"], $world);
			$this->partyDrop = new Position($drops["x"], $drops["y"], $drops["z"], $world);
		}
	}

	public function tick(): void {
		if (!Core::thisServer()->isSubServer() && ($status = $this->getPartyStatus()) !== self::STATUS_INACTIVE) {
			switch ($status) {
				case self::STATUS_COUNTDOWN:
					if ($this->getPartyTimerLeft() <= 0) {
						$this->setDropCount(max(2, floor(count($this->plugin->getServer()->getOnlinePlayers()) / 8)));
						$this->setPartyStatus(self::STATUS_START);
						Core::announceToSS(TextFormat::GI . "Vote party has started! Type " . TextFormat::AQUA . "/vp " . TextFormat::GRAY . "to warp", "raid.horn");
					} elseif ($this->getPartyTimerLeft() <= 5) {
						Core::announceToSS(TextFormat::GI . "Vote party starting in " . $this->getPartyTimerLeft(), "random.click");
					} elseif ($this->getPartyTimerLeft() === 30) {
						Core::announceToSS(TextFormat::GI . "Vote party starting in 30 seconds! " . TextFormat::AQUA . "/vp", "firework.launch");
					}
					break;
				case self::STATUS_START:
					for ($i = 0; $i < $this->getDropCount(); $i++) $this->dropItem(mt_rand(1, 7) === 1);
					if ($this->getPartyTimerLeft() <= 0) {
						$this->setPartyStatus(self::STATUS_INACTIVE);
						Core::announceToSS(TextFormat::GI . "Vote party has ended!");
					}
					break;
			}
		}
	}

	public function setupPrizes(string $type = "lobby"): void {
		switch ($type) {
			case "skyblock": {
					$this->partyDrops["ass"] = [
						ItemRegistry::ENCHANTED_GOLDEN_APPLE(),
						VanillaItems::GOLDEN_APPLE()->setCount(2),
						ItemRegistry::TECHIT_NOTE()->setup("VoteParty", (1000 * mt_rand(1, 3))), // return $this in the techit note setup for prisons and pvp if added back
						ItemRegistry::KEY_NOTE()->setup("Vote Drop Party", "iron", 5),
						ItemRegistry::KEY_NOTE()->setup("Vote Drop Party", "iron", 10),
						ItemRegistry::KEY_NOTE()->setup("Vote Drop Party", "gold", 5),
						ItemRegistry::KEY_NOTE()->setup("Vote Drop Party", "diamond", 5),
						ItemRegistry::KEY_NOTE()->setup("Vote Drop Party", "emerald", 5),
						ItemRegistry::KEY_NOTE()->setup("Vote Drop Party", "vote", 5),
						ItemRegistry::MAX_BOOK()->setup(MaxBook::TYPE_MAX_RARITY, SkyBlockED::RARITY_COMMON, false),
						ItemRegistry::MAX_BOOK()->setup(MaxBook::TYPE_MAX_RARITY, SkyBlockED::RARITY_UNCOMMON, false),
						ItemRegistry::UNBOUND_TOME()->init(50),
						ItemRegistry::SELL_WAND()->init(),
						BlockRegistry::ORE_GENERATOR()->addData(BlockRegistry::ORE_GENERATOR()->asItem(), OreGenerator::TYPE_IRON, 1, 0),
						BlockRegistry::ORE_GENERATOR()->addData(BlockRegistry::ORE_GENERATOR()->asItem(), OreGenerator::TYPE_REDSTONE, 1, 0),
						BlockRegistry::ORE_GENERATOR()->addData(BlockRegistry::ORE_GENERATOR()->asItem(), OreGenerator::TYPE_GOLD, 1, 0),
						BlockRegistry::MOB_SPAWNER()->addData(BlockRegistry::MOB_SPAWNER()->asItem(), 1),
						VanillaItems::DIAMOND()->setCount(32),
						VanillaItems::EMERALD()->setCount(32),
						VanillaItems::ENDER_PEARL()->setCount(8),
						VanillaItems::BLAZE_ROD()->setCount(16),
						ItemRegistry::ESSENCE_OF_SUCCESS()->setup(SkyBlockED::RARITY_RARE, -1, -1, true),
						ItemRegistry::ESSENCE_OF_SUCCESS()->setup(SkyBlockED::RARITY_LEGENDARY, -1, -1, true),
						ItemRegistry::ESSENCE_OF_KNOWLEDGE()->setup(SkyBlockED::RARITY_LEGENDARY, -1, true),
						ItemRegistry::ESSENCE_OF_KNOWLEDGE()->setup(SkyBlockED::RARITY_UNCOMMON, -1, true),
						ItemRegistry::ESSENCE_OF_ASCENSION()->setup(SkyBlockED::RARITY_COMMON),
						ItemRegistry::ESSENCE_OF_ASCENSION()->setup(SkyBlockED::RARITY_UNCOMMON),
						ItemRegistry::ENERGY_BOOSTER(),
						ItemRegistry::GUMMY_ORB()
					];
					$this->partyDrops["good"] = [
						ItemRegistry::ENCHANTED_GOLDEN_APPLE()->setCount(2),
						ItemRegistry::TECHIT_NOTE()->setup("VoteParty", (1000 * mt_rand(5, 10))), // return $this in the techit note setup for prisons and pvp if added back
						ItemRegistry::KEY_NOTE()->setup("Vote Drop Party", "gold", 10),
						ItemRegistry::KEY_NOTE()->setup("Vote Drop Party", "diamond", 10),
						ItemRegistry::KEY_NOTE()->setup("Vote Drop Party", "emerald", 10),
						ItemRegistry::KEY_NOTE()->setup("Vote Drop Party", "divine", 1),
						ItemRegistry::KEY_NOTE()->setup("Vote Drop Party", "vote", 10),
						ItemRegistry::MAX_BOOK()->setup(MaxBook::TYPE_MAX_RARITY, SkyBlockED::RARITY_RARE, false),
						ItemRegistry::MAX_BOOK()->setup(MaxBook::TYPE_MAX_RARITY, SkyBlockED::RARITY_LEGENDARY, false),
						ItemRegistry::MAX_BOOK()->setup(MaxBook::TYPE_MAX_RANDOM_RARITY, -1, false),
						ItemRegistry::UNBOUND_TOME()->init(100),
						BlockRegistry::ORE_GENERATOR()->addData(BlockRegistry::ORE_GENERATOR()->asItem(), OreGenerator::TYPE_LAPIS_LAZULI, 1, 0),
						BlockRegistry::ORE_GENERATOR()->addData(BlockRegistry::ORE_GENERATOR()->asItem(), OreGenerator::TYPE_DIAMOND, 1, 0),
						BlockRegistry::ORE_GENERATOR()->addData(BlockRegistry::ORE_GENERATOR()->asItem(), OreGenerator::TYPE_EMERALD, 1, 0),
						BlockRegistry::AUTOMINER()->addData(BlockRegistry::AUTOMINER()->asItem()),
						ItemRegistry::GEN_BOOSTER()->setup(1000),
						ItemRegistry::GEN_BOOSTER()->setup(5000),
						VanillaBlocks::DIAMOND()->asItem()->setCount(16),
						VanillaBlocks::EMERALD()->asItem()->setCount(16),
						ItemRegistry::ESSENCE_OF_ASCENSION()->setup(SkyBlockED::RARITY_RARE),
						ItemRegistry::ESSENCE_OF_ASCENSION()->setup(SkyBlockED::RARITY_LEGENDARY),
						ItemRegistry::ESSENCE_OF_ASCENSION()->setup(SkyBlockED::RARITY_DIVINE),
						ItemRegistry::ENERGY_BOOSTER(),
						ItemRegistry::GUMMY_ORB()
					];
					break;
				}
			case "prison": {
					$this->partyDrops["ass"] = [
						VanillaItems::ENCHANTED_GOLDEN_APPLE(),
						VanillaItems::GOLDEN_APPLE()->setCount(2),
						ItemRegistry::TECHIT_NOTE()->setup("VoteParty", 1),
						ItemRegistry::KEY_NOTE()->setup("Vote Drop Party", "iron", 5),
						ItemRegistry::KEY_NOTE()->setup("Vote Drop Party", "iron", 10),
						ItemRegistry::KEY_NOTE()->setup("Vote Drop Party", "gold", 5),
						ItemRegistry::KEY_NOTE()->setup("Vote Drop Party", "diamond", 5),
						ItemRegistry::KEY_NOTE()->setup("Vote Drop Party", "emerald", 5),
						ItemRegistry::KEY_NOTE()->setup("Vote Drop Party", "vote", 5),
						ItemRegistry::REDEEMABLE_BOOK()->setup(RedeemableBook::TYPE_MAX_RARITY, PrisonED::RARITY_COMMON, false),
						ItemRegistry::REDEEMABLE_BOOK()->setup(RedeemableBook::TYPE_MAX_RARITY, PrisonED::RARITY_UNCOMMON, false),
						ItemRegistry::UNBOUND_TOME()->init(50),
						ItemRegistry::ESSENCE_OF_SUCCESS()->setup(PrisonED::RARITY_RARE, -1, -1, true),
						ItemRegistry::ESSENCE_OF_SUCCESS()->setup(PrisonED::RARITY_LEGENDARY, -1, -1, true),
						ItemRegistry::ESSENCE_OF_KNOWLEDGE()->setup(PrisonED::RARITY_UNCOMMON, -1, true),
						ItemRegistry::MINE_NUKE(),
						ItemRegistry::HASTE_BOMB(),
						ItemRegistry::POUCH_OF_ESSENCE(),
						VanillaItems::GOLD_INGOT()->setCount(32),
						VanillaItems::IRON_INGOT()->setCount(32),
						VanillaBlocks::DIAMOND()->asItem()->setCount(16),
						VanillaBlocks::EMERALD()->asItem()->setCount(16),
						ItemRegistry::ESSENCE_OF_ASCENSION()->setup(PrisonED::RARITY_COMMON),
						ItemRegistry::ESSENCE_OF_ASCENSION()->setup(PrisonED::RARITY_UNCOMMON),
					];
					$this->partyDrops["good"] = [
						VanillaItems::ENCHANTED_GOLDEN_APPLE()->setCount(2),
						ItemRegistry::TECHIT_NOTE()->setup("VoteParty", 2),
						ItemRegistry::KEY_NOTE()->setup("Vote Drop Party", "gold", 10),
						ItemRegistry::KEY_NOTE()->setup("Vote Drop Party", "diamond", 10),
						ItemRegistry::KEY_NOTE()->setup("Vote Drop Party", "emerald", 10),
						ItemRegistry::KEY_NOTE()->setup("Vote Drop Party", "divine", 1),
						ItemRegistry::KEY_NOTE()->setup("Vote Drop Party", "vote", 10),
						ItemRegistry::REDEEMABLE_BOOK()->setup(RedeemableBook::TYPE_MAX_RARITY, PrisonED::RARITY_RARE, false),
						ItemRegistry::REDEEMABLE_BOOK()->setup(RedeemableBook::TYPE_MAX_RARITY, PrisonED::RARITY_LEGENDARY, false),
						ItemRegistry::REDEEMABLE_BOOK()->setup(RedeemableBook::TYPE_MAX_RARITY, PrisonED::RARITY_DIVINE, false),
						ItemRegistry::REDEEMABLE_BOOK()->setup(RedeemableBook::TYPE_RANDOM_RARITY, -1, true),
						ItemRegistry::UNBOUND_TOME()->init(75),
						ItemRegistry::SALE_BOOSTER()->setup(1.5),
						ItemRegistry::SALE_BOOSTER()->setup(1.2),
						VanillaBlocks::DIAMOND()->asItem()->setCount(32),
						VanillaBlocks::EMERALD()->asItem()->setCount(32),
						VanillaBlocks::EMERALD()->asItem()->setCount(32),
						ItemRegistry::ESSENCE_OF_ASCENSION()->setup(PrisonED::RARITY_RARE),
						ItemRegistry::ESSENCE_OF_ASCENSION()->setup(PrisonED::RARITY_LEGENDARY),
						ItemRegistry::ESSENCE_OF_ASCENSION()->setup(PrisonED::RARITY_DIVINE),
						ItemRegistry::ESSENCE_OF_PROGRESS()->setup(PrisonED::RARITY_LEGENDARY, -1, true),
					];
					break;
				}
			default: {
					break;
			}
		}
	}

	public function getVoteSites(): array {
		return $this->sites;
	}

	public function getVoteSite(int $id): ?VoteSite {
		return $this->sites[$id] ?? null;
	}

	public function getPrizeVendor(): PrizeVendor {
		return $this->vendor;
	}

	public function getVoteBox(): ?VoteBoxEntity {
		return $this->voteBox;
	}

	public function syncVoteCount() : void{
		$servers = [];
		foreach(Core::thisServer()->getSubServers(false, true) as $server){
			$servers[] = $server->getIdentifier();
		}
		(new ServerSubUpdatePacket([
			"server" => $servers,
			"type" => "vote",
			"data" => [
				"type" => "sync"
			]
		]))->queue();
	}

	public function getVoteCount(): int {
		return $this->voteCount;
	}

	public function setVoteCount(int $count, bool $send = false) : void{
		$this->voteCount = $count;

		if($send){
			$servers = [];
			foreach(Core::thisServer()->getSubServers(false, true) as $server){
				$servers[] = $server->getIdentifier();
			}
			(new ServerSubUpdatePacket([
				"server" => $servers,
				"type" => "vote",
				"data" => [
					"type" => "retrieve",
					"count" => $count
				]
			]))->queue();
		}
	}

	public function addVoteCount(bool $send = true) : void{
		$this->setVoteCount($this->getVoteCount() + 1);
		if($send){
			$servers = [];
			foreach(Core::thisServer()->getSubServers(false, true) as $server){
				$servers[] = $server->getIdentifier();
			}
			(new ServerSubUpdatePacket([
				"server" => $servers,
				"type" => "vote",
				"data" => [
					"type" => "count"
				]
			]))->queue();
		}
		if(
			$this->getVoteCount() >= $this->getRequiredVotes() &&
			!Core::thisServer()->isSubServer()
		){
			$this->startParty();
		}
	}

	public function getRequiredVotes() : int{
		return self::VOTE_PARTY_BASE;
	}

	public function getDropCount() : int{
		return $this->dropCount;
	}

	public function setDropCount(int $cnt) : void{
		$this->dropCount = $cnt;
	}

	public function addVote(Player $player): bool {
		$box = $this->getVoteBox();
		if ($box instanceof VoteBoxEntity) {
			$box->showNewTexts([
				TextFormat::YELLOW . "No way.....",
				TextFormat::YELLOW . $player->getName() . TextFormat::AQUA . " just voted!!",
				TextFormat::YELLOW . "They got a ton of free prizes!",
				TextFormat::YELLOW . "Do you want " . TextFormat::RED . "FREE PRIZES" . TextFormat::YELLOW . " too?",
				TextFormat::YELLOW . "Tap me to learn how to vote!"
			]);
			$box->setBusting(5);
		}
		$this->setVotedToday($player);
		$this->addVoteCount();
		if (($count = $this->getVoteCount()) % 10 == 0) {
			$keys = 3;
			switch ($count) {
				case 10:
					$keys = 3;
					break;
				case 20:
					$keys = 4;
					break;
				default:
					$keys = 5;
					break;
			}
			$sender = new ConsoleCommandSender($this->plugin->getServer(), new Language("eng"));
			foreach ([
				"core.staff", "prison.staff", "skyblock.staff",
				"core.tier3", "prison.tier3", "skyblock.tier3",
			] as $permission) {
				$sender->addAttachment($this->plugin, $permission, true);
			}
			Server::getInstance()->dispatchCommand($sender, "keyall emerald " . $keys);
			foreach ($this->plugin->getServer()->getOnlinePlayers() as $player) {
				$player->sendMessage(TextFormat::GI . TextFormat::YELLOW . $count . TextFormat::GRAY . " votes have been received on the server! Everyone has been given " . TextFormat::GREEN . $keys . " Emerald keys" . TextFormat::GRAY . "!");
			}
			return true;
		}
		return false;
	}

	public function updateTops(): void {
		foreach ($this->sites as $id => $page) {
			$page->updateTop();
			$page->updateTop(1);
		}
	}

	public function getWinnerEntry(Player $player): ?WinnerEntry {
		foreach ($this->sites as $site) {
			foreach (($site->winCache[1] ?? []) as $winner) {
				if ($winner->getUser()->getXuid() == $player->getXuid())
					return $winner;
			}
		}
		return null;
	}

	public function tickWinners(): void {
		foreach ($this->sites as $site) {
			$site->getWinners(1, false, function (array $winners): void {
				foreach ($winners as $winner) {
					if (!$winner->hasClaimed()) {
						$player = $winner->getUser()->getPlayer();
						if ($player instanceof Player) {
							$player->sendMessage(TextFormat::BOLD . TextFormat::YELLOW . "[" . TextFormat::OBFUSCATED . str_repeat(TextFormat::GOLD . "|" . TextFormat::RED . "|", 3) . TextFormat::RESET . TextFormat::BOLD . TextFormat::YELLOW . "] " . TextFormat::RESET . TextFormat::AQUA . "You won the vote prize drawing last month! Type " . TextFormat::YELLOW . "/winner " . TextFormat::AQUA . "to see what you won!");
						}
					}
				}
			});
		}
	}

	public function hasVotedToday(Player $player): bool {
		return $this->hasVotedToday[$player->getName()] ?? false;
	}

	public function setVotedToday(Player $player, bool $voted = true): void {
		$this->hasVotedToday[$player->getName()] = $voted;
	}

	public function startParty() : void{
		$this->setVoteCount(0);
		$this->setPartyStatus(self::STATUS_COUNTDOWN);

		Core::announceToSS(TextFormat::GI . "Vote party is starting soon! Type " . TextFormat::AQUA . "/vp " . TextFormat::GRAY . "to warp", "firework.launch");
	}

	public function getPartyStatus() : int{
		return $this->partyStatus;
	}

	public function setPartyStatus(int $status, bool $send = true) : void{
		$this->partyStatus = $status;
		$this->partyTimer = time() + (self::STATUS_TIMES[$status] ?? 0);

		if($send){
			$servers = [];
			foreach(Core::thisServer()->getSubServers(false, true) as $server){
				$servers[] = $server->getIdentifier();
			}
			(new ServerSubUpdatePacket([
				"server" => $servers,
				"type" => "vote",
				"data" => [
					"type" => "party",
					"status" => $status,
					"count" => 0,
				]
			]))->queue();			
		}
	}

	public function getPartyTimerLeft() : int{
		return $this->partyTimer - time();
	}

	public function getPartySpawn() : Position{
		return $this->partySpawn;
	}

	public function getPartyDrop() : Position{
		return $this->partyDrop;
	}

	public function getPartyDrops(bool $better = false) : array{
		return $this->partyDrops[$better ? "good" : "ass"];
	}

	public function dropItem(bool $better = false) : void{
		$world = $this->getPartyDrop()->getWorld();
		$drop = $this->getPartyDrop()->add(mt_rand(-9, 9), 0, mt_rand(-9, 9));
		$items = $this->getPartyDrops($better);
		if (count($items) < 1) return;
		$item = null;

		$recursions = 0;
		while (is_null($item) && $recursions < 5) {
			$item = $items[array_rand($items)];
			$recursions++;
		}
		if (is_null($item)) {
			Utils::dumpVals("Failed to drop VoteParty item!");
			return;
		}

		if(
			$item instanceof MaxBook || 
			$item instanceof RedeemableBook || 
			$item instanceof MineNuke || 
			$item instanceof PrisonEssence || $item instanceof SkyBlockEssence ||
			$item instanceof HasteBomb
		){
			$item->init();
		}

		if($item instanceof PrisonPouchOfEssence || $item instanceof SkyBlockPouchOfEssence){
			$item->setup("Vote Drop Party", mt_rand(1, 4) * 25)->init();
		}elseif($item instanceof PrisonTechitNote || $item instanceof SkyBlockTechitNote){
			if($item->getCount() === 1){
				$item->setup("Vote Drop Party", (1000 * mt_rand(1, 3)));
			}else{
				$item->setup("Vote Drop Party", (1000 * mt_rand(5, 10)));
			}
		}elseif($item instanceof EnergyBooster){
			$item->setup((!$better ? mt_rand(1, 3) : mt_rand(4, 5)))->init();
		}elseif($item instanceof GummyOrb){
			$item->setup((!$better ? mt_rand(1, 3) : mt_rand(4, 5)))->init();
		}

		$world->dropItem($drop, $item);
	}

}