<?php

namespace core\chat\emoji;

use core\utils\TextFormat;

class EmojiLibrary {

	const TYPE_DEFAULT = 0;
	const TYPE_AVENGETECH = 1;
	const TYPE_FACE = 2;
	const TYPE_HAND = 3;
	const TYPE_BODY_PART = 4;
	const TYPE_NATURE = 5;
	const TYPE_FOOD = 6;
	const TYPE_ICON = 7;

	const TYPE_NAMES = [
		self::TYPE_DEFAULT => "Default",
		self::TYPE_AVENGETECH => "AvengeTech",
		self::TYPE_FACE => "Face",
		self::TYPE_HAND => "Hand",
		self::TYPE_BODY_PART => "Body Part",
		self::TYPE_NATURE => "Nature",
		self::TYPE_FOOD => "Food",
		self::TYPE_ICON => "Icon",
	];

	const EMOJIS = [
		":food:" => [
			"icon" => TextFormat::ICON_FOOD,
			"type" => self::TYPE_DEFAULT
		],
		":armor:" => [
			"icon" => TextFormat::ICON_ARMOR,
			"type" => self::TYPE_DEFAULT
		],
		":minecoin:" => [
			"icon" => TextFormat::ICON_MINECOIN,
			"type" => self::TYPE_DEFAULT
		],
		":token:" => [
			"icon" => TextFormat::ICON_TOKEN,
			"type" => self::TYPE_DEFAULT
		],

		":endermite:" => [
			"icon" => TextFormat::ICON_ENDERMITE,
			"type" => self::TYPE_AVENGETECH
		],
		":blaze:" => [
			"icon" => TextFormat::ICON_BLAZE,
			"type" => self::TYPE_AVENGETECH
		],
		":ghast:" => [
			"icon" => TextFormat::ICON_GHAST,
			"type" => self::TYPE_AVENGETECH
		],
		":enderman:" => [
			"icon" => TextFormat::ICON_ENDERMAN,
			"type" => self::TYPE_AVENGETECH
		],
		":wither:" => [
			"icon" => TextFormat::ICON_WITHER,
			"type" => self::TYPE_AVENGETECH
		],
		":enderdragon:" => [
			"icon" => TextFormat::ICON_ENDERDRAGON,
			"type" => self::TYPE_AVENGETECH,
			"alias" => [":ed:"],
		],
		":warden:" => [
			"icon" => TextFormat::ICON_WARDEN,
			"type" => self::TYPE_AVENGETECH,
		],
		":youtube:" => [
			"icon" => TextFormat::ICON_YOUTUBE,
			"type" => self::TYPE_AVENGETECH,
			"alias" => [":yt:"],
		],

		":developer:" => [
			"icon" => TextFormat::ICON_DEV,
			"type" => self::TYPE_AVENGETECH,
			"alias" => [":dev:"]
		],
		":builder:" => [
			"icon" => TextFormat::ICON_BUILDER,
			"type" => self::TYPE_AVENGETECH,
		],
		":artist:" => [
			"icon" => TextFormat::ICON_ARTIST,
			"type" => self::TYPE_AVENGETECH
		],

		":trainee:" => [
			"icon" => TextFormat::ICON_TRAINEE,
			"type" => self::TYPE_AVENGETECH
		],
		":jr_mod:" => [
			"icon" => TextFormat::ICON_JR_MOD,
			"type" => self::TYPE_AVENGETECH
		],
		":mod:" => [
			"icon" => TextFormat::ICON_MOD,
			"type" => self::TYPE_AVENGETECH
		],
		":sr_mod:" => [
			"icon" => TextFormat::ICON_SR_MOD,
			"type" => self::TYPE_AVENGETECH
		],
		":head_mod:" => [
			"icon" => TextFormat::ICON_HEAD_MOD,
			"type" => self::TYPE_AVENGETECH
		],
		":manager:" => [
			"icon" => TextFormat::ICON_MANAGER,
			"type" => self::TYPE_AVENGETECH
		],
		":owner:" => [
			"icon" => TextFormat::ICON_OWNER,
			"type" => self::TYPE_AVENGETECH
		],

		":avengetech:" => [
			"icon" => TextFormat::ICON_AVENGETECH,
			"type" => self::TYPE_AVENGETECH,
			"alias" => [":at:"],
		],

		":money_bag:" => [
			"icon" => TextFormat::EMOJI_MONEY_BAG,
			"type" => self::TYPE_ICON,
		],
		":fire:" => [
			"icon" => TextFormat::EMOJI_FIRE,
			"type" => self::TYPE_NATURE,
		],
		":eye:" => [
			"icon" => TextFormat::EMOJI_EYE,
			"type" => self::TYPE_BODY_PART,
		],
		":mouth:" => [
			"icon" => TextFormat::EMOJI_MOUTH,
			"type" => self::TYPE_BODY_PART,
		],
		":star:" => [
			"icon" => TextFormat::EMOJI_STAR,
			"type" => self::TYPE_NATURE,
		],
		":drips:" => [
			"icon" => TextFormat::EMOJI_DRIPS,
			"type" => self::TYPE_NATURE,
		],
		":drop:" => [
			"icon" => TextFormat::EMOJI_DROP,
			"type" => self::TYPE_NATURE,
		],
		":trophy:" => [
			"icon" => TextFormat::EMOJI_TROPHY,
			"type" => self::TYPE_ICON,
		],
		":salt:" => [
			"icon" => TextFormat::EMOJI_SALT,
			"type" => self::TYPE_FOOD,
		],
		":ok_hand:" => [
			"icon" => TextFormat::EMOJI_OK_HAND,
			"type" => self::TYPE_HAND,
			"alias" => [":ok:"]
		],
		":gotem:" => [
			"icon" => TextFormat::EMOJI_GOTEM,
			"type" => self::TYPE_HAND,
			"alias" => [":gottem:"]
		],

		":sun:" => [
			"icon" => TextFormat::EMOJI_SUN,
			"type" => self::TYPE_NATURE,
		],
		":moon:" => [
			"icon" => TextFormat::EMOJI_MOON,
			"type" => self::TYPE_NATURE,
		],
		":rainbow:" => [
			"icon" => TextFormat::EMOJI_RAINBOW,
			"type" => self::TYPE_NATURE,
		],
		":lightning:" => [
			"icon" => TextFormat::EMOJI_LIGHTNING,
			"type" => self::TYPE_NATURE,
		],
		":earth:" => [
			"icon" => TextFormat::EMOJI_EARTH,
			"type" => self::TYPE_NATURE,
		],
		":explosion:" => [
			"icon" => TextFormat::EMOJI_EXPLOSION,
			"type" => self::TYPE_NATURE,
		],
		":waves:" => [
			"icon" => TextFormat::EMOJI_WAVES,
			"type" => self::TYPE_NATURE,
		],

		":cake:" => [
			"icon" => TextFormat::EMOJI_CAKE,
			"type" => self::TYPE_FOOD,
		],
		":cheese:" => [
			"icon" => TextFormat::EMOJI_CHEESE,
			"type" => self::TYPE_FOOD,
		],
		":burger:" => [
			"icon" => TextFormat::EMOJI_BURGER,
			"type" => self::TYPE_FOOD,
		],
		":fries:" => [
			"icon" => TextFormat::EMOJI_FRIES,
			"type" => self::TYPE_FOOD,
		],
		":soda:" => [
			"icon" => TextFormat::EMOJI_SODA,
			"type" => self::TYPE_FOOD,
		],

		":lock:" => [
			"icon" => TextFormat::EMOJI_LOCK,
			"type" => self::TYPE_ICON,
		],
		":unlock:" => [
			"icon" => TextFormat::EMOJI_UNLOCK,
			"type" => self::TYPE_ICON,
		],
		":100:" => [
			"icon" => TextFormat::EMOJI_100,
			"type" => self::TYPE_ICON,
		],
		":checkmark:" => [
			"icon" => TextFormat::EMOJI_CHECKMARK,
			"type" => self::TYPE_ICON,
			"alias" => [":check:"]
		],
		":x:" => [
			"icon" => TextFormat::EMOJI_X,
			"type" => self::TYPE_ICON,
		],
		":confetti:" => [
			"icon" => TextFormat::EMOJI_CONFETTI,
			"type" => self::TYPE_ICON,
		],

		":smile:" => [
			"icon" => TextFormat::EMOJI_SMILE,
			"type" => self::TYPE_FACE,
		],
		":frown:" => [
			"icon" => TextFormat::EMOJI_FROWN,
			"type" => self::TYPE_FACE,
		],
		":side:" => [
			"icon" => TextFormat::EMOJI_SIDE,
			"type" => self::TYPE_FACE,
		],
		":tear:" => [
			"icon" => TextFormat::EMOJI_TEAR,
			"type" => self::TYPE_FACE,
			"alias" => [":sad:"]
		],
		":cry:" => [
			"icon" => TextFormat::EMOJI_CRY,
			"type" => self::TYPE_FACE,
			"alias" => [":sob:"]
		],
		":laugh:" => [
			"icon" => TextFormat::EMOJI_LAUGH,
			"type" => self::TYPE_FACE,
		],
		":smirk:" => [
			"icon" => TextFormat::EMOJI_SMIRK,
			"type" => self::TYPE_FACE,
		],
		":shh:" => [
			"icon" => TextFormat::EMOJI_SHH,
			"type" => self::TYPE_FACE,
		],
		":cool:" => [
			"icon" => TextFormat::EMOJI_COOL,
			"type" => self::TYPE_FACE,
		],
		":devil_happy:" => [
			"icon" => TextFormat::EMOJI_DEVIL_HAPPY,
			"type" => self::TYPE_FACE,
			"alias" => [":devil:"]
		],
		":devil_mad:" => [
			"icon" => TextFormat::EMOJI_DEVIL_MAD,
			"type" => self::TYPE_FACE,
		],
		":skull:" => [
			"icon" => TextFormat::EMOJI_SKULL,
			"type" => self::TYPE_FACE,
		],
		":crossbones:" => [
			"icon" => TextFormat::EMOJI_CROSSBONES,
			"type" => self::TYPE_FACE,
		],

		":heart_red:" => [
			"icon" => TextFormat::EMOJI_HEART_RED,
			"type" => self::TYPE_ICON,
			"alias" => [":heart:"]
		],
		":heart_white:" => [
			"icon" => TextFormat::EMOJI_HEART_WHITE,
			"type" => self::TYPE_ICON,
		],
		":heart_blue:" => [
			"icon" => TextFormat::EMOJI_HEART_BLUE,
			"type" => self::TYPE_ICON,
		],
		":heart_yellow:" => [
			"icon" => TextFormat::EMOJI_HEART_YELLOW,
			"type" => self::TYPE_ICON,
		],
		":heart_purple:" => [
			"icon" => TextFormat::EMOJI_HEART_PURPLE,
			"type" => self::TYPE_ICON,
		],
		":heart_black:" => [
			"icon" => TextFormat::EMOJI_HEART_BLACK,
			"type" => self::TYPE_ICON,
		],

		//update 1 emojis
		":wink:" => [
			"icon" => TextFormat::EMOJI_WINK,
			"type" => self::TYPE_FACE,
		],
		":laugh_cry:" => [
			"icon" => TextFormat::EMOJI_LAUGH_CRY,
			"type" => self::TYPE_FACE,
			"alias" => [":joy:"]
		],
		":cute:" => [
			"icon" => TextFormat::EMOJI_CUTE,
			"type" => self::TYPE_FACE,
		],
		":mad:" => [
			"icon" => TextFormat::EMOJI_MAD,
			"type" => self::TYPE_FACE,
		],
		":red_mad:" => [
			"icon" => TextFormat::EMOJI_RED_MAD,
			"type" => self::TYPE_FACE,
			"alias" => [":redmad:", ":rmad:"]
		],
		":happy:" => [
			"icon" => TextFormat::EMOJI_HAPPY,
			"type" => self::TYPE_FACE,
		],
		":happier:" => [
			"icon" => TextFormat::EMOJI_HAPPIER,
			"type" => self::TYPE_FACE,
		],
		":grimace:" => [
			"icon" => TextFormat::EMOJI_GRIMACE,
			"type" => self::TYPE_FACE,
		],
		":disappointed:" => [
			"icon" => TextFormat::EMOJI_DISAPPOINTED,
			"type" => self::TYPE_FACE,
		],
		":straight_face:" => [
			"icon" => TextFormat::EMOJI_STRAIGHT_FACE,
			"type" => self::TYPE_FACE,
			"alias" => [":strface:"]
		],
		":cold:" => [
			"icon" => TextFormat::EMOJI_COLD,
			"type" => self::TYPE_FACE,
			"alias" => [":brr:"]
		],
		":kiss:" => [
			"icon" => TextFormat::EMOJI_KISS,
			"type" => self::TYPE_FACE,
		],
		":clown:" => [
			"icon" => TextFormat::EMOJI_CLOWN,
			"type" => self::TYPE_FACE,
			"alias" => [":bozo:"]
		],
		":straight_eyes:" => [
			"icon" => TextFormat::EMOJI_STRAIGHT_EYES,
			"type" => self::TYPE_FACE,
			"alias" => [":streyes:"],
		],
		":mindblown:" => [
			"icon" => TextFormat::EMOJI_MINDBLOWN,
			"type" => self::TYPE_FACE,
		],
		":weary:" => [
			"icon" => TextFormat::EMOJI_WEARY,
			"type" => self::TYPE_FACE,
		],
		":concerned:" => [
			"icon" => TextFormat::EMOJI_CONCERNED,
			"type" => self::TYPE_FACE,
		],
		":dead:" => [
			"icon" => TextFormat::EMOJI_DEAD,
			"type" => self::TYPE_FACE,
		],
		":blush:" => [
			"icon" => TextFormat::EMOJI_BLUSH,
			"type" => self::TYPE_FACE,
		],
		":cowboy:" => [
			"icon" => TextFormat::EMOJI_COWBOY,
			"type" => self::TYPE_FACE,
			"alias" => [":yeehaw:"]
		],
		":vomit:" => [
			"icon" => TextFormat::EMOJI_VOMIT,
			"type" => self::TYPE_FACE,
		],
		":wow:" => [
			"icon" => TextFormat::EMOJI_WOW,
			"type" => self::TYPE_FACE,
		],
		":heart_eyes:" => [
			"icon" => TextFormat::EMOJI_HEART_EYES,
			"type" => self::TYPE_FACE,
			"alias" => [":heyes:"]
		],
		":poop:" => [
			"icon" => TextFormat::EMOJI_POOP,
			"type" => self::TYPE_FACE,
			"alias" => [":stinky:"]
		],
		":cat:" => [
			"icon" => TextFormat::EMOJI_CAT,
			"type" => self::TYPE_FACE,
			"alias" => [":meow:"]
		],
		":dog:" => [
			"icon" => TextFormat::EMOJI_DOG,
			"type" => self::TYPE_FACE,
			"alias" => [":woof:"]
		],

		":rose:" => [
			"icon" => TextFormat::EMOJI_ROSE,
			"type" => self::TYPE_NATURE,
		],
		":sunflower:" => [
			"icon" => TextFormat::EMOJI_SUNFLOWER,
			"type" => self::TYPE_NATURE,
		],
		":scissors:" => [
			"icon" => TextFormat::EMOJI_SCISSORS,
			"type" => self::TYPE_ICON,
		],
		":question:" => [
			"icon" => TextFormat::EMOJI_QUESTION,
			"type" => self::TYPE_ICON,
			"alias" => [":?:"]
		],
		":exclamation:" => [
			"icon" => TextFormat::EMOJI_EXCLAMATION,
			"type" => self::TYPE_ICON,
			"alias" => [":!:"]
		],
		":arrow_up:" => [
			"icon" => TextFormat::EMOJI_ARROW_UP,
			"type" => self::TYPE_ICON,
			"alias" => [":up:"]
		],
		":arrow_down:" => [
			"icon" => TextFormat::EMOJI_ARROW_DOWN,
			"type" => self::TYPE_ICON,
			"alias" => [":down:"]
		],
		":arrow_right:" => [
			"icon" => TextFormat::EMOJI_ARROW_RIGHT,
			"type" => self::TYPE_ICON,
			"alias" => [":right:"]
		],
		":arrow_left:" => [
			"icon" => TextFormat::EMOJI_ARROW_LEFT,
			"type" => self::TYPE_ICON,
			"alias" => [":left:"]
		],
		":arrow_up_left:" => [
			"icon" => TextFormat::EMOJI_ARROW_UP_LEFT,
			"type" => self::TYPE_ICON,
			"alias" => [":upleft:", ":aul:"]
		],
		":arrow_down_left:" => [
			"icon" => TextFormat::EMOJI_ARROW_DOWN_LEFT,
			"type" => self::TYPE_ICON,
			"alias" => [":downleft:", ":adl:"]
		],
		":arrow_up_right:" => [
			"icon" => TextFormat::EMOJI_ARROW_UP_RIGHT,
			"type" => self::TYPE_ICON,
			"alias" => [":upright:", ":aur:"]
		],
		":arrow_down_right:" => [
			"icon" => TextFormat::EMOJI_ARROW_DOWN_RIGHT,
			"type" => self::TYPE_ICON,
			"alias" => [":downright:", ":adr:"]
		],
		":cap:" => [
			"icon" => TextFormat::EMOJI_CAP,
			"type" => self::TYPE_ICON,
		],
		":music:" => [
			"icon" => TextFormat::EMOJI_MUSIC,
			"type" => self::TYPE_ICON,
			"alias" => [":note:"]
		],
		":negative:" => [
			"icon" => TextFormat::EMOJI_NEGATIVE,
			"type" => self::TYPE_ICON,
		],
		":denied:" => [
			"icon" => TextFormat::EMOJI_DENIED,
			"type" => self::TYPE_ICON,
		],
		":sparkles:" => [
			"icon" => TextFormat::EMOJI_SPARKLES,
			"type" => self::TYPE_ICON,
		],

		":dollar_sign:" => [
			"icon" => TextFormat::EMOJI_DOLLAR_SIGN,
			"type" => self::TYPE_ICON,
			"alias" => [":$:"]
		],
		":winged_money:" => [
			"icon" => TextFormat::EMOJI_WINGED_MONEY,
			"type" => self::TYPE_ICON,
			"alias" => [":flydolla:"]
		],
		":caution:" => [
			"icon" => TextFormat::EMOJI_CAUTION,
			"type" => self::TYPE_ICON,
		],
		":bell:" => [
			"icon" => TextFormat::EMOJI_BELL,
			"type" => self::TYPE_ICON,
		],
		":lightbulb:" => [
			"icon" => TextFormat::EMOJI_LIGHTBULB,
			"type" => self::TYPE_ICON,
		],

		":hourglass_full:" => [
			"icon" => TextFormat::EMOJI_HOURGLASS_FULL,
			"type" => self::TYPE_ICON,
			"alias" => [":hgf:"]
		],
		":hourglass_empty:" => [
			"icon" => TextFormat::EMOJI_HOURGLASS_EMPTY,
			"type" => self::TYPE_ICON,
			"alias" => [":hge:"]
		],
		":plug:" => [
			"icon" => TextFormat::EMOJI_PLUG,
			"type" => self::TYPE_ICON,
		],
		":mail_open:" => [
			"icon" => TextFormat::EMOJI_MAIL_OPEN,
			"type" => self::TYPE_ICON,
			"alias" => [":mailo:"]
		],
		":mail_closed:" => [
			"icon" => TextFormat::EMOJI_MAIL_CLOSED,
			"type" => self::TYPE_ICON,
			"alias" => [":mail:"]
		],
		":mailbox:" => [
			"icon" => TextFormat::EMOJI_MAILBOX,
			"type" => self::TYPE_ICON,
		],
		":8ball:" => [
			"icon" => TextFormat::EMOJI_8_BALL,
			"type" => self::TYPE_ICON,
		],
		":snowflake:" => [
			"icon" => TextFormat::EMOJI_SNOWFLAKE,
			"type" => self::TYPE_NATURE,
		],
		":controller:" => [
			"icon" => TextFormat::EMOJI_CONTROLLER,
			"type" => self::TYPE_ICON,
		],
		":eyes:" => [
			"icon" => TextFormat::EMOJI_EYES,
			"type" => self::TYPE_BODY_PART,
		],
		":blue_eye:" => [
			"icon" => TextFormat::EMOJI_BLUE_EYE,
			"type" => self::TYPE_BODY_PART,
			"alias" => [":eyeb:"]
		],
		":brain:" => [
			"icon" => TextFormat::EMOJI_BRAIN,
			"type" => self::TYPE_BODY_PART,
		],
		":wave:" => [
			"icon" => TextFormat::EMOJI_WAVE,
			"type" => self::TYPE_HAND,
		],
		":fist_left:" => [
			"icon" => TextFormat::EMOJI_FIST_LEFT,
			"type" => self::TYPE_HAND,
			"alias" => [":fistl:"]
		],
		":fist_right:" => [
			"icon" => TextFormat::EMOJI_FIST_RIGHT,
			"type" => self::TYPE_HAND,
			"alias" => [":fistr:"]
		],
		":fist_up:" => [
			"icon" => TextFormat::EMOJI_FIST_UP,
			"type" => self::TYPE_HAND,
			"alias" => [":fistup:"]
		],
		":hand_up:" => [
			"icon" => TextFormat::EMOJI_HAND_UP,
			"type" => self::TYPE_HAND,
			"alias" => [":handup:"]
		],
		":thumbs_up:" => [
			"icon" => TextFormat::EMOJI_THUMBS_UP,
			"type" => self::TYPE_HAND,
			"alias" => [":thumbsup:", ":tup:"]
		],
		":thumbs_down:" => [
			"icon" => TextFormat::EMOJI_THUMBS_DOWN,
			"type" => self::TYPE_HAND,
			"alias" => [":thumbsdown:", ":tdown:"]
		],
		":techie:" => [
			"icon" => TextFormat::EMOJI_TECHIE,
			"type" => self::TYPE_AVENGETECH
		],
		":peace_out:" => [
			"icon" => TextFormat::EMOJI_PEACE_OUT,
			"type" => self::TYPE_HAND,
			"alias" => [":peace:"]
		],
		":peace_sign:" => [
			"icon" => TextFormat::EMOJI_PEACE_SIGN,
			"type" => self::TYPE_ICON,
			"alias" => [":peaces:"]
		],

		":rock:" => [
			"icon" => TextFormat::EMOJI_ROCK,
			"type" => self::TYPE_ICON
		],
		":paper:" => [
			"icon" => TextFormat::EMOJI_PAPER,
			"type" => self::TYPE_ICON
		],
		":ear_right:" => [
			"icon" => TextFormat::EMOJI_EAR_RIGHT,
			"type" => self::TYPE_BODY_PART,
			"alias" => [":earr:"]
		],
		":ear_left:" => [
			"icon" => TextFormat::EMOJI_EAR_LEFT,
			"type" => self::TYPE_BODY_PART,
			"alias" => [":ear:", ":earl:"]
		],
		":cookie:" => [
			"icon" => TextFormat::EMOJI_COOKIE,
			"type" => self::TYPE_FOOD
		],
		":donut:" => [
			"icon" => TextFormat::EMOJI_DONUT,
			"type" => self::TYPE_FOOD
		],
		":lollipop:" => [
			"icon" => TextFormat::EMOJI_LOLLIPOP,
			"type" => self::TYPE_FOOD
		],
		":apple:" => [
			"icon" => TextFormat::EMOJI_APPLE,
			"type" => self::TYPE_FOOD
		],
		":lemon:" => [
			"icon" => TextFormat::EMOJI_LEMON,
			"type" => self::TYPE_FOOD
		],
		":pear:" => [
			"icon" => TextFormat::EMOJI_PEAR,
			"type" => self::TYPE_FOOD
		],
		":cherry:" => [
			"icon" => TextFormat::EMOJI_CHERRY,
			"type" => self::TYPE_FOOD
		],
		":pizza:" => [
			"icon" => TextFormat::EMOJI_PIZZA,
			"type" => self::TYPE_FOOD
		],
		":mushroom:" => [
			"icon" => TextFormat::EMOJI_MUSHROOM,
			"type" => self::TYPE_NATURE
		],
		":clover:" => [
			"icon" => TextFormat::EMOJI_CLOVER,
			"type" => self::TYPE_NATURE
		],
		":tree:" => [
			"icon" => TextFormat::EMOJI_TREE,
			"type" => self::TYPE_NATURE
		],
	];

	const EMOJI_MUSHROOM = "";
	const EMOJI_CLOVER = "";
	const EMOJI_TREE = "";

	public static array $conversionList = [];
	public array $categories = [];

	public function __construct() {
		foreach (self::TYPE_NAMES as $type => $name) {
			$this->categories[$type] = new EmojiCategory($type, $name);
		}
		foreach (self::EMOJIS as $emoji => $data) {
			if ($data["disabled"] ?? false) continue;
			$this->categories[$data["type"]]->addEmoji(new Emoji($emoji, $data["icon"], $data["type"], $data["alias"] ?? []));
			self::$conversionList[$emoji] = $data["icon"];
			if (count($data["alias"] ?? []) > 0) {
				foreach ($data["alias"] as $alias) {
					self::$conversionList[$alias] = $data["icon"];
				}
			}
		}
	}

	public static function getConversionList(): array {
		return self::$conversionList;
	}

	public function getCategories(): array {
		return $this->categories;
	}

	public function getCategory(int $type = self::TYPE_DEFAULT): ?EmojiCategory {
		return $this->categories[$type] ?? null;
	}

	public function getEmoji(string $code): string {
		return self::getConversionList()[$code] ?? "";
	}

	public static function convertWithEmojis(string $text): string {
		if (stristr($text, ":")) {
			foreach (self::getConversionList() as $code => $emoji) {
				$text = str_replace($code, $emoji, $text);
			}
		}
		return $text;
	}
}
