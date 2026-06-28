<?php
/**
 * User Model - handles user CRUD and authentication data
 */

declare(strict_types=1);

class UserModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Find user by ID
     */
    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    /**
     * Find user by email
     */
    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public function findByLogin(string $login): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE email = ? OR username = ? LIMIT 1');
        $stmt->execute([$login, $login]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    /**
     * Find user by remember token
     */
    public function findByRememberToken(string $token): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE remember_token = ? AND status = "active" LIMIT 1');
        $stmt->execute([$token]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    /**
     * Find user by reset token
     */
    public function findByResetToken(string $token): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE reset_token = ? AND reset_expires > NOW() LIMIT 1');
        $stmt->execute([$token]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    /**
     * Get all users with pagination
     */
    public function getAll(int $page = 1, int $perPage = ADMIN_PER_PAGE, ?string $role = null): array
    {
        $offset = getOffset($page, $perPage);
        $params = [];
        $where = '1=1';

        if ($role) {
            $where .= ' AND role = ?';
            $params[] = $role;
        }

        $countStmt = $this->db->prepare("SELECT COUNT(*) FROM users WHERE $where");
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();

        $params[] = $perPage;
        $params[] = $offset;
        $stmt = $this->db->prepare("SELECT id, name, username, email, role, avatar, status, last_login, created_at FROM users WHERE $where ORDER BY created_at DESC LIMIT ? OFFSET ?");
        $stmt->execute($params);

        return ['data' => $stmt->fetchAll(), 'total' => $total];
    }

    /**
     * Create new user
     */
    public function create(array $data): int
    {
        $stmt = $this->db->prepare('INSERT INTO users (name, username, email, password, role, avatar, bio, phone, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([
            $data['name'],
            $data['username'],
            $data['email'],
            Security::hashPassword($data['password']),
            $data['role'] ?? 'author',
            $data['avatar'] ?? null,
            $data['bio'] ?? null,
            $data['phone'] ?? null,
            $data['status'] ?? 'active',
        ]);
        return (int) $this->db->lastInsertId();
    }

    /**
     * Update user
     */
    public function update(int $id, array $data): bool
    {
        $fields = [];
        $params = [];

        $allowed = ['name', 'username', 'email', 'role', 'avatar', 'bio', 'phone', 'status'];
        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $fields[] = "$field = ?";
                $params[] = $data[$field];
            }
        }

        if (!empty($data['password'])) {
            $fields[] = 'password = ?';
            $params[] = Security::hashPassword($data['password']);
        }

        if (empty($fields)) {
            return false;
        }

        $params[] = $id;
        $sql = 'UPDATE users SET ' . implode(', ', $fields) . ' WHERE id = ?';
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Delete user
     */
    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM users WHERE id = ? AND role != "super_admin"');
        return $stmt->execute([$id]);
    }

    /**
     * Set remember token
     */
    public function setRememberToken(int $id, string $token): bool
    {
        $stmt = $this->db->prepare('UPDATE users SET remember_token = ? WHERE id = ?');
        return $stmt->execute([$token, $id]);
    }

    /**
     * Clear remember token
     */
    public function clearRememberToken(int $id): bool
    {
        $stmt = $this->db->prepare('UPDATE users SET remember_token = NULL WHERE id = ?');
        return $stmt->execute([$id]);
    }

    /**
     * Set password reset token
     */
    public function setResetToken(int $id, string $token): bool
    {
        $stmt = $this->db->prepare('UPDATE users SET reset_token = ?, reset_expires = DATE_ADD(NOW(), INTERVAL 1 HOUR) WHERE id = ?');
        return $stmt->execute([$token, $id]);
    }

    /**
     * Clear reset token
     */
    public function clearResetToken(int $id): bool
    {
        $stmt = $this->db->prepare('UPDATE users SET reset_token = NULL, reset_expires = NULL WHERE id = ?');
        return $stmt->execute([$id]);
    }

    /**
     * Update last login timestamp
     */
    public function updateLastLogin(int $id): bool
    {
        $stmt = $this->db->prepare('UPDATE users SET last_login = NOW() WHERE id = ?');
        return $stmt->execute([$id]);
    }

    /**
     * Count users
     */
    public function count(?string $role = null): int
    {
        if ($role) {
            $stmt = $this->db->prepare('SELECT COUNT(*) FROM users WHERE role = ?');
            $stmt->execute([$role]);
        } else {
            $stmt = $this->db->query('SELECT COUNT(*) FROM users');
        }
        return (int) $stmt->fetchColumn();
    }

    /**
     * Get top authors by article count
     */
    public function getTopAuthors(int $limit = 10): array
    {
        $stmt = $this->db->prepare('SELECT u.id, u.name, u.avatar, COUNT(n.id) as article_count, SUM(n.views) as total_views FROM users u LEFT JOIN news n ON u.id = n.author_id AND n.status = "published" GROUP BY u.id ORDER BY article_count DESC LIMIT ?');
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }
}
