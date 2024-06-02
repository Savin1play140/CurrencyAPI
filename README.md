# CurrencyAPI | PocketMine-MP
Two-way economic A.P.I. to create currencies on the one hand, and on the other the usual economic plugin PMMP-5
[README RU](README_RU.md)

# For create currency
Main:
```php
use gmp\eco\API;


	/* ... */
		API::registerCurrency(new YourCurrency());
	/* ... */
```
Instead of YourCurrency the class of your currency
YourCurrency:
```php
<?php
namespace your\plugin\space;

use gmp\eco\currency\Currency;

class YourCurrency implements Currency {
	public function getPrice(): int {
		return 1;
	}
	public function getExchangable(): string {
		return "Dollar";
	}
	public function onBuy(int $count): void { /* code */ }
	public function onSell(int $count): void { /* code */ }
	public function setPrice(int $price): void { /* code */ }
	public function getName(): string {
		return "Currency"; // currency name
	}
	public function getSing(): string {
		return "C"; // sing
	}
	public function isBuyable(): bool { return true; }
	public function isSalable(): bool { return true; }
}
```
# For use economy
```php
// To add a player's currency to the balance
$target->add($currency->getName(), $count);
// To remove a player's currency from the balance
$target->remove($currency->getName(), $count);
// To set the player's currency balance
$target->set($currency->getName(), $count);
// To complete a transaction
$target->transaction($currency->getName(), $count $player);
// To get the player's currency balance
$count = $target->get($currency->getName());
```

# Commands
Default:
/dollar
 - set <count: int> [player: string] operators only
 - add <count: int> [player: string] operators only
 - remove <count: int> [player: string] operators only
 - transaction <count: int> <player: int> all players
/coinio
 - set <count: int> <?player: string> operators only
 - add <count: int> <?player: string> operators only
 - remove <count: int> [player: string] operators only
 - transaction <count: int> <player: int> all players
Added to other plugins:
/CurrencyName
 - set <count: int> <?player: string> operators only
 - add <count: int> <?player: string> operators only
 - remove <count: int> [player: string] operators only
 - transaction <count: int> <player: int> all players

