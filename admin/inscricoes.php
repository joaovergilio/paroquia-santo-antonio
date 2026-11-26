<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/crud.php';
exige_login(ACESSO_CONTEUDO);

const STATUS_INSCRICAO = ['nova' => 'Nova', 'contatada' => 'Já entramos em contato', 'confirmada' => 'Confirmada', 'cancelada' => 'Cancelada'];

$movimentos = [];
foreach (q('SELECT id, nome FROM movimento ORDER BY nome')->fetchAll() as $m) {
    $movimentos[$m['id']] = $m['nome'];
}

crud([
    'tabela' => 'inscricao',
    'titulo' => 'Inscrições nos movimentos',
    'singular' => 'inscrição',
    'botao_novo' => 'Nova inscrição',
    'ajuda' => '<p>Aqui chegam as inscrições feitas pelo site. Entre em contato com a pessoa e depois clique em <b>Editar</b> para mudar a situação para <b>Já entramos em contato</b> ou <b>Confirmada</b>.</p>
                <p>Use os filtros para ver só um movimento. Inscrições feitas na secretaria também podem ser cadastradas aqui.</p>',
    'ordem' => "FIELD(status, 'nova', 'contatada', 'confirmada', 'cancelada'), criado_em DESC",
    'filtros' => ['movimento_id' => ['Movimento', $movimentos], 'status' => ['Situação', STATUS_INSCRICAO]],
    'linha_classe' => fn($l) => $l['status'] === 'cancelada' ? 'inativo' : '',
    'campos' => [
        'movimento_id' => ['rotulo' => 'Movimento', 'tipo' => 'select', 'opcoes' => $movimentos, 'obrigatorio' => true, 'vazio' => '— escolha —'],
        'nome' => ['rotulo' => 'Nome', 'obrigatorio' => true, 'max' => 120],
        'telefone' => ['rotulo' => 'Telefone', 'obrigatorio' => true, 'max' => 20, 'linha' => 1],
        'nascimento' => ['rotulo' => 'Nascimento', 'tipo' => 'data', 'linha' => 1],
        'email' => ['rotulo' => 'E-mail', 'tipo' => 'email', 'max' => 150],
        'comunidade' => ['rotulo' => 'Comunidade / bairro', 'max' => 120],
        'mensagem' => ['rotulo' => 'Mensagem / anotações', 'tipo' => 'textarea', 'linhas' => 3],
        'status' => ['rotulo' => 'Situação', 'tipo' => 'select', 'opcoes' => STATUS_INSCRICAO, 'obrigatorio' => true, 'padrao' => 'nova'],
    ],
    'lista' => [
        'criado_em' => ['Data', fn($l) => data_br($l['criado_em'])],
        'nome' => ['Nome', fn($l) => '<strong>' . e($l['nome']) . '</strong><br><small class="suave">' . e($l['telefone'])
            . ($l['email'] ? ' · ' . e($l['email']) : '') . '</small>'],
        'movimento_id' => 'Movimento',
        'status' => ['Situação', fn($l) => '<span class="etiqueta etiqueta-' . e($l['status']) . '">' . e(STATUS_INSCRICAO[$l['status']]) . '</span>'],
    ],
]);
