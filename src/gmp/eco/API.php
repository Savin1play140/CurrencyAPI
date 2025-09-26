<?php
namespace gmp\eco;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\plugin\Plugin;
use pocketmine\utils\Config;
use pocketmine\Server;

use gmp\eco\command\CurrencyCommand;
use gmp\eco\currency\{Dollar, CoinIO, Currency};
use gmp\eco\player\Player;

use gmp\eco\command\api\PacketHooker;

final class API {
	private static array $currencies = [];
	private static array $pluginsOfCurrencies = [];
	private static \AttachableLogger $logger;
	private static ?API $instance = null;
	private static Config $api_config;
	private static Config $lang;

	public function __construct(
		private PluginEP $main
	) {}


	public function getMain(): PluginEP { return $this->main; }


	public function init(\AttachableLogger $logger): void {
		@mkdir($this->main->getDataFolder());
		@mkdir($this->main->getDataFolder()."lang/");
		@mkdir($this->main->getDataFolder()."players/");

		self::$api_config = new Config(
			$this->main->getDataFolder()."settings.yml",
			Config::YAML,
			[
				"lang" => "EN_US",
				"coin_coff_buy" => "0.0026",
				"coin_coff_sell" => "0.0025"
			]
		);

		$logger->info("Config file: ".($this->main->getDataFolder()."settings.yml"));

		self::$lang = new Config(
			$this->main->getDataFolder()."lang/".self::$api_config->get("lang", "EN_US").".json",
			Config::JSON,
			[
				"command" => [
					"about" => "action with {command.name}"
				],
				"sub_command" => [
					"about" => "perform the {name}",
					"not_found" => "Player not found",
					"successful" => "Balance {target.name}'s has {action} to {count}{sing}"
				],
				"player" => [
					"set" => "Your balance is set to {count}{sing}",
					"add" => "{count}{sing} was added to your balance, now it's {balance}{sing}",
					"remove" => "{count}{sing} was removed from your balance, now it's {balance}{sing}",
					"dept" => "Your have been deducted {count}{sing} due to debt, now it's {balance}{sing}",
					"saved" => "Your balance successful saved",
					"nomoney" => "Missing {missing}{sing}",
					"nocurrency" => "ERROR: currency not exists"
				],
				"subform" => [
					"not_buy" => "you're missing {c.name}, count: {count.not}",
					"not_sell" => "you're missing {c.name}, count: {count.not}"
				]
			]
		);
		self::$instance = $this;
		self::$logger = $logger;
		self::$pluginName = $this->main->getName();

		self::registerCurrency($this->main->getName(), new Dollar());
		self::registerCurrency($this->main->getName(), new CoinIO());

		if(!PacketHooker::isRegistered()) {
			PacketHooker::register($this->main);
		}

		$logger->info("Configured language: ".self::$api_config->get("lang", "EN_US"));
		$logger->info("CoinIO coefficient for \"Buy\": ".self::$api_config->get("coin_coff_buy", 0.01));
		$logger->info("CoinIO coefficient for \"Sell\": ".self::$api_config->get("coin_coff_sell", 0.01));

	}


	public static function getLang(): Config {
		if (self::$lang == null) self::$lang = new Config(self::$instance->main->getDataFolder()."lang/".self::$api_config->get("lang", "EN_US").".json");
		return self::$lang;
	}


	public static function getAPIConfig(): Config {
		if (self::$api_config == null) self::$api_config = new Config(self::$instance->main->getDataFolder()."settings.yml", Config::YAML);
		return self::$api_config;
	}


	public function PlayerQ(Player $player): void {
		$player->removeCurrentWindow();
		$player->saveConfig();
	}


	public static function registerCurrency(string $pluginName, Currency $currency): void {
		self::$currencies[strtolower($currency->getName())] = $currency;
		self::$pluginsOfCurrencies[$currency->getName()] = $pluginName;
		Server::getInstance()->getCommandMap()->register($pluginName, new CurrencyCommand($currency, self::$instance));
	}

	public static function getCurrencies(): array {
		return self::$currencies;
	}

	public static function getCurrencyByName(string $name): ?Currency {
		if (isset(self::$currencies[strtolower($name)])) return self::$currencies[strtolower($name)];
		return null;
	}

	public static function existsCurrency(string $name): bool {
		$null = false;
		if (is_null(self::$currencies[strtolower($name)])) $null = true;

		$is_currency = false;
		if (self::$currencies[strtolower($name)] instanceof Currency) $is_currency = true;
		
		if ($null == false && $is_currency == true) {
			return true;
		} else {
			return false;
		}
	}


	public static function getPluginNameByCurrencyName(string $currency) : string {
		return self::$pluginsOfCurrencies[$currency];
	}

	public static function getPluginNameByCurrency(Currency $currency) : string {
		return self::getPluginNameByCurrencyName($currency->getName());
	}


	public static function Logger(): \AttachableLogger {
		return self::$logger;
	}
}