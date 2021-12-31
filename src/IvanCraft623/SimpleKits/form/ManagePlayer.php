<?php

declare(strict_types=1);

namespace IvanCraft623\SimpleKits\form;

use IvanCraft623\SimpleKits\SimpleKits;
use IvanCraft623\SimpleKits\database\PlayerData;
use IvanCraft623\SimpleKits\form\api\ModalForm;
use IvanCraft623\SimpleKits\form\api\SimpleForm;
use IvanCraft623\SimpleKits\translator\Translator;

use pocketmine\player\Player;

final class ManagePlayer {

	private SimpleKits $plugin;

	private Translator $translator;

	private Player $player;

	private PlayerData $playerdata;
	
	public function __construct(Player $player, PlayerData $playerdata) {
		$this->plugin = SimpleKits::getInstance();
		$this->translator = $this->plugin->getTranslator();
		$this->player = $player;
		$this->playerdata = $playerdata;
		$this->sendMain();
	}

	public function sendMain(): void {
		$playerdata = $this->playerdata;
		$form = new SimpleForm(function (Player $player, string $result = null) {
			if ($result === null) {
				return;
			}

			switch ($result) {
				case 'purchased':
					$this->sendEditPurchasedKits();
				break;

				case 'selected':
					$this->sendChangeSelectedKit();
				break;

				case 'delete':
					$this->sendPlayerDeleteConfirm();
				break;
			}
		});
		$form->setTitle($this->translator->translate($this->player, "form.manageplayer.title"));
		$form->setContent(
			$this->translator->translate($this->player, "player.data.name", ["%player" => $playerdata->getName()])."\n".
			$this->translator->translate($this->player, "player.data.purchasedkits", ["%kits" => implode(", ", $playerdata->getPurchasedKits())])."\n".
			$this->translator->translate($this->player, "player.data.selectedkit", ["%kit" => $playerdata->getSelectedKit()])
		);
		$form->addButton($this->translator->translate($this->player, "form.manageplayer.editpurchased"), -1, "", "purchased");
		$form->addButton($this->translator->translate($this->player, "form.manageplayer.changeselected"), -1, "", "selected");
		$form->addButton($this->translator->translate($this->player, "form.delete"), -1, "", "delete");
		$this->player->sendForm($form);
	}

	public function sendEditPurchasedKits(): void {
		$form = new SimpleForm(function (Player $player, string $result = null) {
			if ($result === null) {
				$this->sendMain();
				return;
			}

			switch ($result) {
				case 'add':
					$this->sendAddPurchasedKits();
				break;

				case 'remove':
					$this->sendRemovePurchasedKit();
				break;
			}
		});
		$form->setTitle($this->translator->translate($this->player, "form.manageplayer.title"));
		$form->setContent($this->translator->translate($this->player, "form.selectoption"));
		$form->addButton($this->translator->translate($this->player, "form.add"), -1, "", "add");
		$form->addButton($this->translator->translate($this->player, "form.remove"), -1, "", "remove");
		$this->player->sendForm($form);
	}

	public function sendAddPurchasedKits(): void {
		$playerdata = $this->playerdata;
		$form = new SimpleForm(function (Player $player, string $kit = null) {
			if ($kit === null) {
				$this->sendEditPurchasedKits();
				return;
			}
			$playerdata = $this->playerdata;
			$playerdata->addPurchasedKit($kit);
			$player->sendMessage($this->translator->translate($this->player, "form.manageplayer.editpurchased.add.success", [
				"%kit"    => $kit,
				"%player" => $playerdata->getName()
			]));
		});
		$form->setTitle($this->translator->translate($this->player, "form.manageplayer.title"));
		$form->setContent($this->translator->translate($this->player, "form.selectoption"));
		foreach ($this->plugin->getKitManager()->getKits() as $kit) {
			if (!$playerdata->hasPurchasedKit($kit->getName())) {
				$form->addButton($kit->getName(), -1, "", $kit->getName());
			}
		}
		$this->player->sendForm($form);
	}

	public function sendRemovePurchasedKit(): void {
		$playerdata = $this->playerdata;
		$form = new SimpleForm(function (Player $player, string $kit = null) {
			if ($kit === null) {
				$this->sendEditPurchasedKits();
				return;
			}
			$playerdata = $this->playerdata;
			$playerdata->removePurchasedKit($kit);
			$player->sendMessage($this->translator->translate($this->player, "form.manageplayer.editpurchased.remove.success", [
				"%kit"    => $kit,
				"%player" => $playerdata->getName()
			]));
		});
		$form->setTitle($this->translator->translate($this->player, "form.manageplayer.title"));
		$form->setContent($this->translator->translate($this->player, "form.selectoption"));
		foreach ($playerdata->getPurchasedKits() as $kit) {
			$form->addButton($kit, -1, "", $kit);
		}
		$this->player->sendForm($form);
	}

	public function sendChangeSelectedKit(): void {
		$playerdata = $this->playerdata;
		$form = new SimpleForm(function (Player $player, string $kit = null) {
			if ($kit === null) {
				$this->sendMain();
				return;
			}

			$playerdata = $this->playerdata;
			$selected = $playerdata->getSelectedKit();
			if ($kit !== $selected) {
				$playerdata->setSelectedKit($kit);
				$player->sendMessage($this->translator->translate($player, "kit.select.success", [
					"%kit" => $kit
				]));
			} else {
				$playerdata->setSelectedKit(null);
				$player->sendMessage($this->translator->translate($player, "kit.deselect.success", [
					"%kit" => $kit
				]));
			}
		});
		$selected = $playerdata->getSelectedKit();
		$form->setTitle($this->translator->translate($this->player, "form.manageplayer.title"));
		$form->setContent($this->translator->translate($this->player, "form.selectoption"));
		foreach ($playerdata->getPurchasedKits() as $kit) {
			$form->addButton($kit.($kit === $selected ? "\n§r".$this->translator->translate($this->player, "selected") : ""), -1, "", $kit);
		}
		$this->player->sendForm($form);
	}

	public function sendPlayerDeleteConfirm(): void {
		$playerdata = $this->playerdata;
		$form = new ModalForm(function (Player $player, bool $result = null) {
			if ($result === null || !$result) {
				return;
			}

			$playerdata = $this->playerdata;
			$this->plugin->getDataManager()->deletePlayer($playerdata, function() use ($player, $playerdata) {
				$player->sendMessage($this->translator->translate($this->player, "form.manageplayer.players.delete.success", ["%player" => $playerdata->getName()]));
			});
		});
		$form->setTitle($this->translator->translate($this->player, "form.delete"));
		$form->setContent(
			$this->translator->translate($this->player, "player.data.name", ["%player" => $playerdata->getName()])."\n"."\n".
			$this->translator->translate($this->player, "form.delete.confirm")
		);
		$form->setButton1($this->translator->translate($this->player, "form.delete"));
		$form->setButton2($this->translator->translate($this->player, "form.cancel"));
		$this->player->sendForm($form);
	}
}