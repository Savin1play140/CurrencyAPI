# CurrencyAPI | PocketMine-MP
[![](https://poggit.pmmp.io/shield.state/CurrencyAPI)](https://poggit.pmmp.io/p/CurrencyAPI)

Two-way economic A.P.I. to create currencies on the one hand, and on the other the usual economic plugin PocketMine-MP 5
[README RU](README_RU.md)

# What next?
  - Next steps:
    - [x] Currency count limit
    - [x] Currency buy/sell limit
    - [x] Saving player balance to SQL database
    - [x] Saving currency price to SQL database

# For create currency
Main:
```php
use gmp\eco\API;

	/* ... */
		API::getCurrencyManager()->registerCurrency($main->getName(), new YourCurrency());
	/* ... */
```
Instead of YourCurrency the class of your currency
Your Currency:
```php
<?php
namespace your\plugin\space;

use gmp\eco\currency\Currency;

class YourCurrency implements Currency {
	public function getPrice(): float {
		return 1;
	}
	public function getExchangeable(): string {
		return "Dollar";
	}
	public function onBuy(float $count): void { /* code */ }
	public function onSell(float $count): void { /* code */ }
	public function setPrice(float $price): void { /* code */ }
	public function getName(): string {
		return "Currency"; // currency name
	}
	public function getSing(): string {
		return "C"; // sing
	}
	public function isBuyable(): bool { return true; }
	public function isSalable(): bool { return true; }

	public function maxCount(): float { return PHP_float_MAX; }

	public function buyLimit(): float { return PHP_float_NAX; }
	public function sellLimit(): float { return PHP_float_MAX; }
}
```
# For use economic side:
Add to balance:
```php
$player->add($currencyName, $count);
```
For remove from balance:
```php
$player->remove($currencyName, $count);
```
For set balance:
```php
$player->set($currencyName, $count);
```
For complete transaction:
```php
$player->transaction($currencyName, $count, $player);
```
For get balance
```php
$count = $player->get($currencyName);
```

# Commands
/[currency]
 - sell <count: float> all players
 - buy <count: float> all players
 - set <count: float> [player: string] operators only
 - add <count: float> [player: string] operators only
 - remove <count: float> [player: string] operators only
 - transaction <count: float> <player: string> all players <br>
