<?php

namespace Toxic;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;

class BSListener implements Listener {

    private $plugin;

    public function __construct(Main $plugin) {
        $this->plugin = $plugin;
    }



    // Event listener method
    public function onPlayerJoin(PlayerJoinEvent $event): void {
        $player = $event->getPlayer();
    }

    public function onPlayerLogin(PlayerLoginEvent $event){
        /** PART 1 - Player Related stuff */
        $player = $event->getPlayer();
        $uuid = $player->getUniqueId()->toString();
        $reason = $this->plugin->getProvider()->getBanReason($uuid);
        $duration = $this->f($this->plugin->getProvider()->getBanDuration($uuid));
        /** PART 2 - Config Related Stuff */
        $banmsg = $this->plugin->getConfig()->get("TEMPBan-MSG");
        $permbanmsg = $this->plugin->getConfig()->get("PERMBan-MSG");
        /** PART 3 - Ban MSG */
        $banmsg = str_replace("{duration}", $duration, $mutemsg);
        $banmsg = str_replace("{reason}", $reason, $mutemsg);
        $banmsg = str_replace("{RED}", TF::RED, $mutemsg);
        $banmsg = str_replace("{WHITE}", TF::WHITE, $mutemsg);
        $banmsg = str_replace("{RESET}", TF::RESET, $mutemsg);
        $banmsg = str_replace("{GREEN}", TF::GREEN, $mutemsg);
        /** PART 4 - Perm Ban MSG */
        $permbanmsg = str_replace("{reason}", $reason, $mutemsg);
        $permbanmsg = str_replace("{RED}", TF::RED, $mutemsg);
        $permbanmsg = str_replace("{WHITE}", TF::WHITE, $mutemsg);
        $permbanmsg = str_replace("{RESET}", TF::RESET, $mutemsg);
        $permbanmsg = str_replace("{GREEN}", TF::GREEN, $mutemsg);
        /** PART 5 - stop player from joining */
        if($this->plugin->getProvider()->isBanned($uuid) === true){
           $player->kick($banmsg);
        } else if($this->plugin->getProvider()->isPermBan($uuid) === true){
           $player->kick($permbanmsg);
        }
    }

    public function onPlayerChat(PlayerChatEvent $event){
        /** PART 1 - Player Related stuff */
        $player = $event->getPlayer();
        $uuid = $player->getUniqueId()->toString();
        $reason = $this->plugin->getProvider()->getMuteReason($uuid);
        $duration = $this->f($this->plugin->getProvider()->getMuteDuration($uuid));
        /** PART 2 - Config Related Stuff */
        $mutemsg = $this->plugin->getConfig()->get("TEMPMute-MSG");
        $permmutemsg = $this->plugin->getConfig()->get("PERMMute-MSG");
        /** PART 3 - Mute MSG */
        $mutemsg = str_replace("{duration}", $duration, $mutemsg);
        $mutemsg = str_replace("{reason}", $reason, $mutemsg);
        $mutemsg = str_replace("{RED}", TF::RED, $mutemsg);
        $mutemsg = str_replace("{WHITE}", TF::WHITE, $mutemsg);
        $mutemsg = str_replace("{RESET}", TF::RESET, $mutemsg);
        $mutemsg = str_replace("{GREEN}", TF::GREEN, $mutemsg);
        /** PART 4 - Perm Mute MSG */
        $permmutemsg = str_replace("{duration}", $duration, $mutemsg);
        $permmutemsg = str_replace("{reason}", $reason, $mutemsg);
        $permmutemsg = str_replace("{RED}", TF::RED, $mutemsg);
        $permmutemsg = str_replace("{WHITE}", TF::WHITE, $mutemsg);
        $permmutemsg = str_replace("{RESET}", TF::RESET, $mutemsg);
        $permmutemsg = str_replace("{GREEN}", TF::GREEN, $mutemsg);
        /** PART 5 - stop player from chatting */
        if($this->plugin->getProvider()->isMuted($uuid) === true){
           $event->cancel();
           $player->sendMessage($mutemsg);
        } else if($this->plugin->getProvider()->isPermMuted($uuid) === true){
           $event->cancel();
           $player->sendMessage($permmutemsg);
        }
    }

    public function f(int $seconds): string {
        $days = floor($seconds / 86400);
        $hours = floor(($seconds % 86400) / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $seconds = $seconds % 60;

        $duration = "";
        
        if ($days > 0) {
            $duration .= $days . "d ";
        }

        if ($hours > 0) {
            $duration .= $hours . "h ";
        }

        if ($minutes > 0) {
            $duration .= $minutes . "m ";
        }

        if ($seconds > 0 || empty($duration)) {
            $duration .= $seconds . "s";
        }

        return trim($duration);
    }
}
