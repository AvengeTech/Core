<?php

namespace core\announce;

use core\{
	Core,
	AtPlayer as Player
};
use pocketmine\utils\SingletonTrait;

class Announce {
	use SingletonTrait;

	public Announcement $announcement;
	public ?\Closure $afterAnnouncement = null;

	public function __construct(public Core $plugin) {
		self::setInstance($this);
		$this->announcement = new Announcement(
			"&4&l&k||&r &l&cAnnouncements &4&k||",
			[
				new SubAnnouncement("&l&bSKYBLOCK SEASON &c2!!", [
					"Season 2 of SkyBlock is now live! Check it out at &bskyblock.avengetech.net",
					"&e50 PERCENT OFF SALE UNTIL JUNE 7TH!!",
					"&bstore.avengetech.net",
				], "5/31/25", "6/7/25"),
				new SubAnnouncement("&a&lSKYBLOCK SEASON &c0!!", [
					"Season ZERO of SkyBlock &aOUT NOW!!!",
					"&e50 PERCENT OFF TIL JULY!! EVERY PURCHASE COMES WITH LIMITED TIME &bBORGOR &eCAPE",
					"&bstore.avengetech.net"
				], "6/20/24", "7/1/24"),
				new SubAnnouncement("&l&bNEW MONTH, &eNEW STUFF!", [
					"Monthly leaderboards have all reset, get to the top of them for a chance to earn &eREAL PRIZES!!",
					"We also reached our monthly donation goal last month, so claim a &eFREE COSMETIC BUNDLE&7 in our store AND a &b40%% off sale &athis week only",
					"&estore.avengetech.net &c(9/1-9/12)",
				], "9/1/22", "9/12/22"),
				new SubAnnouncement("&l&cHappy &f4th of &9July!", [
					//"Sale has been raised to &b75%% off&7 on the 4th of july &e&lONLY",
					"Claim the &bMurica cape&7 along with &c50 Firework lobby gadgets &eFREE&7 from our store for FREE to celebrate! Can be found under &aCosmetics > Limited Time",
					"(Free cosmetic bundle available from &a7/4 &7to &c7/7 &7at &estore.avengetech.net&7)",
				], "7/4/22", "7/7/22"),
				new SubAnnouncement("&l&aBRAND NEW UPDATES!!", [
					"Honey wake up... New AvengeTech update dropped :wow:",
					"Brand new &elobby&7, new &esubscription rank :edplus:&7, new &askyblock season&7, AND &dprison KOTH events!",
					"Check out a full list of changes at &eavengetech.net/discord",
					"&e(ALSO CHECK THE NEW 50%% OFF SALE AT &bSTORE.avengetech.net - Sale duration: 7/1-7/10)",
				], "7/1/22", "7/12/22"),
				/**new SubAnnouncement("&l&2HOLIDAY &cSALE", [
					"Everything in our store is now &e50%% off &aALL MONTH!!!",
					"Take advantage of this offer at &bstore.avengetech.net",
				], "12/1/21", "12/31/22"),*/
				new SubAnnouncement(":confetti: &l&bNEW YEARS SALE&r :confetti:", [
					"Everything in our store is now &e70%% off &aTHIS WEEKEND ONLY!!!",
					"Take advantage of this offer at &bstore.avengetech.net",
				], "1/1/21", "1/3/22"),
				new SubAnnouncement("&l&6THANSKGIVING &eSALE", [
					"Everything in our store is now &e60 percent off &7for a limited time!",
					"Take advantage of this offer at &bstore.avengetech.net",
					"Sale duration: &a11/25&7-&c12/1",
				], "11/30/21", "12/1/21"),
				/**new SubAnnouncement("&l&eAPPLICATIONS NOW OPEN!", [
					"Staff application: &bavengetech.net/staffplz &7(Must be 14+ to apply!)",
					"YouTuber application: &cavengetech.net/ytrankpls &7(Must have at least 250 subscribers)",
					"Good luck to everyone that applies!"
				]),*/
				/**new SubAnnouncement("&e&l&k|||&r &b&lOWNER'S BIRTHDAY SALE &e&l&k|||", [
					"&eSn3ak&7's birthday is this weekend!! (&a9/25&7)",
					"Everything in our store is now &e50 percent off&7 through next week to celebrate! (&a9/25&7-&c10/3&7)",
					"&estore.avengetech.net",
				], "9/25/21", "10/3/21"),
				new SubAnnouncement("&l&bBIRTHDAY EVENT", [
					"Brand new &eprison-event &7server open now for public testing",
					"First 5 players to &eprestige 1&7 get a &l&bBIG PRIZE",
					"Free &estore credits&7, &cranks&7, &bDiscord Nitro&7 and &e&lMORE!!",
					"Check our Discord for all the details: &9avengetech.net/discord"
				], "1/1/20", "1/2/20"),
				new SubAnnouncement("&c&lPOCKETMINE 4", [
					"Our skyblock servers have been updated to support PocketMine API 4!",
					"If you encounter any bugs with this update, &creport them&7 in our &9Discord server",
					"Join at &eavengetech.net/discord",
				]),*/
				/**new SubAnnouncement("&c&lPRISONS: SEASON &e2", [
					"We have finally released our brand new prisons season!",
					"Brand new &ecells&7, &cgangs&7, &bcustom enchantments&7, and &l&aMUCH MORE&r&7! Check it out now!",
				]),
				new SubAnnouncement("&c&l1.17 UPDATE!!", [
					"A big Minecraft update will be releasing on &eJune 8th&7. To continue playing on AvengeTech, &c&lDO NOT&r&7 update! We recommend &6turning off auto updates&7 to prevent this",
					"You will be notified &ein-game&7 and on &9Discord&7 when it's safe to update!",
					"It will take us some time to get our servers running on this major version, so please be patient.",
					"Join our Discord for updates: &eavengetech.net/discord",
				]),*/
				new SubAnnouncement("&a&lSKYBLOCK SEASON &c3!!", [
					"Season 3 of SkyBlock &aOUT NOW!!!",
					"&aSALE EXTENDED!!!",
					"&e60 PERCENT OFF TIL TUESDAY ONLY!!",
					"&bstore.avengetech.net"
				], "4/20/21", "5/5/21"),
				new SubAnnouncement("&e&lSTAFF APPLICATIONS OPEN!!", [
					"Think you have what it takes to be staff?? &aApply now!",
					"&eavengetech.net/staffplz",
					"&c(Must be 14 or older to apply.)"
				], "8/19/21", "9/22/21"),
				new SubAnnouncement("&l&eHAPPY NEW YEAR!!", [
					"Everything in the store is now &e70 percent off! (January 1st)",
					"The sale will go down by &e10 percent&7 until it's over.",
					"Get your goods now at &estore.avengetech.net",
				], "1/1/21", "1/9/21"),
				/**new SubAnnouncement("&a&lCREATIVE BETA (RE)RELEASE!", [
					"Our new beta server is now open to &4&lWITHER &r&7and &9&lENDERDRAGON &r&7ranked players!",
					"Purchase a rank to access it now! Or, you can purchase a &eBeta Whitelist &7under the Other category in our store",
					"&estore.avengetech.net"
				]),*/
				new SubAnnouncement("&e&l&k|||&r &b&lBIRTHDAY SALE &e&l&k|||", [
					"&eSn3ak&7's birthday is this &cFriday&7!! (&a9/25&7)",
					"Everything in our store is now &e50 percent off&7 all weekend!",
					"&estore.avengetech.net",
				], "9/17/20", "9/27/20"),
				new SubAnnouncement("&d&lMARZ &eS&6A&eL&6E &e(BOOSTED)", [
					"Use code &e'marz' &7at the checkout screen of our store to get &a55 PERCENT&7 off your next purchase!!",
					"Marz by Sn3ak on &ball streaming platforms now!",
					"&dhttps://ffm.to/marzz",
					"Sale now going until &eTHE END OF JUNE!",
					"&astore.avengetech.net",
				], "6/5/20", "7/1/20"),
				/**new SubAnnouncement("&e&l1.13 UPDATE PROGRESS", [
					"Prison servers are running, and other servers are being worked on!",
					"Please be patient with us as we work to get this update out. Thank you!"
				]),*/
				/**new SubAnnouncement("&a&lCoronavirus Sale", [
					"Use code '&bcorona&7' for &e20 percent off &7any purchase when checking out in the store all weekend!",
					"Code can only be used &cONE TIME &7per user. (&a3/27&7-&c3/30&7)",
					"&bstore.avengetech.net"
				]),*/
				new SubAnnouncement("&l&bNew month? &aN&eE&aW &eS&aA&eL&eE", [
					"Everything in the store is now &e25 percent off! &7Get your goods now before the sale ends at &estore.avengetech.net",
					"Sale duration: &a8/1&7-&c8/7",
				], "7/31/21", "8/8/21"),
				new SubAnnouncement("&l&dE&5a&ds&5t&de&5r &eS&aA&eL&eE", [
					"Everything in the store is now &e50 percent off! &7Get your goods now before the sale ends at &estore.avengetech.net",
					"Sale duration: &a4/10&7-&c4/13",
				], "4/13/20", "4/13/20"),
				new SubAnnouncement("&e&lTOP VOTER GIVEAWAY", [
					"&e3&7 random players who vote &eEVERY DAY OF THE MONTH &7will receive a &bFREE RANK or rank upgrade&7 at the end of the month!",
					"Vote at &bavengetech.net/vote&7 everyday for a chance to win!",
					"Type &e/topvoters&7 in chat for more info",
				]),
				new SubAnnouncement("&9&lJOIN OUR DISCORD", [
					"&a!!! &7Verified players now have access to a special &bVerified Cape! &7Activate it by typing &e/dc",
					//"Join our &bDiscord Server &7to participate in &eexclusive giveaways &7and to be the first to know about &enew updates!",
					"Type &e/verify &7in chat to learn how to connect your Discord account to your AvengeTech account!",
					"You can join at &eavengetech.net/discord",
					"(P.S. You can also get a free &dNitro Boost&7 cape now by boosting our server!)"
				]),
			]
		);
	}

	public function getAnnouncement(): Announcement {
		return $this->announcement;
	}

	public function getAfterAnnouncementClosure(): ?\Closure {
		return $this->afterAnnouncement;
	}

	public function setAfterAnnouncementClosure(?\Closure $closure = null): void {
		$this->afterAnnouncement = $closure;
	}

	public function onJoin(Player $player): void {
		if (($a = $this->getAnnouncement()) !== null) {
			$a->send($player);
		}
	}
}
