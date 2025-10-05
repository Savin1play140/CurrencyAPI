<?php
namespace gmp\eco;

use pocketmine\event\player\{
	PlayerJoinEvent, PlayerMoveEvent,
	PlayerQuitEvent, PlayerCreationEvent,
	PlayerLoginEvent
};
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\utils\Config;
use gmp\eco\util\database\libasynql;

use gmp\eco\player\{Player, OfflinePlayer};
use gmp\eco\util\SQL;


final class PluginEP extends PluginBase implements Listener {
	private API $api;
	private array $conf;

	public function onEnable(): void {
		$this->api = new API($this);
		
		$this->conf = $this->api->getAPIConfig()->get("database", [
			"type" => "sqlite", // or "mysql"
			"sqlite" => [
				"file" => "sqlite.db"
			],
			"mysql" => [
				"host" => "127.0.0.1",
				"username" => "root",
				"password" => ""
			]
		]);

		$this->getServer()->getPluginManager()->registerEvents($this, $this);
	}


	public function onCreation(PlayerCreationEvent $event): void {
		$event->setBaseClass(Player::class);
		$event->setPlayerClass(Player::class);
	}

	public function onJoin(PlayerJoinEvent $event): void {
		$player = $event->getPlayer();
		if (!($player instanceof Player)) return;

		try {
			$sql = new SQL($this->conf, $player->getName(), $this->getDataFolder());
			$sql->setDefaults(["dollar" => 100]);

			$player->init($this->api, $sql);

			$this->api->getPlayerManager()->playerJoin($player);
		} catch (Exception|Error $e) {
			$player->kick("Internal error");
			$this->getLogger()->error($e);
		}

	}

	public function playerQuit(PlayerQuitEvent $event): void {
		$player = $event->getPlayer();
		if (!($player instanceof Player)) return;
		$this->api->PlayerQ($player);

		$sql = new SQL($this->conf, $player->getName(), $this->getDataFolder());
		$sql->setDefaults(["dollar" => 100]);

		$offlinePlayer = new OfflinePlayer($player->getName(), $player->getSaveData(), $this->api, $sql);

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