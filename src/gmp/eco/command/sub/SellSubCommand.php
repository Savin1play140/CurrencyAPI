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

class SellSubCommand extends BaseSubCommand {
	public function __construct(
		private Currency $currency,
		private API $API
	) {
		parent::__construct("sell", "sell currency");
		$this->setPermission(DefaultPermissions::ROOT_USER);
	}
	protected function prepare(): void {
		$this->registerArgument(0, new FloatArgument("count", false));
	}
	public function onRun(CommandSender $sender, string $aliasUsed, array $args): void {
		$currency = $this->currency;

		if (!$currency->isSalable()) {
			$sender->sendMessage("You can't sell");
			return;
		}

		$count = round($args["count"], 2);
		if ($count <= 0 or is_null($count)) return;

		if (round($sender->get($currency->getName()), 2) < round($count, 2)) {
			$sender->sendMessage("you're missing ".$currency->getName().", count: ".number_format($count-$sender->get($currency->getName()), 0, ".", ","));
			$sender->sendMessage("for selling ".$currency->getName()."(".$currency->getSing().")");
			return;
		}

		$sender->remove($currency->getName(), round($count, 2));
		$sender->add($currency->getExchangeable(), round(round($currency->getPrice(), 2)*$count, 2));
		$currency->onSell(round($count, 2));

	}
}
