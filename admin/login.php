<?php
require_once __DIR__ . '/../includes/bootstrap.php';

if (usuario_atual()) {
    redirect('index.php');
}

$erro = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    if (fazer_login(post('email'), $_POST['senha'] ?? '')) {
        redirect('index.php');
    }
    $erro = 'E-mail ou senha incorretos.';
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Entrar · Painel da Paróquia</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="tela-login">
<form method="post" class="cartao login-cartao">
    <a class="marca" href="../index.php"><?= e(cfg('paroquia_nome')) ?></a>
    <p class="suave">Painel da secretaria. Entre com seu e-mail e senha.</p>
    <?php if ($erro): ?><div class="alerta alerta-erro"><?= e($erro) ?></div><?php endif; ?>
    <?= csrf_field() ?>
    <label>E-mail
        <input type="email" name="email" required autofocus value="<?= e(post('email')) ?>">
    </label>
    <label>Senha
        <input type="password" name="senha" required>
    </label>
    <button class="botao botao-cheio" type="submit" style="width:100%">Entrar</button>
    <p class="dica" style="margin-top:1rem"><a href="../index.php">Voltar para o site</a></p>
</form>
</body>
</html>
