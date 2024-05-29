<?php
namespace gmp\eco\command\sub;

use CortexPE\Commando\BaseSubCommand;
use CortexPE\Commando\args\{RawStringArgument, IntegerArgument};

use pocketmine\command\{Command, CommandSender};
use pocketmine\permission\DefaultPermissions;
use pocketmine\Server;

use gmp\eco\player\Player;
use gmp\eco\{API, Form};
use gmp\eco\currency\Currency;

class SubTransactionCommand extends BaseSubCommand {
	public function __construct(
		private Currency $currency,
		private API $API
	) {
		parent::__construct("transaction", "transaction between balances currency");
		$this->setPermission(DefaultPermissions::ROOT_USER);
	}
	protected function prepare(): void {
		$this->registerArgument(0, new IntegerArgument("count", false));
		$this->registerArgument(1, new RawStringArgument("player", false));
	}
	public function onRun(CommandSender $sender, string $aliasUsed, array $args): void {
		$target = Server::getInstance()->getPlayerExact($args["player"]);
		if (!$target instanceof Player) return;
		if (isset($args["count"])) {
			$count = $args["count"];
		} else {
			$this->sendUsage();
			return;
		}
		$sender->transaction($this->currency->getName(), $count, $target);
	}
}
