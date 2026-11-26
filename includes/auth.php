<?php
// Autenticação e controle de acesso por perfil

const PERFIS = [
    'admin' => 'Administrador',
    'secretaria' => 'Secretaria',
    'tesouraria' => 'Tesouraria',
];

const ACESSO_TODOS = ['admin', 'secretaria', 'tesouraria'];
const ACESSO_CONTEUDO = ['admin', 'secretaria'];    // horários, agenda, santos, padres, movimentos
const ACESSO_FINANCEIRO = ['admin', 'tesouraria'];  // financeiro e transparência
const ACESSO_ADMIN = ['admin'];                     // usuários e dados da paróquia

function usuario_atual(): ?array
{
    return $_SESSION['usuario'] ?? null;
}

function pode(array $perfis): bool
{
    $u = usuario_atual();
    return $u !== null && in_array($u['perfil'], $perfis, true);
}

function fazer_login(string $email, string $senha): bool
{
    $u = q('SELECT * FROM usuario WHERE email = ? AND ativo = 1', [strtolower($email)])->fetch();
    if (!$u || !password_verify($senha, $u['senha_hash'])) {
        return false;
    }
    session_regenerate_id(true);
    $_SESSION['usuario'] = ['id' => (int)$u['id'], 'nome' => $u['nome'], 'email' => $u['email'], 'perfil' => $u['perfil']];
    return true;
}

// Use no topo de cada página do painel
function exige_login(array $perfis = ACESSO_TODOS): void
{
    $u = usuario_atual();
    if (!$u) {
        redirect('login.php');
    }
    // Se o usuário foi desativado ou mudou de perfil, a sessão é atualizada
    $atual = q('SELECT nome, perfil, ativo FROM usuario WHERE id = ?', [$u['id']])->fetch();
    if (!$atual || !$atual['ativo']) {
        session_destroy();
        redirect('login.php');
    }
    $_SESSION['usuario']['perfil'] = $atual['perfil'];
    $_SESSION['usuario']['nome'] = $atual['nome'];

    if (!pode($perfis)) {
        http_response_code(403);
        $titulo = 'Acesso negado';
        require __DIR__ . '/admin_header.php';
        echo '<div class="cartao"><p>Seu perfil não tem permissão para acessar esta página.</p></div>';
        require __DIR__ . '/admin_footer.php';
        exit;
    }
}
