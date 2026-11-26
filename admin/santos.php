<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/crud.php';
exige_login(ACESSO_CONTEUDO);

$meses = [];
for ($m = 1; $m <= 12; $m++) {
    $meses[$m] = nome_mes($m);
}

crud([
    'tabela' => 'santo',
    'titulo' => 'Santos',
    'singular' => 'santo',
    'botao_novo' => 'Novo santo',
    'ajuda' => '<p>Os santos aparecem na página <a href="../santos.php" target="_blank">Santos</a>. No dia da festa, o santo aparece em destaque na página inicial como <b>Santo do dia</b>.</p>',
    'ordem' => 'mes, dia, nome',
    'filtros' => ['mes' => ['Mês', $meses]],
    'validar' => function (array $v) {
        return checkdate((int)$v['mes'], (int)$v['dia'], 2024) ? null : 'Dia e mês da festa não formam uma data válida.';
    },
    'campos' => [
        'nome' => ['rotulo' => 'Nome', 'obrigatorio' => true, 'max' => 120, 'placeholder' => 'Ex.: São José'],
        'dia' => ['rotulo' => 'Dia da festa', 'tipo' => 'numero', 'obrigatorio' => true, 'min' => 1, 'maximo' => 31, 'linha' => 1],
        'mes' => ['rotulo' => 'Mês', 'tipo' => 'select', 'opcoes' => $meses, 'obrigatorio' => true, 'vazio' => '— mês —', 'linha' => 1],
        'titulo' => ['rotulo' => 'Título', 'max' => 120, 'placeholder' => 'Mártir, Doutor da Igreja, Apóstolo...'],
        'padroeiro' => ['rotulo' => 'Padroeiro(a) de', 'max' => 200, 'placeholder' => 'Ex.: das famílias e dos trabalhadores'],
        'resumo' => ['rotulo' => 'Resumo (uma frase)', 'max' => 300, 'dica' => 'Aparece nos cartões e no destaque da página inicial.'],
        'historia' => ['rotulo' => 'História', 'tipo' => 'textarea', 'linhas' => 8, 'dica' => 'Deixe uma linha em branco para separar parágrafos.'],
        'oracao' => ['rotulo' => 'Oração', 'tipo' => 'textarea', 'linhas' => 4],
        'imagem' => ['rotulo' => 'Imagem', 'tipo' => 'imagem'],
    ],
    'lista' => [
        'dia' => ['Festa', fn($l) => sprintf('%02d/%02d', $l['dia'], $l['mes'])],
        'nome' => 'Nome',
        'titulo' => 'Título',
        'historia' => ['História', fn($l) => $l['historia'] ? '<span class="etiqueta etiqueta-ativo">Sim</span>' : '<span class="etiqueta etiqueta-nova">Falta</span>'],
    ],
]);
