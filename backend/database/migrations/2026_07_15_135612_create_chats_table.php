<?php

declare(strict_types=1);

use Wibiesana\Padi\Core\DatabaseManager;

return new class
{
    public function up(): void
    {
        $db = DatabaseManager::connection();
        $driver = DatabaseManager::getDriver();

        if ($driver === 'sqlite') {
            $sql = "CREATE TABLE IF NOT EXISTS chats (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                sender_id INTEGER NOT NULL,
                receiver_id INTEGER NOT NULL,
                message TEXT NOT NULL,
                is_read INTEGER DEFAULT 0,
                created_by INTEGER,
                updated_by INTEGER,
                created_at INTEGER DEFAULT (strftime('%s', 'now')),
                updated_at INTEGER DEFAULT (strftime('%s', 'now')),
                FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
                FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL
            )";

            $db->exec($sql);
            $db->exec("CREATE INDEX IF NOT EXISTS idx_chats_sender_id ON chats(sender_id)");
            $db->exec("CREATE INDEX IF NOT EXISTS idx_chats_receiver_id ON chats(receiver_id)");
            $db->exec("CREATE INDEX IF NOT EXISTS idx_chats_created_by ON chats(created_by)");
        } elseif ($driver === 'pgsql') {
            $sql = "CREATE TABLE IF NOT EXISTS chats (
                id SERIAL PRIMARY KEY,
                sender_id INTEGER NOT NULL,
                receiver_id INTEGER NOT NULL,
                message TEXT NOT NULL,
                is_read INTEGER DEFAULT 0,
                created_by INTEGER,
                updated_by INTEGER,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
                FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL
            )";

            $db->exec($sql);
            $db->exec("CREATE INDEX IF NOT EXISTS idx_chats_sender_id ON chats(sender_id)");
            $db->exec("CREATE INDEX IF NOT EXISTS idx_chats_receiver_id ON chats(receiver_id)");
            $db->exec("CREATE INDEX IF NOT EXISTS idx_chats_created_by ON chats(created_by)");

            $db->exec("
                DROP TRIGGER IF EXISTS update_chats_updated_at ON chats;
                CREATE TRIGGER update_chats_updated_at 
                    BEFORE UPDATE ON chats 
                    FOR EACH ROW 
                    EXECUTE FUNCTION update_updated_at_column();
            ");
        } else {
            // MySQL/MariaDB
            $sql = "CREATE TABLE IF NOT EXISTS chats (
                id INT AUTO_INCREMENT PRIMARY KEY,
                sender_id INT NOT NULL,
                receiver_id INT NOT NULL,
                message TEXT NOT NULL,
                is_read TINYINT DEFAULT 0,
                created_by INT NULL,
                updated_by INT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_chats_sender_id (sender_id),
                INDEX idx_chats_receiver_id (receiver_id),
                INDEX idx_chats_created_by (created_by),
                FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
                FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

            $db->exec($sql);
        }

        echo "✓ Chats table created\n";
    }

    public function down(): void
    {
        $db = DatabaseManager::connection();
        $driver = DatabaseManager::getDriver();

        if ($driver === 'pgsql') {
            $db->exec("DROP TRIGGER IF EXISTS update_chats_updated_at ON chats");
        }

        $db->exec("DROP TABLE IF EXISTS chats");
        echo "✓ Chats table dropped\n";
    }
};