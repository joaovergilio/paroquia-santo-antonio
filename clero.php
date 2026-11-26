<?php
require_once __DIR__ . '/includes/bootstrap.php';

$clero = q('SELECT * FROM clero WHERE ativo = 1 ORDER BY ordem IS NULL, ordem, nome')->fetchAll();

$titulo = 'Nossos padres';
require __DIR__ . '/includes/site_header.php';
?>
<section class="secao">
    <div class="container">
        <h1>Nossos padres</h1>
        <p class="introducao">Os sacerdotes que servem a paróquia e as próximas celebrações de cada um.</p>

        <?php if (!$clero): ?>
            <p class="suave">Nenhum sacerdote cadastrado.</p>
        <?php endif; ?>
        <?php foreach ($clero as $c): ?>
            <?php $agendaPadre = proximos_horarios(5, null, (int)$c['id'], 14); ?>
            <article class="cartao clero-card <?= $c['foto'] ? '' : 'sem-foto' ?>" id="padre-<?= $c['id'] ?>">
                <?php if ($c['foto']): ?>
                    <div class="clero-foto"><img src="uploads/<?= e($c['foto']) ?>" alt="Foto de <?= e(nome_clero($c)) ?>"></div>
                <?php endif; ?>
                <div class="clero-texto">
                    <h2><?= e(nome_clero($c)) ?></h2>
                    <p class="suave"><?= e($c['funcao']) ?><?= $c['ordenacao'] ? ' · ordenado em ' . data_br($c['ordenacao']) : '' ?></p>
                    <?= paragrafos($c['biografia']) ?>
                </div>
                <aside class="clero-agenda">
                    <h3>Próximas celebrações</h3>
                    <?php if (!$agendaPadre): ?>
                        <p class="suave">Nenhuma nos próximos 14 dias.</p>
                    <?php else: ?>
                        <ul class="lista-horarios compacta">
                            <?php foreach ($agendaPadre as $m): ?>
                                <li>
                                    <div class="lh-quando"><strong><?= hora_br(date('H:i', strtotime($m['quando']))) ?></strong><span><?= e(quando_amigavel($m['quando'])) ?></span></div>
                                    <div><?php if ($m['id']): ?><a href="agenda.php?id=<?= $m['id'] ?>"><?= e($m['titulo']) ?></a><?php else: ?><?= e($m['titulo']) ?><?php endif; ?>
                                        <small class="suave"><?= e($m['local']) ?></small></div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </aside>
            </article>
        <?php endforeach; ?>
    </div>
</section>
<?php require __DIR__ . '/includes/site_footer.php'; ?>
