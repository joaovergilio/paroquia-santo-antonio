<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/crud.php';
exige_login(ACESSO_CONTEUDO);

crud([
    'tabela' => 'celebracao',
    'titulo' => 'Agenda e datas comemorativas',
    'singular' => 'item da agenda',
    'botao_novo' => 'Novo item na agenda',
    'ajuda' => '<p>Cadastre aqui tudo o que acontece <b>numa data específica</b>: missas especiais, datas comemorativas (Páscoa, Natal, Corpus Christi...), festas e encontros.</p>
                <p>Nas <b>datas comemorativas</b>, preencha o campo <b>História da data</b>: o site mostra o texto para os fiéis entenderem o que se celebra. Itens dos próximos 60 dias também aparecem no quadro de horários.</p>',
    'ordem' => 'data_hora DESC',
    'filtros' => ['tipo' => ['Tipo', TIPOS_CELEBRACAO]],
    'linha_classe' => fn($l) => $l['data_hora'] < agora() ? 'passado' : '',
    'campos' => [
        'titulo' => ['rotulo' => 'Título', 'obrigatorio' => true, 'max' => 150, 'placeholder' => 'Ex.: Missa de Natal'],
        'tipo' => ['rotulo' => 'Tipo', 'tipo' => 'select', 'opcoes' => TIPOS_CELEBRACAO, 'obrigatorio' => true, 'padrao' => 'celebracao'],
        'data_hora' => ['rotulo' => 'Data e hora', 'tipo' => 'datahora', 'obrigatorio' => true],
        'local' => ['rotulo' => 'Local', 'max' => 150, 'padrao' => 'Igreja Matriz'],
        'celebrante_id' => ['rotulo' => 'Celebrante', 'tipo' => 'select', 'opcoes' => clero_opcoes(), 'vazio' => '— não definido —'],
        'descricao' => ['rotulo' => 'Descrição / avisos', 'tipo' => 'textarea', 'linhas' => 3, 'placeholder' => 'O que as pessoas precisam saber: o que levar, programação...'],
        'historia' => ['rotulo' => 'História da data', 'tipo' => 'textarea', 'linhas' => 7,
            'dica' => 'Breve história do que se celebra. Deixe uma linha em branco para separar parágrafos.'],
        'imagem' => ['rotulo' => 'Imagem (opcional)', 'tipo' => 'imagem'],
    ],
    'lista' => [
        'data_hora' => 'Quando',
        'titulo' => 'Título',
        'tipo' => 'Tipo',
        'celebrante_id' => 'Celebrante',
        'historia' => ['História', fn($l) => $l['historia'] ? '<span class="etiqueta etiqueta-ativo">Sim</span>' : '<span class="suave">—</span>'],
    ],
]);
