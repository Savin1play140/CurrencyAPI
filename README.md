# EconomyAPI | PocketMine-MP

Двухсторонний экономический A.P.I. для создания валют с одной стороны, а с другой обычный экономический плагин PMMP-5

<br/><br/>
Примеры использования:
<br/><br/>
SimpleForm
-----------------------------------
<br/>

# Для создания валюты:
Main:
```php
use gmp\eco\API;
/* ... */
API::registerCurrency(new YourCurrency());
/* ... */
```
Вместо YourCurrency класс вашей валюты
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
	//public function setPrice(int $price): void { /* code */ }
	public function getName(): string {
		return "Dollar"; // currency name
	}
	public function getSing(): string {
		return "$"; // sing
	}
	public function isBuyable(): bool { return true; }
	public function isSalable(): bool { return true; }
}
```
# Для использования экономической стороны:
```php
// Для добавления к балансу валюты игрока
$target->add($currency->getName(), $count);
// Для удаления из баланса валюты игрока
$target->remove($currency->getName(), $count);
// Для установки баланса валюты игрока
$target->set($currency->getName(), $count);
// Для получения баланса валюты игрока
$count = $target->get($currency->getName());
```
