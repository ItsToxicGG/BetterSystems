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

class UnMuteCommand extends Command {

    public function __construct() {
        parent::__construct("unmute", "Un Perm mute players", "/unmute");
        $this->setPermission("bs.mute");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) {
        if (!$sender instanceof Player) {
            $sender->sendMessage(TF::RED . "This command can only be used in-game!");
            return true;
        }

        $this->sendUnmuteForm($sender);
        return true;
    }

    public function sendUnmuteForm(Player $muter) {
        $form = new CustomForm(function (Player $player, $data) {
            if ($data !== null) {
                $username = $data[1];
                $provider = BetterSystem::getInstance()->getProvider();
    
                if ($provider->isMutedByUsername($username)) {
                    $provider->unmutePlayerByUsername($username);
                    $ev = new PlayerUnMuteEvent($player, $username);
                    $ev->call();
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
