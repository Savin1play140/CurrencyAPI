<?php
namespace gmp\eco\player;

use pocketmine\network\mcpe\NetworkSession;
use pocketmine\player\Player as PPlayer;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\player\PlayerInfo;
use pocketmine\lang\Translatable;
use pocketmine\entity\Location;
use pocketmine\utils\Config;
use pocketmine\Server;

use gmp\eco\event\{AddEvent, RemoveEvent, SetEvent, TransactionEvent};
use gmp\eco\util\SQL;
use gmp\eco\API;
use Exception;
use Error;

final class Player extends PPlayer {
	private ?API $API = null;
	private SQL $save;

	public function __construct(Server $server, NetworkSession $session, PlayerInfo $oldPI, bool $authenticated, Location $spawnLocation, ?CompoundTag $namedtag) {
		$username = str_replace(" ", "_", $oldPI->getUsername());
		$newPI = new PlayerInfo($username, $oldPI->getUuid(), $oldPI->getSkin(), $oldPI->getLocale(), $oldPI->getExtraData());
		parent::__construct($server, $session, $newPI, $authenticated, $spawnLocation, $namedtag);
	}

	public function init(API $API, SQL $conf): void {
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

	public function set(string $currencyName, float $count, bool $message = true, bool $event = true): void {
		$currencyName = strtolower($currencyName);
		if (!API::getCurrencyManager()->existsCurrency($currencyName)) return;

		if ($event) {
			$event = new SetEvent($this, $count, API::getCurrencyManager()->getCurrencyByName($currencyName));
			$event->call();
			if ($event->isCancelled()) return;
		}

		$this->save->set($currencyName, $count);

		$sing = API::getCurrencyManager()->getCurrencyByName($currencyName)->getSing();
		if ($message) $this->sendActionBarMessage(
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


	public function add(string $currencyName, float $count, bool $message = true, bool $event = true): bool {
		$currencyName = strtolower($currencyName);
		if (!API::getCurrencyManager()->existsCurrency($currencyName)) return false;
		$currency = API::getCurrencyManager()->getCurrencyByName($currencyName);

		if ($event) {
			$event = new AddEvent($this, $count, $currency);
			$event->call();
			if ($event->isCancelled()) return false;
		}

		$this->set($currencyName, $this->get($currencyName)+$count, false, false);
		$sing = $currency->getSing();

		if ($message) $this->sendActionBarMessage(
			str_replace(
				["{count}", "{sing}", "{balance}"],
				[number_format($count, 2, ".", ","), $sing, (string) $this->get($currencyName)],
				API::getLang()->getNested("player.add")
			)
		);
		return true;
	}

	public function remove(string $currencyName, float $count, bool $message = true, bool $event = true): bool {
		$currencyName = strtolower($currencyName);
		if (!API::getCurrencyManager()->existsCurrency($currencyName)) return false;

		if (!$this->haveCurrency($currencyName)) {
			if ($message) $this->sendActionBarMessage(
				API::getLang()->getNested("player.nocurrency")
			);
			return false;
		}

		$boolean = $this->get($currencyName) < $count;
		if ($boolean) {
			$sing = API::getCurrencyManager()->getCurrencyByName($currencyName)->getSing();
			if (!$message) $this->sendMessage(
				str_replace(
					["{missing}", "{sing}"],
					[
						number_format($count - $this->get($currencyName), 2, ".", ","),
						$sing
					],
					API::getLang()->getNested("player.nomoney")
				)
			);
			return false;
		}

		if ($event) {
			$event = new RemoveEvent($this, $count, API::getCurrencyManager()->getCurrencyByName($currencyName));
			$event->call();
			if ($event->isCancelled()) return false;
		}

		$this->set($currencyName, $this->get($currencyName)-$count, false, false);

		$sing = API::getCurrencyManager()->getCurrencyByName($currencyName)->getSing();

		if (!$message) $this->sendMessage(
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
		if (!API::getCurrencyManager()->existsCurrency($currencyName)) return false;
		if (!$this->haveCurrency($currencyName)) return false;

		$successfully = $this->remove($currencyName, $count);
		$currency = API::getCurrencyManager()->getCurrencyByName($currencyName);
		if ($successfully) {
			if (!is_null($callable0)) $callable0($currency);
		} else {
			if (!is_null($callable1)) $callable1($currency);
		}
	}

	public function transaction(string $currencyName, float $count, Player $player, bool $event = true): bool {
		if ($event) {
			$event = new TransactionEvent($this, $player, $count, API::getCurrencyManager()->getCurrencyByName($currencyName));
			$event->call();
			if ($event->isCancelled()) return false;
		}

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
		} catch (Exception|Error $e) {
			throw $e;
		}
	}


	public function disconnect(Translatable|string $reason, Translatable|string|null $quitMessage = null, Translatable|string|null $disconnectScreenMessage = null) : void{
		$this->removeCurrentWindow();
		try {
			$this->saveConfig();
			$this->save->save();
			$this->save->close();
		} catch (Exception|Error $e) {
			$this->getServer()->getLogger()->error($e);
		}
		parent::disconnect($reason, $quitMessage, $disconnectScreenMessage);
	}

	public function getAPI() : ?API{
		return $this->API;
	}
}