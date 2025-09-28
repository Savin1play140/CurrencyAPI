<?php
namespace gmp\eco;

use pocketmine\player\Player;

use gmp\eco\event\{BuyEvent, SellEvent};
use gmp\eco\currency\{Currency, Dollar};
use gmp\eco\form\CustomForm;

final class SubForm {

	// sell
	public static function send0(string $name, string $content, Player $player, Currency $currency): void {
		$form = new CustomForm(
			function (Player $sender, ?array $data) use ($currency) {
				if(is_null($data)) return;
				if (!$currency->isSalable()) {
					$sender->sendMessage("You can't sell");
					return;
				}
				$count = (float)$data[1];
				if ($count <= 0 or is_null($count)) return;

				if (round($sender->get($currency->getName()), 2) < round($count, 2)) {
					$sender->sendMessage("you're missing ".$currency->getName().", count: ".number_format($count-$sender->get($currency->getName()), 0, ".", ","));
					$sender->sendMessage("for selling ".$currency->getName()."(".$currency->getSing().")");
					return;
				}

				$event = new SellEvent($sender, $count, $currency);
				$event->call();
				if ($event->isCancelled()) {
					return;
				}

				if ($sender->remove($currency->getName(), round($count, 2), true, false)) {
					$sender->add($currency->getExchangeable(), round(round($currency->getPrice(), 2)*$count, 2), true, false);
					$currency->onSell(round($count, 2));
				} else {
					$this->sendMessage(
						str_replace(
							"{missing}",
							number_format($count-$this->get($currency->getName()), 2, ".", ","),
							str_replace(
								"{sing}",
								$sing,
								API::getLang()->getNested("player.nomoney")
							)
						)
					);
				}
			}
		);
		$form->setTitle($name);
		$form->addLabel($content);
		$form->addInput("count", "Integer only", "1000");
		$form->sendToPlayer($player);
	}


	// buy
	public static function send1(string $name, string $content, Player $player, Currency $currency): void {
		$sing = API::getCurrencyByName($currency->getExchangeable())->getSing();
		$content = "§l".$currency->getName()." price: ".number_format($currency->getPrice(), 2, ".", ",").$currency->getSing().
			"\nYou have: ".number_format($player->get($currency->getExchangeable()), 2, ".", ",").$sing.
			"\n     and: ".number_format($player->get($currency->getName()), 2, ".", ",").$currency->getSing();
		$form = new CustomForm(
			function (Player $sender, ?array $data) use ($currency) {
				if(is_null($data)) return;
				if (!$currency->isBuyable()) {
					$sender->sendMessage("You can't buy");
					return;
				}
				$count = round($data[1], 2);
				if ($count <= 0 or is_null($count)) return;

				if ($sender->get($currency->getExchangeable()) < round(round($currency->getPrice(), 2)*$count, 2)) {
					$sender->sendMessage("you're missing ".$currency->getExchangeable().", count: ".number_format(round(round($currency->getPrice(), 2)*$count, 2)-$sender->get($currency->getExchangeable()), 0, ".", ","));
					$sender->sendMessage("for buying ".$currency->getName()."(".$currency->getSing().")");
					return;
				}

				$event = new BuyEvent($sender, $count, $currency);
				$event->call();
				if ($event->isCancelled()) return;

				if($sender->remove($currency->getExchangeable(), round(round($currency->getPrice(), 2)*$count, 2), true, false)) {
					$sender->add($currency->getName(), round($count, 2), true, false);
					$currency->onBuy(round($count, 2));
				} else {
					$this->sendMessage(
						str_replace(
							"{missing}",
							number_format($count-$this->get($currency->getName()), 2, ".", ","),
							str_replace(
								"{sing}",
								$sing,
								API::getLang()->getNested("player.nomoney")
							)
						)
					);
				}
			}
		);
		$form->setTitle($name);
		$form->addLabel($content);
		$form->addInput("count", "Integer only", "1000");
		$form->sendToPlayer($player);
	}

}