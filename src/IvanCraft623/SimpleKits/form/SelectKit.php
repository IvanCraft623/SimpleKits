<?php

declare(strict_types=1);

namespace IvanCraft623\SimpleKits\form;

use IvanCraft623\SimpleKits\SimpleKits;
use IvanCraft623\SimpleKits\database\PlayerData;
use IvanCraft623\SimpleKits\kit\Kit;
use IvanCraft623\SimpleKits\form\api\SimpleForm;
use IvanCraft623\SimpleKits\translator\Translator;

use pocketmine\player\Player;

final class SelectKit {

	private SimpleKits $plugin;

	private Translator $translator;

	private Player $player;

	private PlayerData $playerdata;
	
	public function __construct(Player $player) {
		$this->plugin = SimpleKits::getInstance();
		$this->translator = $this->plugin->getTranslator();
		$this->player = $player;
		$data = $this->plugin->getDataManager()->getPlayerData($player);
		if ($data !== null) {
			$this->playerdata = $data;
			$this->sendMain();
		} else {
			$this->plugin->getDataManager()->addPlayer($player, [], null, function(PlayerData $data) {
				$this->playerdata = $data;
				$this->sendMain();
			});
		}
	}

	public function sendMain(): void {
		$form = new SimpleForm(function(Player $player, string $result = null) {
			if ($result === null) {
				return;
			}

			switch ($result) {
				case 'Free':
					$this->sendPurchaseFreeKits();
				break;

				case 'Buy':
					$this->sendPurchaseBuyKits();
				break;

				case 'Premium':
					$this->sendPurchasePremiumKits();
				break;

				case 'PurchasedKits':
					$this->sendSelectPurchasedKit();
				break;
			}
		});
		$form->setTitle($this->translator->translate($this->player, "form.selectkit.title"));
		$form->setContent($this->translator->translate($this->player, "form.selectoption"));
		$form->addButton($this->translator->translate($this->player, "form.selectkit.free"), 0, "textures/ui/icon_armor", "Free");
		$form->addButton($this->translator->translate($this->player, "form.selectkit.buy"), 0, "textures/ui/MCoin", "Buy");
		$form->addButton($this->translator->translate($this->player, "form.selectkit.premium"), 0, "textures/ui/icon_best3", "Premium");
		$form->addButton($this->translator->translate($this->player, "form.selectkit.purchased", ["%kits" => count($this->playerdata->getPurchasedKits())]), 0, "textures/ui/icon_unlocked", "PurchasedKits");
		$this->player->sendForm($form);
	}

	public function sendPurchaseFreeKits(): void {
		$form = new SimpleForm(function(Player $player, Kit $result = null) {
			if ($result === null) {
				$this->sendMain();
				return;
			}
			$this->plugin->getFormManager()->sendPurchaseKit($player, $result);
		});
		$form->setTitle($this->translator->translate($this->player, "form.selectkit.free"));
		$form->setContent($this->translator->translate($this->player, "form.selectoption"));
		foreach ($this->plugin->getKitManager()->getKits() as $kit) {
			if (($kit->getPrice() == 0 && !$kit->needsPermission()) && !$this->playerdata->hasPurchasedKit($kit->getName())) {
				$form->addButton($kit->getName(), -1, "", $kit);
			}
		}
		$this->player->sendForm($form);
	}

	public function sendPurchaseBuyKits(): void {
		$form = new SimpleForm(function (Player $player, Kit $result = null) {
			if ($result === null) {
				$this->sendMain();
				return;
			}

			$this->plugin->getFormManager()->sendPurchaseKit($player, $result);
		});
		$form->setTitle($this->translator->translate($this->player, "form.selectkit.buy"));
		$form->setContent($this->translator->translate($this->player, "form.selectoption"));
		foreach ($this->plugin->getKitManager()->getKits() as $kit) {
			if (($kit->getPrice() != 0 && !$kit->needsPermission()) && !$this->playerdata->hasPurchasedKit($kit->getName())) {
				$form->addButton($kit->getName(), -1, "", $kit);
			}
		}
		$this->player->sendForm($form);
	}

	public function sendPurchasePremiumKits(): void {
		$form = new SimpleForm(function (Player $player, Kit $result = null) {
			if ($result === null) {
				$this->sendMain();
				return;
			}

			if ($player->hasPermission($result->getPermission())) {
				$this->plugin->getFormManager()->sendPurchaseKit($player, $result);
			} else {
				$player->sendMessage($this->translator->translate($this->player, "kit.premium.no.permission"));
			}
		});
		$form->setTitle($this->translator->translate($this->player, "form.selectkit.premium"));
		$form->setContent($this->translator->translate($this->player, "form.selectoption"));
		foreach ($this->plugin->getKitManager()->getKits() as $kit) {
			if ($kit->needsPermission() && !$this->playerdata->hasPurchasedKit($kit->getName())) {
				$form->addButton($kit->getName(), -1, "", $kit);
			}
		}
		$this->player->sendForm($form);
	}

	public function sendSelectPurchasedKit(): void {
		$form = new SimpleForm(function (Player $player, string $kit = null) {
			if ($kit === null) {
				$this->sendMain();
				return;
			}
			$selected = $this->playerdata->getSelectedKit();
			if ($kit !== $selected) {
				$this->playerdata->setSelectedKit($kit);
				$player->sendMessage($this->translator->translate($player, "kit.select.success", [
					"%kit" => $kit
				]));
			} else {
				$this->playerdata->setSelectedKit(null);
				$player->sendMessage($this->translator->translate($player, "kit.deselect.success", [
					"%kit" => $kit
				]));
			}
		});
		$selected = $this->playerdata->getSelectedKit();
		$form->setTitle($this->translator->translate($this->player, "form.selectkit.purchased", ["%kits" => count($this->playerdata->getPurchasedKits())]));
		$form->setContent($this->translator->translate($this->player, "form.selectoption"));
		foreach ($this->playerdata->getPurchasedKits() as $kit) {
			$form->addButton($kit.($kit === $selected ? "\n§r".$this->translator->translate($this->player, "selected") : ""), -1, "", $kit);
		}
		$this->player->sendForm($form);
	}
}