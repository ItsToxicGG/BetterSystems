<?php

namespace Toxic\event;

use pocketmine\player\Player;

class PlayerUnBanEvent extends BSEvent {
    
    public $who;

    public function __construct(Player $player, string $who)
    {
        $this->player = $player;
        $this->who = $who;
    }

    public function getWho(): Player{
        return $this->who;
    }
}