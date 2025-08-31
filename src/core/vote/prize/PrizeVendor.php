<?php

namespace core\vote\prize;

use pocketmine\block\{
	VanillaBlocks
};
use pocketmine\item\{
	VanillaItems
};
use prison\Prison;

use core\Core;
use core\utils\BlockRegistry;
use core\utils\ItemRegistry;
use pocketmine\block\utils\MobHeadType;
use pocketmine\data\bedrock\item\ItemTypeNames;
use prison\enchantments\Enchantments;
use skyblock\generators\tile\OreGenerator;
use skyblock\enchantments\EnchantmentRegistry as SBER;
use skyblock\enchantments\EnchantmentData as SBED;
use skyblock\enchantments\item\MaxBook;

class PrizeVendor{

	public $prizes = [];

	public function __construct() {
		Core::getInstance()->getScheduler()->scheduleDelayedTask(new \pocketmine\scheduler\ClosureTask(function (): void {
			Core::getInstance()->getVote()->getPrizeVendor()->setup();
		}), 40);
	}

	public function setup(): void {
		$servertype = Core::getInstance()->getNetwork()->getServerType();
		$id = Core::getInstance()->getNetwork()->getIdentifier();
		switch ($servertype) {
			case "prison":
				$hb = ItemRegistry::HASTE_BOMB();
				$hb->init();
				$this->prizes["daily"] = new PrizeCluster(0, [
					new PrizeItem("key:vote", 3),
					new PrizeItem("techits", 500),
					new PrizeItem($hb, 2),
					new PrizeItem(VanillaItems::DIAMOND_PICKAXE()),
					new PrizeItem(VanillaItems::DIAMOND_AXE()),
				]);

				$mn = ItemRegistry::MINE_NUKE();
				$mn->init();
				$this->prizes[1] = new PrizeCluster(1, [
					new PrizeItem("techits", 1000),
					new PrizeItem($mn, 2),
				]);

				$da = ItemRegistry::EFFECT_ITEM();
				$nt = ItemRegistry::NAMETAG();
				$nt->init();
				$dt = ItemRegistry::CUSTOM_DEATH_TAG();
				$dt->init();
				$this->prizes[2] = new PrizeCluster(2, [
					//new PrizeItem($da, 1, true, 1),
					new PrizeItem($nt, 1),
					new PrizeItem($dt, 1),
				]);

				$book = ItemRegistry::REDEEMED_BOOK();
				$this->prizes[3] = new PrizeCluster(3, [
					new PrizeItem("kit:enderdragon"),
					new PrizeItem($book, 1, true, 1),
				]);

				$this->prizes[4] = new PrizeCluster(4, [
					new PrizeItem("key:vote", 5),
					new PrizeItem("xp", 25),
				]);

				$eff = ItemRegistry::REDEEMED_BOOK();
				$ench = Enchantments::EFFICIENCY();
				$ench->setStoredLevel(2);
				$eff->setup($ench);
				$exp = ItemRegistry::REDEEMED_BOOK();
				$ench = Enchantments::EXPLOSIVE();
				$ench->setStoredLevel(1);
				$exp->setup($ench);
				$this->prizes[5] = new PrizeCluster(5, [
					new PrizeItem($eff, 1),
					new PrizeItem($exp, 1),
					new PrizeItem(VanillaItems::DIAMOND_PICKAXE(), 1),
				]);

				$this->prizes[6] = new PrizeCluster(6, [
					new PrizeItem("techits", 15000),
				]);

				$hb = ItemRegistry::HASTE_BOMB();
				$hb->init();
				$this->prizes[7] = new PrizeCluster(7, [
					new PrizeItem($hb, 3),
					new PrizeItem("key:gold", 10),
					new PrizeItem($book, 1, true, 1),
					new PrizeItem($book, 1, true, 2),
				]);

				$this->prizes[8] = new PrizeCluster(8, [
					new PrizeItem($mn, 3),
					new PrizeItem($hb, 1),
				]);

				$tr = ItemRegistry::REDEEMED_BOOK();
				$ench = Enchantments::TRANSFUSION();
				$ench->setStoredLevel(2);
				$tr->setup($ench);
				$sb = ItemRegistry::SALE_BOOSTER();
				$sb->setup(1.5);
				$this->prizes[9] = new PrizeCluster(9, [
					new PrizeItem($sb, 1),
					new PrizeItem($tr, 1),
				]);

				$kp = ItemRegistry::REDEEMED_BOOK();
				$ench = Enchantments::KEYPLUS();
				$ench->setStoredLevel(1);
				$kp->setup($ench);
				$this->prizes[10] = new PrizeCluster(10, [
					new PrizeItem("xp", 30),
					new PrizeItem($kp, 1),
				]);

				$this->prizes[11] = new PrizeCluster(11, [
					new PrizeItem($da, 2, true, 2),
				]);

				$kab = ItemRegistry::REDEEMED_BOOK();
				$ench = Enchantments::KABOOM();
				$ench->setStoredLevel(2);
				$kab->setup($ench);
				$this->prizes[12] = new PrizeCluster(12, [
					new PrizeItem("tag:random", 1, true),
					new PrizeItem($kab, 1),
				]);

				$this->prizes[13] = new PrizeCluster(13, [
					new PrizeItem(VanillaItems::ENCHANTED_GOLDEN_APPLE(), 8),
				]);

				$sb = ItemRegistry::SALE_BOOSTER();
				$sb->setup(2);
				$this->prizes[14] = new PrizeCluster(14, [
					new PrizeItem("techits", 20000),
					new PrizeItem($sb, 1),
				]);

				$this->prizes[15] = new PrizeCluster(15, [
					new PrizeItem(VanillaBlocks::END_ROD()->asItem(), 5),
				]);

				$hb = ItemRegistry::HASTE_BOMB();
				$hb->init();
				$this->prizes[16] = new PrizeCluster(16, [
					new PrizeItem($hb, 3),
					new PrizeItem("xp", 50),
					new PrizeItem("key:iron", 20),
					new PrizeItem("key:diamond", 15),
				]);

				$unb = ItemRegistry::REDEEMED_BOOK();
				$ench = Enchantments::UNBREAKING();
				$ench->setStoredLevel(5);
				$unb->setup($ench);
				$exp = ItemRegistry::REDEEMED_BOOK();
				$ench = Enchantments::EXPLOSIVE();
				$ench->setStoredLevel(2);
				$exp->setup($ench);
				$xpm = ItemRegistry::REDEEMED_BOOK();
				$ench = Enchantments::XP_MAGNET();
				$ench->setStoredLevel(1);
				$xpm->setup($ench);
				$this->prizes[17] = new PrizeCluster(17, [
					new PrizeItem($dt, 5),
					new PrizeItem($unb, 1),
					new PrizeItem($exp, 1),
					new PrizeItem($xpm, 1),
				]);

				$kab = ItemRegistry::REDEEMED_BOOK();
				$ench = Enchantments::KABOOM();
				$ench->setStoredLevel(2);
				$kab->setup($ench);
				$zeus = ItemRegistry::REDEEMED_BOOK();
				$ench = Enchantments::ZEUS();
				$ench->setStoredLevel(2);
				$zeus->setup($ench);
				$this->prizes[18] = new PrizeCluster(18, [
					new PrizeItem("techits", 30000),
					new PrizeItem($kab, 1),
					new PrizeItem($zeus, 1),
				]);

				$ench = ItemRegistry::REDEEMED_BOOK();
				$ench2 = ItemRegistry::REDEEMED_BOOK();
				$ench3 = ItemRegistry::REDEEMED_BOOK();
				$this->prizes[19] = new PrizeCluster(19, [
					new PrizeItem($ench, 4, true, 1),
					new PrizeItem($ench, 3, true, 2),
					new PrizeItem($ench, 3, true, 3),
				]);

				$this->prizes[20] = new PrizeCluster(20, [
					new PrizeItem("key:all", 10),
					new PrizeItem(VanillaItems::ENCHANTED_GOLDEN_APPLE(), 16),
				]);

				$this->prizes[21] = new PrizeCluster(21, [
					new PrizeItem("tag:random", 5, true),
					new PrizeItem("techits", 35000),
				]);

				$this->prizes[22] = new PrizeCluster(22, [
					new PrizeItem("techits", 35000),
					new PrizeItem($da, 1, true, 3),
				]);

				$gears = ItemRegistry::REDEEMED_BOOK();
				$ench = Enchantments::GEARS();
				$ench->setStoredLevel(2);
				$gears->setup($ench);
				$bun = ItemRegistry::REDEEMED_BOOK();
				$ench = Enchantments::BUNNY();
				$ench->setStoredLevel(2);
				$bun->setup($ench);
				$this->prizes[23] = new PrizeCluster(23, [
					new PrizeItem($gears, 1),
					new PrizeItem($bun, 1),
				]);

				$prot = ItemRegistry::REDEEMED_BOOK();
				$ench = Enchantments::PROTECTION();
				$ench->setStoredLevel(4);
				$prot->setup($ench);
				$this->prizes[24] = new PrizeCluster(24, [
					new PrizeItem($prot, 1),
				]);

				$sb = ItemRegistry::SALE_BOOSTER();
				$sb->setup(1.2);
				$eff = ItemRegistry::REDEEMED_BOOK();
				$ench = Enchantments::EFFICIENCY();
				$ench->setStoredLevel(5);
				$eff->setup($ench);
				$this->prizes[25] = new PrizeCluster(25, [
					new PrizeItem($mn, 7),
					new PrizeItem($sb, 7),
					new PrizeItem($eff, 1),
				]);

				$this->prizes[26] = new PrizeCluster(26, [
					new PrizeItem("key:all", 25)
				]);

				$this->prizes[27] = new PrizeCluster(27, [
					new PrizeItem("techits", 100000)
				]);

				$bp = ItemRegistry::REDEEMED_BOOK();
				$ench = Enchantments::BLAST_PROTECTION();
				$ench->setStoredLevel(4);
				$bp->setup($ench);
				$sw = ItemRegistry::REDEEMED_BOOK();
				$ench = Enchantments::SHOCKWAVE();
				$ench->setStoredLevel(2);
				$sw->setup($ench);
				$ov = ItemRegistry::REDEEMED_BOOK();
				$ench = Enchantments::OVERLORD();
				$ench->setStoredLevel(2);
				$ov->setup($ench);
				$this->prizes[28] = new PrizeCluster(28, [
					new PrizeItem(VanillaItems::ENCHANTED_GOLDEN_APPLE(), 16),
					new PrizeItem($bp, 1),
					new PrizeItem($sw, 1),
					new PrizeItem($ov, 1),
				]);

				$exp = ItemRegistry::REDEEMED_BOOK();
				$ench = Enchantments::EXPLOSIVE();
				$ench->setStoredLevel(3);
				$exp->setup($ench);
				$om = ItemRegistry::REDEEMED_BOOK();
				$ench = Enchantments::ORE_MAGNET();
				$ench->setStoredLevel(3);
				$om->setup($ench);
				$tr = ItemRegistry::REDEEMED_BOOK();
				$ench = Enchantments::TRANSFUSION();
				$ench->setStoredLevel(3);
				$tr->setup($ench);
				$kp = ItemRegistry::REDEEMED_BOOK();
				$ench = Enchantments::KEYPLUS();
				$ench->setStoredLevel(1);
				$kp->setup($ench);
				$this->prizes[29] = new PrizeCluster(29, [
					new PrizeItem("key:all", 35),
					new PrizeItem($exp, 1),
					new PrizeItem($om, 1),
					new PrizeItem($tr, 1),
					new PrizeItem($kp, 1),
				]);

				$this->prizes[30] = new PrizeCluster(30, [
					new PrizeItem("techits", 400000),
					new PrizeItem("key:vote", 30),
					new PrizeItem($hb, 8),
					new PrizeItem(VanillaItems::ENCHANTED_GOLDEN_APPLE(), 32),
				]);

				$sb = ItemRegistry::SALE_BOOSTER();
				$sb->setup(2);
				$this->prizes["end"] = new PrizeCluster(31, [
					new PrizeItem("key:divine", 1),
					new PrizeItem(ItemRegistry::ELYTRA(), 1, false, -1, "Elytra"),
					new PrizeItem($hb, 16),
					new PrizeItem($mn, 16),
					new PrizeItem($sb, 4),
				]);
				break;

			case "skyblock":
				if (stristr(Core::thisServer()->getIdentifier(), "archive") !== false) return;
				$wand = ItemRegistry::SELL_WAND();
				$wand->init();

				$this->prizes["daily"] = new PrizeCluster(0, [
					new PrizeItem("key:vote", 3),
					new PrizeItem("techits", 750),
					new PrizeItem($wand, 3),
					new PrizeItem(VanillaItems::DIAMOND_PICKAXE()),
					new PrizeItem(VanillaItems::DIAMOND_AXE()),
					new PrizeItem('command:giveinstantopen "{player}" 10', 1, false, -1, "10 minutes of instant crate opening"),
				]);

				$this->prizes[1] = new PrizeCluster(1, [
					new PrizeItem("techits", 1000)
				]);

				$this->prizes[2] = new PrizeCluster(2, [
					new PrizeItem(BlockRegistry::ORE_GENERATOR()->addData(BlockRegistry::ORE_GENERATOR()->asItem(), OreGenerator::TYPE_COAL, 1, 0))
				]);

				$this->prizes[3] = new PrizeCluster(3, [
					new PrizeItem("kit:enderdragon", 1),
				]);

				$this->prizes[4] = new PrizeCluster(4, [
					new PrizeItem("key:iron", 5),
					new PrizeItem("key:gold", 3),
					new PrizeItem("key:diamond", 2),
					new PrizeItem("key:emerald", 1),
				]);

				$er = ItemRegistry::UNBOUND_TOME();
				$er->init(mt_rand(40, 60));

				$this->prizes[5] = new PrizeCluster(5, [
					new PrizeItem(BlockRegistry::ORE_GENERATOR()->addData(BlockRegistry::ORE_GENERATOR()->asItem(), OreGenerator::TYPE_COAL, 1, 0), 2),
					new PrizeItem($er),
				]);

				$this->prizes[6] = new PrizeCluster(6, [
					new PrizeItem(BlockRegistry::MOB_SPAWNER()->addData(BlockRegistry::MOB_SPAWNER()->asItem(), 1), 1)
				]);

				$this->prizes[7] = new PrizeCluster(7, [
					new PrizeItem("techits", 5000)
				]);

				$this->prizes[8] = new PrizeCluster(8, [
					new PrizeItem("key:iron", 10),
					new PrizeItem("key:gold", 6),
					new PrizeItem("key:diamond", 4),
					new PrizeItem("key:emerald", 2),
				]);

				$this->prizes[9] = new PrizeCluster(9, []);
				$this->prizes[9]->addItem(new PrizeItem(BlockRegistry::ORE_GENERATOR()->addData(BlockRegistry::ORE_GENERATOR()->asItem(), OreGenerator::TYPE_COAL, 1, 0), 2));
				$this->prizes[9]->addItem(new PrizeItem(BlockRegistry::ORE_GENERATOR()->addData(BlockRegistry::ORE_GENERATOR()->asItem(), OreGenerator::TYPE_IRON, 1, 0), 2));

				$this->prizes[10] = new PrizeCluster(10, [
					new PrizeItem("key:iron", 10),
					new PrizeItem("key:gold", 6),
					new PrizeItem("key:diamond", 4),
					new PrizeItem("key:emerald", 2),
				]);

				$this->prizes[11] = new PrizeCluster(11, [
					new PrizeItem(BlockRegistry::ORE_GENERATOR()->addData(BlockRegistry::ORE_GENERATOR()->asItem(), OreGenerator::TYPE_LAPIS_LAZULI, 1, 0), 1),
					new PrizeItem("techits", 7500)
				]);

				$book = ItemRegistry::MAX_BOOK();
				$book->setup(MaxBook::TYPE_MAX_RARITY, SBED::RARITY_COMMON);
				$book2 = ItemRegistry::MAX_BOOK();
				$book2->setup(MaxBook::TYPE_MAX_RARITY, SBED::RARITY_UNCOMMON);
				$this->prizes[12] = new PrizeCluster(12, [
					new PrizeItem($book, 2, true, 1),
					new PrizeItem($book2, 2, true, 2)
				]);

				$this->prizes[13] = new PrizeCluster(13, [
					new PrizeItem(VanillaBlocks::HOPPER()->asItem(), 64),
				]);

				$this->prizes[14] = new PrizeCluster(14, [
					new PrizeItem("techits", 10000)
				]);

				$er = ItemRegistry::UNBOUND_TOME();
				$er->init(mt_rand(70, 90));
				$this->prizes[15] = new PrizeCluster(15, [
					new PrizeItem(VanillaBlocks::NETHER_REACTOR_CORE()->asItem(), 1),
					new PrizeItem(VanillaBlocks::BELL()->asItem(), 1),
					new PrizeItem($er),
				]);

				$this->prizes[16] = new PrizeCluster(16, [
					new PrizeItem(BlockRegistry::MOB_SPAWNER()->addData(BlockRegistry::MOB_SPAWNER()->asItem(), 7), 1)
				]);
				$this->prizes[16]->addItem(new PrizeItem(BlockRegistry::ORE_GENERATOR()->addData(BlockRegistry::ORE_GENERATOR()->asItem(), OreGenerator::TYPE_COAL, 1, 0), 2));
				$this->prizes[16]->addItem(new PrizeItem(BlockRegistry::ORE_GENERATOR()->addData(BlockRegistry::ORE_GENERATOR()->asItem(), OreGenerator::TYPE_IRON, 1, 0), 1));
				$this->prizes[16]->addItem(new PrizeItem(BlockRegistry::ORE_GENERATOR()->addData(BlockRegistry::ORE_GENERATOR()->asItem(), OreGenerator::TYPE_REDSTONE, 1, 0), 1));

				$this->prizes[17] = new PrizeCluster(17, [
					new PrizeItem("tag:random", 5, true)
				]);

				$this->prizes[18] = new PrizeCluster(18, [
					new PrizeItem("techits", 10000),
					new PrizeItem(BlockRegistry::ORE_GENERATOR()->addData(BlockRegistry::ORE_GENERATOR()->asItem(), OreGenerator::TYPE_OBSIDIAN, 1, 0), 1)
				]);

				$book = ItemRegistry::MAX_BOOK(); //1);
				$book->setup(MaxBook::TYPE_MAX_RARITY, SBED::RARITY_COMMON);
				$book2 = ItemRegistry::MAX_BOOK(); //2);
				$book2->setup(MaxBook::TYPE_MAX_RARITY, SBED::RARITY_UNCOMMON);
				$book3 = ItemRegistry::MAX_BOOK(); //3);
				$book3->setup(MaxBook::TYPE_MAX_RARITY, SBED::RARITY_RARE);
				$this->prizes[19] = new PrizeCluster(19, [
					new PrizeItem($book, 3, true, 1),
					new PrizeItem($book2, 2, true, 2),
					new PrizeItem($book3, 2, true, 3),
				]);
				$this->prizes[20] = new PrizeCluster(20, [
					new PrizeItem("key:iron", 15),
					new PrizeItem("key:gold", 10),
					new PrizeItem("key:diamond", 8),
					new PrizeItem("key:emerald", 6),
				]);
				$this->prizes[21] = new PrizeCluster(21, [
					new PrizeItem("techits", 50000)
				]);

				$db = BlockRegistry::DIMENSIONAL_BLOCK();
				$db->addData(1, 0, $dbi = $db->asItem());
				$this->prizes[22] = new PrizeCluster(22, [
					new PrizeItem($dbi, 2)
				]);

				$this->prizes[23] = new PrizeCluster(23);
				$this->prizes[23]->addItem(new PrizeItem(BlockRegistry::ORE_GENERATOR()->addData(BlockRegistry::ORE_GENERATOR()->asItem(), OreGenerator::TYPE_COAL, 1, 0), 2));
				$this->prizes[23]->addItem(new PrizeItem(BlockRegistry::ORE_GENERATOR()->addData(BlockRegistry::ORE_GENERATOR()->asItem(), OreGenerator::TYPE_IRON, 1, 0), 2));
				$this->prizes[23]->addItem(new PrizeItem(BlockRegistry::ORE_GENERATOR()->addData(BlockRegistry::ORE_GENERATOR()->asItem(), OreGenerator::TYPE_REDSTONE, 1, 0), 2));
				$this->prizes[23]->addItem(new PrizeItem(BlockRegistry::ORE_GENERATOR()->addData(BlockRegistry::ORE_GENERATOR()->asItem(), OreGenerator::TYPE_LAPIS_LAZULI, 1, 0), 2));
				$this->prizes[23]->addItem(new PrizeItem(BlockRegistry::ORE_GENERATOR()->addData(BlockRegistry::ORE_GENERATOR()->asItem(), OreGenerator::TYPE_DIAMOND, 1, 0), 2));
				$this->prizes[23]->addItem(new PrizeItem(BlockRegistry::ORE_GENERATOR()->addData(BlockRegistry::ORE_GENERATOR()->asItem(), OreGenerator::TYPE_EMERALD, 1, 0), 2));

				$this->prizes[24] = new PrizeCluster(24, [
					new PrizeItem("key:diamond", 12)
				]);

				$er = ItemRegistry::UNBOUND_TOME();
				$er->init(mt_rand(70, 90));

				$book = ItemRegistry::MAX_BOOK(); //1);
				$book->setup(MaxBook::TYPE_MAX_RARITY, SBED::RARITY_COMMON);
				$book2 = ItemRegistry::MAX_BOOK(); //2);
				$book2->setup(MaxBook::TYPE_MAX_RARITY, SBED::RARITY_UNCOMMON);
				$book3 = ItemRegistry::MAX_BOOK(); //3);
				$book3->setup(MaxBook::TYPE_MAX_RARITY, SBED::RARITY_RARE);
				$book4 = ItemRegistry::MAX_BOOK(); //4);
				$book4->setup(MaxBook::TYPE_MAX_RARITY, SBED::RARITY_LEGENDARY);
				$this->prizes[25] = new PrizeCluster(25, [
					new PrizeItem(VanillaBlocks::MOB_HEAD()->setMobHeadType(MobHeadType::CREEPER())->asItem(), 1, false, -1, "Creeper Head"),
					new PrizeItem($book, 5, true, 1),
					new PrizeItem($book2, 4, true, 2),
					new PrizeItem($book3, 3, true, 3),
					new PrizeItem($book4, 3, true, 4),
					new PrizeItem($er, 2),
				]);
				$this->prizes[26] = new PrizeCluster(26, [
					new PrizeItem("key:iron", 25),
					new PrizeItem("key:gold", 20),
					new PrizeItem("key:diamond", 15),
					new PrizeItem("key:emerald", 10),
				]);

				$this->prizes[27] = new PrizeCluster(27, [
					new PrizeItem("techits", 200000),
					new PrizeItem(BlockRegistry::MOB_SPAWNER()->addData(BlockRegistry::MOB_SPAWNER()->asItem(), 8), 1)
				]);

				$this->prizes[28] = new PrizeCluster(28, [
					new PrizeItem("techits", 200000)
				]);
				$this->prizes[28]->addItem(new PrizeItem(BlockRegistry::ORE_GENERATOR()->addData(BlockRegistry::ORE_GENERATOR()->asItem(), OreGenerator::TYPE_COAL, 1, 0), 3));
				$this->prizes[28]->addItem(new PrizeItem(BlockRegistry::ORE_GENERATOR()->addData(BlockRegistry::ORE_GENERATOR()->asItem(), OreGenerator::TYPE_IRON, 1, 0), 3));
				$this->prizes[28]->addItem(new PrizeItem(BlockRegistry::ORE_GENERATOR()->addData(BlockRegistry::ORE_GENERATOR()->asItem(), OreGenerator::TYPE_REDSTONE, 1, 0), 3));

				$this->prizes[29] = new PrizeCluster(29, [
					new PrizeItem("key:emerald", 20)
				]);
				$this->prizes[29]->addItem(new PrizeItem(BlockRegistry::ORE_GENERATOR()->addData(BlockRegistry::ORE_GENERATOR()->asItem(), OreGenerator::TYPE_COAL, 1, 0), 3));
				$this->prizes[29]->addItem(new PrizeItem(BlockRegistry::ORE_GENERATOR()->addData(BlockRegistry::ORE_GENERATOR()->asItem(), OreGenerator::TYPE_IRON, 1, 0), 3));
				$this->prizes[29]->addItem(new PrizeItem(BlockRegistry::ORE_GENERATOR()->addData(BlockRegistry::ORE_GENERATOR()->asItem(), OreGenerator::TYPE_REDSTONE, 1, 0), 3));
				$this->prizes[29]->addItem(new PrizeItem(BlockRegistry::ORE_GENERATOR()->addData(BlockRegistry::ORE_GENERATOR()->asItem(), OreGenerator::TYPE_LAPIS_LAZULI, 1, 0), 3));
				$this->prizes[29]->addItem(new PrizeItem(BlockRegistry::ORE_GENERATOR()->addData(BlockRegistry::ORE_GENERATOR()->asItem(), OreGenerator::TYPE_DIAMOND, 1, 0), 2));
				$this->prizes[29]->addItem(new PrizeItem(BlockRegistry::ORE_GENERATOR()->addData(BlockRegistry::ORE_GENERATOR()->asItem(), OreGenerator::TYPE_EMERALD, 1, 0), 2));
				$this->prizes[29]->addItem(new PrizeItem(BlockRegistry::ORE_GENERATOR()->addData(BlockRegistry::ORE_GENERATOR()->asItem(), OreGenerator::TYPE_OBSIDIAN, 1, 0), 2));

				$am = BlockRegistry::AUTOMINER();
				$am->addData($ami = $am->asItem());
				$this->prizes[30] = new PrizeCluster(30, [
					new PrizeItem("key:iron", 50),
					new PrizeItem("key:gold", 40),
					new PrizeItem("key:diamond", 35),
					new PrizeItem("key:emerald", 30),
					new PrizeItem($ami, 10),
				]);

				$er = ItemRegistry::UNBOUND_TOME();
				$er->init(100);

				$this->prizes["end"] = new PrizeCluster(31, [
					new PrizeItem(BlockRegistry::ORE_GENERATOR()->addData(BlockRegistry::ORE_GENERATOR()->asItem(), OreGenerator::TYPE_GLOWING_OBSIDIAN, 1, 0), 5),
					new PrizeItem(VanillaBlocks::MOB_HEAD()->setMobHeadType(MobHeadType::DRAGON())->asItem(), 1, false, -1, "Enderdragon Head"),
					new PrizeItem(VanillaBlocks::BELL()->asItem(), 1),
					new PrizeItem("techits", 500000),
					new PrizeItem($er, 2),
				]);

				for ($i = 5; $i <= 30; $i += 5) {
					$this->prizes[$i]->addItem(new PrizeItem($wand, 16));
				}

				//foreach($this->prizes as $key => $prize){
				//if($key !== "daily"){
				//$this->prizes[$key] = new PrizeCluster($prize->getDay(), []); //for first few days
				//}
				//}
				break;

			default:
		}
		$cosmetic_prizes = [
			"daily" => [
				new PrizeItem("shards", 100)
			],
			5 => [
				new PrizeItem("lootbox", 5)
			],
			10 => [
				new PrizeItem("lootbox", 5)
			],
			12 => [
				new PrizeItem("shards", 5000)
			],
			15 => [
				new PrizeItem("lootbox", 5)
			],
			20 => [
				new PrizeItem("shards", 20000)
			],
			25 => [
				new PrizeItem("lootbox", 5)
			],
			"end" => [
				new PrizeItem("lootbox", 15),
			],
		];
		foreach ($cosmetic_prizes as $key => $prizes) {
			if (isset($this->prizes[$key])) {
				foreach ($prizes as $prize) {
					$this->prizes[$key]?->addItem($prize);
				}
			}
		}
		echo count($this->prizes) . " prizes setup!", PHP_EOL;
	}

	public function getPrizes(): array {
		return $this->prizes;
	}

	public function getPrizeFor(string|int $day): ?PrizeCluster {
		return $this->prizes[$day] ?? null;
	}

	public function getDailyPrize(): ?PrizeCluster {
		return $this->getPrizeFor("daily");
	}

	public function getLastDayPrize(): ?PrizeCluster {
		return $this->getPrizeFor("end");
	}
}
