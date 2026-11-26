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
        <div class="grade-2 grade-dizimo">
            <div>
                <p class="introducao">O dízimo mantém a igreja, as capelas, a catequese e as ações sociais da paróquia.</p>
                <?php if ($texto): ?>
                    <blockquote class="citacao">“<?= e($texto['texto']) ?>” <cite><?= e($texto['referencia']) ?></cite></blockquote>
                <?php endif; ?>

                <div class="forma-ajuda">
                    <h3>Pelo PIX</h3>
                    <p>Escolha a finalidade e o valor e leia o QR Code no app do banco.</p>
                </div>
                <div class="forma-ajuda">
                    <h3>Na igreja ou na secretaria</h3>
                    <p>Entregue seu envelope do dízimo nas missas ou diretamente na secretaria paroquial (<?= e(cfg('secretaria_horario')) ?>).</p>
                </div>
                <?php if (cfg('banco_info')): ?>
                    <div class="forma-ajuda">
                        <h3>Transferência bancária</h3>
                        <p><?= nl2br(e(cfg('banco_info'))) ?></p>
                    </div>
                <?php endif; ?>
                <div class="forma-ajuda">
                    <h3>Transparência</h3>
                    <p>Todas as entradas e saídas da paróquia são publicadas mês a mês na página de
                        <a href="transparencia.php">transparência</a>.</p>
                </div>
            </div>

            <div class="cartao pix-cartao" data-pix
                 data-chave="<?= e($pix['chave']) ?>" data-nome="<?= e($pix['nome']) ?>" data-cidade="<?= e($pix['cidade']) ?>">
                <h2>Contribuir pelo PIX</h2>
                <?php if ($pix['chave'] === ''): ?>
                    <div class="alerta alerta-aviso">A chave PIX da paróquia ainda não foi cadastrada.
                        A secretaria pode cadastrá-la no painel, em <b>Dados da paróquia</b>.</div>
                <?php else: ?>
                    <div class="seletor-finalidade" role="radiogroup" aria-label="Finalidade">
                        <?php foreach (FINALIDADES as $i => $f): ?>
                            <label class="chip-radio"><input type="radio" name="finalidade" value="<?= e($f) ?>" <?= $i === 0 ? 'checked' : '' ?>> <?= e($f) ?></label>
                        <?php endforeach; ?>
                    </div>
                    <label>Valor (R$)
                        <input inputmode="decimal" placeholder="Ex.: 50,00 (opcional)" data-pix-valor>
                    </label>
                    <div class="valores-rapidos">
                        <?php foreach ([20, 50, 100, 200] as $v): ?><button type="button" class="chip" data-pix-rapido="<?= $v ?>">R$ <?= $v ?></button><?php endforeach; ?>
                    </div>
                    <div class="pix-qr" data-pix-qr aria-label="QR Code PIX"></div>
                    <label>PIX copia e cola
                        <textarea rows="3" readonly data-pix-codigo></textarea>
                    </label>
                    <button type="button" class="botao botao-cheio" data-pix-copiar>Copiar código PIX</button>
                    <p class="dica pix-dados">Chave: <strong><?= e($pix['chave']) ?></strong><br>Favorecido: <?= e($pix['nome']) ?></p>
                    <p class="dica">Confira o nome do favorecido no app do banco antes de confirmar. O valor também pode ser digitado no app.</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="cartao formulario formulario-dizimista" id="cadastro">
            <?php if ($enviado): ?>
                <h2>Cadastro recebido</h2>
                <p>A secretaria vai confirmar seu cadastro e entregar o envelope do dízimo.</p>
            <?php else: ?>
                <h2>Quero ser dizimista</h2>
                <p class="suave">Os dados ficam só com a secretaria e não aparecem no site.</p>
                <?php if ($erro): ?><div class="alerta alerta-erro"><?= e($erro) ?></div><?php endif; ?>
                <form method="post" action="dizimo.php#cadastro">
                    <?= csrf_field() ?>
                    <div class="linha-campos">
                        <label>Nome completo *
                            <input name="nome" required maxlength="120" value="<?= e(post('nome')) ?>">
                        </label>
                        <label>Data de nascimento
                            <input type="date" name="nascimento" max="<?= date('Y-m-d') ?>" value="<?= e(post('nascimento')) ?>">
                        </label>
                    </div>
                    <div class="linha-campos">
                        <label>Telefone / WhatsApp
                            <input name="telefone" maxlength="20" inputmode="tel" value="<?= e(post('telefone')) ?>" data-mascara-telefone>
                        </label>
                        <label>E-mail
                            <input type="email" name="email" maxlength="150" value="<?= e(post('email')) ?>">
                        </label>
                    </div>
                    <div class="linha-campos">
                        <label>Endereço
                            <input name="endereco" maxlength="200" value="<?= e(post('endereco')) ?>">
                        </label>
                        <label>Comunidade / capela
                            <input name="comunidade" maxlength="120" value="<?= e(post('comunidade')) ?>">
                        </label>
                    </div>
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
