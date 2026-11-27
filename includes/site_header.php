<?php
// Cabeçalho do site público. Defina $titulo antes de incluir.
$paginaAtual = basename($_SERVER['SCRIPT_NAME']);
// Só o essencial no menu. Santos, padres, Bíblia e transparência ficam na página inicial e no rodapé.
$links = [
    'horarios.php' => 'Horários',
    'agenda.php' => 'Agenda',
    'movimentos.php' => 'Movimentos',
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
    <?= favicon() ?>
</head>
<body class="site">
<header class="site-topo">
    <div class="container topo-linha">
        <a class="marca" href="index.php">
            <span class="marca-simbolo"><?= cruz() ?></span>
            <span class="marca-texto"><strong><?= e(cfg('paroquia_nome')) ?></strong><small><?= e(cfg('cidade')) ?></small></span>
        </a>
        <button class="menu-toggle" aria-label="Abrir menu" data-toggle-menu>☰</button>
        <nav class="site-menu" id="menu-lateral">
            <?php foreach ($links as $arquivo => $rotulo): ?>
                <a href="<?= $arquivo ?>" class="<?= $paginaAtual === $arquivo ? 'ativo' : '' ?>"><?= $rotulo ?></a>
            <?php endforeach; ?>
            <a href="dizimo.php" class="botao botao-cheio botao-pequeno <?= $paginaAtual === 'dizimo.php' ? 'ativo' : '' ?>">Dízimo</a>
        </nav>
    </div>
</header>
<main>
