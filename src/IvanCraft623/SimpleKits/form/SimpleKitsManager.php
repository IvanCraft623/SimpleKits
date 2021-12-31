<?php

declare(strict_types=1);

namespace IvanCraft623\SimpleKits\form;

use IvanCraft623\SimpleKits\SimpleKits;
use IvanCraft623\SimpleKits\kit\Kit;
use IvanCraft623\SimpleKits\kit\KitEditor;
use IvanCraft623\SimpleKits\form\api\CustomForm;
use IvanCraft623\SimpleKits\form\api\SimpleForm;
use IvanCraft623\SimpleKits\translator\Translator;

use pocketmine\player\Player;

final class SimpleKitsManager {

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
		$form = new SimpleForm(function (Player $player, string $result = null) {
			if ($result === null) {
				return;
			}

			switch ($result) {
				case 'create':
					$this->sendCreateKit();
				break;

				case 'list':
					$this->plugin->getFormManager()->sendKitsManager($player);
				break;

				case 'players':
					$this->plugin->getFormManager()->sendPlayersList($player);
				break;
			}
		});
		$form->setTitle($this->translator->translate($this->player, "form.simplekitsmanager.title"));
		$form->setContent($this->translator->translate($this->player, "form.selectoption"));
		$form->addButton($this->translator->translate($this->player, "form.simplekitsmanager.create"), 0, "textures/ui/icon_book_writable", "create");
		$form->addButton($this->translator->translate($this->player, "form.kitsmanager.list"), 0, "textures/ui/icon_map", "list");
		$form->addButton($this->translator->translate($this->player, "form.playerslist.title"), 0, "textures/ui/icon_multiplayer", "players");
		$this->player->sendForm($form);
	}

	public function sendCreateKit(?string $name = null, ?string $error = null): void {
		$form = new CustomForm(function (Player $player, array $result = null) {
			if ($result === null) {
				$this->sendMain();
				return;
			}

			if ($this->plugin->getKitManager()->kitExists($result["name"])) {
				$this->sendCreateKit($result["name"], $this->translator->translate($this->player, "form.simplekitsmanager.create.error"));
				return;
			}
			new KitEditor($player, $result["name"]);
		});
		$form->setTitle($this->translator->translate($this->player, "form.simplekitsmanager.create"));
		$form->addLabel($error ?? $this->translator->translate($this->player, "form.simplekitsmanager.create.label"));
		$form->addInput($this->translator->translate($this->player, "form.simplekitsmanager.create.name"), "", $name, "name");
		$this->player->sendForm($form);
	}
}