<?php

namespace Toxic\event;

use pocketmine\player\Player;

class PlayerBanEvent extends BSEvent {
    
    public $who;
    public $banner;

    public function __construct(Player $player, string $who, string $banner)
    {
        $this->player = $player;
        $this->who = $who;
        $this->banner = $banner;
    }

    public function getWho(): Player{
        return $this->who;
    }

    public function getBanner(): Player{
        return $this->banner;
    }
}