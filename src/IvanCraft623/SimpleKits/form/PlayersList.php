<?php

declare(strict_types=1);

namespace IvanCraft623\SimpleKits\form;

use IvanCraft623\SimpleKits\SimpleKits;
use IvanCraft623\SimpleKits\database\PlayerData;
use IvanCraft623\SimpleKits\form\api\SimpleForm;
use IvanCraft623\SimpleKits\translator\Translator;

use pocketmine\player\Player;

final class PlayersList {

	private SimpleKits $plugin;

	private Translator $translator;

	private Player $player;
	
	public function __construct(Player $player, int $page) {
		$this->plugin = SimpleKits::getInstance();
		$this->translator = $this->plugin->getTranslator();
		$this->player = $player;
		$this->sendMain($page);
	}

	public function sendMain(int $page = 1): void {
		$chunkedPlayes = array_chunk($this->plugin->getDataManager()->getPlayersData(), 16);
		$maxPageNumber = count($chunkedPlayes);
		if (!isset($chunkedPlayes[$page - 1])) {
			$this->player->sendMessage($this->translator->translate($this->player, "form.playerslist.page-notfound", ["%page" => $page]));
			return;
		}
		$form = new SimpleForm(function (Player $player, PlayerData|string $result = null) use ($page) {
			if ($result === null) {
				return;
			}
			if ($result === "next-page") {
				$this->sendMain($page + 1);
			}

			$this->plugin->getFormManager()->sendManagePlayer($player, $result);
		});
		$form->setTitle($this->translator->translate($this->player, "form.playerslist.title"));
		$form->setContent($this->translator->translate($this->player, "form.selectoption"));
		foreach ($chunkedPlayes[$page - 1] as $playerdata) {
			$form->addButton($playerdata->getName(), -1, "", $playerdata);
		}
		if ($page < $maxPageNumber) {
			$form->addButton($this->translator->translate($this->player, "form.playerslist.next-page"), -1, "", "next-page");
		}
		$this->player->sendForm($form);
	}
}