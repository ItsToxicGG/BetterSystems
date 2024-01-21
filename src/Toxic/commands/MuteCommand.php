<?php

namespace Toxic\commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use Toxic\event\{PlayerBanEvent, PlayerUnBanEvent, PlayerUnMuteEvent, PlayerMuteEvent};
use pocketmine\player\Player;
use pocketmine\utils\TextFormat as TF;
use forms\SimpleForm;
use forms\CustomForm;  
use pocketmine\Server;
use Toxic\BetterSystem;

class MuteCommand extends Command {

    public function __construct() {
        parent::__construct("mute", "Temp Mute players", "/mute");
        $this->setPermission("bs.mute");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) {
        if (!$sender instanceof Player) {
            $sender->sendMessage(TF::RED . "This command can only be used in-game!");
            return true;
        }

        $this->sendMuteForm($sender);
        return true;
    }

    public function sendMuteForm(Player $player) {
        $form = new SimpleForm(function (Player $player, $data) {
            if ($data === null) {
                return;
            }

            $targetPlayer = $this->getOnlinePlayers()[$data];
            $this->sendReasonForm($player, $targetPlayer);
        });

        $form->setTitle("Mute Player");
        $form->setContent("Select a player to mute:");

        foreach ($this->getOnlinePlayers() as $onlinePlayer) {
            $form->addButton($onlinePlayer);
        }

        $form->sendToPlayer($player);
    }

    public function sendReasonForm(Player $muter, string $targetPlayer) {
        $form = new CustomForm(function (Player $player, $data) use ($muter, $targetPlayer) {
                $reason = $data[1];
                $duration = $data[2];
                $this->mutePlayer($muter, $targetPlayer, $reason, $duration);
                $ev = new PlayerMuteEvent($muter, $targetPlayer, $muter->getName());
                $ev->call();
        });

        $form->setTitle("Mute");
        $form->addLabel("Enter the reason for muting $targetPlayer:");

        $form->addInput("Reason:");
        $form->addInput("Duration:", "Example: 1d, 2h, 1s");
        $form->sendToPlayer($muter);
    }

    public function mutePlayer(Player $muter, string $targetPlayer, string $reason, string $duration) {
        $muted = Server::getInstance()->getPlayerExact($targetPlayer);
        $uuid = $muted->getUniqueId()->toString();
    
        $config = BetterSystem::getInstance()->getConfig();
        $formattedDuration = $this->parseDuration($duration);
    
        $muteMessage = $config->get("TEMPMute-MSG");
    
        $muteMessage = str_replace("{duration}", $formattedDuration, $muteMessage);
        $muteMessage = str_replace("{reason}", $reason, $muteMessage);
        $muteMessage = str_replace("{RED}", TF::RED, $muteMessage);
        $muteMessage = str_replace("{WHITE}", TF::WHITE, $muteMessage);
        $muteMessage = str_replace("{RESET}", TF::RESET, $muteMessage);
        $muteMessage = str_replace("{GREEN}", TF::GREEN, $muteMessage);
    
        BetterSystem::getInstance()->getProvider()->mutePlayer($uuid, $duration, $reason);
    
        $muter->sendMessage(TF::GREEN . "Successfully muted $targetPlayer for: " . TF::RESET . $reason . " duration: " . $formattedDuration);
    }

    public function getOnlinePlayers(): array {
        $onlinePlayers = []; 
        foreach (Server::getInstance()->getOnlinePlayers() as $player) {
            $onlinePlayers[] = $player->getName();
        }
        return $onlinePlayers;
    }

    private function parseDuration(string $duration): int {
        $duration = strtolower($duration);
    
        $days = 0;
        $hours = 0;
        $minutes = 0;
        $seconds = 0;
    
        $matches = [];
        preg_match_all('/(\d+)([dhrs])/', $duration, $matches);
    
        foreach ($matches[2] as $key => $unit) {
            $value = (int)$matches[1][$key];
            switch ($unit) {
                case 'd':
                    $days += $value;
                    break;
                case 'h':
                    $hours += $value;
                    break;
                case 'm':
                    $minutes += $value;
                    break;
                case 's':
                    $seconds += $value;
                    break;
            }
        }
    
        return $days * 86400 + $hours * 3600 + $minutes * 60 + $seconds;
    }
}
