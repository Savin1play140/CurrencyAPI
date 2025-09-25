<?php
namespace gmp\eco\currency;

interface Currency {
	public function getPrice(): float;
	public function getExchangeable(): string;

	public function getSing(): string;
	public function getName(): string;

	public function onBuy(float $count): void;
	public function onSell(float $count): void;

	public function isBuyable(): bool;
	public function isSalable(): bool;
}