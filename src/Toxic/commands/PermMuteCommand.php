<?php

namespace Toxic\commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat as TF;
use forms\SimpleForm;
use forms\CustomForm;  
use pocketmine\Server;
use Toxic\BetterSystem;

class PermMuteCommand extends Command {

    public function __construct() {
        parent::__construct("permmute", "Perm Mute players", "/permmute");
        $this->setPermission("bs.permmute");
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

        $form->setTitle("Perm Mute Player");
        $form->setContent("Select a player to mute:");

        foreach ($this->getOnlinePlayers() as $onlinePlayer) {
            $form->addButton($onlinePlayer);
        }

        $form->sendToPlayer($player);
    }

    public function sendReasonForm(Player $muter, string $targetPlayer) {
        $form = new CustomForm(function (Player $player, $data) use ($muter, $targetPlayer) {
                $reason = $data[1];
                $this->mutePlayer($muter, $targetPlayer, $reason);
        });

        $form->setTitle("Perm Mute");
        $form->addLabel("Enter the reason for muting $targetPlayer:");

        $form->addInput("Reason:");
        $form->sendToPlayer($muter);
    }

    public function mutePlayer(Player $muter, string $targetPlayer, string $reason) {
        $muted = Server::getInstance()->getPlayerExact($targetPlayer);
        $uuid = $muted->getUniqueId()->toString();
    
        $config = BetterSystem::getInstance()->getConfig();
    
        $muteMessage = $config->get("PERMMute-MSG");
    
        $muteMessage = str_replace("{reason}", $reason, $muteMessage);
        $muteMessage = str_replace("{RED}", TF::RED, $muteMessage);
        $muteMessage = str_replace("{WHITE}", TF::WHITE, $muteMessage);
        $muteMessage = str_replace("{RESET}", TF::RESET, $muteMessage);
        $muteMessage = str_replace("{GREEN}", TF::GREEN, $muteMessage);
    
        BetterSystem::getInstance()->getProvider()->permmutePlayer($uuid, $reason);
    
        $muter->sendMessage(TF::GREEN . "Successfully perm muted $targetPlayer for: " . TF::RESET . $reason);
    }

    public function getOnlinePlayers(): array {
        $onlinePlayers = []; 
        foreach (Server::getInstance()->getOnlinePlayers() as $player) {
            $onlinePlayers[] = $player->getName();
        }
        return $onlinePlayers;
    }
}
