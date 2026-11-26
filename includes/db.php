<?php
// Conexão única com o banco via PDO

function db(): PDO
{
    static $pdo = null;
    if ($pdo !== null) {
        return $pdo;
    }
    try {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    } catch (PDOException $ex) {
        http_response_code(500);
        exit('<h1>Erro ao conectar no banco de dados</h1><p>Confira o arquivo config/config.php e se o banco <b>'
            . htmlspecialchars(DB_NAME) . '</b> foi criado com database/schema.sql.</p>');
    }
    return $pdo;
}

// Atalho: executa uma consulta preparada e devolve o statement
function q(string $sql, array $params = []): PDOStatement
{
    $st = db()->prepare($sql);
    $st->execute($params);
    return $st;
}
