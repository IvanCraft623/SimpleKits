<?php

declare(strict_types=1);

namespace IvanCraft623\SimpleKits\command;

use IvanCraft623\SimpleKits\SimpleKits;
use IvanCraft623\SimpleKits\kit\KitEditor;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\PluginOwned;

final class SimpleKitsCommand extends Command implements PluginOwned {

	private SimpleKits $plugin;

	public function __construct(SimpleKits $plugin) {
		parent::__construct('simplekits', 'Administrate kits');
		$this->plugin = $plugin;
		$this->setPermission("simplekits.simplekits.command");
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
			$sender->sendMessage($this->plugin->getTranslator()->translate($sender, "only-ingame"));
		} else {
			if (isset($args[0])) {
				switch (strtolower($args[0])) {
					case 'create':
						if (!isset($args[1])) {
							$sender->sendMessage($this->plugin->getTranslator()->translate($sender, "command.simplekits.create.usage"));
							return;
						}
						if ($this->plugin->getKitManager()->kitExists($args[1])) {
							$sender->sendMessage($this->plugin->getTranslator()->translate($sender, "command.simplekits.create.aready-exists"));
							return;
						}
						new KitEditor($sender, $args[1]);
					break;

					case 'kits':
						$this->plugin->getFormManager()->sendKitsManager($sender);
					break;

					case 'players':
						$page = 1;
						if (isset($args[1])) {
							if (!is_numeric($args[1])) {
								$sender->sendMessage($this->plugin->getTranslator()->translate($sender, "command.simplekits.players.page.no-numeric"));
								return;
							}
							$page = (int)$args[1];
						}
						$this->plugin->getFormManager()->sendPlayersList($sender, $page);
					break;

					case 'playerdata':
						if (!isset($args[1])) {
							$sender->sendMessage($this->plugin->getTranslator()->translate($sender, "command.simplekits.playerdata.usage"));
							return;
						}
						$playerdata = $this->plugin->getDataManager()->getPlayerData($args[1]);
						if ($playerdata === null) {
							$sender->sendMessage($this->plugin->getTranslator()->translate($sender, "command.simplekits.playerdata.no-found", ["%player" => $args[1]]));
							return;
						}
						$this->plugin->getFormManager()->sendManagePlayer($sender, $playerdata);
					break;

					default:
						$sender->sendMessage(
							$this->plugin->getTranslator()->translate($sender, "command.simplekits.usage.title")."\n"."\n".
							$this->plugin->getTranslator()->translate($sender, "command.simplekits.usage.line1")."\n".
							$this->plugin->getTranslator()->translate($sender, "command.simplekits.usage.line2")."\n".
							$this->plugin->getTranslator()->translate($sender, "command.simplekits.usage.line3")."\n".
							$this->plugin->getTranslator()->translate($sender, "command.simplekits.usage.line4")
						);
					break;
				}
				return;
			}
			$this->plugin->getFormManager()->sendSimpleKitsManager($sender);
		}
	}
}
