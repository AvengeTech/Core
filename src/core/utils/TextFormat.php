<?php

namespace core\utils;

use pocketmine\utils\TextFormat as TextFormatPM;

class TextFormat extends TextFormatPM{

	const DARK_YELLOW = self::ESCAPE . "g";

	const GI = self::GREEN . self::BOLD . "(!) " . self::RESET . self::GRAY;
	const GN = self::GREEN . self::BOLD . "(i) " . self::RESET . self::GRAY;

	const YI = self::YELLOW . self::BOLD . "(!) " . self::RESET . self::GRAY;
	const YN = self::YELLOW . self::BOLD . "(i) " . self::RESET . self::GRAY;

	const RI = self::RED . self::BOLD . "(!) " . self::RESET . self::GRAY;
	const RN = self::RED . self::BOLD . "(i) " . self::RESET . self::GRAY;

	const PI = self::LIGHT_PURPLE . self::BOLD . "(!) " . self::RESET . self::GRAY;
	const PN = self::LIGHT_PURPLE . self::BOLD . "(i) " . self::RESET . self::GRAY;

	const ICON_FOOD = "";
	const ICON_ARMOR = "";
	const ICON_MINECOIN = "";
	const ICON_TOKEN = "";

	const ICON_ENDERMITE = "";
	const ICON_BLAZE = "";
	const ICON_GHAST = "";
	const ICON_ENDERMAN = "";
	const ICON_WITHER = "";
	const ICON_ENDERDRAGON = "";
	const ICON_WARDEN = "";

	const ICON_YOUTUBE = "";

	const ICON_DEV = "";
	const ICON_BUILDER = "";
	const ICON_ARTIST = "";

	const ICON_TRAINEE = "";
	const ICON_JR_MOD = "";
	const ICON_MOD = "";
	const ICON_SR_MOD = "";
	const ICON_HEAD_MOD = "";
	const ICON_MANAGER = "";
	const ICON_OWNER = "";

	const ICON_AVENGETECH = "";

	const EMOJI_MONEY_BAG = "";
	const EMOJI_FIRE = "";
	const EMOJI_EYE = "";
	const EMOJI_MOUTH = "";
	const EMOJI_STAR = "";
	const EMOJI_DRIPS = "";
	const EMOJI_DROP = "";
	const EMOJI_TROPHY = "";
	const EMOJI_SALT = "";
	const EMOJI_OK_HAND = "";
	const EMOJI_GOTEM = "";
	const EMOJI_SUN = "";
	const EMOJI_MOON = "";
	const EMOJI_RAINBOW = "";
	const EMOJI_LIGHTNING = "";
	const EMOJI_EARTH = "";
	const EMOJI_EXPLOSION = "";
	const EMOJI_WAVES = "";
	const EMOJI_CAKE = "";
	const EMOJI_CHEESE = "";
	const EMOJI_BURGER = "";
	const EMOJI_FRIES = "";
	const EMOJI_SODA = "";
	const EMOJI_LOCK = "";
	const EMOJI_UNLOCK = "";
	const EMOJI_100 = "";
	const EMOJI_CHECKMARK = "";
	const EMOJI_X = "";
	const EMOJI_CONFETTI = "";

	const EMOJI_SMILE = "";
	const EMOJI_FROWN = "";
	const EMOJI_SIDE = "";
	const EMOJI_TEAR = "";
	const EMOJI_CRY = "";
	const EMOJI_LAUGH = "";
	const EMOJI_SMIRK = "";
	const EMOJI_SHH = "";
	const EMOJI_COOL = "";
	const EMOJI_DEVIL_HAPPY = "";
	const EMOJI_DEVIL_MAD = "";
	const EMOJI_SKULL = "";
	const EMOJI_CROSSBONES = "";
	const EMOJI_HEART_RED = "";
	const EMOJI_HEART_WHITE = "";
	const EMOJI_HEART_BLUE = "";
	const EMOJI_HEART_YELLOW = "";
	const EMOJI_HEART_PURPLE = "";
	const EMOJI_HEART_BLACK = "";

	//emoji update 1
	const EMOJI_WINK = "";
	const EMOJI_LAUGH_CRY = "";
	const EMOJI_CUTE = "";
	const EMOJI_MAD = "";
	const EMOJI_RED_MAD = "";
	const EMOJI_HAPPY = "";
	const EMOJI_HAPPIER = "";
	const EMOJI_GRIMACE = "";
	const EMOJI_DISAPPOINTED = "";
	const EMOJI_STRAIGHT_FACE = "";
	const EMOJI_COLD = "";
	const EMOJI_KISS = "";
	const EMOJI_CLOWN = "";
	const EMOJI_STRAIGHT_EYES = "";
	const EMOJI_MINDBLOWN = "";
	const EMOJI_WEARY = "";
	const EMOJI_CONCERNED = "";
	const EMOJI_DEAD = "";
	const EMOJI_BLUSH = "";
	const EMOJI_COWBOY = "";
	const EMOJI_VOMIT = "";
	const EMOJI_WOW = "";
	const EMOJI_HEART_EYES = "";
	const EMOJI_POOP = "";
	const EMOJI_CAT = "";
	const EMOJI_DOG = "";

	const EMOJI_ROSE = "";
	const EMOJI_SUNFLOWER = "";
	const EMOJI_SCISSORS = "";
	const EMOJI_QUESTION = "";
	const EMOJI_EXCLAMATION = "";
	const EMOJI_ARROW_UP = "";
	const EMOJI_ARROW_DOWN = "";
	const EMOJI_ARROW_RIGHT = "";
	const EMOJI_ARROW_LEFT = "";
	const EMOJI_ARROW_UP_LEFT = "";
	const EMOJI_ARROW_DOWN_LEFT = "";
	const EMOJI_ARROW_UP_RIGHT = "";
	const EMOJI_ARROW_DOWN_RIGHT = "";
	const EMOJI_CAP = "";
	const EMOJI_MUSIC = "";
	const EMOJI_NEGATIVE = "";
	const EMOJI_DENIED = "";
	const EMOJI_SPARKLES = "";

	const EMOJI_DOLLAR_SIGN = "";
	const EMOJI_WINGED_MONEY = "";
	const EMOJI_CAUTION = "";
	const EMOJI_BELL = "";
	const EMOJI_LIGHTBULB = "";
	const EMOJI_HOURGLASS_FULL = "";
	const EMOJI_HOURGLASS_EMPTY = "";
	const EMOJI_PLUG = "";
	const EMOJI_MAIL_OPEN = "";
	const EMOJI_MAIL_CLOSED = "";
	const EMOJI_MAILBOX = "";
	const EMOJI_8_BALL = "";
	const EMOJI_SNOWFLAKE = "";
	const EMOJI_EYES = "";
	const EMOJI_BLUE_EYE = "";
	const EMOJI_BRAIN = "";
	const EMOJI_WAVE = "";
	const EMOJI_FIST_LEFT = "";
	const EMOJI_FIST_RIGHT = "";
	const EMOJI_FIST_UP = "";
	const EMOJI_HAND_UP = "";
	const EMOJI_THUMBS_UP = "";
	const EMOJI_THUMBS_DOWN = "";
	const EMOJI_TECHIE = "";
	const EMOJI_PEACE_OUT = "";
	const EMOJI_PEACE_SIGN = "";
	const EMOJI_ROCK = "";
	const EMOJI_PAPER = "";
	const EMOJI_EAR_RIGHT = "";
	const EMOJI_EAR_LEFT = "";
	const EMOJI_COOKIE = "";
	const EMOJI_DONUT = "";
	const EMOJI_LOLLIPOP = "";
	const EMOJI_APPLE = "";
	const EMOJI_LEMON = "";
	const EMOJI_PEAR = "";
	const EMOJI_CHERRY = "";
	const EMOJI_PIZZA = "";
	const EMOJI_MUSHROOM = "";
	const EMOJI_CLOVER = "";
	const EMOJI_TREE = "";

	// INPUT MODES
	const EMOJI_CONTROLLER = "";
	const EMOJI_KEYBOARD = "";
	const EMOJI_TOUCH = "";
}
