<?php

declare(strict_types=1);

namespace IvanCraft623\SimpleKits\command;

use IvanCraft623\SimpleKits\SimpleKits;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\PluginOwned;

final class KitsCommand extends Command implements PluginOwned {

	private SimpleKits $plugin;

	public function __construct(SimpleKits $plugin) {
		parent::__construct('kits', 'See avaible kits');
		$this->plugin = $plugin;
		$this->setPermission("simplekits.kits.command");
	}

	public function getOwningPlugin(): SimpleKits {
		return $this->plugin;
	}

	public function execute(CommandSender $sender, string $label, array $args) {
		if (!$this->testPermission($sender)) {
			$sender->sendMessage($this->plugin->getTranslator()->translate($sender, "no.permission"));
			return;
		}
		if (!$sender instanceof Player) {
			$kits = $this->plugin->getKitManager()->getKits();
			$sender->sendMessage($this->plugin->getTranslator()->translate($sender, "command.kits.title", ["%kits" => count($kits)]));
			foreach ($kits as $kit) {
				$sender->sendMessage($this->plugin->getTranslator()->translate($sender, "command.kits.content", [
					"%kit"         => $kit->getName(),
					"%price"       => $kit->getPrice(),
					"%description" => $kit->getDescription(),
					"%permission"  => $kit->getPermission(),
					"%items"       => $kit->getItemsCount()
					]
				));
			}
		} else {
			$this->plugin->getFormManager()->sendSelectKit($sender);
		}
	}
}
