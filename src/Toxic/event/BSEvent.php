<?php

declare(strict_types=1);

namespace Toxic\event;

use pocketmine\event\player\PlayerEvent;
use pocketmine\player\Player;

abstract class BSEvent extends PlayerEvent {

    public function __construct(Player $player)
    {
        $this->player = $player;
    }
}