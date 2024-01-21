<?php

namespace Toxic\tasks;

use pocketmine\scheduler\Task;
use Toxic\BetterSystem;
use Toxic\provider\MySQLProvider;
use Toxic\provider\SQLiteProvider;

class DurationTask extends Task {

    private $plugin;

    public function __construct(BetterSystem $plugin) {
        $this->plugin = $plugin;
    }

    public function onRun(): void {
        $provider = $this->plugin->getProvider();
    
        $allPlayersData = $this->getAllPlayersData();
        foreach ($allPlayersData as $uuid => $data) {
            if ($data['banned'] && $data['banned_duration'] > 0) {
                $newBannedDuration = max(0, $data['banned_duration'] - 1);
                $provider->banPlayer($uuid, $newBannedDuration);
    
                if ($newBannedDuration === 0) {
                    $provider->unbanPlayer($uuid);
                }
            }
    
            if ($data['muted'] && $data['mute_duration'] > 0) {
                $newMuteDuration = max(0, $data['mute_duration'] - 1);
                $provider->mutePlayer($uuid, $newMuteDuration);
    
                if ($newMuteDuration === 0) {
                    $provider->unmutePlayer($uuid);
                }
            }
        }
    }

    private function getAllPlayersData(): array {
        $provider = $this->plugin->getProvider();

        $databaseType = $provider instanceof MySQLProvider ? 'mysql' : 'sqlite';

        switch ($databaseType) {
            case 'mysql':
                $query = "SELECT uuid, banned, banned_duration, muted, mute_duration FROM dc";
                $result = $provider->db->query($query);
                break;

            case 'sqlite':
            default:
                $query = "SELECT uuid, banned, banned_duration, muted, mute_duration FROM dc";
                $result = $provider->db->query($query);
                break;
        }

        $allPlayersData = [];

        while ($row = $result->fetch_assoc()) {
            $allPlayersData[$row['uuid']] = [
                'banned' => $row['banned'],
                'banned_duration' => $row['banned_duration'],
                'muted' => $row['muted'],
                'mute_duration' => $row['mute_duration'],
            ];
        }

        return $allPlayersData;
    }
}
