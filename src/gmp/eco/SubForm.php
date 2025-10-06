<?php
namespace gmp\eco;

use gmp\eco\player\Player;
use gmp\eco\event\{BuyEvent, SellEvent};
use gmp\eco\currency\{Currency};
use jojoe77777\FormAPI\CustomForm;

final class SubForm {

	// sell
	public static function send0(string $name, string $content, Player $player, Currency $currency): void {
		$exchangeable = API::getCurrencyManager()->getCurrencyByName($currency->getExchangeable());
		$sing = $exchangeable->getSing();

		$form = new CustomForm(
			function (Player $sender, ?array $data) use ($currency, $sing) {
				/** @var \gmp\eco\player\Player $sender */
				if(is_null($data)) return;
				if (!$currency->isSalable()) {
					$sender->sendMessage("You can't sell");
					return;
				}
				$count = $data[1] ?? null;
				if ($count === null or $count <= 0) return;
				$count = (float) $count;
				if ($currency->sellLimit() < $count) return;

				//WTF
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
					$sender->sendMessage(
						str_replace(
							"{missing}",
							number_format($count-$sender->get($currency->getName()), 2, ".", ","),
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
		$exchangeable = API::getCurrencyManager()->getCurrencyByName($currency->getExchangeable());
		$sing = $exchangeable->getSing();

		$content = "§l".$currency->getName()." price: ".number_format($currency->getPrice(), 2, ".", ",").$exchangeable->getSing().
			"\nYou have: ".number_format($player->get($exchangeable->getName()), 2, ".", ",").$sing.
			"\n     and: ".number_format($player->get($currency->getName()), 2, ".", ",").$currency->getSing();

			$form = new CustomForm(
			function (Player $sender, ?array $data) use ($currency, $sing) {
				if(is_null($data)) return;
				if (!$currency->isBuyable()) {
					$sender->sendMessage("You can't buy");
					return;
				}
				$count = $data[1] ?? null;
				if($count === null){
					return;
				}
				$count = round($count, 2);
				if ($count <= 0) return;
				if ($currency->buyLimit() < $count) return;

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
					$sender->sendMessage(
						str_replace(
							"{missing}",
							number_format($count-$sender->get($currency->getName()), 2, ".", ","),
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