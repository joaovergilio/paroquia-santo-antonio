<?php
require_once __DIR__ . '/../includes/bootstrap.php';
exige_login(ACESSO_ADMIN);

$grupos = [
    'Identificação' => [
        'paroquia_nome' => ['Nome da paróquia', 'texto'],
        'igreja_nome' => ['Nome da igreja matriz', 'texto'],
        'cidade' => ['Cidade/UF', 'texto'],
        'diocese' => ['Diocese', 'texto'],
        'boas_vindas' => ['Mensagem de boas-vindas (página inicial)', 'textarea'],
    ],
    'Contato e secretaria' => [
        'endereco' => ['Endereço', 'texto'],
        'telefone' => ['Telefone', 'texto'],
        'whatsapp' => ['WhatsApp', 'texto'],
        'email' => ['E-mail', 'texto'],
        'secretaria_horario' => ['Horário de atendimento da secretaria', 'texto'],
    ],
    'Dízimo e doações' => [
        'pix_chave' => ['Chave PIX', 'texto', 'Telefone no formato +5547999999999, e-mail, CNPJ só com números ou chave aleatória.'],
        'pix_titular' => ['Nome do favorecido (como aparece no banco)', 'texto', 'Até 25 letras, sem acentos. Ex.: Paroquia Santo Antonio'],
        'pix_cidade' => ['Cidade do favorecido', 'texto', 'Até 15 letras. Ex.: Laurentino'],
        'banco_info' => ['Dados para transferência (opcional)', 'textarea', 'Banco, agência e conta, se quiser mostrar no site.'],
    ],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    foreach ($grupos as $campos) {
        foreach ($campos as $chave => $_) {
            $valor = mb_substr(post($chave), 0, 1000);
            q('INSERT INTO configuracao (chave, valor) VALUES (?, ?) ON DUPLICATE KEY UPDATE valor = VALUES(valor)', [$chave, $valor]);
        }
    }
    flash('sucesso', 'Dados da paróquia salvos. O site já foi atualizado.');
    redirect('configuracoes.php');
}

$titulo = 'Dados da paróquia';
require __DIR__ . '/../includes/admin_header.php';
?>
<div class="ajuda"><div>
    <p>Estes dados aparecem no cabeçalho, no rodapé e nas páginas do site. A <b>chave PIX</b> é usada para gerar o QR Code na página de dízimo:
        confira com cuidado, de preferência fazendo um PIX de teste de R$ 1,00.</p>
</div></div>
<form method="post" class="cartao formulario" style="max-width: 760px">
    <?= csrf_field() ?>
    <?php foreach ($grupos as $grupo => $campos): ?>
        <section class="config-grupo">
            <h2><?= e($grupo) ?></h2>
            <?php foreach ($campos as $chave => $def): ?>
                <label><?= e($def[0]) ?>
                    <?php if ($def[1] === 'textarea'): ?>
                        <textarea name="<?= $chave ?>" rows="3" maxlength="1000"><?= e(cfg($chave)) ?></textarea>
                    <?php else: ?>
                        <input name="<?= $chave ?>" maxlength="1000" value="<?= e(cfg($chave)) ?>">
                    <?php endif; ?>
                </label>
                <?php if (!empty($def[2])): ?><small class="dica-campo"><?= e($def[2]) ?></small><?php endif; ?>
            <?php endforeach; ?>
        </section>
    <?php endforeach; ?>
    <button class="botao botao-cheio">Salvar dados da paróquia</button>
</form>
<?php require __DIR__ . '/../includes/admin_footer.php'; ?>
