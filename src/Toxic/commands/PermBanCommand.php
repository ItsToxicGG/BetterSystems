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

class PermBanCommand extends Command {

    public function __construct() {
        parent::__construct("permban", "Perm Ban players", "/permban");
        $this->setPermission("bs.permban");
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

        $form->setTitle("Perm Ban Player");
        $form->setContent("Select a player to Ban:");

        foreach ($this->getOnlinePlayers() as $onlinePlayer) {
            $form->addButton($onlinePlayer);
        }

        $form->sendToPlayer($player);
    }

    public function sendReasonForm(Player $banner, string $targetPlayer) {
        $form = new CustomForm(function (Player $player, $data) use ($banner, $targetPlayer) {
            if ($data[0]) {
                $reason = $data[1];
                $this->banPlayer($banner, $targetPlayer, $reason);
            }
        });

        $form->setTitle("Perm Ban");
        $form->addLabel("Enter the reason for muting $targetPlayer:");

        $form->addToggle("Confirm");
        $form->addInput("Reason:");
        $form->sendToPlayer($banner);
    }

    public function banPlayer(Player $banner, string $targetPlayer, string $reason) {
        $banned = Server::getInstance()->getPlayerExact($targetPlayer);
        $uuid = $banned->getUniqueId()->toString();
    
        $config = BetterSystem::getInstance()->getConfig();
    
        $banMessage = $config->get("PERMBan-MSG");
    
        $banMessage = str_replace("{reason}", $reason, $banMessage);
        $banMessage = str_replace("{RED}", TF::RED, $banMessage);
        $banMessage = str_replace("{WHITE}", TF::WHITE, $banMessage);
        $banMessage = str_replace("{RESET}", TF::RESET, $banMessage);
        $banMessage = str_replace("{GREEN}", TF::GREEN, $banMessage);
    
        BetterSystem::getInstance()->getProvider()->permbanPlayer($uuid, $reason);
    
        $banned->kick($banMessage);
    
        $banner->sendMessage(TF::GREEN . "Successfully perm banned $targetPlayer for: " . TF::RESET . $reason);
    }

    public function getOnlinePlayers(): array {
        $onlinePlayers = []; 
        foreach (Server::getInstance()->getOnlinePlayers() as $player) {
            $onlinePlayers[] = $player->getName();
        }
        return $onlinePlayers;
    }
}
