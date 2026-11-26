<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/crud.php';
exige_login(ACESSO_CONTEUDO);

crud([
    'tabela' => 'clero',
    'titulo' => 'Padres e celebrantes',
    'singular' => 'celebrante',
    'botao_novo' => 'Novo padre / celebrante',
    'ajuda' => '<p>Os padres cadastrados aqui aparecem na página <a href="../clero.php" target="_blank">Nossos padres</a> e podem ser escolhidos como celebrante nos horários e na agenda.</p>
                <p>Quando um padre for transferido, desmarque <b>Ativo</b> em vez de excluir: assim o histórico da agenda continua certo.</p>',
    'ordem' => 'ativo DESC, ordem IS NULL, ordem, nome',
    'linha_classe' => fn($l) => $l['ativo'] ? '' : 'inativo',
    'campos' => [
        'titulo' => ['rotulo' => 'Tratamento', 'tipo' => 'select', 'obrigatorio' => true, 'padrao' => 'Pe.', 'linha' => 1,
            'opcoes' => ['Pe.' => 'Pe. (Padre)', 'Frei' => 'Frei', 'Mons.' => 'Mons. (Monsenhor)', 'Dom' => 'Dom (Bispo)', 'Diác.' => 'Diác. (Diácono)', 'Ir.' => 'Ir. (Irmã/Irmão)']],
        'nome' => ['rotulo' => 'Nome', 'obrigatorio' => true, 'max' => 120, 'linha' => 1],
        'funcao' => ['rotulo' => 'Função', 'max' => 80, 'placeholder' => 'Pároco, Vigário paroquial, Diácono...'],
        'biografia' => ['rotulo' => 'Apresentação', 'tipo' => 'textarea', 'linhas' => 6,
            'dica' => 'Onde nasceu, formação, por onde passou, desde quando está na paróquia...'],
        'ordenacao' => ['rotulo' => 'Data de ordenação', 'tipo' => 'data', 'linha' => 2],
        'ordem' => ['rotulo' => 'Ordem no site', 'tipo' => 'numero', 'min' => 0, 'linha' => 2, 'dica' => '1 aparece primeiro.'],
        'foto' => ['rotulo' => 'Foto', 'tipo' => 'imagem'],
        'ativo' => ['rotulo' => 'Ativo (aparece no site)', 'tipo' => 'checkbox', 'padrao' => 1],
    ],
    'lista' => [
        'foto' => 'Foto',
        'nome' => ['Nome', fn($l) => e(nome_clero($l))],
        'funcao' => 'Função',
        'ativo' => 'Ativo',
    ],
]);
