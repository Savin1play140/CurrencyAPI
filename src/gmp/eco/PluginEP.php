<?php
namespace gmp\eco;

use pocketmine\event\player\{PlayerJoinEvent, PlayerMoveEvent, PlayerQuitEvent, PlayerCreationEvent};
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\utils\Config;

use gmp\eco\player\Player;


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
		$config = new Config($this->getDataFolder()."players/".$player->getName().".json", Config::JSON);
		$config->setDefaults(["dollar" => 100]);
		$player->Init($this->api, $config);
		$this->getLogger()->debug("Player use class: ".get_class($player));
	}

	public function playerQuit(PlayerQuitEvent $event): void {
		$player = $event->getPlayer();
		$this->api->PlayerQ($player);
	}

	public function onDisable(): void {
		foreach ($this->getServer()->getOnlinePlayers() as $player) {
			$this->api->PlayerQ($player);
		}
	}
}