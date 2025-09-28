<?php
namespace gmp\eco\command;

use gmp\eco\command\api\BaseCommand;

use pocketmine\command\{Command, CommandSender};
use pocketmine\plugin\{PluginOwned, Plugin};
use pocketmine\permission\DefaultPermissions;

use gmp\eco\player\Player;
use gmp\eco\{API, Form};
use gmp\eco\currency\Currency;
use gmp\eco\command\sub\{
	BuySubCommand, SellSubCommand,
	SubSetCommand, SubAddCommand,
	SubRemoveCommand, SubTransactionCommand
};

class CurrencyCommand extends BaseCommand implements PluginOwned {
	public function getOwningPlugin(): Plugin {
		return $this->API;
	}
	public function __construct(
		private Currency $currency,
		public API $API
	) {
		parent::__construct(
			$this->API->getMain(),
			mb_strtolower($this->currency->getName(), "UTF-8"),
			str_replace("{command.name}", $this->currency->getName(), API::getLang()->getNested("command.about"))
		);
		$this->setPermission(DefaultPermissions::ROOT_USER);
	}

	protected function prepare(): void {
		$this->registerSubCommand(new BuySubCommand($this->currency, $this->API));
		$this->registerSubCommand(new SellSubCommand($this->currency, $this->API));
		$this->registerSubCommand(new SubSetCommand($this->currency, $this->API));
		$this->registerSubCommand(new SubAddCommand($this->currency, $this->API));
		$this->registerSubCommand(new SubRemoveCommand($this->currency, $this->API));
		$this->registerSubCommand(new SubTransactionCommand($this->currency, $this->API));
	}

	public function onRun(CommandSender $sender, string $aliasUsed, array $args): void {
		if (!$sender instanceof Player) return;
		$name = $this->currency->getName();
		$currency = $this->currency;
		$sing = API::getCurrencyByName($this->currency->getExchangeable())->getSing();
		Form::sendSelf(
			"§l".$name." [".$this->API->getPluginNameByCurrency($currency)."]",
			"§l".$name." price: ".number_format($currency->getPrice(), 2, ".", ",").$sing."\nYou have: ".number_format($sender->get($name), 2, ".", ",").$currency->getSing(),
			$sender,
			$this->currency
		);
		return;
	}
}
