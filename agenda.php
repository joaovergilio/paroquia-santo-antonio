<?php
require_once __DIR__ . '/includes/bootstrap.php';

$sqlBase = 'SELECT a.*, c.titulo AS c_titulo, c.nome AS c_nome, c.id AS c_id, c.foto AS c_foto, c.funcao AS c_funcao
            FROM celebracao a LEFT JOIN clero c ON c.id = a.celebrante_id';

// ---------- Página de um item da agenda ----------
if (isset($_GET['id'])) {
    $ev = q("$sqlBase WHERE a.id = ?", [(int)$_GET['id']])->fetch();
    if (!$ev) {
        http_response_code(404);
        redirect('agenda.php');
    }
    $titulo = $ev['titulo'];
    require __DIR__ . '/includes/site_header.php';
    ?>
    <section class="secao">
        <div class="container leitura">
            <a href="agenda.php" class="voltar">Voltar para a agenda</a>
            <span class="etiqueta etiqueta-<?= e($ev['tipo']) ?>"><?= e(TIPOS_CELEBRACAO[$ev['tipo']]) ?></span>
            <h1><?= e($ev['titulo']) ?></h1>
            <dl class="detalhes-evento">
                <div><dt>Quando</dt><dd><?= e(data_extenso($ev['data_hora'])) ?>, às <?= hora_br(date('H:i', strtotime($ev['data_hora']))) ?></dd></div>
                <?php if ($ev['local']): ?><div><dt>Onde</dt><dd><?= e($ev['local']) ?></dd></div><?php endif; ?>
                <?php if ($ev['c_nome']): ?>
                    <div><dt>Celebrante</dt><dd><a href="clero.php#padre-<?= $ev['c_id'] ?>"><?= e(nome_clero(['titulo' => $ev['c_titulo'], 'nome' => $ev['c_nome']])) ?></a><?= $ev['c_funcao'] ? ' · ' . e($ev['c_funcao']) : '' ?></dd></div>
                <?php endif; ?>
            </dl>
            <?php if ($ev['imagem']): ?><img class="imagem-destaque" src="uploads/<?= e($ev['imagem']) ?>" alt=""><?php endif; ?>
            <?php if ($ev['descricao']): ?><div class="texto"><?= paragrafos($ev['descricao']) ?></div><?php endif; ?>
            <?php if ($ev['historia']): ?>
                <section class="historia">
                    <h2>História</h2>
                    <?= paragrafos($ev['historia']) ?>
                </section>
            <?php endif; ?>
        </div>
    </section>
    <?php
    require __DIR__ . '/includes/site_footer.php';
    exit;
}

// ---------- Lista ----------
$tipo = isset($_GET['tipo']) && array_key_exists($_GET['tipo'], TIPOS_CELEBRACAO) ? $_GET['tipo'] : '';
$filtro = $tipo ? ' AND a.tipo = ?' : '';
$params = $tipo ? [agora(), $tipo] : [agora()];
$proximos = q("$sqlBase WHERE a.data_hora >= ?$filtro ORDER BY a.data_hora", $params)->fetchAll();
$passados = q("$sqlBase WHERE a.data_hora < ?$filtro ORDER BY a.data_hora DESC LIMIT 6", $params)->fetchAll();

$porMes = [];
foreach ($proximos as $ev) {
    $porMes[date('Y-m', strtotime($ev['data_hora']))][] = $ev;
}

$titulo = 'Agenda';
require __DIR__ . '/includes/site_header.php';
?>
<section class="secao">
    <div class="container">
        <h1>Agenda</h1>
        <p class="introducao">Missas especiais, datas comemorativas, festas e encontros.</p>

        <nav class="chips" aria-label="Filtrar por tipo">
            <a class="chip <?= $tipo === '' ? 'ativo' : '' ?>" href="agenda.php">Tudo</a>
            <?php foreach (TIPOS_CELEBRACAO as $chave => $rotulo): ?>
                <a class="chip <?= $tipo === $chave ? 'ativo' : '' ?>" href="?tipo=<?= $chave ?>"><?= e($rotulo) ?></a>
            <?php endforeach; ?>
        </nav>

        <?php if (!$porMes): ?>
            <p class="suave">Nada agendado por enquanto.</p>
        <?php endif; ?>
        <?php foreach ($porMes as $mes => $itens): ?>
            <h2 class="titulo-mes"><?= nome_mes((int)substr($mes, 5)) ?> de <?= substr($mes, 0, 4) ?></h2>
            <div class="grade-3"><?php foreach ($itens as $ev): require __DIR__ . '/includes/cartao_evento.php'; endforeach; ?></div>
        <?php endforeach; ?>

        <?php if ($passados): ?>
            <h2 class="espaco-topo">Já aconteceram</h2>
            <div class="grade-3 passados"><?php foreach ($passados as $ev): require __DIR__ . '/includes/cartao_evento.php'; endforeach; ?></div>
        <?php endif; ?>
    </div>
</section>
<?php require __DIR__ . '/includes/site_footer.php'; ?>
