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

class SubSetCommand extends BaseSubCommand {
	public function __construct(
		private Currency $currency,
		private API $API
	) {
		parent::__construct("set", "set balance currency");
		$this->setPermission(DefaultPermissions::ROOT_OPERATOR);
	}
	protected function prepare(): void {
		$this->registerArgument(0, new IntegerArgument("count", false));
		$this->registerArgument(1, new RawStringArgument("player", true));
	}
	public function onRun(CommandSender $sender, string $aliasUsed, array $args): void {
		$target = $sender;
		if (isset($args["player"])) {
			$target = Server::getInstance()->getPlayerExact($args["player"]);
			if (is_null($target)) {
				$sender->sendMessage("§l§cPlayer not found online");
			}
		}
		if (!$target instanceof Player) return;
		if (isset($args["count"])) {
			$count = $args["count"];
		} else {
			$this->sendUsage();
			return;
		}
		$target->set($this->currency->getName(), $count);
		$sender->sendMessage("§l§a{$count}{$this->currency->getSing()} seted to player {$target->getName()}");
	}
}
