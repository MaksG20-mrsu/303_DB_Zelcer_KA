<?php
class Database {
    private static $connection = null;
    
    public static function connect() {
        if (self::$connection === null) {
            try {
                $dbPath = __DIR__ . '/../data/university.db';
                
                // Проверяем существует ли файл БД, если нет - создаем
                if (!file_exists($dbPath)) {
                    self::initializeDatabase($dbPath);
                }
                
                self::$connection = new PDO(
                    "sqlite:$dbPath",
                    null,
                    null,
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_PERSISTENT => false
                    ]
                );
                
                // Включаем поддержку внешних ключей в SQLite
                self::$connection->exec('PRAGMA foreign_keys = ON;');
                
            } catch (PDOException $e) {
                die("Ошибка подключения к базе данных: " . $e->getMessage());
            }
        }
        return self::$connection;
    }
    
    private static function initializeDatabase($dbPath) {
        try {
            // Создаем файл базы данных
            $db = new PDO("sqlite:$dbPath");
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Читаем SQL из файла
            $sqlFile = __DIR__ . '/../data/university.sql';
            $sql = file_get_contents($sqlFile);
            
            if ($sql === false) {
                throw new Exception("Не удалось прочитать SQL файл: $sqlFile");
            }
            
            // Разделяем SQL на отдельные запросы
            $queries = explode(';', $sql);
            
            foreach ($queries as $query) {
                $query = trim($query);
                if (!empty($query)) {
                    $db->exec($query . ';');
                }
            }
            
        } catch (Exception $e) {
            die("Ошибка инициализации базы данных: " . $e->getMessage());
        }
    }
}
?>