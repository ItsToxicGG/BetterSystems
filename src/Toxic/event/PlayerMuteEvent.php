<?php

namespace Toxic\event;

use pocketmine\player\Player;

class PlayerMuteEvent extends BSEvent {
    
    public $who;
    public $muter;

    public function __construct(Player $player, string $who, string $muter)
    {
        $this->player = $player;
        $this->who = $who;
        $this->muter = $muter;
    }

    public function getWho(): Player{
        return $this->who;
    }

    public function getMuter(): Player{
        return $this->muter;
    }
}