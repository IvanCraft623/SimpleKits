<?php

declare(strict_types=1);

namespace IvanCraft623\SimpleKits\kit;

use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\enchantment\StringToEnchantmentParser;
use pocketmine\player\Player;

final class Kit implements \JsonSerializable {

	private string $name;

	private float $price = 0;

	private string $description = "";

	private string $permission = "";

	private array $inventoryItems = [];

	private array $armorInventoryItems = [];

	private ?Item $offHandItem = null;
	
	public function __construct(string $name, float $price = 0, string $description = "", string $permission = "", array $inventoryItems = [], array $armorInventoryItems = [], ?Item $offHandItem = null) {
		$this->name = $name;
		$this->price = $price;
		$this->description = $description;
		$this->permission = $permission;
		$this->inventoryItems = $inventoryItems;
		$this->armorInventoryItems = $armorInventoryItems;
		$this->offHandItem = $offHandItem;
	}

	public function getName(): string {
		return $this->name;
	}

	public function getPrice(): float {
		return $this->price;
	}

	public function getDescription(): string {
		return $this->description;
	}

	public function needsPermission(): bool {
		return $this->permission !== "";
	}

	public function getPermission(): string {
		return $this->permission;
	}

	public function getInventoryItems(): array {
		return $this->inventoryItems;
	}

	public function getArmorInventoryItems(): array {
		return $this->armorInventoryItems;
	}

	public function getOffHandItem(): ?Item {
		return $this->offHandItem;
	}

	public function getItemsCount(): int {
		$items = array_merge($this->inventoryItems, $this->armorInventoryItems);
		if ($this->offHandItem !== null) {
			$items = array_merge($items, [$this->offHandItem]);
		}
		return count($items);
	}

	public function giveItems(Player $player): void {
		foreach ($this->inventoryItems as $index => $item) {
			$player->getInventory()->setItem($index, $item);
		}
		foreach ($this->armorInventoryItems as $index => $item) {
			$player->getArmorInventory()->setItem($index, $item);
		}
		if ($this->offHandItem !== null) {
			$player->getOffHandInventory()->setItem(0, $this->offHandItem);
		}
	}

	public function jsonSerialize(): array {
		$data = [
			"name" => $this->name
		];

		if ($this->price != 0) {
			$data["price"] = $this->price;
		}

		if ($this->description !== "") {
			$data["description"] = $this->description;
		}

		if ($this->permission !== "") {
			$data["permission"] = $this->permission;
		}

		if ($this->inventoryItems !== []) {
			$data["inventoryItems"] = $this->inventoryItems;
		}

		if ($this->armorInventoryItems !== []) {
			$data["armorInventoryItems"] = $this->armorInventoryItems;
		}

		if ($this->offHandItem !== null) {
			$data["offHandItem"] = $this->offHandItem;
		}

		return $data;
	}

	public static function jsonDeserialize(array $data): Kit {
		$name = (string)($data["name"] ?? $data["kit"]);
		$price = (float)($data["price"] ?? 0);
		$description = (string)($data["description"] ?? "");
		$permission = (string)($data["permission"] ?? "");
		$inventoryItems = [];
		if (isset($data["inventoryItems"])) {
			foreach ($data["inventoryItems"] as $slot => $itemData) {
				$inventoryItems[$slot] = Item::jsonDeserialize($itemData);
			}
		}
		$armorInventoryItems = [];
		if (isset($data["armorInventoryItems"])) {
			foreach ($data["armorInventoryItems"] as $slot => $itemData) {
				$armorInventoryItems[$slot] = Item::jsonDeserialize($itemData);
			}
		}
		$offHandItem = null;
		if (isset($data["offHandItem"]) && $data["offHandItem"] !== null) {
			$offHandItem = Item::jsonDeserialize($data["offHandItem"]);
		}
		return new Kit($name, $price, $description, $permission, $inventoryItems, $armorInventoryItems, $offHandItem);
	}
}