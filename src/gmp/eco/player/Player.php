<?php
namespace gmp\eco\player;

use pocketmine\player\Player as PPlayer;
use pocketmine\utils\Config;
use pocketmine\lang\Translatable;

use gmp\eco\API;

final class Player extends PPlayer {
	private ?API $API = null;
	private Config $save;

	public function init(API $API, Config $conf): void {
		$this->username = str_replace(" ", "_", $this->getName());
		$this->API = $API;
		$this->save = $conf;
	}

	public function haveCurrency(string $name): bool {
		$name = strtolower($name);
		if (!API::existsCurrency($name)) return false;
		if ($this->save->get($name) === null) return false;
		return true;
	}


	public function get(string $currencyName): float {
		$currencyName = strtolower($currencyName);
		if (!$this->haveCurrency($currencyName)) return 0;
		return round($this->save->get($currencyName), 2);
	}
	public function set(string $currencyName, float $count): void {
		$currencyName = strtolower($currencyName);
		if (!API::existsCurrency($currencyName)) return;
		$this->save->set($currencyName, $count);
		$sing = API::getCurrencyByName($currencyName)->getSing();
		$this->sendActionBarMessage(
			str_replace(
				"{count}",
				number_format($count, 2, ".", ","),
				str_replace(
					"{sing}",
					$sing,
					API::getLang()->getNested("player.set")
				)
			)
		);
	}

	public function add(string $currencyName, float $count): void {
		$currencyName = strtolower($currencyName);
		if (!API::existsCurrency($currencyName)) return;
		$this->set($currencyName, $this->get($currencyName)+$count);
		$sing = API::getCurrencyByName($currencyName)->getSing();
		$this->sendActionBarMessage(
			str_replace(
				"{count}",
				number_format($count, 2, ".", ","),
				str_replace(
					"{sing}",
					$sing,
					str_replace(
						"{balance}",
						$this->get($currencyName),
						API::getLang()->getNested("player.add")
					)
				)
			)
		);
	}
	public function remove(string $currencyName, float $count, bool $no_message = false): bool {
		$currencyName = strtolower($currencyName);
		if (!API::existsCurrency($currencyName)) return false;
		if (!$this->haveCurrency($currencyName)) {
			$this->sendActionBarMessage(
				API::getLang()->getNested("player.nocurrency")
			);
			return false;
		}
		$boolean = $this->get($currencyName) < $count;
		if ($boolean) {
			$sing = API::getCurrencyByName($currencyName)->getSing();
			$this->sendActionBarMessage(
				str_replace(
					"{missing}",
					number_format($count-$this->get($currencyName), 2, ".", ","),
					str_replace(
						"{sing}",
						$sing,
						API::getLang()->getNested("player.nomoney")
					)
				)
			);
			return false;
		}
		$this->set($currencyName, $this->get($currencyName)-$count);
		$sing = API::getCurrencyByName($currencyName)->getSing();
		if (!$no_message) $this->sendActionBarMessage(
			str_replace(
				"{count}",
				number_format($count, 2, ".", ","),
				str_replace(
					"{sing}",
					$sing,
					str_replace(
						"{balance}",
						number_format($this->get($currencyName), 2, ".", ","),
						API::getLang()->getNested("player.remove")
					)
				)
			)
		);
		return true;
	}
	public function purchase(string $currencyName, float $count, ?callable $callable0, ?callable $callable1) {
		$currencyName = strtolower($currencyName);
		if (!API::existsCurrency($currencyName)) return false;
		if (!$this->haveCurrency($currencyName)) return false;

		$successfully = $this->remove($currencyName, $count);
		$currency = API::getCurrencyByName($currencyName);
		if ($successfully) {
			if (!is_null($callable0)) $callable0($currency);
		} else {
			if (!is_null($callable1)) $callable1($currency);
		}
	}
	public function transaction(string $currencyName, float $count, Player $player): bool {
		if ($this->remove($currencyName, $count, true)) {
			$player->add($currencyName, $count);
			return true;
		}
		return false;
	}


	public function saveConfig(): void {
		try {
			$this->save->save();
			$this->sendActionBarMessage(API::getLang()->getNested("player.saved"));
		} catch (Error $error) {
			throw $error;
		}
	}
	public function disconnect(Translatable|string $reason, Translatable|string|null $quitMessage = null, Translatable|string|null $disconnectScreenMessage = null) : void{
		$this->removeCurrentWindow();
		$this->saveConfig();
		$this->save->save();
		parent::disconnect($reason, $quitMessage, $disconnectScreenMessage);
	}
}