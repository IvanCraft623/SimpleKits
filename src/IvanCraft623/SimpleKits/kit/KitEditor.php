<?php

declare(strict_types=1);

namespace IvanCraft623\SimpleKits\kit;

use IvanCraft623\SimpleKits\SimpleKits;
use IvanCraft623\SimpleKits\form\api\CustomForm;
use IvanCraft623\SimpleKits\kit\Kit;
use IvanCraft623\SimpleKits\translator\Translator;

use pocketmine\event\HandlerListManager;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\item\Item;
use pocketmine\player\Player;
use pocketmine\player\GameMode;

final class KitEditor implements Listener {

	private SimpleKits $plugin;

	private Translator $translator;

	private Player $player;

	private string $name;

	private float $price = 0;

	private string $description = "";

	private string $permission = "";

	private array $inventoryItems = [];

	private array $armorInventoryItems = [];

	private ?Item $offHandItem = null;

	# Backup of player data

	private array $playerInventoryItems = [];

	private array $playerArmorInventoryItems = [];

	private Item $playerOffHandItem;

	private GameMode $playerGameMode;
	
	public function __construct(Player $player, string $name, float $price = 0, string $description = "", string $permission = "", array $inventoryItems = [], array $armorInventoryItems = [], ?Item $offHandItem = null) {
		$this->plugin = SimpleKits::getInstance();
		$this->translator = $this->plugin->getTranslator();
		$this->player = $player;
		$this->name = $name;
		$this->price = $price;
		$this->description = $description;
		$this->permission = $permission;
		$this->inventoryItems = $inventoryItems;
		$this->armorInventoryItems = $armorInventoryItems;
		$this->offHandItem = $offHandItem;
		$this->setConfiguratorMode($player);
		$this->sendForm();
		$this->plugin->getServer()->getPluginManager()->registerEvents($this, $this->plugin);
	}

	private function setConfiguratorMode(Player $player): void {
		# Backup items
		$this->playerInventoryItems = $player->getInventory()->getContents(true);
		$this->playerArmorInventoryItems = $player->getArmorInventory()->getContents(true);
		$this->playerOffHandItem = $player->getOffHandInventory()->getItem(0);
		$this->playerGameMode = $player->getGameMode();

		# Give kit items
		$player->getInventory()->setContents($this->inventoryItems);
		$player->getArmorInventory()->setContents($this->armorInventoryItems);
		$player->getOffHandInventory()->setContents($this->armorInventoryItems);
		if ($this->offHandItem !== null) $player->getOffHandInventory()->setItem(0, $this->offHandItem);
		$player->setGameMode(GameMode::CREATIVE());

		$player->sendMessage($this->translator->translate($player, "kit.editor.enter"));
	}

	private function setPrice(float $price): void {
		$this->price = $price;
	}

	private function setDescription(string $description): void {
		$this->description = $description;
	}

	private function setPermission(string $permission): void {
		$this->permission = $permission;
	}

	private function setInventoryItems(array $inventoryItems): void {
		$this->inventoryItems = $inventoryItems;
	}

	private function setArmorInventoryItems(array $armorInventoryItems): void {
		$this->armorInventoryItems = $armorInventoryItems;
	}

	private function setOffHandItem(?Item $offHandItem): void {
		$this->offHandItem = $offHandItem;
	}
	
	private function save(Kit $kit): void {
		# Save Kit
		$this->plugin->getDataManager()->saveKitData($kit, function() use ($kit) {
			$this->plugin->getKitManager()->registerKit($kit);
			$this->player->sendMessage($this->translator->translate($this->player, "kit.editor.save", ["%kit" => $kit->getName()]));
		});
		# Give old items
		$this->player->getInventory()->setContents($this->playerInventoryItems);
		$this->player->getArmorInventory()->setContents($this->playerArmorInventoryItems);
		$this->player->getOffHandInventory()->setItem(0, $this->playerOffHandItem);
		$this->player->setGameMode($this->playerGameMode);
		# Unregister listeners
		HandlerListManager::global()->unregisterAll($this);
	}
	
	public function chatCommand(PlayerChatEvent $event): void {
		$player = $event->getPlayer();
		$args = explode(" ", $event->getMessage());
		if (strtolower($player->getName()) == strtolower($this->player->getName())) {
			switch (strtolower($args[0])) {
				case "done":
				case "save":
					$offHand = $player->getOffHandInventory()->getItem(0);
					$this->setInventoryItems($player->getInventory()->getContents());
					$this->setArmorInventoryItems($player->getArmorInventory()->getContents());
					$this->setOffHandItem($offHand->isNull() ? null : $offHand);
					$kit = new Kit($this->name, $this->price, $this->description, $this->permission,
						$this->inventoryItems,
						$this->armorInventoryItems,
						$this->offHandItem
					);
					$this->save($kit);
				break;

				case "help":
					$player->sendMessage(
						$this->translator->translate($this->player, "kit.editor.help.title")."\n"."\n".
						$this->translator->translate($this->player, "kit.editor.help.line1")
					);
				break;
				
				default:
					$player->sendMessage($this->translator->translate($this->player, "invalid.command"));
				break;
			}
			$event->cancel();
		}
	}

	private function sendForm(?string $price = null, ?string $description = null, string $permission = null, ?string $error = null): void {
		$form = new CustomForm(function (Player $player, array $result = null) {
			if ($result === null) {
				$this->sendForm();
				return;
			}

			if (!is_numeric($result["price"]) || $result["price"] < 0) {
				$this->sendForm($result["price"], $result["description"], $result["permission"], $this->translator->translate($this->player, "kit.editor.price.error"));
				return;
			}
			$this->setPrice((float)$result["price"]);
			$this->setDescription($result["description"]);
			$this->setPermission($result["permission"]);
			$player->sendMessage($this->translator->translate($this->player, "kit.editor.data.saved"));
		});
		$form->setTitle($this->translator->translate($this->player, "kit.editor.form.title"));
		$form->addLabel($error ?? $this->translator->translate($this->player, "kit.editor.form.label"));
		$form->addInput($this->translator->translate($this->player, "kit.editor.form.price"), "", $price ?? (string)$this->price, "price");
		$form->addInput($this->translator->translate($this->player, "kit.editor.form.description"), "", $description ?? $this->description, "description");
		$form->addInput($this->translator->translate($this->player, "kit.editor.form.permission"), "", $permission ?? $this->permission, "permission");
		$this->player->sendForm($form);
	}
}