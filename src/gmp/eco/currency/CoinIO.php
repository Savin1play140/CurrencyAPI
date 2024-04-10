<?php
namespace gmp\eco\currency;

class CoinIO implements Currency {
	private int $price = 100;
	private float $procent = 0;
	public function getPrice(): int {
		return $this->price;
	}
	public function getProcent(): float {
		return $this->procent/100;
	}
	public function getExchangable(): string {
		return "Dollar";
	}
	public function onBuy(int $count): void {
		$oldPr = $this->getPrice();
		$this->procent += 0.00001*($count/10);
		$this->setPrice((int)round($oldPr+$oldPr*$this->getProcent()));
	}
	public function onSell(int $count): void {
		$oldPr = $this->getPrice();
		if ($this->procent > 0.00000000000000001*($count/10)+75) {
			$this->procent -= 0.00000000000000001*($count*0.00001);
		} else if ($this->procent < 75) {
			$this->procent -= 0;
		}
		$this->setPrice((int)round($oldPr+$oldPr*$this->getProcent()));
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