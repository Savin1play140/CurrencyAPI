<?php
namespace gmp\eco\command\sub;

use CortexPE\Commando\BaseSubCommand;
use CortexPE\Commando\args\{RawStringArgument, FloatArgument};

use pocketmine\command\{Command, CommandSender};
use pocketmine\permission\DefaultPermissions;
use pocketmine\Server;

use gmp\eco\player\Player;
use gmp\eco\{API, Form, PluginEP};
use gmp\eco\currency\Currency;

class SellSubCommand extends BaseSubCommand {
	public function __construct(
		PluginEP $pluginEP,
		private Currency $currency,
		private API $API
	) {
		parent::__construct($pluginEP, "sell", "sell currency");
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

		if (!$currency->isSalable()) {
			$sender->sendMessage("You can't sell");
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
