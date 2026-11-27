<?php
require_once __DIR__ . '/includes/bootstrap.php';

const FINALIDADES = ['Dízimo', 'Oferta', 'Doação para obras da igreja', 'Doação para a festa do padroeiro', 'Doação para ações sociais'];

$enviado = false;
$erro = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $nasc = post('nascimento');
    if (post('site') !== '') {
        $enviado = true;
    } elseif (post('nome') === '' || (post('telefone') === '' && post('email') === '')) {
        $erro = 'Preencha seu nome e um telefone ou e-mail.';
    } elseif (post('email') !== '' && !filter_var(post('email'), FILTER_VALIDATE_EMAIL)) {
        $erro = 'E-mail inválido.';
    } elseif ($nasc !== '' && (!DateTime::createFromFormat('Y-m-d', $nasc) || $nasc > date('Y-m-d'))) {
        $erro = 'Data de nascimento inválida.';
    } elseif (!isset($_POST['consentimento'])) {
        $erro = 'Para enviar, autorize a paróquia a guardar seus dados.';
    } else {
        q("INSERT INTO dizimista (nome, telefone, email, endereco, comunidade, nascimento, origem, status, criado_em)
           VALUES (?, ?, ?, ?, ?, ?, 'site', 'novo', ?)", [
            mb_substr(post('nome'), 0, 120), mb_substr(post('telefone'), 0, 20) ?: null, post('email') ?: null,
            mb_substr(post('endereco'), 0, 200) ?: null, mb_substr(post('comunidade'), 0, 120) ?: null, $nasc ?: null, agora(),
        ]);
        $enviado = true;
    }
}

$pix = [
    'chave' => cfg('pix_chave'),
    'nome' => cfg('pix_titular'),
    'cidade' => cfg('pix_cidade'),
];
$texto = versiculo(54, 9, 7); // 2 Coríntios 9,7

$titulo = 'Dízimo e doações';
require __DIR__ . '/includes/site_header.php';
?>
<section class="secao">
    <div class="container">
        <h1>Dízimo e doações</h1>
        <p class="introducao">O dízimo mantém a igreja, as capelas, a catequese e as ações sociais da paróquia.</p>

        <div class="dizimo-grade">
            <div class="dizimo-texto">
                <?php if ($texto): ?>
                    <blockquote class="citacao"><?= e($texto['texto']) ?> <cite><?= e($texto['referencia']) ?></cite></blockquote>
                <?php endif; ?>

                <h2 class="titulo-bloco">Outras formas de contribuir</h2>
                <ul class="formas">
                    <li>
                        <strong>Na igreja ou na secretaria</strong>
                        <p>Entregue o envelope do dízimo nas missas ou na secretaria paroquial, <?= e(mb_strtolower(mb_substr(cfg('secretaria_horario'), 0, 1)) . mb_substr(cfg('secretaria_horario'), 1)) ?>.</p>
                    </li>
                    <?php if (cfg('banco_info')): ?>
                        <li>
                            <strong>Transferência bancária</strong>
                            <p><?= nl2br(e(cfg('banco_info'))) ?></p>
                        </li>
                    <?php endif; ?>
                    <li>
                        <strong>Para onde vai o dinheiro</strong>
                        <p>As entradas e saídas são publicadas todo mês na página de <a href="transparencia.php">transparência</a>.</p>
                    </li>
                </ul>
            </div>

            <aside class="cartao pix-cartao" data-pix
                   data-chave="<?= e($pix['chave']) ?>" data-nome="<?= e($pix['nome']) ?>" data-cidade="<?= e($pix['cidade']) ?>">
                <h2>Contribuir pelo PIX</h2>
                <?php if ($pix['chave'] === ''): ?>
                    <p class="suave">O PIX da paróquia estará disponível aqui em breve. Enquanto isso, contribua na igreja ou na secretaria.</p>
                <?php else: ?>
                    <p class="pix-passo">1. Escolha a finalidade</p>
                    <div class="seletor-finalidade" role="radiogroup" aria-label="Finalidade">
                        <?php foreach (FINALIDADES as $i => $f): ?>
                            <label class="chip-radio"><input type="radio" name="finalidade" value="<?= e($f) ?>" <?= $i === 0 ? 'checked' : '' ?>> <?= e($f) ?></label>
                        <?php endforeach; ?>
                    </div>
                    <p class="pix-passo">2. Informe o valor <span class="suave">(opcional)</span></p>
                    <input inputmode="decimal" placeholder="R$ 0,00" aria-label="Valor em reais" data-pix-valor>
                    <div class="valores-rapidos">
                        <?php foreach ([20, 50, 100, 200] as $v): ?><button type="button" class="chip" data-pix-rapido="<?= $v ?>">R$ <?= $v ?></button><?php endforeach; ?>
                    </div>
                    <p class="pix-passo">3. Leia o QR Code no app do banco</p>
                    <div class="pix-qr" data-pix-qr aria-label="QR Code PIX"></div>
                    <label>Ou copie o código
                        <textarea rows="3" readonly data-pix-codigo></textarea>
                    </label>
                    <button type="button" class="botao botao-cheio botao-largo" data-pix-copiar>Copiar código PIX</button>
                    <p class="pix-dados">Favorecido: <strong><?= e($pix['nome']) ?></strong> · Chave: <?= e($pix['chave']) ?><br>
                        Confira o nome do favorecido no app antes de confirmar.</p>
                <?php endif; ?>
            </aside>
        </div>
    </div>
</section>

<section class="secao secao-clara" id="cadastro">
    <div class="container dizimista-grade">
        <div>
            <h2>Quero ser dizimista</h2>
            <p>Preencha o cadastro e a secretaria entra em contato para confirmar e entregar o seu envelope do dízimo.</p>
            <p class="suave">Seus dados ficam só com a secretaria e nunca aparecem no site.</p>
        </div>
        <div class="cartao formulario">
            <?php if ($enviado): ?>
                <h3>Cadastro recebido</h3>
                <p>Obrigado! A secretaria vai confirmar seu cadastro e entregar o envelope do dízimo.</p>
            <?php else: ?>
                <?php if ($erro): ?><div class="alerta alerta-erro"><?= e($erro) ?></div><?php endif; ?>
                <form method="post" action="dizimo.php#cadastro">
                    <?= csrf_field() ?>
                    <label>Nome completo <span class="obrigatorio">*</span>
                        <input name="nome" required maxlength="120" value="<?= e(post('nome')) ?>">
                    </label>
                    <div class="linha-campos">
                        <label>Telefone / WhatsApp
                            <input name="telefone" maxlength="20" inputmode="tel" value="<?= e(post('telefone')) ?>" data-mascara-telefone>
                        </label>
                        <label>E-mail
                            <input type="email" name="email" maxlength="150" value="<?= e(post('email')) ?>">
                        </label>
                    </div>
                    <div class="linha-campos">
                        <label>Data de nascimento
                            <input type="date" name="nascimento" max="<?= date('Y-m-d') ?>" value="<?= e(post('nascimento')) ?>">
                        </label>
                        <label>Comunidade / capela
                            <input name="comunidade" maxlength="120" value="<?= e(post('comunidade')) ?>">
                        </label>
                    </div>
                    <label>Endereço
                        <input name="endereco" maxlength="200" value="<?= e(post('endereco')) ?>">
                    </label>
                    <p class="dica">Informe pelo menos um telefone ou e-mail.</p>
                    <label class="check"><input type="checkbox" name="consentimento" value="1" required <?= isset($_POST['consentimento']) ? 'checked' : '' ?>>
                        Autorizo a paróquia a guardar estes dados para o cadastro de dizimista.</label>
                    <label class="oculto" aria-hidden="true">Site <input name="site" tabindex="-1" autocomplete="off"></label>
                    <button class="botao botao-cheio">Enviar cadastro</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</section>
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<?php require __DIR__ . '/includes/site_footer.php'; ?>
