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
				$count = (float)$data[1];
				if ($count < 1 or is_null($count)) return;
				if ($count > 1000000) {
					$sender->sendMessage("You can't sell more 1,000,000");
					return;
				}
				if (round($sender->get($currency->getName()), 2) < round($count, 2)) {
					$sender->sendMessage("you're missing ".$currency->getName().", count: ".number_format($count-$sender->get($currency->getName()), 0, ".", ","));
					return;
				}
				$sender->remove($currency->getName(), round($count, 2));
				$sender->add($currency->getExchangable(), round($currency->getPrice()*$count, 2));
				$currency->onSell(round($count, 2));
			}
		);
		$form->setTitle($name);
		$form->addLabel($content);
		$form->addInput("count", "Ineger only", "1000");
		$form->sendToPlayer($player);
	}
	// buy
	public static function send1(string $name, string $content, Player $player, Currency $currency): void {
		$form = new CustomForm(
			function (Player $sender, ?array $data) use ($currency) {
				if(is_null($data)) return;
				$count = round($data[1], 2);
				if ($count < 1 or is_null($count)) return;
				if ($count > 1000000) {
					$sender->sendMessage("You can't buy more 1,000,000");
					return;
				}
				if ($sender->get($currency->getExchangable()) < round($currency->getPrice()*$count, 2)) {
					$sender->sendMessage("you're missing ".$currency->getExchangable().", count: ".number_format($currency->getPrice()*$count-$sender->get($currency->getExchangable()), 0, ".", ","));
					return;
				}
				$sender->remove($currency->getExchangable(), round($currency->getPrice()*$count, 2));
				$sender->add($currency->getName(), round($count, 2));
				$currency->onBuy(round($count, 2));
			}
		);
		$form->setTitle($name);
		$form->addLabel($content);
		$form->addInput("count", "Ineger only", "1000");
		$form->sendToPlayer($player);
	}
}