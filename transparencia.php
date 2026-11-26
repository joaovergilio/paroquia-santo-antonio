<?php
require_once __DIR__ . '/includes/bootstrap.php';

// O site mostra apenas totais por categoria, nunca nomes de dizimistas ou doadores
$fechamentos = q('SELECT * FROM fechamento_mensal ORDER BY ano DESC, mes DESC')->fetchAll();

$selecionado = $fechamentos[0] ?? null;
foreach ($fechamentos as $f) {
    if (isset($_GET['mes'], $_GET['ano']) && (int)$_GET['mes'] === (int)$f['mes'] && (int)$_GET['ano'] === (int)$f['ano']) {
        $selecionado = $f;
    }
}

$porCategoria = [];
$maiorValor = 0;
if ($selecionado) {
    $inicio = sprintf('%04d-%02d-01', $selecionado['ano'], $selecionado['mes']);
    $fim = date('Y-m-t', strtotime($inicio));
    $porCategoria = q('SELECT tipo, categoria, SUM(valor) AS total FROM lancamento
                       WHERE data BETWEEN ? AND ? GROUP BY tipo, categoria ORDER BY tipo DESC, total DESC', [$inicio, $fim])->fetchAll();
    foreach ($porCategoria as $c) {
        $maiorValor = max($maiorValor, (float)$c['total']);
    }
}

$historico = array_reverse(array_slice($fechamentos, 0, 12));
$maiorHistorico = 0;
foreach ($historico as $h) {
    $maiorHistorico = max($maiorHistorico, (float)$h['total_receitas'], (float)$h['total_despesas']);
}

$titulo = 'Transparência';
require __DIR__ . '/includes/site_header.php';
?>
<section class="secao">
    <div class="container">
        <h1>Prestação de contas</h1>
        <p class="introducao">Todo mês a tesouraria da paróquia fecha o caixa e publica aqui o total de entradas (dízimos, ofertas,
            doações, festas) e de saídas, separados por categoria. Depois de publicado, o mês não pode mais ser alterado.</p>

        <?php if (!$selecionado): ?>
            <div class="cartao"><p class="suave">Ainda não há meses publicados.</p></div>
        <?php else: ?>
            <form class="filtros" method="get">
                <label>Mês publicado
                    <select onchange="location='?'+this.value">
                        <?php foreach ($fechamentos as $f): ?>
                            <option value="mes=<?= $f['mes'] ?>&ano=<?= $f['ano'] ?>" <?= $f['id'] === $selecionado['id'] ? 'selected' : '' ?>><?= nome_mes((int)$f['mes']) ?> de <?= $f['ano'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
            </form>

            <div class="indicadores">
                <div class="indicador"><span>Entradas</span><strong class="positivo"><?= moeda($selecionado['total_receitas']) ?></strong></div>
                <div class="indicador"><span>Saídas</span><strong class="negativo"><?= moeda($selecionado['total_despesas']) ?></strong></div>
                <div class="indicador"><span>Resultado do mês</span><strong><?= moeda($selecionado['total_receitas'] - $selecionado['total_despesas']) ?></strong></div>
            </div>

            <div class="grade-2">
                <?php foreach (['receita' => 'Entradas por categoria', 'despesa' => 'Saídas por categoria'] as $tipo => $rotulo): ?>
                    <section class="cartao">
                        <h2><?= $rotulo ?></h2>
                        <?php $linhas = array_filter($porCategoria, fn($c) => $c['tipo'] === $tipo); ?>
                        <?php if (!$linhas): ?><p class="suave">Nenhum valor neste mês.</p><?php endif; ?>
                        <?php foreach ($linhas as $c): ?>
                            <div class="barra-linha">
                                <span class="barra-rotulo"><?= e($c['categoria']) ?></span>
                                <span class="barra-trilho"><span class="barra barra-<?= $tipo ?>" style="width: <?= $maiorValor ? round($c['total'] / $maiorValor * 100) : 0 ?>%"></span></span>
                                <span class="barra-valor"><?= moeda($c['total']) ?></span>
                            </div>
                        <?php endforeach; ?>
                    </section>
                <?php endforeach; ?>
            </div>

            <?php if (count($historico) > 1): ?>
                <section class="cartao">
                    <h2>Histórico mensal</h2>
                    <div class="historico" role="img" aria-label="Entradas e saídas dos meses publicados">
                        <?php foreach ($historico as $h): ?>
                            <div class="historico-mes">
                                <div class="historico-barras">
                                    <span class="barra-receita" style="height: <?= $maiorHistorico ? round($h['total_receitas'] / $maiorHistorico * 100) : 0 ?>%" title="Entradas: <?= moeda($h['total_receitas']) ?>"></span>
                                    <span class="barra-despesa" style="height: <?= $maiorHistorico ? round($h['total_despesas'] / $maiorHistorico * 100) : 0 ?>%" title="Saídas: <?= moeda($h['total_despesas']) ?>"></span>
                                </div>
                                <small><?= mb_substr(nome_mes((int)$h['mes']), 0, 3) ?>/<?= substr($h['ano'], 2) ?></small>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <p class="legenda"><span class="ponto ponto-receita"></span> Entradas <span class="ponto ponto-despesa"></span> Saídas</p>
                </section>
            <?php endif; ?>
            <p class="suave">Publicado em <?= data_br($selecionado['publicado_em']) ?>. Dúvidas sobre algum valor? Procure a secretaria paroquial.</p>
        <?php endif; ?>
    </div>
</section>
<?php require __DIR__ . '/includes/site_footer.php'; ?>
