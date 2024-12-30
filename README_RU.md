# CurrencyAPI | PocketMine-MP
[![](https://poggit.pmmp.io/shield.state/EconomyAPI_Currences)](https://poggit.pmmp.io/p/EconomyAPI_Currences)<br>
[![](https://poggit.pmmp.io/shield.api/EconomyAPI_Currences)](https://poggit.pmmp.io/p/EconomyAPI_Currences)<br>
Двухсторонний экономический A.P.I. для создания валют с одной стороны, а с другой обычный экономический плагин PMMP-5

# Для создания валюты
Main:
```php
use gmp\eco\API;


	/* ... */
		API::registerCurrency(new YourCurrency(), $main->getName());
	/* ... */
```
Вместо YourCurrency класс вашей валюты
YourCurrency:
```php
<?php
namespace your\plugin\space;

use gmp\eco\currency\Currency;

class YourCurrency implements Currency {
	public function getPrice(): float {
		return 1;
	}
	public function getExchangable(): string {
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
}
```
# Для использования экономической стороны
```php
// Для добавления к балансу валюты игрока
$target->add($currency->getName(), $count);
// Для удаления из баланса валюты игрока
$target->remove($currency->getName(), $count);
// Для установки баланса валюты игрока
$target->set($currency->getName(), $count);
// Для совершения транзакции
$target->transaction($currency->getName(), $count $player);
// Для получения баланса валюты игрока
$count = $target->get($currency->getName());
```

# Команды
По умолчанию:
/dollar
 - set <count: int> [player: string] только операторы
 - add <count: int> [player: string] только операторы
 - remove <count: int> [player: string] только операторы
 - transaction <count: int> <player: int> все игроки
/coinio
 - set <count: int> <?player: string> только операторы
 - add <count: int> <?player: string> только операторы
 - remove <count: int> [player: string] только операторы
 - transaction <count: int> <player: int> все игроки
Добавляемые другими плагинам:
/CurrencyName
 - set <count: int> <?player: string> только операторы
 - add <count: int> <?player: string> только операторы
 - remove <count: int> [player: string] только операторы
 - transaction <count: int> <player: int> все игроки

