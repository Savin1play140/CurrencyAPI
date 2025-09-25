<?php
namespace gmp\eco;

use pocketmine\event\player\{PlayerJoinEvent, PlayerMoveEvent, PlayerQuitEvent, PlayerCreationEvent};
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\plugin\Plugin;
use pocketmine\utils\Config;
use pocketmine\Server;

use gmp\eco\command\CurrencyCommand;
use gmp\eco\currency\{Dollar, CoinIO, Currency};
use gmp\eco\player\Player;

use gmp\eco\command\api\PacketHooker;

final class API extends PluginBase implements Listener {
	private int $dollarPrice = 1;
	private static array $currences = [];
	private static \AttachableLogger $logger;
	private static ?API $instance = null;
	private static Config $api_config;
	private static Config $lang;
	private static string $pluginName = "";

	
	public function onEnable(): void {
		@mkdir($this->getDataFolder());
		@mkdir($this->getDataFolder()."lang/");
		@mkdir($this->getDataFolder()."players/");
		self::$api_config = new Config(
			$this->getDataFolder()."settings.yml",
			Config::YAML,
			[
				"lang" => "EN_US",
				"coin_coff_buy" => "0.0026",
				"coin_coff_sell" => "0.0025"
			]
		);
		$this->getLogger()->info("Config dir: ".($this->getDataFolder()."settings.yml"));
		self::$lang = new Config(
			$this->getDataFolder()."lang/".self::$api_config->get("lang", "EN_US").".json",
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
					"nocurrency" => "ERROR: nocurrency"
				],
				"subform" => [
					"not_buy" => "you're missing {c.name}, count: {count.not}",
					"not_sell" => "you're missing {c.name}, count: {count.not}"
				]
			]
		);
		self::$instance = $this;
		self::$logger = $this->getLogger();
		self::$pluginName = $this->getName();
		$this->getServer()->getPluginManager()->registerEvents($this, $this);


		self::registerCurrency(new Dollar());
		self::registerCurrency(new CoinIO());
		if(!PacketHooker::isRegistered()) {
			PacketHooker::register($this);
		}
		$this->getLogger()->info("Configured language: ".self::$api_config->get("lang", "EN_US"));
		$this->getLogger()->info("CoinIO coofficent for \"Buy\": ".self::$api_config->get("coin_coff_buy", 0.01));
		$this->getLogger()->info("CoinIO coofficent for \"Sell\": ".self::$api_config->get("coin_coff_sell", 0.01));
	}
	public static function getLang(): Config {
		if (self::$lang === null) {
			self::$lang = new Config($this->getDataFolder()."lang/".self::$api_config->get("lang", "EN_US").".json");
		}
		return self::$lang;
	}
	public static function getAPIConfig(): Config {
		if (self::$api_config === null) {
			self::$api_config = new Config($this->getDataFolder()."settings.yml", Config::YAML);
		}
		return self::$api_config;
	}
	public function onCreation(PlayerCreationEvent $event): void {
		$event->setBaseClass(Player::class);
		$event->setPlayerClass(Player::class);
	}
	public function onJoin(PlayerJoinEvent $event): void {
		$player = $event->getPlayer();
		$config = new Config($this->getDataFolder()."players/".$player->getName().".json",Config::JSON);
		$config->setDefaults(["dollar" => 100]);
		$player->Init($this, $config);
		$this->getLogger()->debug("Player use class: ".get_class($player));
	}
	public static function getFolder(): string {
		return self::$instance->getDataFolder();
	}
	public function playerQuit(PlayerQuitEvent $event): void {
		$player = $event->getPlayer();
		$this->PlayerQ($player);
	}
	private function PlayerQ(Player $player): void {
		$player->removeCurrentWindow();
		$player->saveConfig();
	}

	public function onDisable(): void {
		foreach ($this->getServer()->getOnlinePlayers() as $player) {
			$this->PlayerQ($player);
		}
	}

	public static function registerCurrency(Currency $currency, string $pluginName = null): void {
		if ($pluginName === null) $pluginName = self::$pluginName;
		self::$currences[strtolower($currency->getName())] = $currency;
		Server::getInstance()->getCommandMap()->register($pluginName, new CurrencyCommand($currency, self::$instance));
	}
	public static function getCurrences(): array {
		return self::$currences;
	}
	public static function getCurrencyByName(string $name): ?Currency {
		if (isset(self::$currences[strtolower($name)])) return self::$currences[strtolower($name)];
		return null;
	}
	public static function existCurrency(string $name): bool {
		$null = false;
		if (is_null(self::$currences[strtolower($name)])) $null = true;

		$is_currency = false;
		if (self::$currences[strtolower($name)] instanceof Currency) $is_currency = true;
		
		if ($null == false && $is_currency == true) {
			return true;
		} else {
			return false;
		}
	}
	public static function Logger(): \AttachableLogger {
		return self::$logger;
	}
}