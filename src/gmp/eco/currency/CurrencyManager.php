<?php
namespace gmp\eco\currency;

use gmp\eco\command\CurrencyCommand;
use pocketmine\Server;
use gmp\eco\util\SQL;
use gmp\eco\API;

class CurrencyManager {
	private array $currencies = [];
	private array $pluginsOfCurrencies = [];

	private SQL $save;

	public function __construct(
		private API $api
	) {
		$conf = $this->api->getAPIConfig()->get("database", [
			"type" => "sqlite", // or "mysql"
			"sqlite" => [
				"file" => "data.sqlite"
			],
			"mysql" => [
				"host" => "127.0.0.1",
				"username" => "root",
				"password" => ""
			]
		]);
		$this->save = new SQL($conf, "currencies", $this->api->getMain()->getDataFolder());
	}


	public function registerCurrency(string $pluginName, Currency $currency): void {
		$name = strtolower($currency->getName());
		if ($this->existsCurrency($name)) throw new \Exception("Currency already exists!");

		$this->currencies[$name] = $currency;
		$this->pluginsOfCurrencies[$name] = $pluginName;

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
		return $this->getCurrencyByName(strtolower($name)) != null ? true : false;
	}


	public function getPluginNameByCurrencyName(string $name) : string {
		return $this->pluginsOfCurrencies[strtolower($name)];
	}

	public function getPluginNameByCurrency(Currency $currency) : string {
		return $this->getPluginNameByCurrencyName(strtolower($currency->getName()));
	}


	public function saveCurrencyData(string $name): void {
		$name = strtolower($name);
		$currency = $this->getCurrencyByName($name);

		$data = [
			"price" => round($currency->getPrice(), 2)
		];

		$this->save->set($name, $data);
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