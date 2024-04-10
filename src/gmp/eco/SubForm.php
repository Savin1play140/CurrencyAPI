<?php
namespace gmp\eco;

use pocketmine\player\Player;

use jojoe77777\FormAPI\CustomForm;

use gmp\eco\currency\{Currency, Dollar};

final class SubForm {
	public static function send0(string $name, string $content, Player $player, Currency $currency): void {
		$form = new CustomForm(
			function (Player $sender, ?array $data) use ($currency) {
				if(is_null($data)) return;
				$count = (int)$data[1];
				if ($count < 1 or is_null($count)) return;
				if ($sender->get($currency->getName()) < $count) {
					$sender->sendMessage("you're missing ".$currency->getName().", count: ".$count-$sender->get($currency->getName()));
					return;
				}
				$sender->remove($currency->getName(), $count);
				$sender->add($currency->getExchangable(), (int)$currency->getPrice()*$count);
				$currency->onSell($count);
			}
		);
		$form->setTitle($name);
		$form->addLabel($content);
		$form->addInput("count", "Ineger only", "100");
		$form->sendToPlayer($player);
	}
	public static function send1(string $name, string $content, Player $player, Currency $currency): void {
		$form = new CustomForm(
			function (Player $sender, ?array $data) use ($currency) {
				if(is_null($data)) return;
				$count = (int)$data[1];
				if ($count < 1 or is_null($count)) return;
				if ($sender->get($currency->getExchangable()) < $currency->getPrice()*$count) {
					$sender->sendMessage("you're missing ".$currency->getExchangable().", count: ".$currency->getPrice()*$count-$sender->get($currency->getExchangable()));
					return;
				}
				$sender->remove($currency->getExchangable(), $currency->getPrice()*$count);
				$sender->add($currency->getName(), $count);
				$currency->onBuy($count);
			}
		);
		$form->setTitle($name);
		$form->addLabel($content);
		$form->addInput("count", "Ineger only", "100");
		$form->sendToPlayer($player);
	}
}