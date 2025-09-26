<?php
namespace gmp\eco;

use pocketmine\player\Player;

use gmp\eco\form\SimpleForm;

use gmp\eco\currency\{Currency, Dollar};

final class Form {
	public static function sendSelf(string $name, string $content, Player $player, Currency $currency): void {
		$SellBttIndex = $currency->isSalable() ? 0 : -1;
		$BuyBttIndex =  $currency->isSalable() ? 1 : 0;

		$form = new SimpleForm(
			function (Player $sender, ?int $data) use ($name, $content, $currency, $SellBttIndex, $BuyBttIndex) {
				if(is_null($data)) return;
				switch($data) {
					case $SellBttIndex:
						// Button 1
						SubForm::send0($name, $content, $sender, $currency);
						break;
					case $BuyBttIndex:
						// Button 2
						SubForm::send1($name, $content, $sender, $currency);
						break;
					default:
						// Form closed
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