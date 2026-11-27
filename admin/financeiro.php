<?php
require_once __DIR__ . '/../includes/bootstrap.php';
exige_login(ACESSO_FINANCEIRO);

const CATEGORIAS_RECEITA = ['Dízimo', 'Ofertas das missas', 'Doações', 'Festas e eventos', 'Intenções de missa', 'Taxas de sacramentos', 'Outras entradas'];
const CATEGORIAS_DESPESA = ['Água, luz e telefone', 'Manutenção e obras', 'Salários e encargos', 'Material litúrgico', 'Ação social', 'Contribuição à Diocese', 'Eventos', 'Outras saídas'];

$mes = min(12, max(1, (int)($_GET['mes'] ?? date('n'))));
$ano = (int)($_GET['ano'] ?? date('Y'));
$voltar = "financeiro.php?mes=$mes&ano=$ano";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $acao = post('acao');

    if ($acao === 'lancar') {
        $tipo = post('tipo') === 'despesa' ? 'despesa' : 'receita';
        $valor = parse_valor(post('valor'));
        $data = post('data');
        $descricao = post('descricao');
        $categoria = post('categoria');
        $categoriasValidas = $tipo === 'receita' ? CATEGORIAS_RECEITA : CATEGORIAS_DESPESA;

        if ($valor <= 0 || !DateTime::createFromFormat('Y-m-d', $data) || $descricao === '' || !in_array($categoria, $categoriasValidas, true)) {
            flash('erro', 'Preencha tipo, categoria, valor, data e descrição.');
        } elseif (mes_publicado($data)) {
            flash('erro', 'Esse mês já foi publicado na transparência e não aceita novos lançamentos.');
        } else {
            try {
                $comprovante = upload_arquivo('comprovante', ['jpg', 'jpeg', 'png', 'pdf']);
                q('INSERT INTO lancamento (tipo, categoria, valor, descricao, data, comprovante, usuario_id, criado_em)
                   VALUES (?, ?, ?, ?, ?, ?, ?, ?)',
                    [$tipo, $categoria, $valor, mb_substr($descricao, 0, 255), $data, $comprovante, usuario_atual()['id'], agora()]);
                flash('sucesso', ($tipo === 'receita' ? 'Entrada' : 'Saída') . ' de ' . moeda($valor) . ' lançada.');
                $voltar = 'financeiro.php?mes=' . (int)date('n', strtotime($data)) . '&ano=' . (int)date('Y', strtotime($data));
            } catch (RuntimeException $ex) {
                flash('erro', $ex->getMessage());
            }
        }
    }

    if ($acao === 'excluir') {
        $l = q('SELECT * FROM lancamento WHERE id = ?', [(int)post('id')])->fetch();
        if (!$l) {
            flash('erro', 'Lançamento não encontrado.');
        } elseif (mes_publicado($l['data'])) {
            flash('erro', 'Mês publicado: use o estorno em vez de excluir.');
        } elseif (q('SELECT 1 FROM lancamento WHERE estorno_de = ?', [$l['id']])->fetchColumn()) {
            flash('erro', 'Este lançamento já tem um estorno e não pode ser excluído.');
        } else {
            q('DELETE FROM lancamento WHERE id = ?', [$l['id']]);
            apagar_upload($l['comprovante']);
            flash('sucesso', 'Lançamento excluído.');
        }
    }

    // Em mês publicado, a correção é feita com um lançamento inverso no mês atual
    if ($acao === 'estornar') {
        $l = q('SELECT * FROM lancamento WHERE id = ?', [(int)post('id')])->fetch();
        $jaEstornado = $l ? q('SELECT 1 FROM lancamento WHERE estorno_de = ?', [$l['id']])->fetchColumn() : true;
        if (!$l || $jaEstornado || $l['estorno_de']) {
            flash('erro', 'Lançamento inexistente ou já estornado.');
        } elseif (mes_publicado(date('Y-m-d'))) {
            flash('erro', 'O mês atual também está publicado. Não é possível estornar agora.');
        } else {
            q('INSERT INTO lancamento (tipo, categoria, valor, descricao, data, estorno_de, usuario_id, criado_em) VALUES (?, ?, ?, ?, ?, ?, ?, ?)', [
                $l['tipo'] === 'receita' ? 'despesa' : 'receita', 'Estorno', $l['valor'],
                mb_substr('Estorno do lançamento #' . $l['id'] . ' de ' . data_br($l['data']) . ': ' . $l['descricao'], 0, 255),
                date('Y-m-d'), $l['id'], usuario_atual()['id'], agora(),
            ]);
            flash('sucesso', 'Estorno lançado em ' . data_br(date('Y-m-d')) . '.');
        }
    }
    redirect($voltar);
}

$inicio = sprintf('%04d-%02d-01', $ano, $mes);
$fim = date('Y-m-t', strtotime($inicio));
$publicado = mes_publicado($inicio);

$lancamentos = q('SELECT l.*, u.nome AS usuario,
                  (SELECT COUNT(*) FROM lancamento e WHERE e.estorno_de = l.id) AS estornado
                  FROM lancamento l JOIN usuario u ON u.id = l.usuario_id
                  WHERE l.data BETWEEN ? AND ? ORDER BY l.data DESC, l.id DESC', [$inicio, $fim])->fetchAll();

$receitas = array_sum(array_map(fn($l) => $l['tipo'] === 'receita' ? $l['valor'] : 0, $lancamentos));
$despesas = array_sum(array_map(fn($l) => $l['tipo'] === 'despesa' ? $l['valor'] : 0, $lancamentos));

$titulo = 'Entradas e saídas';
require __DIR__ . '/../includes/admin_header.php';
?>
<div class="ajuda"><div>
    <p>Registre aqui todo dinheiro que <b>entra</b> (dízimos, ofertas, doações, festas) e que <b>sai</b> (contas, obras, ação social). Anexe o comprovante quando tiver.</p>
    <p>No fim do mês, vá em <a href="fechamento.php">Fechamento do mês</a> para mostrar os totais no site.</p>
</div></div>

<form class="filtros" method="get">
    <select name="mes" aria-label="Mês"><?php for ($m = 1; $m <= 12; $m++): ?><option value="<?= $m ?>" <?= $m === $mes ? 'selected' : '' ?>><?= nome_mes($m) ?></option><?php endfor; ?></select>
    <select name="ano" aria-label="Ano"><?php for ($a = (int)date('Y') + 1; $a >= (int)date('Y') - 4; $a--): ?><option <?= $a === $ano ? 'selected' : '' ?>><?= $a ?></option><?php endfor; ?></select>
    <button class="botao">Ver mês</button>
    <?php if ($publicado): ?><span class="etiqueta etiqueta-publicado">Publicado na transparência</span><?php endif; ?>
</form>

<div class="indicadores">
    <div class="indicador"><span>Entradas</span><strong class="positivo"><?= moeda($receitas) ?></strong></div>
    <div class="indicador"><span>Saídas</span><strong class="negativo"><?= moeda($despesas) ?></strong></div>
    <div class="indicador"><span>Saldo</span><strong class="<?= $receitas - $despesas < 0 ? 'negativo' : 'positivo' ?>"><?= moeda($receitas - $despesas) ?></strong></div>
</div>

<div class="grade-2 grade-larga">
    <section class="cartao">
        <h2><?= nome_mes($mes) ?> de <?= $ano ?></h2>
        <?php if (!$lancamentos): ?>
            <p class="suave">Nenhum lançamento neste mês.</p>
        <?php else: ?>
            <div class="rolagem">
                <table class="tabela">
                    <thead><tr><th>Data</th><th>Descrição</th><th>Categoria</th><th class="num">Valor</th><th></th></tr></thead>
                    <tbody>
                    <?php foreach ($lancamentos as $l): ?>
                        <tr>
                            <td><?= data_br($l['data']) ?></td>
                            <td><?= e($l['descricao']) ?>
                                <br><small class="suave">por <?= e($l['usuario']) ?><?php if ($l['comprovante']): ?> · <a href="../uploads/<?= e($l['comprovante']) ?>" target="_blank">comprovante</a><?php endif; ?></small></td>
                            <td><?= e($l['categoria']) ?></td>
                            <td class="num <?= $l['tipo'] === 'receita' ? 'positivo' : 'negativo' ?>"><?= $l['tipo'] === 'receita' ? '+' : '−' ?> <?= moeda($l['valor']) ?></td>
                            <td class="acoes">
                                <?php if (!$publicado && !$l['estornado']): ?>
                                    <form method="post" onsubmit="return confirm('Excluir este lançamento?')">
                                        <?= csrf_field() ?><input type="hidden" name="acao" value="excluir"><input type="hidden" name="id" value="<?= $l['id'] ?>">
                                        <button class="link-perigo">Excluir</button>
                                    </form>
                                <?php elseif ($publicado && !$l['estornado'] && !$l['estorno_de']): ?>
                                    <form method="post" onsubmit="return confirm('Lançar um estorno deste valor no mês atual?')">
                                        <?= csrf_field() ?><input type="hidden" name="acao" value="estornar"><input type="hidden" name="id" value="<?= $l['id'] ?>">
                                        <button class="link-perigo">Estornar</button>
                                    </form>
                                <?php elseif ($l['estornado']): ?>
                                    <span class="suave">Estornado</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>

    <form method="post" enctype="multipart/form-data" class="cartao formulario" data-lancamento>
        <h2>Novo lançamento</h2>
        <?= csrf_field() ?>
        <input type="hidden" name="acao" value="lancar">
        <div class="seletor-tipo">
            <label><input type="radio" name="tipo" value="receita" checked> Entrada</label>
            <label><input type="radio" name="tipo" value="despesa"> Saída</label>
        </div>
        <label>Categoria
            <select name="categoria" required data-categorias
                    data-receita="<?= e(json_encode(CATEGORIAS_RECEITA)) ?>"
                    data-despesa="<?= e(json_encode(CATEGORIAS_DESPESA)) ?>">
                <?php foreach (CATEGORIAS_RECEITA as $c): ?><option><?= e($c) ?></option><?php endforeach; ?>
            </select>
        </label>
        <label>Valor (R$)
            <input name="valor" inputmode="decimal" required placeholder="0,00">
        </label>
        <label>Data
            <input type="date" name="data" required value="<?= date('Y-m-d') ?>">
        </label>
        <label>Descrição
            <input name="descricao" maxlength="255" required placeholder="Ex.: Dízimos do domingo 29/11">
        </label>
        <label>Comprovante (opcional)
            <input type="file" name="comprovante" accept=".jpg,.jpeg,.png,.pdf">
        </label>
        <button class="botao botao-cheio">Lançar</button>
    </form>
</div>
<?php require __DIR__ . '/../includes/admin_footer.php'; ?>
