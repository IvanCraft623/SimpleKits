<?php

declare(strict_types=1);

namespace IvanCraft623\SimpleKits\form;

use IvanCraft623\SimpleKits\SimpleKits;
use IvanCraft623\SimpleKits\kit\Kit;
use IvanCraft623\SimpleKits\form\api\ModalForm;
use IvanCraft623\SimpleKits\form\api\SimpleForm;
use IvanCraft623\SimpleKits\translator\Translator;

use pocketmine\player\Player;

final class KitsManager {

	private SimpleKits $plugin;

	private Translator $translator;

	private Player $player;
	
	public function __construct(Player $player) {
		$this->plugin = SimpleKits::getInstance();
		$this->translator = $this->plugin->getTranslator();
		$this->player = $player;
		$this->sendMain();
	}

	public function sendMain(): void {
		$form = new SimpleForm(function (Player $player, Kit $kit = null) {
			if ($kit === null) {
				return;
			}

			$this->sendKitData($kit);
		});
		$form->setTitle($this->translator->translate($this->player, "form.kitsmanager.title"));
		$form->setContent($this->translator->translate($this->player, "form.selectoption"));
		foreach ($this->plugin->getKitManager()->getKits() as $kit) {
			$form->addButton($kit->getName(), -1, "", $kit);
		}
		$this->player->sendForm($form);
	}

	public function sendKitData(Kit $kit): void {
		$form = new SimpleForm(function (Player $player, string $result = null) use ($kit) {
			if ($result === null) {
				return;
			}

			switch ($result) {
				case 'edit':
					new KitEditor($player, $kit->getName(), $kit->getPrice(), $kit->getDescription(), $kit->getPermission(), $kit->getInventoryItems(), $kit->getArmorInventoryItems(), $kit->getOffHandItem());
				break;

				case 'delete':
					$this->sendKitDeleteConfirm($kit);
				break;
			}
		});
		$form->setTitle($this->translator->translate($this->player, "form.kitsmanager.list"));
		$form->setContent(
			$this->translator->translate($this->player, "kit.data.name", ["%kit" => $kit->getName()])."\n".
			$this->translator->translate($this->player, "kit.data.price", ["%price" => $kit->getPrice()])."\n".
			$this->translator->translate($this->player, "kit.data.description", ["%description" => $kit->getDescription()])
		);
		$form->addButton($this->translator->translate($this->player, "form.edit"), -1, "", "edit");
		$form->addButton($this->translator->translate($this->player, "form.delete"), -1, "", "delete");
		$this->player->sendForm($form);
	}

	public function sendKitDeleteConfirm(Kit $kit): void {
		$form = new ModalForm(function (Player $player, bool $result = null) use ($kit) {
			if ($result === null || !$result) {
				return;
			}

			$this->plugin->getDataManager()->deleteKit($kit, function() use ($player, $kit) {
				$player->sendMessage($this->translator->translate($this->player, "form.kitsmanager.delete.success", ["%kit" => $kit->getName()]));
			});
		});
		$form->setTitle($this->translator->translate($this->player, "form.delete"));
		$form->setContent(
			$this->translator->translate($this->player, "kit.data.name", ["%kit" => $kit->getName()])."\n"."\n".
			$this->translator->translate($this->player, "form.delete.confirm")
		);
		$form->setButton1($this->translator->translate($this->player, "form.delete"));
		$form->setButton2($this->translator->translate($this->player, "form.cancel"));
		$this->player->sendForm($form);
	}
}