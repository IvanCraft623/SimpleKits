<?php

declare(strict_types=1);

namespace IvanCraft623\SimpleKits;

use IvanCraft623\SimpleKits\command\KitCommand;
use IvanCraft623\SimpleKits\command\KitsCommand;
use IvanCraft623\SimpleKits\command\SimpleKitsCommand;
use IvanCraft623\SimpleKits\database\DataManager;
use IvanCraft623\SimpleKits\form\FormManager;
use IvanCraft623\SimpleKits\task\PlayerDataUpdateTask;
use IvanCraft623\SimpleKits\translator\Translator;
use IvanCraft623\SimpleKits\kit\KitManager;

use pocketmine\plugin\PluginBase;
use pocketmine\player\Player;
use pocketmine\utils\SingletonTrait;

final class SimpleKits extends PluginBase {
	use SingletonTrait;

	private DataManager $dataManager;

	private FormManager $formManager;

	private KitManager $kitManager;

	private Translator $translator;

	private ?string $economy = null;

	private ?PluginBase $economyInstance = null;

	public const ECONOMYAPI = "EconomyAPI";
	public const BEDROCKECONOMY = "BedrockEconomy";
	
	protected function onLoad(): void {
		self::setInstance($this);
		$this->dataManager = DataManager::getInstance();
		$this->formManager = FormManager::getInstance();
		$this->kitManager = KitManager::getInstance();
		$this->translator = Translator::getInstance();
	}
	
	protected function onEnable(): void {
		$this->saveDefaultConfig();
		$this->loadEconomy();
		$this->dataManager->createContext();
		$this->dataManager->loadKits();
		$this->dataManager->loadLanguages();
		$this->getServer()->getCommandMap()->register('SimpleKits', new SimpleKitsCommand($this));
		if ((bool)$this->getConfig()->get("kit-command")) {
			$this->getServer()->getCommandMap()->register('SimpleKits', new KitCommand($this));
		}
		if ((bool)$this->getConfig()->get("kits-command")) {
			$this->getServer()->getCommandMap()->register('SimpleKits', new KitsCommand($this));
		}

		# Set default language
		$language = $this->translator->getLanguage((string)$this->getConfig()->get("language", "en_US"));
		if ($language === null) {
			throw new \Exception("Invalid default language provided");
		} else {
			$this->translator->setDefaultLanguage($language);
		}

		# Players Data
		$this->getScheduler()->scheduleRepeatingTask(new PlayerDataUpdateTask($this), 20 * 60);
	}
	
	protected function onDisable(): void {
		$this->dataManager->getDatabase()?->close();
	}
	
	public function getPrefix(): string {
		return (string)$this->getConfig()->get("prefix");
	}
	
	public function getDataManager(): DataManager {
		return $this->dataManager;
	}
	
	public function getFormManager(): FormManager {
		return $this->formManager;
	}
	
	public function getKitManager(): KitManager {
		return $this->kitManager;
	}
	
	public function getTranslator(): Translator {
		return $this->translator;
	}

	private function loadEconomy(): void {
		$manager = $this->getServer()->getPluginManager();
		$economyInstance = $manager->getPlugin(self::ECONOMYAPI) ?? $manager->getPlugin(self::BEDROCKECONOMY);
		if ($economyInstance !== null) {
			$this->economy = $economyInstance->getDescription()->getName();
			$this->economyInstance = $economyInstance;
		} else {
			$this->getLogger()->warning("No supported economy plugin was found, some features may not work properly.");
		}
	}

	public function getEconomy(): ?PluginBase {
		return $this->economy;
	}

	public function getMoney(Player $player): ?int {
		if ($this->economy === self::ECONOMYAPI) {
			return (($money = $this->economyInstance->myMoney($player)) === false) ? null : $money;
		} elseif ($this->economy === self::BEDROCKECONOMY) {
			$manager = $this->economyInstance->getAccountManager();
			if (!$manager->hasAccount($player->getXuid())) {
				if (!$manager->addAccount($player->getXuid(), $player->getName())) {
					return null;
				}
			}
			$account = $manager->getAccount($player->getXuid());
			return $account->getBalance();
		}
		return null;
	}

	public function substractMoney(Player $player, float $amount): bool {
		if ($this->economy === self::ECONOMYAPI) {
			return $this->economyInstance->reduceMoney($player, $amount) === $this->economyInstance::RET_SUCCESS;
		} elseif ($this->economy === self::BEDROCKECONOMY) {
			$manager = $this->economyInstance->getAccountManager();
			if (!$manager->hasAccount($player->getXuid())) {
				if (!$manager->addAccount($player->getXuid(), $player->getName())) {
					return false;
				}
			}
			$account = $manager->getAccount($player->getXuid());
			return $account->decrementBalance((int)$amount);
		}
		return false;
	}
}