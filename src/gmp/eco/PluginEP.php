<?php
namespace gmp\eco;

use pocketmine\event\player\{PlayerJoinEvent, PlayerMoveEvent, PlayerQuitEvent, PlayerCreationEvent};
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\utils\Config;

use gmp\eco\player\{Player, OfflinePlayer};


final class PluginEP extends PluginBase implements Listener {
	private API $api;

	public function onEnable(): void {
		$this->api = new API($this);
		$this->api->init($this->getLogger());

		$this->getServer()->getPluginManager()->registerEvents($this, $this);
	}


	public function onCreation(PlayerCreationEvent $event): void {
		$event->setBaseClass(Player::class);
		$event->setPlayerClass(Player::class);
	}

	public function onJoin(PlayerJoinEvent $event): void {
		$player = $event->getPlayer();
		if (!($player instanceof Player)) return;

		$config = new Config($this->getDataFolder()."players/".$player->getName().".json", Config::JSON);
		$config->setDefaults(["dollar" => 100]);
		$player->init($this->api, $config);

		$this->api->getPlayerManager()->playerJoin($player);
	}

	public function playerQuit(PlayerQuitEvent $event): void {
		$player = $event->getPlayer();
		if (!($player instanceof Player)) return;
		$this->api->PlayerQ($player);

		$config = new Config($this->getDataFolder()."players/".$player->getName().".json", Config::JSON);
		$config->setDefaults(["dollar" => 100]);
		$offlinePlayer = new OfflinePlayer($player->getName(), $player->getSaveData(), $this->api, $config);

		$this->api->getPlayerManager()->playerQuit($offlinePlayer);
	}

	public function onDisable(): void {
		foreach ($this->getServer()->getOnlinePlayers() as $player) {
			if (!($player instanceof Player)) continue;
			$this->api->PlayerQ($player);
		}

		$this->api->getCurrencyManager()->saveCurrenciesData();
	}
}