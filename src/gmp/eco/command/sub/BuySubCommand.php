<?php
namespace gmp\eco\command\sub;

use CortexPE\Commando\BaseSubCommand;
use CortexPE\Commando\args\{RawStringArgument, FloatArgument};
use pocketmine\command\{CommandSender};
use pocketmine\permission\DefaultPermissions;
use gmp\eco\{API, PluginEP};
use gmp\eco\currency\Currency;

class BuySubCommand extends BaseSubCommand {
	public function __construct(
		PluginEP $pluginEP,
		private Currency $currency,
		private API $API
	) {
		parent::__construct($pluginEP, "buy", "buy currency");
		$this->setPermission(DefaultPermissions::ROOT_USER);
	}

	public function getAPI() : API{
		return $this->API;
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

		if(!$sender instanceof \gmp\eco\player\Player){
			//Assertion Fault
			return;
		}

		$count = $args["count"] ?? null;
		if ($count === null) return;
		$count = round($count, 2);
		if ($count <= 0) return;

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
