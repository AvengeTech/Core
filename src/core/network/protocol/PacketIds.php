<?php

namespace core\network\protocol;

interface PacketIds {

	//essential: 0-299
	const SERVER_ALIVE = 0;
	const SERVER_GET_PLAYERS = 1;
	const SERVER_GET_ALL_PLAYERS = 2;
	const SERVER_POST_PLAYERS = 3;
	const SERVER_GET_STATUS = 4;
	const SERVER_SET_STATUS = 5;
	const SERVER_WHITELIST = 6;
	const SERVER_ANNOUNCEMENT = 7;
	const SERVER_COMMAND = 8;

	const SERVER_SUB_UPDATE = 15;

	const STAFF_CHAT = 20;
	const STAFF_BAN = 21;
	const STAFF_BAN_IP = 22;
	const STAFF_BAN_DEVICE = 23;
	const STAFF_MUTE = 24;
	const STAFF_WARN = 25;
	const STAFF_ANTICHEAT_NOTICE = 26;
	const STAFF_COMMAND_SEE = 27;

	const PLAYER_MESSAGE = 40;
	const PLAYER_TRANSFER = 41;
	const PLAYER_TRANSFER_COMPLETE = 42;
	const PLAYER_CONNECT = 43;
	const PLAYER_DISCONNECT = 44;
	const PLAYER_SUMMON = 45;
	const PLAYER_CHAT = 46;
	const PLAYER_TELL = 47;
	const PLAYER_SESSION_SAVED = 48;
	const PLAYER_RECONNECT = 49;
	const PLAYER_LOAD_ACTION = 50;

	const DATA_SYNC = 60;

	//skyblock: 300-399
	const SKYBLOCK_ISLAND_INVITE = 300;

	//battles: 400-499

}
