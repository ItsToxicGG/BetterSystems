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

class KickCommand extends Command {

    public function __construct() {
        parent::__construct("kick", "Kick players", "/kick");
        $this->setPermission("bs.kick");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) {
        if (!$sender instanceof Player) {
            $sender->sendMessage(TF::RED . "This command can only be used in-game!");
            return true;
        }

        $this->sendKickForm($sender);
        return true;
    }

    public function sendKickForm(Player $player) {
        $form = new SimpleForm(function (Player $player, $data) {
            if ($data === null) {
                return;
            }

            $targetPlayer = $this->getOnlinePlayers()[$data];
            $this->sendReasonForm($player, $targetPlayer);
        });

        $form->setTitle("Kick Player");
        $form->setContent("Select a player to kick:");

        foreach ($this->getOnlinePlayers() as $onlinePlayer) {
            $form->addButton($onlinePlayer);
        }

        $form->sendToPlayer($player);
    }

    public function sendReasonForm(Player $kicker, string $targetPlayer) {
        $form = new CustomForm(function (Player $player, $data) use ($kicker, $targetPlayer) {
            if ($data[0]) {
                $reason = $data[1];
                $this->kickPlayer($kicker, $targetPlayer, $reason);
            }
        });

        $form->setTitle("Kick Reason");
        $form->addLabel("Enter the reason for kicking $targetPlayer:");

        $form->addToggle("Confirm");
        $form->addInput("Reason:");

        $form->sendToPlayer($kicker);
    }

    public function kickPlayer(Player $kicker, string $targetPlayer, string $reason) {
        $target = Server::getInstance()->getPlayer($targetPlayer);

        if ($target instanceof Player) {
            $target->kick($reason);
            $kicker->sendMessage(TF::GREEN . "Successfully kicked $targetPlayer for: " . TF::RESET . $reason);
        } else {
            $kicker->sendMessage(TF::RED . "Player $targetPlayer not found or not online.");
        }
    }

    public function getOnlinePlayers(): array {
        $onlinePlayers = [];
        foreach (Server::getInstance()->getOnlinePlayers() as $player) {
            $onlinePlayers[] = $player->getName();
        }
        return $onlinePlayers;
    }
}
