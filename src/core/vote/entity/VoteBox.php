<?php

namespace core\vote\entity;

use pocketmine\item\VanillaItems;
use pocketmine\world\{
	sound\PopSound,
	particle\HappyVillagerParticle
};
use pocketmine\entity\{
	Human,
	Location,
	Skin
};

use pocketmine\event\entity\{
	EntityDamageEvent,
	EntityDamageByEntityEvent
};
use pocketmine\math\Vector3;

use core\AtPlayer as Player;
use core\vote\Structure;
use core\vote\uis\VoteRewardsUi;
use core\etc\pieces\skin\Skin as CSkin;
use core\utils\{
	TextFormat,
	Utils
};

class VoteBox extends Human{

	const BUST_LIMIT = 10;

	public $aliveTicks = 0;

	public $bustTimer = 0;
	public $bustForce = 0.5;
	public $bust = [];

	public $jump = 0;

	public $doText = false;
	public $currentText = "";
	public $texts = [];
	public $spot = 0;
	public $textWait = 0;
	public $afterWait = 0;
	public $wait = 0;

	public $lineThing = true;
	public $lineTick = 0;

	public function __construct(Location $level, Skin $nbt) {
		parent::__construct($level, $nbt);

		$customGeometry = json_decode(file_get_contents("/[REDACTED]/skins/custom/ballot_box.geo.json"), true, 512, JSON_THROW_ON_ERROR);
		$geometry = $customGeometry["minecraft:geometry"][0]["description"]["identifier"];
		$geometryData = json_encode($customGeometry, JSON_THROW_ON_ERROR);

		$this->setSkin(new Skin("BallotBox", CSkin::getSkinData("custom/ballot_box"), "", $geometry, $geometryData));
		$this->setCanSaveWithChunk(false);
		$this->setNametagVisible(true);
		$this->setNameTagAlwaysVisible(true);
		$this->setNametag("");
		$this->setScale(0.9);
	}

	public function entityBaseTick(int $tickDiff = 1): bool {
		$hasUpdate = parent::entityBaseTick($tickDiff);
		$this->aliveTicks++;

		$this->setRotation($this->getLocation()->getYaw() + 10, 0);

		if ($this->isBusting()) {
			$this->jump();

			$this->bustTimer--;
			if ($this->bustTimer % 3 == 0 && $this->bustTimer > 30)
				$this->bust();

			if ($this->bustTimer <= 0) {
				$this->clearBust();
				$this->jump = 0;
			}
		}

		if ($this->isDoingText()) {
			$text = substr($this->currentText, 0, $this->spot);
			if (substr($text, -1) == TextFormat::ESCAPE) {
				$this->spot += 3;
				$text = substr($this->currentText, 0, $this->spot);
			}

			$this->lineTick++;
			if ($this->lineTick > 10) {
				$this->lineTick = 0;
				$this->lineThing = !$this->lineThing;
			}

			$this->setNametag($text . ($this->lineThing ? TextFormat::WHITE . " |" : " "));

			$this->spot++;
			if (substr($this->currentText, 0, $this->spot) == $text) {
				$this->wait++;
				if (!empty($this->texts)) {
					if ($this->wait > $this->textWait) {
						$this->currentText = array_shift($this->texts);
						$this->wait = 0;
						$this->spot = 0;
					}
				} else {
					if ($this->wait > $this->afterWait) {
						$this->setNametag("");
						$this->doText = false;
						$this->wait = 0;
						$this->spot = 0;
					}
				}
			}
		} else {
			if ($this->aliveTicks % 300 == 0) {
				$this->jump(1.75);
				$this->getWorld()->addSound($this->getPosition(), new PopSound());
				$texts = Structure::VOTE_BOX_TEXTS[array_rand(Structure::VOTE_BOX_TEXTS)];
				$this->showNewTexts($texts);
				$this->jump = 0;
			}
		}

		if ($this->aliveTicks % 20 == 0) {
			$this->getWorld()->addParticle($this->getPosition()->add(mt_rand(-5, 5) / 10, mt_rand(0, 10) / 10, mt_rand(-5, 5) / 10), new HappyVillagerParticle());
		}

		return true;
	}

	public function attack(EntityDamageEvent $source): void {
		$source->cancel();
		if ($source instanceof EntityDamageByEntityEvent) {
			$player = $source->getDamager();
			if ($player instanceof Player) {
				$player->showModal(new VoteRewardsUi($player));
			}
		}
	}

	public function isBusting(): bool {
		return $this->bustTimer > 0;
	}

	public function setBusting(int $seconds, float $force = 0.5): void {
		$this->bustTimer = $seconds * 20;
		$this->setBustForce($force);
	}

	public function getBustForce(): float {
		return $this->bustForce;
	}

	public function setBustForce(float $force): void {
		$this->bustForce = $force;
	}

	public function bust(): void {
		$yaw = mt_rand(0, 360);
		$pitch = -84;

		$item = VanillaItems::PAPER();

		$motX = -sin($yaw / 180 * M_PI) * cos($pitch / 180 * M_PI);
		$motY = -sin($pitch / 180 * M_PI);
		$motZ = cos($yaw / 180 * M_PI) * cos($pitch / 180 * M_PI);
		$motV = (new Vector3($motX, $motY, $motZ))->multiply($this->getBustForce());

		$entity = Utils::dropTempItem($this->getWorld(), $this->getPosition()->add(0, 0.5, 0), $item, $motV, 999);
		$entity->setCanSaveWithChunk(false);

		$this->bust[] = $entity;
		if (count($this->bust) > self::BUST_LIMIT) {
			$bust = array_shift($this->bust);
			if (!$bust->closed) $bust->flagForDespawn();
		}

		$this->getWorld()->addSound($this->getPosition(), new PopSound());
	}

	public function clearBust(): void {
		foreach ($this->bust as $bust) {
			if (!$bust->closed) $bust->flagForDespawn();
		}
		$this->bust = [];
	}

	public function jump(float $multiplier = 1): void {
		if ($this->jump > 0) {
			$this->jump--;
		} else {
			$this->jump = 10;
			$this->motion->y = $this->gravity * (4 * $multiplier);
		}
	}

	public function isDoingText(): bool {
		return $this->doText;
	}

	public function showNewTexts(array $texts, int $textWait = 30, int $afterWait = 60): void {
		$this->doText = true;
		$this->currentText = array_shift($texts) ?? "";
		$this->texts = $texts;
		$this->textWait = $textWait;
		$this->afterWait = $afterWait;

		$this->setNametag("");
	}

	public function addText(string $text): void {
		if (!$this->isDoingText()) {
			$this->showNewTexts([$text]);
		} else {
			$this->texts[] = $text;
		}
	}
}
