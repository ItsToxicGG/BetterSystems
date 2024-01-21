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

class BanCommand extends Command {

    public function __construct() {
        parent::__construct("ban", "Temp Ban players", "/ban");
        $this->setPermission("bs.ban");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) {
        if (!$sender instanceof Player) {
            $sender->sendMessage(TF::RED . "This command can only be used in-game!");
            return true;
        }

        $this->sendBanForm($sender);
        return true;
    }

    public function sendBanForm(Player $player) {
        $form = new SimpleForm(function (Player $player, $data) {
            if ($data === null) {
                return;
            }

            $targetPlayer = $this->getOnlinePlayers()[$data];
            $this->sendReasonForm($player, $targetPlayer);
        });

        $form->setTitle("Ban Player");
        $form->setContent("Select a player to Ban:");

        foreach ($this->getOnlinePlayers() as $onlinePlayer) {
            $form->addButton($onlinePlayer);
        }

        $form->sendToPlayer($player);
    }

    public function sendReasonForm(Player $banner, string $targetPlayer) {
        $form = new CustomForm(function (Player $player, $data) use ($banner, $targetPlayer) {
            if ($data[0]) {
                $t = Server::getInstance()->getPlayerExact($targetPlayer);
                $reason = $data[1];
                $duration = $data[2];
                $this->banPlayer($banner, $targetPlayer, $reason, $duration);
                $ev = new PlayerBanEvent($banner, $targetPlayer, $banner->getName());
                $ev->call();
            }
        });

        $form->setTitle("Ban");
        $form->addLabel("Enter the reason for muting $targetPlayer:");

        $form->addToggle("Confirm");
        $form->addInput("Reason:");
        $form->addInput("Duration:", "Example: 1d, 2h, 1s");
        $form->sendToPlayer($banner);
    }

    public function banPlayer(Player $banner, string $targetPlayer, string $reason, string $duration) {
        $banned = Server::getInstance()->getPlayerExact($targetPlayer);
        $uuid = $banned->getUniqueId()->toString();
    
        $config = BetterSystem::getInstance()->getConfig();
        $formattedDuration = $this->parseDuration($duration);
    
        $banMessage = $config->get("TEMPBan-MSG");
    
        $banMessage = str_replace("{duration}", $formattedDuration, $banMessage);
        $banMessage = str_replace("{reason}", $reason, $banMessage);
        $banMessage = str_replace("{RED}", TF::RED, $banMessage);
        $banMessage = str_replace("{WHITE}", TF::WHITE, $banMessage);
        $banMessage = str_replace("{RESET}", TF::RESET, $banMessage);
        $banMessage = str_replace("{GREEN}", TF::GREEN, $banMessage);
    
        BetterSystem::getInstance()->getProvider()->banPlayer($uuid, $duration, $reason);
    
        $banned->kick($banMessage);
    
        $banner->sendMessage(TF::GREEN . "Successfully banned $targetPlayer for: " . TF::RESET . $reason . " duration: " . $formattedDuration);
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
