<?php
namespace gmp\eco;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\plugin\Plugin;
use pocketmine\utils\Config;
use pocketmine\Server;

use gmp\eco\command\CurrencyCommand;
use gmp\eco\currency\{Dollar, CoinIO, Currency, CurrencyManager};
use gmp\eco\player\PlayerManager;
use gmp\eco\player\Player;

use gmp\eco\command\api\PacketHooker;

final class API {
	private static \AttachableLogger $logger;
	private static ?API $instance = null;
	private static Config $api_config;
	private static Config $lang;
	private static CurrencyManager $cm;
	private static PlayerManager $pm;

	public function __construct(
		private PluginEP $main
	) {
		$this->init($this->main->getLogger());
	}

	public static function getCurrencyManager(): CurrencyManager { return self::$cm; }
	public static function getPlayerManager(): PlayerManager { return self::$pm; }
	public function getMain(): PluginEP { return $this->main; }


	public function init(\AttachableLogger $logger): void {
		@mkdir($this->main->getDataFolder());
		@mkdir($this->main->getDataFolder()."lang/");

		self::$api_config = new Config(
			$this->main->getDataFolder()."settings.yml",
			Config::YAML,
			[
				"lang" => "EN_US",
				"coin_coff_buy" => "0.0026",
				"coin_coff_sell" => "0.0025",
				"database" => [
					"type" => "sqlite",
					"sqlite" => [
						"file" => "data.db"
					],
					"mysql" => [
						"host" => "127.0.0.1",
						"username" => "root",
						"password" => ""
					]
				]
			]
		);

		self::$cm = new CurrencyManager($this);
		self::$pm = new PlayerManager($this);

		$logger->info("Config file: ".($this->main->getDataFolder()."settings.yml"));

		$database = self::$api_config->get("database");

		$logger->info("Database type: ".$database["type"]);
		if ($database["type"] == "sqlite") {
			$logger->info("Database file: ".$this->main->getDataFolder().$database["sqlite"]["file"]);
		} elseif ($database["type"] == "mysql") {
			$logger->info("Database host: ".$database["mysql"]["host"]);
			$logger->info("Database user: ".$database["mysql"]["username"]);
		}

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

		self::$cm->registerCurrency($this->main->getName(), new Dollar());
		self::$cm->registerCurrency($this->main->getName(), new CoinIO());

		if(!PacketHooker::isRegistered()) {
			PacketHooker::register($this->main);
		}

		$logger->info("Configured language: ".self::$api_config->get("lang", "EN_US"));
		$logger->info("CoinIO coefficient for \"Buy\": ".self::$api_config->get("coin_coff_buy", 0.01));
		$logger->info("CoinIO coefficient for \"Sell\": ".self::$api_config->get("coin_coff_sell", 0.01));

	}


	public static function getLang(): Config {
		if (self::$lang == null && $main != null)
			self::$lang = new Config(
				self::$instance->main->getDataFolder()."lang/".self::$api_config->get("lang", "EN_US").".json",
				Config::JSON
			);

		return self::$lang;
	}


	public static function getAPIConfig(): Config {
		if (self::$api_config == null && $main != null)
			self::$api_config = new Config(
				self::$instance->main->getDataFolder()."settings.yml",
				Config::YAML
			);

		return self::$api_config;
	}


	public function PlayerQ(Player $player): void {
		$player->removeCurrentWindow();
		$player->saveConfig();
	}


	public static function Logger(): \AttachableLogger {
		return self::$logger;
	}
}