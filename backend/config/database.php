<?php
/**
 * Technoknowledge Backend - Database Connection & Initialization
 * Supports both SQLite (zero configuration out of the box) and MySQL.
 */

declare(strict_types=1);

require_once __DIR__ . '/config.php';

class Database {
    private static ?PDO $instance = null;
    private static ?string $driver = null;

    public static function getConnection(): PDO {
        if (self::$instance !== null) {
            return self::$instance;
        }

        $config = require __DIR__ . '/config.php';
        $driver = $config['db']['driver'] ?? 'sqlite';

        // Try MySQL first if configured
        if ($driver === 'mysql') {
            try {
                $my = $config['db']['mysql'];
                $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=%s', $my['host'], $my['port'], $my['name'], $my['charset']);
                $pdo = new PDO($dsn, $my['user'], $my['pass'], [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                    PDO::ATTR_TIMEOUT            => 3,
                ]);
                self::$instance = $pdo;
                self::$driver = 'mysql';
                self::initSchema($pdo, 'mysql');
                return $pdo;
            } catch (Throwable $e) {
                // If MySQL fails and app is in development, fall back to SQLite with a log
                error_log('MySQL connection failed (' . $e->getMessage() . '). Falling back to SQLite.');
            }
        }

        // SQLite connection (default / fallback)
        $sqlitePath = $config['db']['sqlite']['path'];
        $dir = dirname($sqlitePath);
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }

        $pdo = new PDO('sqlite:' . $sqlitePath, null, null, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        // Enable WAL mode for high concurrency
        $pdo->exec('PRAGMA journal_mode = WAL;');
        self::$instance = $pdo;
        self::$driver = 'sqlite';
        self::initSchema($pdo, 'sqlite');
        return $pdo;
    }

    public static function getDriver(): string {
        return self::$driver ?? 'sqlite';
    }

    /**
     * Initialize schema and default admin user if needed
     */
    private static function initSchema(PDO $pdo, string $driver): void {
        if ($driver === 'mysql') {
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS submissions (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    form_type VARCHAR(50) NOT NULL,
                    form_label VARCHAR(100) NOT NULL,
                    name VARCHAR(255) NULL,
                    whatsapp VARCHAR(50) NULL,
                    email VARCHAR(255) NULL,
                    institution VARCHAR(255) NULL,
                    role VARCHAR(100) NULL,
                    city VARCHAR(100) NULL,
                    child_name VARCHAR(150) NULL,
                    child_age VARCHAR(50) NULL,
                    courses TEXT NULL,
                    class_type VARCHAR(50) NULL,
                    program VARCHAR(150) NULL,
                    interest VARCHAR(255) NULL,
                    interests TEXT NULL,
                    iam VARCHAR(100) NULL,
                    subject VARCHAR(255) NULL,
                    message TEXT NULL,
                    notes TEXT NULL,
                    teachers_count VARCHAR(50) NULL,
                    students_count VARCHAR(50) NULL,
                    source VARCHAR(100) NULL,
                    page VARCHAR(255) NULL,
                    status VARCHAR(50) NOT NULL DEFAULT 'New',
                    admin_notes TEXT NULL,
                    ip_address VARCHAR(64) NULL,
                    user_agent TEXT NULL,
                    consent TINYINT(1) NOT NULL DEFAULT 1,
                    raw_json LONGTEXT NULL,
                    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    INDEX idx_form_type (form_type),
                    INDEX idx_status (status),
                    INDEX idx_whatsapp (whatsapp),
                    INDEX idx_created_at (created_at)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");

            $pdo->exec("
                CREATE TABLE IF NOT EXISTS admins (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    username VARCHAR(50) NOT NULL UNIQUE,
                    password_hash VARCHAR(255) NOT NULL,
                    name VARCHAR(100) NOT NULL,
                    email VARCHAR(255) NOT NULL,
                    last_login DATETIME NULL,
                    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");

            $pdo->exec("
                CREATE TABLE IF NOT EXISTS rate_limits (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    identifier VARCHAR(100) NOT NULL,
                    action VARCHAR(50) NOT NULL,
                    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    INDEX idx_rate (identifier, created_at)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");
        } else {
            // SQLite
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS submissions (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    form_type TEXT NOT NULL,
                    form_label TEXT NOT NULL,
                    name TEXT,
                    whatsapp TEXT,
                    email TEXT,
                    institution TEXT,
                    role TEXT,
                    city TEXT,
                    child_name TEXT,
                    child_age TEXT,
                    courses TEXT,
                    class_type TEXT,
                    program TEXT,
                    interest TEXT,
                    interests TEXT,
                    iam TEXT,
                    subject TEXT,
                    message TEXT,
                    notes TEXT,
                    teachers_count TEXT,
                    students_count TEXT,
                    source TEXT,
                    page TEXT,
                    status TEXT NOT NULL DEFAULT 'New',
                    admin_notes TEXT,
                    ip_address TEXT,
                    user_agent TEXT,
                    consent INTEGER NOT NULL DEFAULT 1,
                    raw_json TEXT,
                    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
                );
                CREATE INDEX IF NOT EXISTS idx_sub_form ON submissions(form_type);
                CREATE INDEX IF NOT EXISTS idx_sub_status ON submissions(status);
                CREATE INDEX IF NOT EXISTS idx_sub_phone ON submissions(whatsapp);
                CREATE INDEX IF NOT EXISTS idx_sub_created ON submissions(created_at);

                CREATE TABLE IF NOT EXISTS admins (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    username TEXT NOT NULL UNIQUE,
                    password_hash TEXT NOT NULL,
                    name TEXT NOT NULL,
                    email TEXT NOT NULL,
                    last_login DATETIME,
                    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
                );

                CREATE TABLE IF NOT EXISTS rate_limits (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    identifier TEXT NOT NULL,
                    action TEXT NOT NULL,
                    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
                );
                CREATE INDEX IF NOT EXISTS idx_rate ON rate_limits(identifier, created_at);
            ");
        }

        // Seed default admin if table is empty
        $stmt = $pdo->query("SELECT COUNT(*) FROM admins");
        if ($stmt && (int)$stmt->fetchColumn() === 0) {
            $defaultUser = 'admin';
            $defaultPass = 'TechnoAdmin2026!';
            $defaultHash = password_hash($defaultPass, PASSWORD_BCRYPT);
            $ins = $pdo->prepare("INSERT INTO admins (username, password_hash, name, email, created_at) VALUES (?, ?, ?, ?, ?)");
            $ins->execute([
                $defaultUser,
                $defaultHash,
                'Technoknowledge Admin',
                'info@thetechnoknowledge.com',
                date('Y-m-d H:i:s')
            ]);
        }
    }
}
