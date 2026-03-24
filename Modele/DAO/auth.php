<?php
// Système d'authentification simple
session_start();

// Identifiant et mot de passe en dur
const ADMIN_IDENTIFIANT = 'admin';
const ADMIN_PASSWORD = 'admin';

// Load config if present (create a config.php with your Alwaysdata credentials)
if (file_exists(__DIR__ . '/config.php')) {
    require_once __DIR__ . '/config.php';
}

/**
 * Vérifier si l'utilisateur est authentifié
 */
function isAuthenticated() {
    return isset($_SESSION['authenticated']) && $_SESSION['authenticated'] === true;
}

/**
 * Authentifier l'utilisateur
 */
function authenticate($identifiant, $password) {
    if ($identifiant === ADMIN_IDENTIFIANT && $password === ADMIN_PASSWORD) {
        $_SESSION['authenticated'] = true;
        $_SESSION['login_time'] = time();
        $_SESSION['user'] = $identifiant;
        return true;
    }
    return false;
}

/**
 * Rediriger vers la page de login si non authentifié
 */
function requireAuth() {
    if (!isAuthenticated()) {
        header('Location: /login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
        exit;
    }
}

/**
 * Déconnecter l'utilisateur
 */
function logout() {
    $_SESSION['authenticated'] = false;
    session_destroy();
    header('Location: login.php');
    exit;
}

/**
 * Connexion à la base de données SQLite
 */
function getDBConnection() {
    if (defined('DB_TYPE') && DB_TYPE === 'mysql') {
        $host = DB_HOST;
        $port = DB_PORT;
        $dbname = DB_NAME;
        $user = DB_USER;
        $pass = DB_PASS;
        $charset = DB_CHARSET;

        if (empty($dbname) || empty($user)) {
            die('Configuration MySQL manquante : vérifie `config.php` ou définissez les variables d\'environnement.');
        }

        $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset={$charset}";
        try {
            $pdo = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
            // When using MySQL on Alwaysdata we assume tables already exist (no local initialization)
            return $pdo;
        } catch (PDOException $e) {
            die("Erreur de connexion MySQL: " . $e->getMessage());
        }
    }

    // Fallback to local SQLite
    $dbPath = __DIR__ . '/data/gestion_joueurs.db';
    $dbDir = dirname($dbPath);
    
    // Créer le répertoire data s'il n'existe pas
    if (!is_dir($dbDir)) {
        mkdir($dbDir, 0755, true);
    }
    
    try {
        $pdo = new PDO('sqlite:' . $dbPath);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Initialiser la base de données si elle n'existe pas
        initializeDatabase($pdo);
        
        return $pdo;
    } catch (PDOException $e) {
        die("Erreur de connexion à la base de données: " . $e->getMessage());
    }
}

/**
 * Initialiser la base de données avec les tables nécessaires
 */
function initializeDatabase($pdo) {
    // Only run initialization for SQLite. If using MySQL (Alwaysdata), we assume tables are already created.
    if (defined('DB_TYPE') && DB_TYPE === 'mysql') {
        return;
    }

    // Vérifier si la table Joueur existe (SQLite)
    $stmt = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='Joueur'");
    if ($stmt && $stmt->fetch()) {
        return; // La base de données est déjà initialisée
    }
    
    // Créer les tables
    $sql = file_get_contents(__DIR__ . '/database.sqlite.sql');
    
    // Exécuter les commandes SQL
    $pdo->exec($sql);
}
?>
