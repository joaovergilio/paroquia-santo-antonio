<?php
require_once __DIR__ . '/includes/bootstrap.php';

$horarios = q('SELECT h.*, c.titulo AS c_titulo, c.nome AS c_nome, c.id AS c_id FROM horario h
               LEFT JOIN clero c ON c.id = h.celebrante_id
               WHERE h.ativo = 1 ORDER BY h.dia_semana, h.hora')->fetchAll();
$porDia = array_fill(0, 7, []);
foreach ($horarios as $h) {
    $porDia[(int)$h['dia_semana']][] = $h;
}
$tipos = array_values(array_unique(array_column($horarios, 'tipo')));

$especiais = q('SELECT a.*, c.titulo AS c_titulo, c.nome AS c_nome FROM celebracao a
                LEFT JOIN clero c ON c.id = a.celebrante_id
                WHERE a.data_hora BETWEEN ? AND ? ORDER BY a.data_hora',
    [agora(), date('Y-m-d 23:59:59', strtotime('+60 days'))])->fetchAll();

$hoje = (int)date('w');
$titulo = 'Horários';
require __DIR__ . '/includes/site_header.php';
?>
<section class="secao">
    <div class="container">
        <h1>Horários</h1>
        <p class="introducao">Horários de toda semana. Datas especiais estão <a href="#especiais">mais abaixo</a>.</p>

        <?php if ($tipos): ?>
            <div class="chips" data-filtro-horarios role="group" aria-label="Filtrar por tipo">
                <button class="chip ativo" data-tipo="">Todos</button>
                <?php foreach ($tipos as $t): ?><button class="chip" data-tipo="<?= e($t) ?>"><?= e($t) ?></button><?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="grade-semana">
            <?php foreach ([0, 1, 2, 3, 4, 5, 6] as $d): ?>
                <section class="dia-coluna <?= $d === $hoje ? 'hoje' : '' ?> <?= !$porDia[$d] ? 'vazio' : '' ?>">
                    <h2><?= DIAS_SEMANA[$d] ?><?= $d === $hoje ? ' <span class="etiqueta">hoje</span>' : '' ?></h2>
                    <?php if (!$porDia[$d]): ?>
                        <p class="suave">Sem celebrações fixas.</p>
                    <?php endif; ?>
                    <?php foreach ($porDia[$d] as $h): ?>
                        <div class="horario-item" data-tipo="<?= e($h['tipo']) ?>">
                            <strong class="horario-hora"><?= hora_br($h['hora']) ?></strong>
                            <div>
                                <span class="horario-tipo tipo-<?= e(strtolower(strtok($h['tipo'], ' '))) ?>"><?= e($h['tipo']) ?></span>
                                <?php if ($h['local']): ?><small><?= e($h['local']) ?></small><?php endif; ?>
                                <?php if ($h['c_nome']): ?><small>Celebrante: <a href="clero.php#padre-<?= $h['c_id'] ?>"><?= e(nome_clero(['titulo' => $h['c_titulo'], 'nome' => $h['c_nome']])) ?></a></small><?php endif; ?>
                                <?php if ($h['observacao']): ?><small class="suave"><?= e($h['observacao']) ?></small><?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </section>
            <?php endforeach; ?>
        </div>

        <h2 class="espaco-topo" id="especiais">Datas especiais (próximos 60 dias)</h2>
        <?php if (!$especiais): ?>
            <p class="suave">Nenhuma celebração especial nos próximos 60 dias.</p>
        <?php else: ?>
            <div class="cartao rolagem">
                <table class="tabela">
                    <thead><tr><th>Data</th><th>Horário</th><th>Celebração</th><th>Local</th><th>Celebrante</th></tr></thead>
                    <tbody>
                    <?php foreach ($especiais as $a): ?>
                        <tr>
                            <td><?= e(data_extenso($a['data_hora'])) ?></td>
                            <td><strong><?= hora_br(date('H:i', strtotime($a['data_hora']))) ?></strong></td>
                            <td><a href="agenda.php?id=<?= $a['id'] ?>"><?= e($a['titulo']) ?></a><br><small class="suave"><?= e(TIPOS_CELEBRACAO[$a['tipo']]) ?></small></td>
                            <td><?= e($a['local']) ?></td>
                            <td><?= e(nome_clero(['titulo' => $a['c_titulo'], 'nome' => $a['c_nome']])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <div class="cartao aviso-secretaria">
            <h3>Secretaria paroquial</h3>
            <p><?= e(cfg('secretaria_horario')) ?> · <?= e(cfg('telefone')) ?>.
                Batizados, casamentos, intenções de missa e declarações são agendados na secretaria.</p>
        </div>
    </div>
</section>
<?php require __DIR__ . '/includes/site_footer.php'; ?>
