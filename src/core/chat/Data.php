<?php

namespace core\chat;

use pocketmine\utils\TextFormat;

class Data {

	public array $chat_formats = [
		"default" => [
			"chat" => TextFormat::YELLOW . "{NAME}: " . TextFormat::GRAY . "{MESSAGE}",
			"chat_ranked" => "{RANK} {NAMECOLOR}{NAME}: " . TextFormat::GRAY . "{MESSAGE}",
			"nametag" => TextFormat::YELLOW . "{NAME}",
			"nametag_ranked" => "{RANK} {NAMECOLOR}{NAME}",
		],

		"prison" => [
			"chat" => "{PRISON_RANK} {TAG}" . TextFormat::YELLOW . "{NAME}: " . TextFormat::GRAY . "{MESSAGE}",
			"chat_ranked" => "{RANK} {PRISON_RANK} {TAG}{NAMECOLOR}{NAME}: " . TextFormat::GRAY . "{MESSAGE}",
			"nametag" => "{PRISON_RANK} {TAG}" . TextFormat::YELLOW . "{NAME}{PVP}{BOUNTY}",
			"nametag_ranked" => "{RANK} {PRISON_RANK} {TAG}{NAMECOLOR}{NAME}{PVP}{BOUNTY}",
		],

		"skyblock" => [
			"chat" => "{TAG}" . TextFormat::YELLOW . "{NAME}: " . TextFormat::GRAY . "{MESSAGE}",
			"chat_ranked" => "{RANK} {TAG}{NAMECOLOR}{NAME}: " . TextFormat::GRAY . "{MESSAGE}",
			"nametag" => "{TAG}" . TextFormat::YELLOW . "{NAME}{PVP}",
			"nametag_ranked" => "{RANK} {TAG}{NAMECOLOR}{NAME}{PVP}",
		],

		"pvp" => [
			"chat" => "{LEVEL}{TAG}" . TextFormat::YELLOW . "{NAME}: " . TextFormat::GRAY . "{MESSAGE}",
			"chat_ranked" => "{RANK} {LEVEL}{TAG}{NAMECOLOR}{NAME}: " . TextFormat::GRAY . "{MESSAGE}",
			"nametag" => "{LEVEL}{TAG}" . TextFormat::YELLOW . "{NAME}",
			"nametag_ranked" => "{RANK} {LEVEL}{TAG}{NAMECOLOR}{NAME}",
		],
		// add {FACTION} but its like a faction tag 3-4 letters with the member ranking(**, *, +, -)
		"faction" => [
			"chat" => "{TAG}" . TextFormat::YELLOW . "{NAME}: " . TextFormat::GRAY . "{MESSAGE}",
			"chat_ranked" => "{RANK} {TAG}{NAMECOLOR}{NAME}: " . TextFormat::GRAY . "{MESSAGE}",
			"nametag" => "{TAG}" . TextFormat::YELLOW . "{NAME}",
			"nametag_ranked" => "{RANK} {TAG}{NAMECOLOR}{NAME}",
		],

	];
}
