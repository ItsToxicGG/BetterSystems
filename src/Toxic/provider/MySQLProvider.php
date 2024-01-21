<?php

namespace Toxic\provider;

use pocketmine\player\Player;
use pocketmine\utils\UUID;

class MySQLProvider {

    public $db;

    public function __construct(\mysqli $database) {
        $this->db = $database;
        $this->initDatabase();
    }

    private function initDatabase() {
        $query = "CREATE TABLE IF NOT EXISTS bettersystems (
            uuid VARCHAR(36) PRIMARY KEY,
            username VARCHAR(32) NOT NULL,
            banned BOOLEAN DEFAULT false,
            muted BOOLEAN DEFAULT false,
            permban BOOLEAN DEFAULT false,
            permmute BOOLEAN DEFAULT false,
            mute_duration INT DEFAULT 0,
            banned_duration INT DEFAULT 0,
            kick_count INT DEFAULT 0,
            ban_reason VARCHAR(255) DEFAULT '',
            mute_reason VARCHAR(255) DEFAULT ''
        )";

        $this->db->query($query);
    }

    public function getPlayerData(string $uuid): ?array {
        $stmt = $this->db->prepare("SELECT * FROM bettersystems WHERE uuid = ?");
        $stmt->bind_param("s", $uuid);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            return null;
        }

        return $result->fetch_assoc();
    }

    public function addPlayerData(string $uuid, string $username): void {
        if (!$this->playerDataExists($uuid)) {
            $stmt = $this->db->prepare("INSERT INTO bettersystems (uuid, username) VALUES (?, ?)");
            $stmt->bind_param("ss", $uuid, $username);
            $stmt->execute();
        }
    }
    
    private function playerDataExists(string $uuid): bool {
        $uuidString = $uuid;
        $query = "SELECT COUNT(*) as count FROM bettersystems WHERE uuid = '$uuidString'";
        $result = $this->db->query($query);
    
        if ($result === false) {
            return true; 
        }
    
        $row = $result->fetch_assoc();
        return $row['count'] > 0;
    }    

    public function isBanned(string $uuid): bool {
        $data = $this->getPlayerData($uuid);

        return $data !== null && $data['banned'];
    }

    public function getBanDuration(string $uuid): int {
        $stmt = $this->db->prepare("SELECT banned_duration FROM bettersystems WHERE uuid = ?");
        $stmt->bind_param("s", $uuid);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            return 0;
        }

        return $result->fetch_assoc()['banned_duration'];
    }

    public function getBanReason(string $uuid): string {
        $stmt = $this->db->prepare("SELECT ban_reason FROM bettersystems WHERE uuid = ?");
        $stmt->bind_param("s", $uuid);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            return "";
        }

        return $result->fetch_assoc()['ban_reason'];
    }

    public function getMuteDuration(string $uuid): int {
        $stmt = $this->db->prepare("SELECT mute_duration FROM bettersystems WHERE uuid = ?");
        $stmt->bind_param("s", $uuid);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            return 0;
        }

        return $result->fetch_assoc()['mute_duration'];
    }

    public function getMuteReason(string $uuid): string {
        $stmt = $this->db->prepare("SELECT mute_reason FROM bettersystems WHERE uuid = ?");
        $stmt->bind_param("s", $uuid);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            return "";
        }

        return $result->fetch_assoc()['mute_reason'];
    }

    public function banPlayer(string $uuid, int $duration = 0, string $reason = ""): void {
        $stmt = $this->db->prepare("UPDATE bettersystems SET banned = true, banned_duration = ?, ban_reason = ? WHERE uuid = ?");
        $stmt->bind_param("iss", $duration, $reason, $uuid);
        $stmt->execute();
    }

    public function mutePlayer(string $uuid, int $duration = 0, string $reason = ""): void {
        $stmt = $this->db->prepare("UPDATE bettersystems SET muted = true, mute_duration = ?, mute_reason = ? WHERE uuid = ?");
        $stmt->bind_param("iss", $duration, $reason, $uuid);
        $stmt->execute();
    }

    public function permbanPlayer(string $uuid, string $reason = ""): void {
        $stmt = $this->db->prepare("UPDATE bettersystems SET permban = true, ban_reason = ? WHERE uuid = ?");
        $stmt->bind_param("iss", $reason, $uuid);
        $stmt->execute();
    }

    public function permmutePlayer(string $uuid, string $reason = ""): void {
        $stmt = $this->db->prepare("UPDATE bettersystems SET muted = true, mute_reason = ? WHERE uuid = ?");
        $stmt->bind_param("iss", $reason, $uuid);
        $stmt->execute();
    }

    public function unbanPlayer(string $uuid): void {
        $stmt = $this->db->prepare("UPDATE bettersystems SET banned = false, banned_duration = 0 WHERE uuid = ?");
        $stmt->bind_param("s", $uuid);
        $stmt->execute();
    }

    public function unpermbanPlayer(string $uuid): void {
        $stmt = $this->db->prepare("UPDATE bettersystems SET permban = false WHERE uuid = ?");
        $stmt->bind_param("s", $uuid);
        $stmt->execute();
    }

    public function unpermmutePlayer(string $uuid): void {
        $stmt = $this->db->prepare("UPDATE bettersystems SET permmute = false WHERE uuid = ?");
        $stmt->bind_param("s", $uuid);
        $stmt->execute();
    }

    public function unpermmutePlayerByUsername(string $username): void {
        $stmt = $this->db->prepare("UPDATE bettersystems SET permmute = false WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
    }

    public function unpermbanPlayerByUsername(string $username): void {
        $stmt = $this->db->prepare("UPDATE bettersystems SET permban = false WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
    }

    public function unmutePlayerByUsername(string $username): void {
        $stmt = $this->db->prepare("UPDATE bettersystems SET muted = false WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
    }

    public function unbanPlayerByUsername(string $username): void {
        $stmt = $this->db->prepare("UPDATE bettersystems SET banned = false WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
    }

    public function isMutedByUsername(string $username): bool {
        $stmt = $this->db->prepare("SELECT muted FROM bettersystems WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            return false;
        }

        return $result->fetch_assoc()['muted'];
    }

    public function isBannedByUsername(string $username): bool {
        $stmt = $this->db->prepare("SELECT banned FROM bettersystems WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            return false;
        }

        return $result->fetch_assoc()['banned'];
    }

    public function isPermBanByUsername(string $username): bool {
        $stmt = $this->db->prepare("SELECT permban FROM bettersystems WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            return false;
        }

        return $result->fetch_assoc()['permban'];
    }

    public function isPermMuteByUsername(string $username): bool {
        $stmt = $this->db->prepare("SELECT permmute FROM bettersystems WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            return false;
        }

        return $result->fetch_assoc()['permmute'];
    }

    public function isMuted(string $uuid): bool {
        $data = $this->getPlayerData($uuid);

        return $data !== null && $data['muted'];
    }

    public function isPermBan(string $uuid): bool {
        $data = $this->getPlayerData($uuid);

        return $data !== null && $data['permban'];
    }

    public function isPermMuted(string $uuid): bool {
        $data = $this->getPlayerData($uuid);

        return $data !== null && $data['permmute'];
    }   

    public function unmutePlayer(string $uuid): void {
        $stmt = $this->db->prepare("UPDATE bettersystems SET muted = false, mute_duration = 0 WHERE uuid = ?");
        $stmt->bind_param("s", $uuid);
        $stmt->execute();
    }

    public function addKickCount(string $uuid): void {
        $stmt = $this->db->prepare("UPDATE bettersystems SET kick_count = kick_count + 1 WHERE uuid = ?");
        $stmt->bind_param("s", $uuid);
        $stmt->execute();
    }

    public function removeKickCount(string $uuid): void {
        $stmt = $this->db->prepare("UPDATE bettersystems SET kick_count = GREATEST(kick_count - 1, 0) WHERE uuid = ?");
        $stmt->bind_param("s", $uuid);
        $stmt->execute();
    }

    public function setKickCount(string $uuid, int $count): void {
        $stmt = $this->db->prepare("UPDATE bettersystems SET kick_count = ? WHERE uuid = ?");
        $stmt->bind_param("is", $count, $uuid);
        $stmt->execute();
    }

    public function closeDatabase() {
        $this->db->close();
    }
}
