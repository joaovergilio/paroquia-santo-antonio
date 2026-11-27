<?php
require_once __DIR__ . '/../includes/bootstrap.php';
exige_login(ACESSO_FINANCEIRO);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $mes = (int)post('mes');
    $ano = (int)post('ano');
    $inicio = sprintf('%04d-%02d-01', $ano, max(1, $mes));
    $fim = date('Y-m-t', strtotime($inicio));

    if ($mes < 1 || $mes > 12 || mes_publicado($inicio)) {
        flash('erro', 'Mês inválido ou já publicado.');
    } elseif ($fim >= date('Y-m-d')) {
        flash('erro', 'Só é possível publicar um mês depois que ele terminar.');
    } else {
        $totais = q("SELECT
                       COALESCE(SUM(CASE WHEN tipo = 'receita' THEN valor ELSE 0 END), 0) AS receitas,
                       COALESCE(SUM(CASE WHEN tipo = 'despesa' THEN valor ELSE 0 END), 0) AS despesas
                     FROM lancamento WHERE data BETWEEN ? AND ?", [$inicio, $fim])->fetch();
        q('INSERT INTO fechamento_mensal (mes, ano, total_receitas, total_despesas, usuario_id, publicado_em) VALUES (?, ?, ?, ?, ?, ?)',
            [$mes, $ano, $totais['receitas'], $totais['despesas'], usuario_atual()['id'], agora()]);
        flash('sucesso', nome_mes($mes) . " de $ano publicado na página de transparência.");
    }
    redirect('fechamento.php');
}

$meses = [];
for ($i = 1; $i <= 12; $i++) {
    $ts = strtotime(date('Y-m-01') . " -$i month");
    $t = q("SELECT COUNT(*) AS qtd,
              COALESCE(SUM(CASE WHEN tipo = 'receita' THEN valor ELSE 0 END), 0) AS receitas,
              COALESCE(SUM(CASE WHEN tipo = 'despesa' THEN valor ELSE 0 END), 0) AS despesas
            FROM lancamento WHERE data BETWEEN ? AND ?", [date('Y-m-01', $ts), date('Y-m-t', $ts)])->fetch();
    $pub = q('SELECT f.*, u.nome AS usuario FROM fechamento_mensal f JOIN usuario u ON u.id = f.usuario_id WHERE mes = ? AND ano = ?',
        [(int)date('n', $ts), (int)date('Y', $ts)])->fetch();
    // Mostra o mês passado sempre; os anteriores só se tiverem lançamentos ou já estiverem publicados
    if ($i > 1 && !$pub && (int)$t['qtd'] === 0) {
        continue;
    }
    $meses[] =['mes' => (int)date('n', $ts), 'ano' => (int)date('Y', $ts), 'totais' => $t, 'publicacao' => $pub];
}

$titulo = 'Fechamento do mês';
require __DIR__ . '/../includes/admin_header.php';
?>
<div class="ajuda"><div>
    <p>Quando um mês terminar e todos os lançamentos estiverem conferidos, clique em <b>Publicar</b>. Os totais aparecem na página
        <a href="../transparencia.php" target="_blank">Transparência</a> e os lançamentos daquele mês ficam travados.
        Erros depois disso só podem ser corrigidos com estorno.</p>
</div></div>
<div class="cartao rolagem">
    <table class="tabela">
        <thead><tr><th>Mês</th><th class="num">Lançamentos</th><th class="num">Entradas</th><th class="num">Saídas</th><th class="num">Saldo</th><th>Situação</th></tr></thead>
        <tbody>
        <?php foreach ($meses as $m): $t = $m['totais']; ?>
            <tr>
                <td><a href="financeiro.php?mes=<?= $m['mes'] ?>&ano=<?= $m['ano'] ?>"><?= nome_mes($m['mes']) ?>/<?= $m['ano'] ?></a></td>
                <td class="num"><?= (int)$t['qtd'] ?></td>
                <td class="num positivo"><?= moeda($t['receitas']) ?></td>
                <td class="num negativo"><?= moeda($t['despesas']) ?></td>
                <td class="num"><strong><?= moeda($t['receitas'] - $t['despesas']) ?></strong></td>
                <td>
                    <?php if ($m['publicacao']): ?>
                        <span class="etiqueta etiqueta-publicado">Publicado</span>
                        <small class="suave">em <?= data_br($m['publicacao']['publicado_em']) ?> por <?= e($m['publicacao']['usuario']) ?></small>
                    <?php else: ?>
                        <form method="post" onsubmit="return confirm('Publicar <?= nome_mes($m['mes']) ?>/<?= $m['ano'] ?>? Depois disso os lançamentos do mês ficam travados.')">
                            <?= csrf_field() ?>
                            <input type="hidden" name="mes" value="<?= $m['mes'] ?>">
                            <input type="hidden" name="ano" value="<?= $m['ano'] ?>">
                            <button class="botao botao-pequeno">Publicar</button>
                        </form>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php require __DIR__ . '/../includes/admin_footer.php'; ?>
