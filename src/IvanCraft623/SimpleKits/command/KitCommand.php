<?php

declare(strict_types=1);

namespace IvanCraft623\SimpleKits\command;

use IvanCraft623\SimpleKits\SimpleKits;
use IvanCraft623\SimpleKits\kit\Kit;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\PluginOwned;

final class KitCommand extends Command implements PluginOwned {

	private SimpleKits $plugin;

	public function __construct(SimpleKits $plugin) {
		parent::__construct('kit', 'Claim selected kit');
		$this->plugin = $plugin;
	}

	public function getOwningPlugin(): SimpleKits {
		return $this->plugin;
	}

	public function execute(CommandSender $sender, string $label, array $args) {
		if (!$sender instanceof Player) {
			$sender->sendMessage($this->plugin->getTranslator()->translate($sender, "only-ingame"));
		} else {
			if (($name = $this->plugin->getDataManager()->getPlayerData($sender)?->getSelectedKit()) !== null) {
				if (($kit = $this->plugin->getKitManager()->getKit($name)) !== null) {
					$kit->giveItems($sender);
					$sender->sendMessage($this->plugin->getTranslator()->translate($sender, "kit.claim.success", ["%kit" => $kit->getName()]));
					return;
				}
			}
			$sender->sendMessage($this->plugin->getTranslator()->translate($sender, "kit.claim.error"));
		}
	}
}
