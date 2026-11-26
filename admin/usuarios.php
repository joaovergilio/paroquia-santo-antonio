<?php
require_once __DIR__ . '/../includes/bootstrap.php';
exige_login(ACESSO_ADMIN);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $id = (int)post('id');

    // Usuários são desativados, nunca excluídos (mantém o histórico do financeiro)
    if (post('acao') === 'alternar') {
        if ($id === usuario_atual()['id']) {
            flash('erro', 'Você não pode desativar o próprio usuário.');
        } else {
            q('UPDATE usuario SET ativo = 1 - ativo WHERE id = ?', [$id]);
            flash('sucesso', 'Situação do usuário alterada.');
        }
        redirect('usuarios.php');
    }

    $nome = post('nome');
    $email = strtolower(post('email'));
    $perfil = array_key_exists(post('perfil'), PERFIS) ? post('perfil') : 'secretaria';
    $senha = $_POST['senha'] ?? '';

    if ($nome === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        flash('erro', 'Informe nome e um e-mail válido.');
    } elseif (q('SELECT 1 FROM usuario WHERE email = ? AND id <> ?', [$email, $id])->fetchColumn()) {
        flash('erro', 'Já existe um usuário com esse e-mail.');
    } elseif ((!$id || $senha !== '') && strlen($senha) < 6) {
        flash('erro', 'A senha precisa ter pelo menos 6 caracteres.');
    } elseif ($id === usuario_atual()['id'] && $perfil !== 'admin') {
        flash('erro', 'Você não pode tirar o perfil de administrador de si mesmo.');
    } else {
        if ($id) {
            q('UPDATE usuario SET nome = ?, email = ?, perfil = ? WHERE id = ?', [$nome, $email, $perfil, $id]);
            if ($senha !== '') {
                q('UPDATE usuario SET senha_hash = ? WHERE id = ?', [password_hash($senha, PASSWORD_DEFAULT), $id]);
            }
            flash('sucesso', 'Usuário atualizado.');
        } else {
            q('INSERT INTO usuario (nome, email, senha_hash, perfil, ativo, criado_em) VALUES (?, ?, ?, ?, 1, ?)',
                [$nome, $email, password_hash($senha, PASSWORD_DEFAULT), $perfil, agora()]);
            flash('sucesso', 'Usuário criado.');
        }
        redirect('usuarios.php');
    }
    redirect('usuarios.php' . ($id ? "?editar=$id" : ''));
}

$usuarios = q('SELECT * FROM usuario ORDER BY ativo DESC, nome')->fetchAll();
$editar = isset($_GET['editar']) ? q('SELECT * FROM usuario WHERE id = ?', [(int)$_GET['editar']])->fetch() : null;

$titulo = 'Usuários do painel';
require __DIR__ . '/../includes/admin_header.php';
?>
<div class="ajuda"><div>
    <p><b>Secretaria</b>: horários, agenda, padres, santos, movimentos, inscrições e dizimistas.
        <b>Tesouraria</b>: dizimistas, entradas e saídas e transparência.
        <b>Administrador</b>: tudo, incluindo dados da paróquia e usuários.</p>
</div></div>
<div class="grade-2 grade-larga">
    <section class="cartao rolagem">
        <table class="tabela">
            <thead><tr><th>Nome</th><th>E-mail</th><th>Perfil</th><th>Situação</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($usuarios as $u): ?>
                <tr class="<?= $u['ativo'] ? '' : 'inativo' ?>">
                    <td><?= e($u['nome']) ?></td>
                    <td><?= e($u['email']) ?></td>
                    <td><?= e(PERFIS[$u['perfil']]) ?></td>
                    <td><?= $u['ativo'] ? 'Ativo' : 'Inativo' ?></td>
                    <td class="acoes">
                        <a class="botao botao-pequeno" href="?editar=<?= $u['id'] ?>">Editar</a>
                        <?php if ((int)$u['id'] !== usuario_atual()['id']): ?>
                            <form method="post"><?= csrf_field() ?><input type="hidden" name="acao" value="alternar"><input type="hidden" name="id" value="<?= $u['id'] ?>">
                                <button class="link-perigo"><?= $u['ativo'] ? 'Desativar' : 'Reativar' ?></button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </section>

    <form method="post" class="cartao formulario <?= $editar ? 'em-foco' : '' ?>">
        <h2><?= $editar ? 'Editar usuário' : 'Novo usuário' ?></h2>
        <?= csrf_field() ?>
        <input type="hidden" name="id" value="<?= (int)($editar['id'] ?? 0) ?>">
        <label>Nome
            <input name="nome" required maxlength="100" value="<?= e($editar['nome'] ?? '') ?>">
        </label>
        <label>E-mail
            <input type="email" name="email" required maxlength="150" value="<?= e($editar['email'] ?? '') ?>">
        </label>
        <label>Perfil
            <select name="perfil">
                <?php foreach (PERFIS as $chave => $rotulo): ?>
                    <option value="<?= $chave ?>" <?= ($editar['perfil'] ?? 'secretaria') === $chave ? 'selected' : '' ?>><?= e($rotulo) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label><?= $editar ? 'Nova senha (deixe vazio para manter)' : 'Senha' ?>
            <input type="password" name="senha" minlength="6" <?= $editar ? '' : 'required' ?> autocomplete="new-password">
        </label>
        <button class="botao botao-cheio">Salvar</button>
        <?php if ($editar): ?><a href="usuarios.php" class="link-secundario">Cancelar</a><?php endif; ?>
    </form>
</div>
<?php require __DIR__ . '/../includes/admin_footer.php'; ?>
