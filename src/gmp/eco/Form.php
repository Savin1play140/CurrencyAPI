<?php
namespace gmp\eco;

use gmp\eco\player\Player;
use jojoe77777\FormAPI\SimpleForm;
use gmp\eco\currency\{Currency};

final class Form {
	public static function sendSelf(string $name, string $content, Player $player, Currency $currency): void {
		$SellBttIndex = $currency->isSalable() ? 0 : -2;
		$BuyBttIndex =  $currency->isSalable() ? 1 : 0;
		$BuyBttIndex =  $currency->isBuyable() ? $BuyBttIndex : -1;

		if (API::getPlayerManager()->getBoughtCurrency($currency->getName()) >= $currency->maxCount())
			$BuyBttIndex = -1;

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
		if ($SellBttIndex >= 0) $form->addButton("Sell");
		if ($BuyBttIndex >= 0) $form->addButton("Buy");
		$form->sendToPlayer($player);
	}
}