<?php
namespace gmp\eco\currency;

class Dollar implements Currency {
	public function getPrice(): float {
        return 1.0;
    }
	public function getExchangable(): string {
		return "Dollar";
	}
	public function onBuy(float $count): void {}
	public function onSell(float $count): void {}
	//public function setPrice(float $price): void {}
	public function getName(): string {
		return "Dollar";
	}
	public function getSing(): string {
		return "$";
	}
	public function isBuyable(): bool { return false; }
	public function isSalable(): bool { return false; }
}