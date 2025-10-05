<?php
namespace gmp\eco\player;

use pocketmine\player\OfflinePlayer as OPlayer;
use pocketmine\nbt\tag\CompoundTag;
use gmp\eco\util\SQL;
use gmp\eco\API;

class OfflinePlayer extends OPlayer {
	private ?API $api = null;
	private SQL $save;

	public function __construct(
		private string $name,
		private ?CompoundTag $namedtag,
		API $api,
		SQL $sql
	){
		parent::__construct(str_replace(" ", "_", $name), $namedtag);
		$this->init($api, $sql);
	}

	public function init(API $api, SQL $sql): void {
		$this->api = $api;
		$this->save = $sql;
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