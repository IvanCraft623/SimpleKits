<?php

declare(strict_types=1);

namespace IvanCraft623\SimpleKits\database;

use IvanCraft623\SimpleKits\SimpleKits;

final class PlayerData {

	private SimpleKits $plugin;

	private DataManager $dataManager;

	private string $name;

	private array $purchasedKits = [];

	private ?string $selectedKit = null;

	public int $lastSync;
	
	public function __construct(string $name, array $purchasedKits = [], ?string $selectedKit = null) {
		$this->plugin = SimpleKits::getInstance();
		$this->dataManager = $this->plugin->getDataManager();
		$this->name = $name;
		$this->purchasedKits = $purchasedKits;
		$this->selectedKit = $selectedKit;
		$this->lastSync = $this->plugin->getServer()->getTick();
	}
	
	public function getName(): string {
		return $this->name;
	}

	public function getPurchasedKits(): array {
		return $this->purchasedKits;
	}

	public function setPurchasedKits(array $kits): void {
		$this->updateCache($kits, $this->selectedKit);
	}

	public function hasPurchasedKit(string $kit): bool {
		return in_array($kit, $this->purchasedKits, true);
	}

	public function addPurchasedKit(string $kit): void {
		if (!$this->hasPurchasedKit($kit)) {
			$kits = $this->purchasedKits;
			$kits[] = $kit;
			$this->setPurchasedKits($kits);
		}
	}

	public function removePurchasedKit(string $kit): void {
		$kits = $this->purchasedKits;
		foreach ($kits as $key => $kt) {
			if ($kt === $kit) {
				unset($kits[$key]);
				$this->setPurchasedKits($kits);
				break;
			}
		}
	}

	public function getSelectedKit(): ?string {
		return $this->selectedKit;
	}

	public function setSelectedKit(?string $kit): void {
		$this->updateCache($this->purchasedKits, $kit);
	}

	public function updateCache(array $purchasedKits, ?string $selectedKit, bool $updateDatabase = true): bool {
		if ($this->purchasedKits === $purchasedKits && $this->selectedKit === $selectedKit) {
			return false;
		}
		$this->purchasedKits = $purchasedKits;
		$this->selectedKit = $selectedKit;
		if ($updateDatabase) {
			$this->dataManager->getDatabase()->executeGeneric('data.players.set', [
				"player" => $this->name,
				"purchasedKits" => json_encode($purchasedKits),
				"selectedKit" => json_encode($selectedKit)
			], null, $this->dataManager->onError);
		}
		return true;
	}

	public function syncData(callable $onComplete) {
		$this->dataManager->getDatabase()->executeSelect('data.players.get', [
			"player" => $this->name
		], function(array $rows) use ($onComplete) {
			$data = $rows[0];
			$data["purchasedKits"] = json_decode($data["purchasedKits"]);
			$data["selectedKit"] = json_decode($data["selectedKit"]);
			$this->purchasedKits = $data["purchasedKits"];
			$this->selectedKit = $data["selectedKit"];
			$this->lastSync = $this->plugin->getServer()->getTick();
			$onComplete();
		}, $this->dataManager->onError);
	}
}