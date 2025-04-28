<?php
/**
 * DatabaseManager - Classe gestionnaire de connexions aux bases de données
 * Supporte les bases SQL (MySQL) et NoSQL (MongoDB)
 */
class DatabaseManager {
    private static $pdoConnection = null;
    private static $mongoClient = null;
    private static $mongoDatabase = null;
    
    /**
     * Obtient une connexion à la base de données MySQL
     * @return PDO Instance PDO pour MySQL
     */
    public static function getSQL() {
        if (self::$pdoConnection === null) {
            // Configuration pour MySQL
            $dbUrl = getenv('JAWSDB_URL');
            if ($dbUrl) {
                $dbParts = parse_url($dbUrl);
                $host = $dbParts['host'];
                $username = $dbParts['user'];
                $password = $dbParts['pass'];
                $dbname = ltrim($dbParts['path'], '/');
                $port = isset($dbParts['port']) ? $dbParts['port'] : 3306;
            } else {
                $host = 'localhost';
                $dbname = 'DB_EcoRide';
                $username = 'root';
                $password = 'Molfarka8';
                $port = '3306';
            }
            
            try {
                self::$pdoConnection = new PDO(
                    "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4",
                    $username,
                    $password,
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false
                    ]
                );
            } catch (PDOException $e) {
                die("Erreur de connexion MySQL: " . $e->getMessage());
            }
        }
        
        return self::$pdoConnection;
    }
    
    /**
     * Obtient une connexion à la base de données MongoDB
     * @param string $collectionName Nom de la collection (optionnel)
     * @return MongoDB\Database|MongoDB\Collection Instance de la base ou collection MongoDB
     */
    public static function getNoSQL($collectionName = null) {
        if (self::$mongoClient === null) {
            if (!class_exists('MongoDB\Client')) {
                if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
                    require_once __DIR__ . '/../vendor/autoload.php';
                } else {
                    die("L'extension MongoDB n'est pas installée. Exécutez: composer require mongodb/mongodb");
                }
            }
            
            // Configuration pour MongoDB
            $mongoUrl = getenv('MONGODB_URI') ?: 'mongodb://localhost:27017';
            $dbName = getenv('MONGODB_DATABASE') ?: 'EcoRideReviews';
            
            try {
                self::$mongoClient = new MongoDB\Client($mongoUrl);
                self::$mongoDatabase = self::$mongoClient->{$dbName};
            } catch (Exception $e) {
                die("Erreur de connexion MongoDB: " . $e->getMessage());
            }
        }
        
        return $collectionName ? self::$mongoDatabase->{$collectionName} : self::$mongoDatabase;
    }
}

// Pour la rétrocompatibilité avec le code existant
$pdo = DatabaseManager::getSQL();
