<?php
/**
 * Settings Model - key-value site settings
 */

declare(strict_types=1);

class SettingsModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function get(string $key, mixed $default = ''): mixed
    {
        $stmt = $this->db->prepare('SELECT setting_value FROM settings WHERE setting_key = ? LIMIT 1');
        $stmt->execute([$key]);
        $result = $stmt->fetchColumn();
        return $result !== false ? $result : $default;
    }

    public function getAllAsArray(): array
    {
        $stmt = $this->db->query('SELECT setting_key, setting_value FROM settings');
        $settings = [];
        while ($row = $stmt->fetch()) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
        return $settings;
    }

    public function getByGroup(string $group): array
    {
        $stmt = $this->db->prepare('SELECT setting_key, setting_value FROM settings WHERE setting_group = ?');
        $stmt->execute([$group]);
        $settings = [];
        while ($row = $stmt->fetch()) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
        return $settings;
    }

    public function set(string $key, mixed $value, string $group = 'general'): bool
    {
        $stmt = $this->db->prepare('INSERT INTO settings (setting_key, setting_value, setting_group) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), setting_group = VALUES(setting_group)');
        return $stmt->execute([$key, $value, $group]);
    }

    public function setMultiple(array $settings, string $group = 'general'): bool
    {
        $this->db->beginTransaction();
        try {
            foreach ($settings as $key => $value) {
                $this->set($key, $value, $group);
            }
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }
}
