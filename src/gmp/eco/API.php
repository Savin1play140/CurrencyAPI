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

use CortexPE\Commando\PacketHooker;

final class API extends PluginBase implements Listener {
	private int $dollarPrice = 1;
	private static array $currences = [];
	private static \AttachableLogger $logger;
	private static ?API $instance = null;

	
	public function onEnable(): void {
		self::$instance = $this;
		self::$logger = $this->getLogger();
		if (!file_exists($this->getDataFolder()."players/")) mkdir($this->getDataFolder()."players/", 0777, true);
		$this->getLogger()->info("Plugn loaded");
		$this->getServer()->getPluginManager()->registerEvents($this, $this);


		self::registerCurrency(new Dollar());
		self::registerCurrency(new CoinIO());
		if(!PacketHooker::isRegistered()) {
			PacketHooker::register($this);
		}
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
		$this->getLogger()->info("Player use class: ".get_class($player));
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

	public static function registerCurrency(Currency $currency): void {
		self::$currences[strtolower($currency->getName())] = $currency;
		Server::getInstance()->getCommandMap()->register("Economy", new CurrencyCommand($currency, self::$instance));
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