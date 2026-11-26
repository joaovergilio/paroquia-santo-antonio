<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/crud.php';
exige_login(ACESSO_CONTEUDO);

$clero = clero_opcoes();
$tipos = array_combine(TIPOS_HORARIO, TIPOS_HORARIO);

crud([
    'tabela' => 'horario',
    'titulo' => 'Horários fixos',
    'singular' => 'horário',
    'botao_novo' => 'Novo horário',
    'ajuda' => '<p>Estes são os horários que <b>se repetem toda semana</b> (ex.: missa de domingo às 8h). Eles formam o quadro da página <a href="../horarios.php" target="_blank">Horários</a>.</p>
                <p>Para uma missa que acontece <b>só uma vez</b> (Natal, festa, missa de 7º dia...), use a <a href="agenda.php">Agenda</a>. Para suspender um horário por um tempo, desmarque <b>Ativo</b>.</p>',
    'ordem' => 'dia_semana, hora',
    'filtros' => ['dia_semana' => ['Dia', DIAS_SEMANA], 'tipo' => ['Tipo', $tipos]],
    'linha_classe' => fn($l) => $l['ativo'] ? '' : 'inativo',
    'campos' => [
        'dia_semana' => ['rotulo' => 'Dia da semana', 'tipo' => 'select', 'opcoes' => DIAS_SEMANA, 'obrigatorio' => true, 'vazio' => '— escolha o dia —', 'linha' => 1],
        'hora' => ['rotulo' => 'Horário', 'tipo' => 'hora', 'obrigatorio' => true, 'linha' => 1],
        'tipo' => ['rotulo' => 'O que acontece', 'tipo' => 'select', 'opcoes' => $tipos, 'obrigatorio' => true, 'padrao' => 'Missa'],
        'local' => ['rotulo' => 'Local', 'max' => 120, 'padrao' => 'Igreja Matriz', 'placeholder' => 'Igreja Matriz, Capela São José...'],
        'celebrante_id' => ['rotulo' => 'Celebrante', 'tipo' => 'select', 'opcoes' => $clero, 'vazio' => '— não definido —',
            'dica' => 'Para cadastrar um padre novo, vá em "Padres".'],
        'observacao' => ['rotulo' => 'Observação', 'max' => 200, 'placeholder' => 'Ex.: Missa com as crianças'],
        'ativo' => ['rotulo' => 'Ativo (aparece no site)', 'tipo' => 'checkbox', 'padrao' => 1],
    ],
    'lista' => [
        'dia_semana' => 'Dia',
        'hora' => 'Hora',
        'tipo' => 'Tipo',
        'local' => 'Local',
        'celebrante_id' => 'Celebrante',
        'ativo' => 'Ativo',
    ],
]);
