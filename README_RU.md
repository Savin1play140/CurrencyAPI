# CurrencyAPI | PocketMine-MP
[![](https://poggit.pmmp.io/shield.state/CurrencyAPI)](https://poggit.pmmp.io/p/CurrencyAPI)

Двухсторонний экономический A.P.I. для создания валют с одной стороны, а с другой обычный экономический плагин PocketMine-MP 5

# Что потом?
  - Далее:
    - [x] Лимит на количество валюты
    - [x] Лимит на транзакцию
    - [x] Сохранение баланса игрока в базе данных SQL
    - [x] Сохранение стоимости валютв базе данных SQL

# Для создания валюты
Main:
```php
use gmp\eco\API;

	/* ... */
		API::getCurrencyManager()->registerCurrency($main->getName(), new YourCurrency());
	/* ... */
```
Вместо YourCurrency класс вашей валюты
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
# Для использования экономической стороны
Для пополнения баланса:
```php
$target->add($currencyName, $count);
```
Для вычита с баланса:
```php
$target->remove($currencyName, $count);
```
Для указания баланса:
```php
$target->set($currencyName, $count);
```
Для совершения транзакции:
```php
$target->transaction($currencyName, $count $player);
```
Для получения баланса:
```php
$count = $target->get($currencyName);
```

# Команды
По умолчанию:
/[currency]
 - sell <count: float> все игроки
 - buy <count: float> все игроки
 - set <count: float> [player: string] только операторы
 - add <count: float> [player: string] только операторы
 - remove <count: float> [player: string] только операторы
 - transaction <count: float> <player: string> все игроки
