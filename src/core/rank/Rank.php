<?php

namespace core\rank;

use core\Core;
use core\rank\commands\{
	AddSub,
	ChatEffects,
	SetRank,
	RankUpgrade,
	Redeem,
	AddRedeem,
	JoinMessage
};
use core\session\mysqli\data\{
	MySqlQuery,
	MySqlRequest
};
use core\user\User;
use core\utils\TextFormat;

class Rank {

	const HIERARCHY_STAFF = Structure::RANK_HIERARCHY['trainee'];
	const HIERARCHY_JR_MOD = Structure::RANK_HIERARCHY['jr_mod'];
	const HIERARCHY_MOD = Structure::RANK_HIERARCHY['mod'];
	const HIERARCHY_SR_MOD = Structure::RANK_HIERARCHY['sr_mod'];
	const HIERARCHY_HEAD_MOD = Structure::RANK_HIERARCHY['head_mod'];
	const HIERARCHY_MANAGER = Structure::RANK_HIERARCHY['manager'];
	const HIERARCHY_OWNER = Structure::RANK_HIERARCHY['owner'];

	const HIERARCHY_BUILDER = Structure::RANK_HIERARCHY['builder'];
 	const HIERARCHY_DEVELOPER = Structure::RANK_HIERARCHY['developer'];
 	const HIERARCHY_ARTIST = Structure::RANK_HIERARCHY['artist'];

	const NAME_COLORS = [
		-1 => TextFormat::YELLOW,
		0 => TextFormat::RED,
		1 => TextFormat::GOLD,
		2 => TextFormat::GREEN,
		3 => TextFormat::AQUA,
		4 => TextFormat::LIGHT_PURPLE,
		5 => TextFormat::DARK_PURPLE,
		6 => TextFormat::WHITE
	];

	public Redeemer $redeemer;

	public function __construct(public Core $plugin) {
		$this->redeemer = new Redeemer($this);

		$map = $plugin->getServer()->getCommandMap();
		$map->registerAll("rank", [
			new AddSub($plugin, "addsub", "Add to a player's rank subscription!"),
			new ChatEffects($plugin, "chateffects", "Chat effects! (Warden)"),
			new SetRank($plugin, "setrank", "Set a player's rank"),
			new RankUpgrade($plugin, "rankupgrade", "Upgrade player to next rank"),
			new Redeem($plugin, "redeem", "Claim a redeem code"),
			new AddRedeem($plugin, "addredeem", "Add a redeem code"),
			new JoinMessage($plugin, "joinmessage", "Toggle your join message (Ranked)"),
		]);
	}

	public function getRedeemer(): Redeemer {
		return $this->redeemer;
	}

	public static function validRank(string $rank): bool {
		return isset(Structure::RANK_HIERARCHY[$rank]);
	}

	public function userByNick(string $nick, \Closure $onCompletion): void {
		Core::getInstance()->getSessionManager()->sendStrayRequest(new MySqlRequest(
			"user_by_nick_" . $nick,
			new MySqlQuery("main", "SELECT xuid FROM nicknames WHERE nick=?", [$nick])
		), function (MySqlRequest $request) use ($onCompletion): void {
			$rows = $request->getQuery()->getResult()->getRows();
			if (count($rows) > 0) {
				$data = array_shift($rows);
				Core::getInstance()->getUserPool()->useUser($data["xuid"], function (User $user) use ($onCompletion): void {
					$onCompletion($user);
				});
			} else {
				$onCompletion(null);
			}
		});
	}
}
