<?php
namespace gmp\eco\currency;

use gmp\eco\command\CurrencyCommand;
use pocketmine\Server;
use pocketmine\utils\Config;
use gmp\eco\API;

class CurrencyManager {
	private array $currencies = [];
	private array $pluginsOfCurrencies = [];

	private Config $save;

	public function __construct(
		private API $api
	) {
		$save_dir = $this->api->getMain()->getDataFolder();
		$this->save = new Config($save_dir."currencies.json", Config::JSON);
	}


	public function registerCurrency(string $pluginName, Currency $currency): void {
		$this->currencies[strtolower($currency->getName())] = $currency;
		$this->pluginsOfCurrencies[$currency->getName()] = $pluginName;
		Server::getInstance()->getCommandMap()->register($pluginName, new CurrencyCommand($currency, $this->api));
		$this->loadCurrencyData($currency);
	}

	public function getCurrencies(): array {
		return $this->currencies;
	}

	public function getCurrencyByName(string $name): ?Currency {
		if (isset($this->currencies[strtolower($name)])) return $this->currencies[strtolower($name)];
		return null;
	}

	public function existsCurrency(string $name): bool {
		$exists = false;
		if (!is_null($this->currencies[strtolower($name)])) $exists = true;

		$is_currency = false;
		if ($this->currencies[strtolower($name)] instanceof Currency) $is_currency = true;
		
		return $exists && $is_currency;
	}


	public function getPluginNameByCurrencyName(string $currency) : string {
		return $this->pluginsOfCurrencies[$currency];
	}

	public function getPluginNameByCurrency(Currency $currency) : string {
		return $this->getPluginNameByCurrencyName($currency->getName());
	}


	public function saveCurrencyData(string $currencyName): void {
		$currencyName = strtolower($currencyName);
		$currency = $this->getCurrencyByName($currencyName);

		$data = [
			"price" => round($currency->getPrice(), 2)
		];

		$this->save->set($currencyName, $data);
		$this->save->save();
	}

	public function saveCurrenciesData() : void {
		foreach ($this->currencies as $name => $currency) {
			$this->saveCurrencyData($name);
		}
	}


	public function loadCurrencyData(Currency $currency) : void {
		$name = strtolower($currency->getName());
		if ($this->save->get($name) === null) return;

		$data = $this->save->get($name, ["price" => round($currency->getPrice(), 2)]);

		$currency->setPrice($data["price"]);
	}
}