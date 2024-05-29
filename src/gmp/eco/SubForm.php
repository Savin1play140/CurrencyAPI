<?php
namespace gmp\eco;

use pocketmine\player\Player;

use jojoe77777\FormAPI\CustomForm;

use gmp\eco\currency\{Currency, Dollar};

final class SubForm {
	// sell
	public static function send0(string $name, string $content, Player $player, Currency $currency): void {
		$form = new CustomForm(
			function (Player $sender, ?array $data) use ($currency) {
				if(is_null($data)) return;
				$count = (int)$data[1];
				if ($count < 1 or is_null($count)) return;
				if ($count > 1000000) {
					$sender->sendMessage("You can't sell more 1 000 000");
					return;
				}
				if ($sender->get($currency->getName()) < (int)round($count)) {
					$sender->sendMessage("you're missing ".$currency->getName().", count: ".$count-$sender->get($currency->getName()));
					return;
				}
				$sender->remove($currency->getName(), (int)round($count));
				$sender->add($currency->getExchangable(), (int)round($currency->getPrice()*$count));
				$currency->onSell((int)round($count));
			}
		);
		$form->setTitle($name);
		$form->addLabel($content);
		$form->addInput("count", "Ineger only", "100");
		$form->sendToPlayer($player);
	}
	// buy
	public static function send1(string $name, string $content, Player $player, Currency $currency): void {
		$form = new CustomForm(
			function (Player $sender, ?array $data) use ($currency) {
				if(is_null($data)) return;
				$count = (int)$data[1];
				if ($count < 1 or is_null($count)) return;
				if ($count > 1000000) {
					$sender->sendMessage("You can't buy more 1 000 000");
					return;
				}
				if ($sender->get($currency->getExchangable()) < (int)round($currency->getPrice()*$count)) {
					$sender->sendMessage("you're missing ".$currency->getExchangable().", count: ".$currency->getPrice()*$count-$sender->get($currency->getExchangable()));
					return;
				}
				$sender->remove($currency->getExchangable(), (int)round($currency->getPrice()*$count));
				$sender->add($currency->getName(), (int)round($count));
				$currency->onBuy((int)round($count));
			}
		);
		$form->setTitle($name);
		$form->addLabel($content);
		$form->addInput("count", "Ineger only", "100");
		$form->sendToPlayer($player);
	}
}