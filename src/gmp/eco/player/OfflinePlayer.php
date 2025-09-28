<?php
namespace gmp\eco\player;

use pocketmine\player\OfflinePlayer as OPlayer;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\utils\Config;
use gmp\eco\API;

class offlinePlayer extends OPlayer {
	private ?API $API = null;
	private Config $save;

	public function __construct(
		private string $name,
		private ?CompoundTag $namedtag,
		API $api,
		Config $conf
	){
		parent::__construct($name, $namedtag);
		$this->init($api, $conf);
	}

	public function init(API $API, Config $conf): void {
		$this->API = $API;
		$this->save = $conf;
	}


	public function haveCurrency(string $name): bool {
		$name = strtolower($name);
		if (!API::getCurrencyManager()->existsCurrency($name)) return false;
		if ($this->save->get($name) === null) return false;
		return true;
	}

	public function get(string $currencyName): float {
		$currencyName = strtolower($currencyName);
		if (!$this->haveCurrency($currencyName)) return 0;

		return round($this->save->get($currencyName), 2);
	}
}