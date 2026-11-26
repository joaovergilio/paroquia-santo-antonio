<?php
require_once __DIR__ . '/includes/bootstrap.php';

$proximasMissas = proximos_horarios(4, 'Missa');
$agenda = q("SELECT a.*, c.titulo AS c_titulo, c.nome AS c_nome FROM celebracao a
             LEFT JOIN clero c ON c.id = a.celebrante_id
             WHERE a.data_hora >= ? ORDER BY a.data_hora LIMIT 3", [agora()])->fetchAll();
$santo = santo_do_dia();
$santoHoje = $santo !== null;
$santo = $santo ?: proximo_santo();
$versiculo = versiculo_do_dia();

$titulo = 'Início';
require __DIR__ . '/includes/site_header.php';
?>
<section class="hero">
    <div class="container hero-grade">
        <div>
            <h1><?= e(cfg('paroquia_nome')) ?></h1>
            <p class="hero-texto"><?= e(cfg('igreja_nome')) ?>, <?= e(cfg('cidade')) ?>.</p>
            <p class="hero-texto"><?= e(cfg('boas_vindas')) ?></p>
        </div>
        <div class="proximas-missas">
            <h2>Próximas missas</h2>
            <?php if (!$proximasMissas): ?>
                <p class="suave">Nenhuma missa cadastrada para os próximos dias.</p>
            <?php endif; ?>
            <ul class="lista-horarios">
                <?php foreach ($proximasMissas as $m): ?>
                    <li>
                        <div class="lh-quando">
                            <strong><?= hora_br(date('H:i', strtotime($m['quando']))) ?></strong>
                            <span><?= e(quando_amigavel($m['quando'])) ?></span>
                        </div>
                        <div>
                            <?php if ($m['id']): ?><a href="agenda.php?id=<?= $m['id'] ?>"><?= e($m['titulo']) ?></a><?php else: ?><?= e($m['titulo']) ?><?php endif; ?>
                            <small class="suave"><?= e($m['local']) ?><?= $m['celebrante'] ? ' · ' . e($m['celebrante']) : '' ?></small>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
            <a href="horarios.php">Todos os horários</a>
        </div>
    </div>
</section>

<section class="secao">
    <div class="container">
        <div class="cabecalho-secao">
            <h2>Agenda</h2>
            <a href="agenda.php">Ver agenda completa</a>
        </div>
        <?php if (!$agenda): ?>
            <p class="suave">Nenhuma celebração especial agendada.</p>
        <?php else: ?>
            <div class="grade-3">
                <?php foreach ($agenda as $ev): require __DIR__ . '/includes/cartao_evento.php'; endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<section class="secao secao-clara">
    <div class="container grade-2">
        <?php if ($santo): ?>
            <div>
                <h2><?= $santoHoje ? 'Santo do dia' : 'Próxima festa, ' . (int)$santo['dia'] . ' de ' . mb_strtolower(nome_mes((int)$santo['mes'])) ?></h2>
                <p><a href="santos.php?id=<?= $santo['id'] ?>"><strong><?= e($santo['nome']) ?></strong></a></p>
                <p><?= e($santo['resumo']) ?></p>
            </div>
        <?php endif; ?>
        <?php if ($versiculo): ?>
            <div>
                <h2>Palavra do dia</h2>
                <blockquote class="citacao">“<?= e($versiculo['texto']) ?>” <cite><a href="<?= e($versiculo['link']) ?>"><?= e($versiculo['referencia']) ?></a></cite></blockquote>
            </div>
        <?php endif; ?>
    </div>
</section>
<?php require __DIR__ . '/includes/site_footer.php'; ?>
