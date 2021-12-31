<?php

declare(strict_types=1);

namespace IvanCraft623\SimpleKits\form;

use IvanCraft623\SimpleKits\SimpleKits;
use IvanCraft623\SimpleKits\database\PlayerData;
use IvanCraft623\SimpleKits\kit\Kit;
use IvanCraft623\SimpleKits\form\api\ModalForm;
use IvanCraft623\SimpleKits\translator\Translator;

use pocketmine\player\Player;

final class PurchaseKit {

	private SimpleKits $plugin;

	private Translator $translator;

	private Player $player;

	private PlayerData $playerdata;

	private Kit $targetKit;
	
	public function __construct(Player $player, Kit $targetKit) {
		$this->plugin = SimpleKits::getInstance();
		$this->translator = $this->plugin->getTranslator();
		$this->player = $player;
		$this->targetKit = $targetKit;
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
		$form = new ModalForm(function (Player $player, bool $result = null) {
			if ($result === null || !$result) {
				return;
			}

			$kit = $this->targetKit;
			if ($this->playerdata->hasPurchasedKit($kit->getName())) {
				$player->sendMessage($this->translator->translate($player, "kit.already.purchased"));
				return;
			}
			if ($kit->needsPermission() && !$player->hasPermission($kit->getPermission())) {
				$player->sendMessage($this->translator->translate($player, "kit.premium.no.permission"));
				return;
			}
			if (($price = $kit->getPrice()) > 0) {
				$money = $this->plugin->getMoney($player);
				if ($money === null) {
					$player->sendMessage($this->translator->translate($player, "economy.error"));
					return;
				}
				if (($money - $price) < 0) {
					$player->sendMessage($this->translator->translate($player, "kit.no.enough.money"));
					return;
				}
				if (!$this->plugin->substractMoney($player, $price)) {
					$player->sendMessage($this->translator->translate($player, "economy.error"));
					return;
				}
			}
			$this->playerdata->addPurchasedKit($kit->getName());
			$player->sendMessage($this->translator->translate($player, "kit.purchase.success", ["%kit" => $kit->getName()]));
		});
		$kit = $this->targetKit;
		$form->setTitle($this->translator->translate($this->player, "form.purchasekit.title"));
		$form->setContent(
			$this->translator->translate($this->player, "kit.data.name", ["%kit" => $kit->getName()])."\n".
			$this->translator->translate($this->player, "kit.data.price", ["%price" => $kit->getPrice()])."\n".
			$this->translator->translate($this->player, "kit.data.description", ["%description" => $kit->getDescription()])."\n".
			$this->translator->translate($this->player, "form.purchasekit.content.mymoney", ["%money" => ($this->plugin->getMoney($this->player) ?? $this->translator->translate($this->player, "messge.error"))])."\n"."\n".
			$this->translator->translate($this->player, "form.purchasekit.content.text")
		);
		$form->setButton1($this->translator->translate($this->player, "form.buy"));
		$form->setButton2($this->translator->translate($this->player, "form.cancel"));
		$form->sendToPlayer($this->player);
	}
}