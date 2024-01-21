<?php

namespace Toxic\provider;

use pocketmine\player\Player;
use pocketmine\utils\UUID;
use SQLite3;

class SQLiteProvider {

    public $db;

    public function __construct(string $databasePath) {
        $this->db = new SQLite3($databasePath);
        $this->initDatabase();
    }

    private function initDatabase() {
        $query = "CREATE TABLE IF NOT EXISTS bettersystems (
            uuid VARCHAR(36) PRIMARY KEY,
            username VARCHAR(32) NOT NULL,
            banned INTEGER DEFAULT 0,
            muted INTEGER DEFAULT 0,
            permban INTEGER DEFAULT 0,
            permmute INTEGER DEFAULT 0,
            mute_duration INTEGER DEFAULT 0,
            banned_duration INTEGER DEFAULT 0,
            kick_count INTEGER DEFAULT 0,
            ban_reason VARCHAR(255) DEFAULT '',
            mute_reason VARCHAR(255) DEFAULT ''
        )";

        $this->db->exec($query);
    }

    public function getPlayerData(string $uuid): ?array {
        $stmt = $this->db->prepare("SELECT * FROM bettersystems WHERE uuid = :uuid");
        $stmt->bindValue(':uuid', $uuid, SQLITE3_TEXT);
        $result = $stmt->execute()->fetchArray(SQLITE3_ASSOC);

        if ($result === false) {
            return null;
        }

        return $result;
    }

    public function addPlayerData(string $uuid, string $username): void {
        if (!$this->playerDataExists($uuid)) {
            $stmt = $this->db->prepare("INSERT INTO bettersystems (uuid, username) VALUES (:uuid, :username)");
            $stmt->bindValue(':uuid', $uuid, SQLITE3_TEXT);
            $stmt->bindValue(':username', $username, SQLITE3_TEXT);
            $stmt->execute();
        }
    }

    private function playerDataExists(string $uuid): bool {
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM bettersystems WHERE uuid = :uuid");
        $stmt->bindValue(':uuid', $uuid, SQLITE3_TEXT);
        $result = $stmt->execute()->fetchArray(SQLITE3_ASSOC);

        return ($result['count'] > 0);
    }

    public function isBanned(string $uuid): bool {
        $data = $this->getPlayerData($uuid);

        return $data !== null && $data['banned'];
    }

    public function getBanDuration(string $uuid): int {
        $stmt = $this->db->prepare("SELECT banned_duration FROM bettersystems WHERE uuid = :uuid");
        $stmt->bindValue(':uuid', $uuid, SQLITE3_TEXT);
        $result = $stmt->execute()->fetchArray(SQLITE3_ASSOC);

        if ($result === false) {
            return 0;
        }

        return $result['banned_duration'];
    }

    public function getBanReason(string $uuid): string {
        $stmt = $this->db->prepare("SELECT ban_reason FROM bettersystems WHERE uuid = :uuid");
        $stmt->bindValue(':uuid', $uuid, SQLITE3_TEXT);
        $result = $stmt->execute()->fetchArray(SQLITE3_ASSOC);

        if ($result === false) {
            return "";
        }

        return $result['ban_reason'];
    }

    public function getMuteDuration(string $uuid): int {
        $stmt = $this->db->prepare("SELECT mute_duration FROM bettersystems WHERE uuid = :uuid");
        $stmt->bindValue(':uuid', $uuid, SQLITE3_TEXT);
        $result = $stmt->execute()->fetchArray(SQLITE3_ASSOC);

        if ($result === false) {
            return 0;
        }

        return $result['mute_duration'];
    }

    public function getMuteReason(string $uuid): string {
        $stmt = $this->db->prepare("SELECT mute_reason FROM bettersystems WHERE uuid = :uuid");
        $stmt->bindValue(':uuid', $uuid, SQLITE3_TEXT);
        $result = $stmt->execute()->fetchArray(SQLITE3_ASSOC);

        if ($result === false) {
            return "";
        }

        return $result['mute_reason'];
    }

    public function banPlayer(string $uuid, int $duration = 0, string $reason = ""): void {
        $stmt = $this->db->prepare("UPDATE bettersystems SET banned = 1, banned_duration = :duration, ban_reason = :reason WHERE uuid = :uuid");
        $stmt->bindValue(':uuid', $uuid, SQLITE3_TEXT);
        $stmt->bindValue(':duration', $duration, SQLITE3_INTEGER);
        $stmt->bindValue(':reason', $reason, SQLITE3_TEXT);
        $stmt->execute();
    }

    public function mutePlayer(string $uuid, int $duration = 0, string $reason = ""): void {
        $stmt = $this->db->prepare("UPDATE bettersystems SET muted = 1, mute_duration = :duration, mute_reason = :reason WHERE uuid = :uuid");
        $stmt->bindValue(':uuid', $uuid, SQLITE3_TEXT);
        $stmt->bindValue(':duration', $duration, SQLITE3_INTEGER);
        $stmt->bindValue(':reason', $reason, SQLITE3_TEXT);
        $stmt->execute();
    }

    public function permbanPlayer(string $uuid, string $reason = ""): void {
        $stmt = $this->db->prepare("UPDATE bettersystems SET permban = 1, ban_reason = :reason WHERE uuid = :uuid");
        $stmt->bindValue(':uuid', $uuid, SQLITE3_TEXT);
        $stmt->bindValue(':reason', $reason, SQLITE3_TEXT);
        $stmt->execute();
    }

    public function permmutePlayer(string $uuid, string $reason = ""): void {
        $stmt = $this->db->prepare("UPDATE bettersystems SET permmute = 1, mute_reason = :reason WHERE uuid = :uuid");
        $stmt->bindValue(':uuid', $uuid, SQLITE3_TEXT);
        $stmt->bindValue(':reason', $reason, SQLITE3_TEXT);
        $stmt->execute();
    }

    public function unbanPlayer(string $uuid): void {
        $stmt = $this->db->prepare("UPDATE bettersystems SET banned = 0, banned_duration = 0 WHERE uuid = :uuid");
        $stmt->bindValue(':uuid', $uuid, SQLITE3_TEXT);
        $stmt->execute();
    }

    public function unpermbanPlayer(string $uuid): void {
        $stmt = $this->db->prepare("UPDATE bettersystems SET permban = 0 WHERE uuid = :uuid");
        $stmt->bindValue(':uuid', $uuid, SQLITE3_TEXT);
        $stmt->execute();
    }

    public function unpermmutePlayer(string $uuid): void {
        $stmt = $this->db->prepare("UPDATE bettersystems SET permmute = 0 WHERE uuid = :uuid");
        $stmt->bindValue(':uuid', $uuid, SQLITE3_TEXT);
        $stmt->execute();
    }

    public function unpermbanPlayerByUsername(string $username): void {
        $stmt = $this->db->prepare("UPDATE bettersystems SET permban = 0 WHERE username = :username");
        $stmt->bindValue(':username', $username, SQLITE3_TEXT);
        $stmt->execute();
    }

    public function unpermmutePlayerByUsername(string $username): void {
        $stmt = $this->db->prepare("UPDATE bettersystems SET permmute = 0 WHERE uuid = :username");
        $stmt->bindValue(':username', $username, SQLITE3_TEXT);
        $stmt->execute();
    }

    public function isMutedByUsername(string $username): bool {
        $stmt = $this->db->prepare("SELECT muted FROM bettersystems WHERE username = ?");
        $stmt->bindParam(1, $username, SQLITE3_TEXT);
        $result = $stmt->execute()->fetchArray(SQLITE3_ASSOC);

        return ($result !== false) ? (bool)$result['muted'] : false;
    }

    public function isBannedByUsername(string $username): bool {
        $stmt = $this->db->prepare("SELECT banned FROM bettersystems WHERE username = ?");
        $stmt->bindParam(1, $username, SQLITE3_TEXT);
        $result = $stmt->execute()->fetchArray(SQLITE3_ASSOC);

        return ($result !== false) ? (bool)$result['banned'] : false;
    }

    public function isPermBanByUsername(string $username): bool {
        $stmt = $this->db->prepare("SELECT permban FROM bettersystems WHERE username = ?");
        $stmt->bindParam(1, $username, SQLITE3_TEXT);
        $result = $stmt->execute()->fetchArray(SQLITE3_ASSOC);

        return ($result !== false) ? (bool)$result['permban'] : false;
    }

    public function isPermMuteByUsername(string $username): bool {
        $stmt = $this->db->prepare("SELECT permmute FROM bettersystems WHERE username = ?");
        $stmt->bindParam(1, $username, SQLITE3_TEXT);
        $result = $stmt->execute()->fetchArray(SQLITE3_ASSOC);

        return ($result !== false) ? (bool)$result['permmute'] : false;
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
        $stmt = $this->db->prepare("UPDATE bettersystems SET muted = 0, mute_duration = 0 WHERE uuid = :uuid");
        $stmt->bindValue(':uuid', $uuid, SQLITE3_TEXT);
        $stmt->execute();
    }

    public function addKickCount(string $uuid): void {
        $stmt = $this->db->prepare("UPDATE bettersystems SET kick_count = kick_count + 1 WHERE uuid = :uuid");
        $stmt->bindValue(':uuid', $uuid, SQLITE3_TEXT);
        $stmt->execute();
    }

    public function removeKickCount(string $uuid): void {
        $stmt = $this->db->prepare("UPDATE bettersystems SET kick_count = GREATEST(kick_count - 1, 0) WHERE uuid = :uuid");
        $stmt->bindValue(':uuid', $uuid, SQLITE3_TEXT);
        $stmt->execute();
    }

    public function setKickCount(string $uuid, int $count): void {
        $stmt = $this->db->prepare("UPDATE bettersystems SET kick_count = :count WHERE uuid = :uuid");
        $stmt->bindValue(':uuid', $uuid, SQLITE3_TEXT);
        $stmt->bindValue(':count', $count, SQLITE3_INTEGER);
        $stmt->execute();
    }

    public function closeDatabase() {
        $this->db->close();
    }
}
