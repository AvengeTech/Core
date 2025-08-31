<?php

namespace core\discord;

use core\Core;
use core\discord\command\{
	DiscordSend,
	DiscordForce,
	DiscordCommand,
	DiscordVerify,
	Unlink,
	CommandManager
};
use core\discord\objects\{
	Post,
	Webhook
};
use core\discord\task\UserFromSnowflakeTask;
use core\session\mysqli\data\MySqlQuery;
use core\session\stray\StrayRequest;
use core\user\User;

class Discord {


	public CommandManager $commandManager;
	public ChatQueue $chatQueue;

	public array $snowflakeXuid = [];
	public array $xuidSnowflake = [];

	public static int $ufsId = 0;
	public array $ufs = []; //user from snowflake
	public array $ufsCache = [];

	public function __construct(public Core $plugin) {
		$this->commandManager = new CommandManager();
		$this->chatQueue = new ChatQueue();

		$post = new Post("Server is starting up!", "Network - " . $this->plugin->getNetwork()->getIdentifier());
		$post->setWebhook($this->getConsoleWebhook());
		$post->send();

		$cmdMap = $plugin->getServer()->getCommandMap();
		$cmdMap->registerAll("discord", [
			new DiscordSend($plugin, "discordsend", "Send to Discord (test!)"),
			new Unlink($plugin, "unlink", "Unlink discord account"),
			new DiscordForce($plugin, "discordforce", "Send to Discord command manager (tier 3)"),
			new DiscordCommand($plugin, "discord", "Join at avengetech.net/discord"),
			new DiscordVerify($plugin, "discordverify", "Connect your Discord account!"),
		]);
	}

	public function getCommandManager(): CommandManager {
		return $this->commandManager;
	}

	public function getChatQueue(): ChatQueue {
		return $this->chatQueue;
	}

	public function getConsoleWebhook(string $id = ""): ?Webhook {
		$server = Core::getInstance()->getNetwork()->getThisServer();
		$identifier = $server->isSubServer() ? $server->getParentServer()->getIdentifier() : $server->getIdentifier();
		return Webhook::getWebhookByName($id == "" ? $identifier : $id);
	}

	public function getChatWebhook(): Webhook {
		$server = Core::getInstance()->getNetwork()->getThisServer();
		$identifier = $server->isSubServer() ? $server->getParentServer()->getIdentifier() : $server->getIdentifier();
		return Webhook::getWebhookByName("chat-" . $identifier);
	}

	public function userDataFromSnowflake(int $snowflake, \Closure $closure): void {
		if (isset($this->ufsCache[$snowflake])) {
			$closure($this->ufsCache[$snowflake]);
			return;
		}
		$this->ufs[$id = self::$ufsId++] = $closure;
		Core::getInstance()->getAsyncPool()->submitTask(new UserFromSnowflakeTask($id, $snowflake));
	}

	public function returnUserData(int $taskId, int $snowflake, array $data): void {
		$closure = $this->ufs[$taskId] ?? null;
		$this->ufsCache[$snowflake] = $data;
		if ($closure !== null) {
			unset($this->ufs[$taskId]);
			$closure($data);
		}
	}

	/**
	 * User 2 snowflake
	 */
	public function u2s($user, \Closure $closure): void {
		$xuid = $user instanceof User ? $user->getXuid() : $user;
		Core::getInstance()->getSessionManager()->sendStrayRequest(new StrayRequest("discord_u2s_" . $xuid, new MySqlQuery(
			"main",
			"SELECT snowflake FROM discord_verify WHERE xuid=?",
			[$xuid]
		)), function (StrayRequest $request) use ($closure): void {
			$snowflake = ($request->getQuery()->getResult()->getRows()[0] ?? [])["snowflake"] ?? 0;
			$closure($snowflake);
		});
	}

	/**
	 * Snowflake 2 User
	 */
	public function s2u(int $snowflake, \Closure $closure): void {
		Core::getInstance()->getSessionManager()->sendStrayRequest(new StrayRequest("discord_s2u_" . $snowflake, new MySqlQuery(
			"main",
			"SELECT xuid FROM discord_verify WHERE snowflake=?",
			[$snowflake]
		)), function (StrayRequest $request) use ($closure): void {
			$xuid = ($request->getQuery()->getResult()->getRows()[0] ?? [])["xuid"] ?? 0;
			Core::getInstance()->getUserPool()->useUser($xuid, function (User $user) use ($closure): void {
				$closure($user);
			});
		});
	}
}
