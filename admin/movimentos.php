<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/crud.php';
exige_login(ACESSO_CONTEUDO);

crud([
    'tabela' => 'movimento',
    'titulo' => 'Movimentos e pastorais',
    'singular' => 'movimento',
    'botao_novo' => 'Novo movimento',
    'ajuda' => '<p>Os movimentos aparecem na página <a href="../movimentos.php" target="_blank">Movimentos</a>. Se <b>Aceita inscrição pelo site</b> estiver marcado, as pessoas podem se inscrever e a inscrição chega em <a href="inscricoes.php">Inscrições</a>.</p>',
    'ordem' => 'ativo DESC, ordem IS NULL, ordem, nome',
    'linha_classe' => fn($l) => $l['ativo'] ? '' : 'inativo',
    'msg_vinculo' => 'Este movimento tem inscrições e não pode ser excluído. Desmarque "Ativo" para escondê-lo do site.',
    'campos' => [
        'nome' => ['rotulo' => 'Nome', 'obrigatorio' => true, 'max' => 120, 'placeholder' => 'Ex.: Grupo de Jovens'],
        'publico' => ['rotulo' => 'Para quem é', 'max' => 120, 'placeholder' => 'Ex.: Jovens de 15 a 29 anos'],
        'descricao' => ['rotulo' => 'Descrição', 'tipo' => 'textarea', 'linhas' => 4],
        'encontros' => ['rotulo' => 'Dia e horário dos encontros', 'max' => 200, 'placeholder' => 'Ex.: Sábados, às 20h, no salão paroquial'],
        'coordenador' => ['rotulo' => 'Coordenação', 'max' => 120],
        'ordem' => ['rotulo' => 'Ordem no site', 'tipo' => 'numero', 'min' => 0],
        'imagem' => ['rotulo' => 'Imagem', 'tipo' => 'imagem'],
        'aceita_inscricao' => ['rotulo' => 'Aceita inscrição pelo site', 'tipo' => 'checkbox', 'padrao' => 1],
        'ativo' => ['rotulo' => 'Ativo (aparece no site)', 'tipo' => 'checkbox', 'padrao' => 1],
    ],
    'lista' => [
        'nome' => 'Nome',
        'publico' => 'Público',
        'inscritos' => ['Inscrições', fn($l) => '<a href="inscricoes.php?movimento_id=' . $l['id'] . '">'
            . (int)q('SELECT COUNT(*) FROM inscricao WHERE movimento_id = ?', [$l['id']])->fetchColumn() . ' ver</a>'],
        'aceita_inscricao' => 'Inscrição',
        'ativo' => 'Ativo',
    ],
]);
