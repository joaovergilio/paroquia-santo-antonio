<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/crud.php';
exige_login(ACESSO_TODOS);

const STATUS_DIZIMISTA = ['novo' => 'Novo (aguardando confirmação)', 'ativo' => 'Ativo', 'inativo' => 'Inativo'];

crud([
    'tabela' => 'dizimista',
    'titulo' => 'Dizimistas',
    'singular' => 'dizimista',
    'botao_novo' => 'Novo dizimista',
    'ajuda' => '<p>Cadastro da Pastoral do Dízimo. Quem se cadastra pelo site chega como <b>Novo</b>: confira os dados, entregue o envelope/carnê e mude para <b>Ativo</b>.</p>
                <p>Estes dados são <b>privados</b> e nunca aparecem no site. Os valores recebidos são lançados em <b>Entradas e saídas</b> (categoria Dízimo).</p>',
    'ordem' => "FIELD(status, 'novo', 'ativo', 'inativo'), nome",
    'filtros' => ['status' => ['Situação', STATUS_DIZIMISTA], 'origem' => ['Origem', ['site' => 'Pelo site', 'secretaria' => 'Na secretaria']]],
    'linha_classe' => fn($l) => $l['status'] === 'inativo' ? 'inativo' : '',
    'campos' => [
        'nome' => ['rotulo' => 'Nome', 'obrigatorio' => true, 'max' => 120],
        'telefone' => ['rotulo' => 'Telefone', 'max' => 20, 'linha' => 1],
        'nascimento' => ['rotulo' => 'Nascimento', 'tipo' => 'data', 'linha' => 1, 'dica' => 'Útil para a missa dos aniversariantes.'],
        'email' => ['rotulo' => 'E-mail', 'tipo' => 'email', 'max' => 150],
        'endereco' => ['rotulo' => 'Endereço', 'max' => 200],
        'comunidade' => ['rotulo' => 'Comunidade / capela', 'max' => 120],
        'observacao' => ['rotulo' => 'Observação', 'max' => 255, 'placeholder' => 'Ex.: número do envelope'],
        'origem' => ['rotulo' => 'Origem do cadastro', 'tipo' => 'select', 'opcoes' => ['site' => 'Pelo site', 'secretaria' => 'Na secretaria'], 'obrigatorio' => true, 'padrao' => 'secretaria', 'linha' => 2],
        'status' => ['rotulo' => 'Situação', 'tipo' => 'select', 'opcoes' => STATUS_DIZIMISTA, 'obrigatorio' => true, 'padrao' => 'ativo', 'linha' => 2],
    ],
    'lista' => [
        'nome' => ['Nome', fn($l) => '<strong>' . e($l['nome']) . '</strong><br><small class="suave">' . e(trim(($l['telefone'] ?? '') . ' ' . ($l['email'] ?? ''))) . '</small>'],
        'comunidade' => 'Comunidade',
        'nascimento' => ['Aniversário', fn($l) => $l['nascimento'] ? date('d/m', strtotime($l['nascimento'])) : '<span class="suave">—</span>'],
        'status' => ['Situação', fn($l) => '<span class="etiqueta etiqueta-' . e($l['status']) . '">' . e(explode(' ', STATUS_DIZIMISTA[$l['status']])[0]) . '</span>'],
    ],
]);
