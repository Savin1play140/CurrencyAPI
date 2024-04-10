<?php
namespace gmp\eco\currency;

interface Currency {
	public function getPrice(): int;
	public function getExchangable(): string;
	public function getSing(): string;
	public function getName(): string;
	//function setPrice(int $price): void;
	public function onBuy(int $count): void;
	public function onSell(int $count): void;

	public function isBuyable(): bool;
	public function isSalable(): bool;
}