<?php

declare(strict_types=1);

namespace IvanCraft623\SimpleKits\form;

use IvanCraft623\SimpleKits\kit\Kit;
use IvanCraft623\SimpleKits\database\PlayerData;

use pocketmine\utils\SingletonTrait;
use pocketmine\player\Player;

final class FormManager {
	use SingletonTrait;

	public function sendSelectKit(Player $player): void {
		new SelectKit($player);
	}

	public function sendPurchaseKit(Player $player, Kit $kit): void {
		new PurchaseKit($player, $kit);
	}

	public function sendSimpleKitsManager(Player $player): void {
		new SimpleKitsManager($player);
	}

	public function sendKitsManager(Player $player): void {
		new KitsManager($player);
	}

	public function sendPlayersList(Player $player, int $page = 1): void {
		new PlayersList($player, $page);
	}

	public function sendManagePlayer(Player $player, PlayerData $playerdata): void {
		new ManagePlayer($player, $playerdata);
	}
}
