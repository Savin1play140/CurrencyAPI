<?php
namespace gmp\eco\currency;
use gmp\eco\API;

class CoinIO implements Currency {
	private float $price = 100.0;
	public function getPrice(): float {
		return (float)$this->price;
	}
	public function getExchangeable(): string {
		return "Dollar";
	}
	public function onBuy(float $count): void {
		$oldPrice = $this->getPrice();
		$coin_coff_buy = API::getAPIConfig()->get("coin_coff_buy", 0.01);

		//$newPrice = $oldPrice+(($oldPrice*$coin_coff_buy)*$this->getPercent())*($count*$coin_coff_buy);
		$newPrice = $oldPrice+$oldPrice*$coin_coff_buy*$this->removeZero($count)/10;
		$newPrice = round($newPrice, 2);

		if ($newPrice >= PHP_INT_MAX) {
			$this->setPrice(PHP_INT_MAX);
			return;
		}
		if ($newPrice <= 0) {
			$this->setPrice(0.01);
			return;
		}

		$this->setPrice($newPrice);
	}
	public function onSell(float $count): void {
		$oldPrice = $this->getPrice();
		$coin_coff_sell = API::getAPIConfig()->get("coin_coff_sell", 0.01);

		//$newPrice = $oldPrice-(($oldPrice*$coin_coff_sell)*$this->getPercent())*($count*$coin_coff_sell);
		$newPrice = $oldPrice-$oldPrice*$coin_coff_sell*$this->removeZero($count)/10;
		$newPrice = round($newPrice, 2);

		if ($newPrice >= PHP_INT_MAX) {
			$this->setPrice(PHP_INT_MAX);
			return;
		}
		if ($newPrice <= 0) {
			$this->setPrice(0.01);
			return;
		}

		$this->setPrice($newPrice);
	}
	public function removeZero(float $float): float {
		$zeroCount = substr_count($float, '0');
		$half = 1000;
		$str = str_replace('0', '', "$float", $half);
		return round((float)$str, 2);
	}
	public function setPrice(float $price): void {
		$this->price = $price;
	}
	public function getName(): string {
		return "CoinIO";
	}
	public function getSing(): string {
		return "I";
	}
	public function isBuyable(): bool { return true; }
	public function isSalable(): bool { return true; }

	public function maxCount(): float { return PHP_INT_MAX; }
	public function buyLimit(): float { return 1000000000; }
	public function sellLimit(): float { return 1000000000; }
}