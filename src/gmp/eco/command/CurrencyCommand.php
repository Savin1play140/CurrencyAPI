<?php
namespace gmp\eco\command;

use CortexPE\Commando\BaseCommand;

use pocketmine\command\{Command, CommandSender};
use pocketmine\permission\DefaultPermissions;

use gmp\eco\player\Player;
use gmp\eco\{API, Form};
use gmp\eco\currency\Currency;
use gmp\eco\command\sub\{SubSetCommand, SubAddCommand, SubRemoveCommand, SubTransactionCommand};

class CurrencyCommand extends BaseCommand {
	public function __construct(
		private Currency $currency,
		public API $API
	) {
		parent::__construct(
			$this->API,
			mb_strtolower($this->currency->getName(), "UTF-8"),
			str_replace("{command.name}", $this->currency->getName(), API::getLang()->getNested("command.about"))
		);
		$this->setPermission(DefaultPermissions::ROOT_USER);
	}

	protected function prepare(): void {
		$this->registerSubCommand(new SubSetCommand($this->currency, $this->API));
		$this->registerSubCommand(new SubAddCommand($this->currency, $this->API));
		$this->registerSubCommand(new SubRemoveCommand($this->currency, $this->API));
		$this->registerSubCommand(new SubTransactionCommand($this->currency, $this->API));
	}

	public function onRun(CommandSender $sender, string $aliasUsed, array $args): void {
		if (!$sender instanceof Player) return;
		$name = $this->currency->getName();
		$currency = $this->currency;
		$sing = API::getCurrencyByName($this->currency->getExchangable())->getSing();
		Form::sendSelf(
			"§l".$name." [".$this->API->getName()."]",
			"§l".$name." price: ".$currency->getPrice().$sing."\nYou have: ".$sender->get($name).$currency->getSing(),
			$sender,
			$this->currency
		);
		return;
	}
}
