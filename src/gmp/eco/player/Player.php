<?php
namespace gmp\eco\player;

use pocketmine\player\Player as PPlayer;
use pocketmine\utils\Config;
use pocketmine\lang\Translatable;

use gmp\eco\API;

final class Player extends PPlayer {
	private ?API $API = null;
	private Config $save;

	public function Init(API $API, Config $conf): void {
		$this->username = str_replace(" ", "_", $this->getName());
		$this->API = $API;
		$this->save = $conf;
	}

	public function haveCurrency(string $name): bool {
		$name = strtolower($name);
		if (!API::existCurrency($name)) return false;
		if ($this->save->get($name) == null) return false;
		return true;
	}


	public function get(string $currencyName): int {
		$currencyName = strtolower($currencyName);
		if (!$this->haveCurrency($currencyName)) return 0;
		return $this->save->get($currencyName);
	}
	public function set(string $currencyName, int $count): void {
		$currencyName = strtolower($currencyName);
		if (!API::existCurrency($currencyName)) return;
		$this->save->set($currencyName, $count);
		$sing = API::getCurrencyByName($currencyName)->getSing();
		$this->sendActionBarMessage("§2§lYour balance {$currencyName}'s is {$count}{$sing} now");
	}

	public function add(string $currencyName, int $count): void {
		$currencyName = strtolower($currencyName);
		if (!API::existCurrency($currencyName)) return;
		$this->save->set($currencyName, $this->save->get($currencyName)+$count);
		$sing = API::getCurrencyByName($currencyName)->getSing();
		$this->sendActionBarMessage("§2§l{$count}{$sing} added to your balance {$currencyName}'s, balance is: {$this->save->get($currencyName)}");
	}
	public function remove(string $currencyName, int $count): bool {
		$currencyName = strtolower($currencyName);
		if (!$this->haveCurrency($currencyName)) return false;
		if ($this->save->get($currencyName) < $count) return false;
		$this->save->set($currencyName, $this->save->get($currencyName)-$count);
		$sing = API::getCurrencyByName($currencyName)->getSing();
		$this->sendActionBarMessage("§2§l{$count}{$sing} removed to your balance {$currencyName}'s, balance is: {$this->save->get($currencyName)}");
		return true;
	}
	public function purchase(string $currencyName, int $count, ?callable $callable0, ?callable $callable1) {
		$currencyName = strtolower($currencyName);
		if (!$this->haveCurrency($currencyName)) return false;

		$succsesful = $this->remove($currencyName, $count);
		$currency = API::getCurrenceByName($currencyName);
		if ($succsesful) {
			if (!is_null($callable0)) $callable0($currency);
		} else {
			if (!is_null($callable1)) $callable1($currency);
		}
	}


	public function saveConfig(): void {
		$this->save->save();
		$this->sendActionBarMessage("§2§lYour balance saved");
	}
	public function disconnect(Translatable|string $reason, Translatable|string|null $quitMessage = null, Translatable|string|null $disconnectScreenMessage = null) : void{
		$this->removeCurrentWindow();
		$this->saveConfig();
		$this->save->save();
		parent::disconnect($reason, $quitMessage, $disconnectScreenMessage);
	}
}