<?php
namespace gmp\eco\currency;
use gmp\eco\API;

class CoinIO implements Currency {
	private int $price = 100;
	public function getPrice(): int {
		return $this->price;
	}
	public function getExchangable(): string {
		return "Dollar";
	}
	public function onBuy(int $count): void {
		$oldPrice = $this->getPrice();
		$coin_coff_buy = API::getAPIConfig()->get("coin_coff_buy", 0.01);
		//$newPrice = $oldPrice+(($oldPrice*$coin_coff_buy)*$this->getProcent())*($count*$coin_coff_buy);
		$newPrice = $oldPrice+$oldPrice*$coin_coff_buy*($count/1000);
		if ($newPrice >= PHP_INT_MAX) return;
		if ($newPrice <= PHP_INT_MIN) return;
		if ($newPrice <= 0) return;
		$this->setPrice((int)round($newPrice));
	}
	public function onSell(int $count): void {
		$oldPrice = $this->getPrice();
		$coin_coof_sell = API::getAPIConfig()->get("coin_coff_sell", 0.01);
		//$newPrice = $oldPrice-(($oldPrice*$coin_coof_sell)*$this->getProcent())*($count*$coin_coof_sell);
		$newPrice = $oldPrice-$oldPrice*$coin_coof_sell*($count/1000);
		if ($newPrice >= PHP_INT_MAX) return;
		if ($newPrice <= PHP_INT_MIN) return;
		if ($newPrice <= 0) return;
		$this->setPrice((int)round($newPrice));
	}
	protected function setPrice(int $price): void {
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
}