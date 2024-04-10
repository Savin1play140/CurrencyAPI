<?php
namespace gmp\eco\currency;

class Dollar implements Currency {
	public function getPrice(): int {
        return 1;
    }
	public function getExchangable(): string {
		return "Dollar";
	}
	public function onBuy(int $count): void {}
	public function onSell(int $count): void {}
	//public function setPrice(int $price): void {}
	public function getName(): string {
		return "Dollar";
	}
	public function getSing(): string {
		return "$";
	}
	public function isBuyable(): bool { return false; }
	public function isSalable(): bool { return false; }
}