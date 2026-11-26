<?php
require_once __DIR__ . '/includes/bootstrap.php';

$movimentos = q('SELECT * FROM movimento WHERE ativo = 1 ORDER BY ordem IS NULL, ordem, nome')->fetchAll();
$abertos = array_filter($movimentos, fn($m) => $m['aceita_inscricao']);
$idsAbertos = array_map('intval', array_column($abertos, 'id'));

$enviado = null;
$erro = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $movId = (int)post('movimento_id');
    $nasc = post('nascimento');
    if (post('site') !== '') {
        $enviado = '';                       // robô: finge que deu certo
    } elseif (!in_array($movId, $idsAbertos, true)) {
        $erro = 'Escolha um movimento.';
    } elseif (post('nome') === '' || strlen(preg_replace('/\D/', '', post('telefone'))) < 10) {
        $erro = 'Preencha seu nome e um telefone com DDD.';
    } elseif (post('email') !== '' && !filter_var(post('email'), FILTER_VALIDATE_EMAIL)) {
        $erro = 'E-mail inválido.';
    } elseif ($nasc !== '' && (!DateTime::createFromFormat('Y-m-d', $nasc) || $nasc > date('Y-m-d'))) {
        $erro = 'Data de nascimento inválida.';
    } elseif (!isset($_POST['consentimento'])) {
        $erro = 'Para enviar, autorize a paróquia a usar seus dados para entrar em contato.';
    } else {
        q('INSERT INTO inscricao (movimento_id, nome, nascimento, telefone, email, comunidade, mensagem, status, criado_em)
           VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)', [
            $movId, mb_substr(post('nome'), 0, 120), $nasc ?: null, mb_substr(post('telefone'), 0, 20),
            post('email') ?: null, mb_substr(post('comunidade'), 0, 120) ?: null, mb_substr(post('mensagem'), 0, 1000) ?: null,
            'nova', agora(),
        ]);
        foreach ($movimentos as $m) {
            if ((int)$m['id'] === $movId) {
                $enviado = $m['nome'];
            }
        }
    }
}
$selecionado = (int)($_POST['movimento_id'] ?? $_GET['m'] ?? 0);

$titulo = 'Movimentos e pastorais';
require __DIR__ . '/includes/site_header.php';
?>
<section class="secao">
    <div class="container">
        <h1>Movimentos e pastorais</h1>
        <p class="introducao">Escolha um grupo e faça sua inscrição. A coordenação entra em contato.</p>

        <div class="grade-movimentos">
            <?php foreach ($movimentos as $m): ?>
                <article class="cartao movimento-card" id="mov-<?= $m['id'] ?>">
                    <?php if ($m['imagem']): ?><img src="uploads/<?= e($m['imagem']) ?>" alt="" loading="lazy"><?php endif; ?>
                    <h2><?= e($m['nome']) ?></h2>
                    <?php if ($m['publico']): ?><p class="publico">Para quem: <?= e($m['publico']) ?></p><?php endif; ?>
                    <?= paragrafos($m['descricao']) ?>
                    <dl class="dados-compactos">
                        <?php if ($m['encontros']): ?><dt>Encontros</dt><dd><?= e($m['encontros']) ?></dd><?php endif; ?>
                        <?php if ($m['coordenador']): ?><dt>Coordenação</dt><dd><?= e($m['coordenador']) ?></dd><?php endif; ?>
                    </dl>
                    <?php if ($m['aceita_inscricao']): ?>
                        <a class="botao" href="?m=<?= $m['id'] ?>#inscricao" data-escolher-movimento="<?= $m['id'] ?>">Quero participar</a>
                    <?php else: ?>
                        <p class="suave">Participação por convite ou indicação do pároco.</p>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
        </div>

        <div class="cartao formulario formulario-inscricao" id="inscricao">
            <?php if ($enviado !== null): ?>
                <h2>Inscrição recebida</h2>
                <p>A coordenação<?= $enviado ? ' de <strong>' . e($enviado) . '</strong>' : '' ?> vai entrar em contato pelo telefone informado.</p>
                <a href="movimentos.php" class="botao">Voltar aos movimentos</a>
            <?php elseif (!$abertos): ?>
                <p class="suave">No momento não há inscrições abertas.</p>
            <?php else: ?>
                <h2>Ficha de inscrição</h2>
                <?php if ($erro): ?><div class="alerta alerta-erro"><?= e($erro) ?></div><?php endif; ?>
                <form method="post" action="movimentos.php#inscricao">
                    <?= csrf_field() ?>
                    <label>Movimento ou pastoral *
                        <select name="movimento_id" required data-select-movimento>
                            <option value="">— escolha —</option>
                            <?php foreach ($abertos as $m): ?>
                                <option value="<?= $m['id'] ?>" <?= $selecionado === (int)$m['id'] ? 'selected' : '' ?>><?= e($m['nome']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label>Nome completo *
                        <input name="nome" required maxlength="120" value="<?= e(post('nome')) ?>">
                    </label>
                    <div class="linha-campos">
                        <label>Telefone / WhatsApp *
                            <input name="telefone" required maxlength="20" inputmode="tel" placeholder="(47) 99999-9999" value="<?= e(post('telefone')) ?>" data-mascara-telefone>
                        </label>
                        <label>Data de nascimento
                            <input type="date" name="nascimento" max="<?= date('Y-m-d') ?>" value="<?= e(post('nascimento')) ?>">
                        </label>
                    </div>
                    <div class="linha-campos">
                        <label>E-mail
                            <input type="email" name="email" maxlength="150" value="<?= e(post('email')) ?>">
                        </label>
                        <label>Comunidade / bairro
                            <input name="comunidade" maxlength="120" value="<?= e(post('comunidade')) ?>">
                        </label>
                    </div>
                    <label>Quer contar algo? (opcional)
                        <textarea name="mensagem" rows="3" maxlength="1000"><?= e(post('mensagem')) ?></textarea>
                    </label>
                    <label class="check"><input type="checkbox" name="consentimento" value="1" required <?= isset($_POST['consentimento']) ? 'checked' : '' ?>>
                        Autorizo a paróquia a usar estes dados apenas para entrar em contato sobre esta inscrição.</label>
                    <label class="oculto" aria-hidden="true">Site <input name="site" tabindex="-1" autocomplete="off"></label>
                    <button class="botao botao-cheio">Enviar inscrição</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</section>
<?php require __DIR__ . '/includes/site_footer.php'; ?>
