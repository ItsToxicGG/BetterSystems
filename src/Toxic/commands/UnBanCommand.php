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

class UnBanCommand extends Command {

    public function __construct() {
        parent::__construct("unban", "Un Ban players", "/unban");
        $this->setPermission("bs.ban");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) {
        if (!$sender instanceof Player) {
            $sender->sendMessage(TF::RED . "This command can only be used in-game!");
            return true;
        }

        $this->sendUnbanForm($sender);
        return true;
    }

    public function sendUnbanForm(Player $muter) {
        $form = new CustomForm(function (Player $player, $data) {
            if ($data !== null) {
                $username = $data[1];
                $provider = BetterSystem::getInstance()->getProvider();
    
                if ($provider->isBannedByUsername($username)) {
                    $provider->unbanPlayerByUsername($username);
                    $player->sendMessage("Player $username has been unmuted.");
                } else {
                    $player->sendMessage("Player $username is not muted.");
                }
            }
        });
    
        $form->setTitle("Unmute");
        $form->addLabel("Unmute a player");
        $form->addInput("Player Name:");
        $form->sendToPlayer($muter);
    }    
}
