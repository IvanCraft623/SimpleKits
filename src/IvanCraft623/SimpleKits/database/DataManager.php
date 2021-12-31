<?php

declare(strict_types=1);

namespace IvanCraft623\SimpleKits\database;

use IvanCraft623\SimpleKits\SimpleKits;
use IvanCraft623\SimpleKits\kit\Kit;
use IvanCraft623\SimpleKits\translator\Language;

use pocketmine\player\Player;
use pocketmine\utils\SingletonTrait;

use poggit\libasynql\DataConnector;
use poggit\libasynql\libasynql;
use poggit\libasynql\SqlError;

final class DataManager {
	use SingletonTrait;

	private SimpleKits $plugin;

	private string $pluginPath;

	private ?DataConnector $database = null;

	public \Closure $onError;

	private array $playersdata;
	
	public function __construct() {
		$this->plugin = SimpleKits::getInstance();
		$this->pluginPath = $this->plugin->getDataFolder();

		$this->onError = function(SqlError $result): void{
			$this->plugin->getLogger()->emergency($result->getQuery() . ' - ' . $result->getErrorMessage());
		};
	}

	public function createContext(): void {
		$this->database = libasynql::create($this->plugin, $this->plugin->getConfig()->get("database"), [
			"sqlite" => "database/sqlite.sql",
			"mysql"  => "database/mysql.sql",
		]);

		$this->database->executeGeneric('table.players');
		$this->database->executeGeneric('table.kits');
	}

	public function getDatabase(): ?DataConnector {
		return $this->database;
	}

	public function getPlayersData(): array {
		return $this->playersdata;
	}

	public function getPlayerData(Player|string $player): ?PlayerData {
		$name = $player instanceof Player ? $player->getName() : $player;
		return $this->playersdata[$name] ?? null;
	}

	public function loadPlayersData(): void {
		$this->database->executeSelect('data.players.getAll', [
		], function(array $rows) {
			if ($rows === []) {
				return;
			}

			foreach ($rows as $entry) {
				$data = $entry;
				$data["purchasedKits"] = json_decode($data["purchasedKits"], true);
				$data["selectedKit"] = json_decode($data["selectedKit"], true);
				if (!isset($this->playersdata[$data["player"]])) {
					$this->playersdata[$data["player"]] = new PlayerData($data["player"], $data["purchasedKits"], $data["selectedKit"]);
				} else {
					$this->playersdata[$data["player"]]->updateCache($data["purchasedKits"], $data["selectedKit"], false);
					$this->playersdata[$data["player"]]->lastSync = $this->plugin->getServer()->getTick();
				}
			}
		}, $this->onError);
	}

	/**
	 * Add a player to the database if is not there yet
	 * @param string[] $purchasedKits
	 */
	public function addPlayer(Player|string $player, array $purchasedKits = [], ?string $selectedKit = null, ?callable $onComplete = null): void {
		$name = $player instanceof Player ? $player->getName() : $player;
		$this->database->executeGeneric('data.players.add', [
			"player"        => $name,
			"purchasedKits" => json_encode($purchasedKits),
			"selectedKit"   => json_encode($selectedKit)
		], function() use ($onComplete, $name, $purchasedKits, $selectedKit) {
			if (!isset($this->playersdata[$name])) $this->playersdata[$name] = new PlayerData($name, $purchasedKits, $selectedKit);
			if ($onComplete !== null) {
				$onComplete($this->playersdata[$name]);
			}
		}, $this->onError);
	}

	public function deletePlayer(PlayerData|Player|string $player, ?callable $onComplete = null): void {
		$name = ($player instanceof PlayerData || $player instanceof Player) ? $player->getName() : $player;
		$this->database->executeGeneric('data.players.delete', [
			"player"        => $name
		], function() use ($name, $onComplete) {
			unset($this->playersdata[$name]);
			if ($onComplete !== null) {
				$onComplete();
			}
		}, $this->onError);
	}

	public function loadKits(): void {
		$this->database->executeSelect('data.kits.getAll', [
		], function(array $rows) {
			if ($rows === []) {
				return;
			}

			foreach ($rows as $entry) {
				$data = $entry;
				$data["inventoryItems"] = json_decode($data["inventoryItems"], true);
				$data["armorInventoryItems"] = json_decode($data["armorInventoryItems"], true);
				$data["offHandItem"] = json_decode($data["offHandItem"], true);
				$this->plugin->getKitManager()->registerKit(Kit::jsonDeserialize($data));
			}
		}, $this->onError);
	}
	
	public function saveKitData(Kit $kit, ?callable $onComplete = null): void {
		$this->database->executeChange('data.kits.set', [
			"kit"                 => $kit->getName(),
			"price"               => $kit->getPrice(),
			"description"         => $kit->getDescription(),
			"permission"          => $kit->getPermission(),
			"inventoryItems"      => json_encode($kit->getInventoryItems()),
			"armorInventoryItems" => json_encode($kit->getArmorInventoryItems()),
			"offHandItem"         => json_encode($kit->getOffHandItem()),
		], function(int $affectedRows) use ($onComplete) {
			if ($onComplete !== null) {
				$onComplete();
			}
		}, $this->onError);
	}

	public function deleteKit(Kit|string $kit, ?callable $onComplete = null): void {
		$name = $kit instanceof Kit ? $kit->getName() : $kit;
		$this->database->executeGeneric('data.kits.delete', [
			"kit"        => $name
		], function() use ($name, $onComplete) {
			$this->plugin->getKitManager()->unregisterKit($name);
			if ($onComplete !== null) {
				$onComplete();
			}
		}, $this->onError);
	}

	public function saveLanguageResources(): void {
		$this->plugin->saveResource('language/en_US.yml');
		$this->plugin->saveResource('language/es_MX.yml');
	}

	public function loadLanguages(): void {
		foreach (glob($this->pluginPath . "language" . DIRECTORY_SEPARATOR . "*.yml") as $file) {
			$locale = basename($file, ".yml");
			$data = yaml_parse(file_get_contents($file));
			$this->plugin->getTranslator()->registerLanguage(new Language($locale, $data));
		}
	}
}
