<?php
require_once __DIR__ . '/includes/bootstrap.php';

// ---------- Página de um santo ----------
if (isset($_GET['id'])) {
    $s = q('SELECT * FROM santo WHERE id = ?', [(int)$_GET['id']])->fetch();
    if (!$s) {
        redirect('santos.php');
    }
    $titulo = $s['nome'];
    require __DIR__ . '/includes/site_header.php';
    ?>
    <section class="secao">
        <div class="container leitura">
            <a href="santos.php" class="voltar">Todos os santos</a>
            <h1><?= e($s['nome']) ?></h1>
            <p class="suave">Festa em <?= (int)$s['dia'] ?> de <?= mb_strtolower(nome_mes((int)$s['mes'])) ?><?= $s['titulo'] ? ' · ' . e($s['titulo']) : '' ?></p>
            <?php if ($s['imagem']): ?><img class="imagem-destaque" src="uploads/<?= e($s['imagem']) ?>" alt=""><?php endif; ?>
            <?php if ($s['padroeiro']): ?><p>Padroeiro(a) <?= e($s['padroeiro']) ?>.</p><?php endif; ?>
            <?php if ($s['resumo']): ?><p class="introducao"><?= e($s['resumo']) ?></p><?php endif; ?>
            <section class="historia">
                <h2>História</h2>
                <?= paragrafos($s['historia']) ?: '<p class="suave">História ainda não cadastrada.</p>' ?>
            </section>
            <?php if ($s['oracao']): ?>
                <section class="oracao">
                    <h2>Oração</h2>
                    <?= paragrafos($s['oracao']) ?>
                </section>
            <?php endif; ?>
        </div>
    </section>
    <?php
    require __DIR__ . '/includes/site_footer.php';
    exit;
}

// ---------- Lista de santos ----------
$santos = q('SELECT * FROM santo ORDER BY mes, dia, nome')->fetchAll();
$doDia = santo_do_dia();
$porMes = [];
foreach ($santos as $s) {
    $porMes[(int)$s['mes']][] = $s;
}

$titulo = 'Santos';
require __DIR__ . '/includes/site_header.php';
?>
<section class="secao">
    <div class="container">
        <h1>Santos</h1>
        <p class="introducao">A história de cada santo e o dia da sua festa.</p>

        <?php if ($doDia): ?>
            <a class="cartao destaque-hoje" href="?id=<?= $doDia['id'] ?>">
                <span class="suave">Hoje a Igreja celebra</span>
                <strong><?= e($doDia['nome']) ?></strong>
                <span><?= e($doDia['resumo']) ?></span>
            </a>
        <?php endif; ?>

        <label class="busca">Procurar santo
            <input type="search" placeholder="Nome ou padroado" data-busca-santos>
        </label>
        <p class="suave" data-sem-resultado hidden>Nenhum santo encontrado.</p>

        <?php foreach ($porMes as $mes => $lista): ?>
            <section class="grupo-santos" data-grupo>
                <h2 class="titulo-mes"><?= nome_mes($mes) ?></h2>
                <div class="grade-santos">
                    <?php foreach ($lista as $s): ?>
                        <a class="santo-card <?= $doDia && $doDia['id'] === $s['id'] ? 'hoje' : '' ?>" href="?id=<?= $s['id'] ?>"
                           data-texto="<?= e(mb_strtolower($s['nome'] . ' ' . $s['titulo'] . ' ' . $s['padroeiro'] . ' ' . $s['resumo'])) ?>">
                            <span class="santo-card-texto">
                                <small><?= (int)$s['dia'] ?> de <?= mb_strtolower(nome_mes((int)$s['mes'])) ?></small>
                                <strong><?= e($s['nome']) ?></strong>
                                <?php if ($s['padroeiro']): ?><small class="suave">Padroeiro(a) <?= e($s['padroeiro']) ?></small><?php endif; ?>
                            </span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endforeach; ?>
    </div>
</section>
<?php require __DIR__ . '/includes/site_footer.php'; ?>
