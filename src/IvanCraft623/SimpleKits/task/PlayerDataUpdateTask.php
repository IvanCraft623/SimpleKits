<?php

declare(strict_types=1);

namespace IvanCraft623\SimpleKits\task;

use IvanCraft623\SimpleKits\SimpleKits;

use pocketmine\scheduler\Task;

class PlayerDataUpdateTask extends Task {

	private SimpleKits $plugin;
	
	public function __construct(SimpleKits $plugin) {
		$this->plugin = $plugin;
	}

	public function onRun(): void {
		$this->plugin->getDataManager()->loadPlayersData();
	}
}