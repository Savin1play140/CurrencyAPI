<?php
namespace gmp\eco;

use pocketmine\player\Player;

use jojoe77777\FormAPI\SimpleForm;

use gmp\eco\currency\{Currency, Dollar};

final class Form {
	public static function sendSelf(string $name, string $content, Player $player, Currency $currency): void {
		$form = new SimpleForm(
			function (Player $sender, ?int $data) use ($name, $content, $currency) {
				if(is_null($data)) return;
				switch($data) {
					case 0:
						// Button 1
						SubForm::send0($name, $content, $sender, $currency);
						break;
					case 1:
						// Button 2
						SubForm::send1($name, $content, $sender, $currency);
						break;
					default:
						// Form closed
						API::Logger()->info($sender->getName()."\'s close form");
						break;
				}
			}
		);
		$form->setTitle($name);
		$form->setContent($content);
		if ($currency->isSalable()) $form->addButton("Sell");
		if ($currency->isBuyable()) $form->addButton("Buy");
		$form->sendToPlayer($player);
	}
}