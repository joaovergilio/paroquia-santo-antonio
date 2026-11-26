<?php
// Cabeçalho do site público. Defina $titulo antes de incluir.
$paginaAtual = basename($_SERVER['SCRIPT_NAME']);
$links = [
    'index.php' => 'Início',
    'horarios.php' => 'Horários',
    'agenda.php' => 'Agenda',
    'santos.php' => 'Santos',
    'clero.php' => 'Nossos padres',
    'movimentos.php' => 'Movimentos',
    'biblia.php' => 'Bíblia',
    'transparencia.php' => 'Transparência',
];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($titulo ?? 'Início') ?> · <?= e(cfg('paroquia_nome')) ?></title>
    <meta name="description" content="<?= e(cfg('paroquia_nome') . ' - ' . cfg('cidade')) ?>: horários de missa, agenda, movimentos, dízimo e transparência.">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="site">
<header class="site-topo">
    <div class="container topo-linha">
        <a class="marca" href="index.php">
            <span class="marca-texto"><strong><?= e(cfg('paroquia_nome')) ?></strong><small><?= e(cfg('cidade')) ?></small></span>
        </a>
        <button class="menu-toggle" aria-label="Abrir menu" data-toggle-menu>☰</button>
        <nav class="site-menu" id="menu-lateral">
            <?php foreach ($links as $arquivo => $rotulo): ?>
                <a href="<?= $arquivo ?>" class="<?= $paginaAtual === $arquivo ? 'ativo' : '' ?>"><?= $rotulo ?></a>
            <?php endforeach; ?>
            <a href="dizimo.php" class="botao botao-cheio botao-pequeno <?= $paginaAtual === 'dizimo.php' ? 'ativo' : '' ?>">Dízimo e doações</a>
        </nav>
    </div>
</header>
<main>
