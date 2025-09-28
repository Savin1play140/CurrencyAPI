<?php
namespace gmp\eco\player;

use gmp\eco\API;

class PlayerManager {
	private array $offlinePlayers = [];
	private array $onlinePlayers = [];

	public function __construct(
		private API $main
	) {}


	public function playerJoin(Player $player) : void {
		if (!empty($this->offlinePlayers[$player->getName()]))
			unset($this->offlinePlayers[$player->getName()]);

		$this->onlinePlayers[$player->getName()] = $player;
	}

	public function playerQuit(OfflinePlayer $player) : void {
		if (!empty($this->onlinePlayers[$player->getName()]))
			unset($this->onlinePlayers[$player->getName()]);

		$this->offlinePlayers[$player->getName()] = $player;
	}


	public function getOfflinePlayers() : array { return $this->offlinePlayers; }
	public function getOnlinePlayers() : array { return $this->onlinePlayers; }
	public function getAllPlayers() : array { return array_merge($this->offlinePlayers, $this->onlinePlayers); }

	public function getBoughtCurrency(string $currencyName) : float {
		$sum = 0;
		foreach ($this->getAllPlayers() as $playerName => $player) {
			$sum += $player->get($currencyName);
		}
		return $sum;
	}
}
?>