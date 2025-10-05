<?php
namespace gmp\eco\event;

use pocketmine\event\Event;
use pocketmine\event\Cancellable;
use gmp\eco\player\Player;
use gmp\eco\currency\Currency;
use pocketmine\event\CancellableTrait;

class TransactionEvent extends Event implements Cancellable {
	use CancellableTrait;

    public function __construct(
		private Player $player,
		private Player $target,
		private float $count,
		private Currency $currency
	) {}

	public function getPlayer() : Player { return $this->player; }
	public function getTarget() : Player { return $this->target; }
	public function getCount() : float { return $this->count; }
	public function getCurrency() : Currency { return $this->currency; }
}