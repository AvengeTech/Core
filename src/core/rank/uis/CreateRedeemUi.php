<?php namespace core\rank\uis;

use core\{
	Core,
	AtPlayer as Player
};
use core\ui\elements\customForm\{
	Label,
	Input,
	Dropdown
};
use core\ui\windows\CustomForm;
use core\utils\TextFormat;

class CreateRedeemUi extends CustomForm{

	public function __construct(Player $player, string $error = ""){
		parent::__construct("Add redeem code");
		$this->addElement(new Label(($error == "" ? "" : TextFormat::RED . "Error: " . $error . "\n" . TextFormat::WHITE) . "Enter the code name:"));
		$this->addElement(new Input("Code", "poop pee"));

		$this->addElement(new Label("Select the type of redeem code this will be:"));
		$this->addElement(new Dropdown("Type", ["Rank", "Techits"]));

		$this->addElement(new Label("Enter value:"));
		$this->addElement(new Input("Value", "e.g: enderdragon or 10000"));
	}

	public function handle($response, Player $player){
		$redeemer = Core::getInstance()->getStats()->getRedeemer();

		$code = $response[1];
		$type = $response[3];
		$value = $response[5];

		if($redeemer->exists($code)){
			$player->showModal(new CreateRedeemUi($player, "A code with this name already exists!"));
			return;
		}
		$tt = ($type == 0 ? "rank" : "techits");
		switch($tt){
			case "rank":
				$ranks = [
					"endermite",
					"blaze",
					"ghast",
					"enderman",
					"wither",
					"enderdragon",
				];
				if(!in_array($value, $ranks)){
					$player->showModal(new CreateRedeemUi($player, "Invalid value supplied! (Ranks: " . implode(", ", $ranks) . ")"));
					return;
				}
				break;
			case "techits":
				$value = (int)$value;
				if($value <= 0){
					$player->showModal(new CreateRedeemUi($player, "Invalid value supplied! (Must be above 0!)"));
					return;
				}
				break;
		}
		$redeemer->addCode($code, $tt . ":" . $value);
		$player->sendMessage(TextFormat::GI . "Added redeem code " . TextFormat::YELLOW . "'" . $code . "'" . TextFormat::GRAY . " for " . TextFormat::GREEN . $value . " " . $tt);
	}

}