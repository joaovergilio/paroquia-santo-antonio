<?php
// Cabeçalho e menu lateral do painel. Defina $titulo antes de incluir.
$paginaAtual = basename($_SERVER['SCRIPT_NAME']);
$menu = [
    '' => [['index.php', 'Início', ACESSO_TODOS]],
    'Celebrações' => [
        ['horarios.php', 'Horários fixos', ACESSO_CONTEUDO],
        ['agenda.php', 'Agenda e datas', ACESSO_CONTEUDO],
        ['clero.php', 'Padres', ACESSO_CONTEUDO],
    ],
    'Conteúdo do site' => [
        ['santos.php', 'Santos', ACESSO_CONTEUDO],
        ['movimentos.php', 'Movimentos', ACESSO_CONTEUDO],
    ],
    'Pessoas' => [
        ['inscricoes.php', 'Inscrições', ACESSO_CONTEUDO],
        ['dizimistas.php', 'Dizimistas', ACESSO_TODOS],
    ],
    'Finanças' => [
        ['financeiro.php', 'Entradas e saídas', ACESSO_FINANCEIRO],
        ['fechamento.php', 'Publicar transparência', ACESSO_FINANCEIRO],
    ],
    'Administração' => [
        ['configuracoes.php', 'Dados da paróquia', ACESSO_ADMIN],
        ['usuarios.php', 'Usuários', ACESSO_ADMIN],
    ],
];
$selos = [
    'inscricoes.php' => pode(ACESSO_CONTEUDO) ? (int)q("SELECT COUNT(*) FROM inscricao WHERE status = 'nova'")->fetchColumn() : 0,
    'dizimistas.php' => (int)q("SELECT COUNT(*) FROM dizimista WHERE status = 'novo'")->fetchColumn(),
];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($titulo ?? 'Painel') ?> · Painel da Paróquia</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="painel">
<header class="painel-topo">
    <button class="menu-toggle" aria-label="Abrir menu" data-toggle-menu>☰</button>
    <a class="marca" href="index.php">Painel da Paróquia</a>
    <div class="usuario-info">
        <span><?= e(usuario_atual()['nome'] ?? '') ?> · <?= e(PERFIS[usuario_atual()['perfil'] ?? ''] ?? '') ?></span>
        <a href="../index.php" target="_blank">Ver site</a>
        <a href="logout.php">Sair</a>
    </div>
</header>
<div class="painel-corpo">
    <nav class="menu-lateral" id="menu-lateral">
        <?php foreach ($menu as $grupo => $itens): ?>
            <?php $visiveis = array_filter($itens, fn($i) => pode($i[2])); ?>
            <?php if (!$visiveis) continue; ?>
            <?php if ($grupo): ?><p class="menu-grupo"><?= e($grupo) ?></p><?php endif; ?>
            <?php foreach ($visiveis as [$arquivo, $rotulo]): ?>
                <a href="<?= $arquivo ?>" class="<?= $paginaAtual === $arquivo ? 'ativo' : '' ?>">
                    <span><?= e($rotulo) ?></span>
                    <?php if (!empty($selos[$arquivo])): ?><span class="selo"><?= $selos[$arquivo] ?></span><?php endif; ?>
                </a>
            <?php endforeach; ?>
        <?php endforeach; ?>
    </nav>
    <main class="conteudo">
        <h1><?= e($titulo ?? '') ?></h1>
        <?= mostrar_flashes() ?>
