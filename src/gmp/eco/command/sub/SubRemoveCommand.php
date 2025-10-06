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

class SubRemoveCommand extends BaseSubCommand {
	public function __construct(
		PluginEP $pluginEP,
		private Currency $currency,
		private API $API
	) {
		parent::__construct($pluginEP, "remove", "remove from balance currency");
		$this->setPermission(DefaultPermissions::ROOT_OPERATOR);
	}

	public function getAPI() : API{
		return $this->API;
	}

	protected function prepare(): void {
		$this->registerArgument(0, new FloatArgument("count", false));
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

		if(!$sender instanceof \gmp\eco\player\Player){
			//Assertion Fault
			return;
		}

		$target->remove($this->currency->getName(), $count);
		$sender->sendMessage(
			str_replace(
				["{count}", "{sing}", "{balance}"],
				[(string)$count, $this->currency->getSing(), (string)$sender->get($this->currency->getName())],
				API::getLang()->getNested("player.remove")
			)
		);
	}
}
