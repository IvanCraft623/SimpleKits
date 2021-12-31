<?php

declare(strict_types=1);

namespace IvanCraft623\SimpleKits\kit;

use IvanCraft623\SimpleKits\database\DataManager;

use pocketmine\player\Player;
use pocketmine\utils\SingletonTrait;

final class KitManager {
	use SingletonTrait;

	private array $kits = [];

	public function getKits(): array {
		return $this->kits;
	}

	public function getKit(string $name): ?Kit {
		return $this->kits[$name] ?? null;
	}

	public function kitExists(string $name): bool {
		return isset($this->kits[$name]);
	}

	public function registerKit(Kit $kit): void {
		$this->kits[$kit->getName()] = $kit;
	}

	public function unregisterKit(Kit|string $kit): void {
		$name = $kit instanceof Kit ? $kit->getName() : $kit;
		unset($this->kits[$name]);
	}
}