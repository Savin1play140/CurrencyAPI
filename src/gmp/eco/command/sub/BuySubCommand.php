<?php
namespace gmp\eco\command\sub;

use gmp\eco\command\api\BaseSubCommand;
use gmp\eco\command\api\args\{RawStringArgument, FloatArgument};

use pocketmine\command\{Command, CommandSender};
use pocketmine\permission\DefaultPermissions;
use pocketmine\Server;

use gmp\eco\player\Player;
use gmp\eco\{API, Form};
use gmp\eco\currency\Currency;

class BuySubCommand extends BaseSubCommand {
	public function __construct(
		private Currency $currency,
		private API $API
	) {
		parent::__construct("buy", "buy currency");
		$this->setPermission(DefaultPermissions::ROOT_USER);
	}
	protected function prepare(): void {
		$this->registerArgument(0, new FloatArgument("count", false));
	}
	public function onRun(CommandSender $sender, string $aliasUsed, array $args): void {
		$currency = $this->currency;

		if (!$currency->isBuyable()) {
			$sender->sendMessage("You can't buy");
			return;
		}

		$count = round($args["count"], 2);
		if ($count <= 0 or is_null($count)) return;

		if ($sender->get($currency->getExchangeable()) < round(round($currency->getPrice(), 2)*$count, 2)) {
			$sender->sendMessage("you're missing ".$currency->getExchangeable().", count: ".number_format(round(round($currency->getPrice(), 2)*$count, 2)-$sender->get($currency->getExchangeable()), 0, ".", ","));
			$sender->sendMessage("for buying ".$currency->getName()."(".$currency->getSing().")");
			return;
		}

		$sender->remove($currency->getExchangeable(), round(round($currency->getPrice(), 2)*$count, 2));
		$sender->add($currency->getName(), round($count, 2));
		$currency->onBuy(round($count, 2));

	}
}
