<?php

namespace Toxic;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\utils\TextFormat as TF;
use Toxic\tasks\DurationTask;
use Toxic\provider\{MySQLProvider, SQLiteProvider};
use Toxic\commands\{BanCommand, KickCommand, MuteCommand, PermBanCommand, PermMuteCommand, PermUnBanCommand, PermUnMuteCommand, UnBanCommand, UnMuteCommand};
use mysqli;
use SQLite3;

class BetterSystem extends PluginBase implements Listener {

    private $provider;

    /** @var BetterSystem */
    private static $instance;

    protected function onLoad(): void { /** ? */ }

    protected function onEnable(): void {
        $this->getServer()->getPluginManager()->registerEvents(new BSListener($this), $this);
        $this->getScheduler()->scheduleRepeatingTask(new DurationTask($this), 20);

        if($this->getConfig()->get("Ban-System") === true){
            $this->unregisterCommands(['ban', 'unban']);
            $this->getServer()->getCommandMap()->register("ban", new BanCommand());
            $this->getServer()->getCommandMap()->register("unban", new UnBanCommand());
            $this->getServer()->getCommandMap()->register("permban", new PermBanCommand());
            $this->getServer()->getCommandMap()->register("unpermban", new PermUnBanCommand());
        } else if($this->getConfig()->get("Kick-System") === true){
            $this->unregisterCommands(['kick']);
            $this->getServer()->getCommandMap()->register("kick", new KickCommand());
        } else if($this->getConfig()->get("Mute-System") === true){
            $this->getServer()->getCommandMap()->register("mute", new MuteCommand());
            $this->getServer()->getCommandMap()->register("unmute", new UnMuteCommand());
            $this->getServer()->getCommandMap()->register("permmute", new PermMuteCommand());
            $this->getServer()->getCommandMap()->register("unpermmute", new PermUnMuteCommand());
        }

        $this->getLogger()->info(TF::GREEN . "BetterSystems has been enabled!");

        $this->registerProvider();
        
        self::$instance = $this;
    }

    protected function onDisable(): void {
        $this->getLogger()->info(TF::RED . "BetterSystems has been disabled!");
        
        if ($this->provider instanceof MySQLProvider || $this->provider instanceof SQLiteProvider) {
            $this->provider->closeDatabase();
        }
    }    

    public function registerProvider() {
        $configValue = strtolower($this->getConfig()->get("Provider"));
    
        switch ($configValue) {
            case "MySQL":
            case "mysqli":
            case "mysql":
                $this->provider = $this->initializeMySQLProvider();
                break;
    
            case "sqlite":
            case "sqlite3":
            case "SQLite":
            case "Sqlite":
            default:
                $this->provider = $this->initializeSQLiteProvider();
                break;
        }
    }
    
    private function initializeMySQLProvider(): MySQLProvider {
        $host = $this->getConfig()->get("SQL-Host");
        $username = $this->getConfig()->get("SQL-Username");
        $password = $this->getConfig()->get("SQL-Password");
        $database = $this->getConfig()->get("SQL-DBName");
        $port = $this->getConfig()->get("SQL-Port");
    
        $mysqli = new mysqli($host, $username, $password, $database, $port);
    
        if ($mysqli->connect_error) {
            $this->getLogger()->error("Failed to connect to MySQL: " . $mysqli->connect_error);
            $this->getServer()->getPluginManager()->disablePlugin($this);
        }
    
        return new MySQLProvider($mysqli);
    }
    
    private function initializeSQLiteProvider(): SQLiteProvider {
        $databasePath = $this->getDataFolder() . "bettersystems.db";
    
        return new SQLiteProvider(new SQLite3($databasePath));
    }    

    public static function getInstance(): BetterSystem{
        return self::$instance;
    }
    
    public function getProvider() {
        return $this->provider;
    }

    private function unregisterCommands(array $commands): void {
        foreach ($commands as $cmd) {
            if (($command = $this->getServer()->getCommandMap()->getCommand($cmd)) !== null) {
                $this->getServer()->getCommandMap()->unregister($command);
            }
        }
    }
}