<?php
require_once __DIR__ . '/../includes/bootstrap.php';
exige_login();

$inscricoesNovas = (int)q("SELECT COUNT(*) FROM inscricao WHERE status = 'nova'")->fetchColumn();
$dizimistasNovos = (int)q("SELECT COUNT(*) FROM dizimista WHERE status = 'novo'")->fetchColumn();
$dizimistasAtivos = (int)q("SELECT COUNT(*) FROM dizimista WHERE status = 'ativo'")->fetchColumn();
$proximas = q('SELECT a.*, c.titulo AS c_titulo, c.nome AS c_nome FROM celebracao a LEFT JOIN clero c ON c.id = a.celebrante_id
               WHERE a.data_hora >= ? ORDER BY a.data_hora LIMIT 6', [agora()])->fetchAll();
$semCelebrante = (int)q("SELECT COUNT(*) FROM horario WHERE ativo = 1 AND tipo = 'Missa' AND celebrante_id IS NULL")->fetchColumn();

$saldoMes = null;
if (pode(ACESSO_FINANCEIRO)) {
    $saldoMes = (float)q("SELECT COALESCE(SUM(CASE WHEN tipo = 'receita' THEN valor ELSE -valor END), 0)
                          FROM lancamento WHERE data BETWEEN ? AND ?", [date('Y-m-01'), date('Y-m-t')])->fetchColumn();
}

$titulo = 'Início';
require __DIR__ . '/../includes/admin_header.php';
?>
<?php if (pode(ACESSO_ADMIN) && cfg('pix_chave') === ''): ?>
    <div class="alerta alerta-aviso">A chave PIX ainda não foi cadastrada. Sem ela a página de dízimo não mostra o QR Code.
        <a href="configuracoes.php">Cadastrar</a></div>
<?php endif; ?>
<?php if (pode(ACESSO_CONTEUDO) && $semCelebrante): ?>
    <div class="alerta alerta-aviso"><?= $semCelebrante ?> horário(s) de missa sem celebrante. <a href="horarios.php">Ver horários</a></div>
<?php endif; ?>

<div class="indicadores">
    <?php if (pode(ACESSO_CONTEUDO)): ?>
        <a class="indicador" href="inscricoes.php?status=nova"><span>Inscrições novas</span><strong><?= $inscricoesNovas ?></strong></a>
    <?php endif; ?>
    <a class="indicador" href="dizimistas.php?status=novo"><span>Dizimistas a confirmar</span><strong><?= $dizimistasNovos ?></strong></a>
    <a class="indicador" href="dizimistas.php?status=ativo"><span>Dizimistas ativos</span><strong><?= $dizimistasAtivos ?></strong></a>
    <?php if ($saldoMes !== null): ?>
        <a class="indicador" href="financeiro.php"><span>Saldo de <?= nome_mes((int)date('n')) ?></span><strong class="<?= $saldoMes < 0 ? 'negativo' : 'positivo' ?>"><?= moeda($saldoMes) ?></strong></a>
    <?php endif; ?>
</div>

<div class="grade-2">
    <section class="cartao">
        <h2>Próximos na agenda</h2>
        <?php if (!$proximas): ?>
            <p class="suave">Nada agendado.</p>
        <?php else: ?>
            <ul class="lista-simples">
                <?php foreach ($proximas as $a): ?>
                    <li>
                        <span><?= e($a['titulo']) ?><br><small class="suave"><?= e(nome_clero(['titulo' => $a['c_titulo'], 'nome' => $a['c_nome']]) ?: 'Sem celebrante') ?></small></span>
                        <span class="suave"><?= data_br($a['data_hora'], true) ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
        <?php if (pode(ACESSO_CONTEUDO)): ?><a href="agenda.php">Abrir agenda</a><?php endif; ?>
    </section>
    <section class="cartao">
        <h2>Como usar</h2>
        <p>Escolha no menu o que quer mudar. Em cada tela, clique em <b>Editar</b> num item ou em <b>Novo</b> para criar,
            preencha e salve. A mudança aparece no site na hora.</p>
        <p>Para tirar algo do site sem apagar, desmarque <b>Ativo</b>.</p>
    </section>
</div>
<?php require __DIR__ . '/../includes/admin_footer.php'; ?>
