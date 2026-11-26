<?php
// Tela de cadastro genérica do painel (listar, criar, editar e excluir).
// Cada página do painel só descreve a tabela e os campos; a tela é montada aqui.
//
// Tipos de campo: texto, textarea, email, numero, data, datahora, hora, select, checkbox, imagem
// Opções de campo: rotulo, tipo, obrigatorio, opcoes (select), vazio (texto da opção vazia),
//                  placeholder, dica, max, padrao, linha (agrupa campos lado a lado)

function crud(array $d): void
{
    $tabela = $d['tabela'];
    $campos = $d['campos'];
    $pagina = basename($_SERVER['SCRIPT_NAME']);
    $filtros = $d['filtros'] ?? [];
    $filtrosAtivos = [];
    foreach ($filtros as $campo => [, $opcoes]) {
        if (isset($_GET[$campo]) && is_string($_GET[$campo]) && array_key_exists($_GET[$campo], $opcoes)) {
            $filtrosAtivos[$campo] = $_GET[$campo];
        }
    }
    $base = $pagina . '?' . http_build_query($filtrosAtivos);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        csrf_check();
        $id = (int)post('id');
        $atual = $id ? q("SELECT * FROM $tabela WHERE id = ?", [$id])->fetch() : null;
        if ($id && !$atual) {
            flash('erro', 'Registro não encontrado.');
            redirect($base);
        }

        if (post('acao') === 'excluir') {
            try {
                q("DELETE FROM $tabela WHERE id = ?", [$id]);
                foreach ($campos as $nome => $c) {
                    if (($c['tipo'] ?? '') === 'imagem') {
                        apagar_upload($atual[$nome] ?? null);
                    }
                }
                flash('sucesso', 'Excluído.');
            } catch (PDOException $ex) {
                flash('erro', $d['msg_vinculo'] ?? 'Não foi possível excluir porque há outros registros ligados a este. Você pode desmarcar "Ativo" para escondê-lo do site.');
            }
            redirect($base);
        }

        [$valores, $erros] = crud_ler_formulario($campos);
        if (!$erros && isset($d['validar'])) {
            $msg = $d['validar']($valores, $id);
            if ($msg) {
                $erros[] = $msg;
            }
        }
        $antigas = [];
        if (!$erros) {
            try {
                foreach ($campos as $nome => $c) {
                    if (($c['tipo'] ?? '') !== 'imagem') {
                        continue;
                    }
                    $novo = upload_arquivo($nome, $c['extensoes'] ?? ['jpg', 'jpeg', 'png', 'webp']);
                    if ($novo || isset($_POST["remover_$nome"])) {
                        $valores[$nome] = $novo;
                        $antigas[] = $atual[$nome] ?? null;
                    }
                }
            } catch (RuntimeException $ex) {
                $erros[] = $ex->getMessage();
            }
        }
        if ($erros) {
            flash('erro', implode(' ', $erros));
            $_SESSION['crud_rascunho'] = $_POST;
            redirect($base . '&' . ($id ? "editar=$id" : 'novo=1') . '#formulario');
        }

        if ($id) {
            $sets = implode(', ', array_map(fn($c) => "$c = ?", array_keys($valores)));
            q("UPDATE $tabela SET $sets WHERE id = ?", array_merge(array_values($valores), [$id]));
        } else {
            $valores['criado_em'] = agora();
            $cols = implode(', ', array_keys($valores));
            $marcas = implode(', ', array_fill(0, count($valores), '?'));
            q("INSERT INTO $tabela ($cols) VALUES ($marcas)", array_values($valores));
        }
        array_map('apagar_upload', $antigas);
        flash('sucesso', $id ? 'Alterações salvas.' : ($d['msg_criado'] ?? 'Cadastro criado.'));
        redirect($base);
    }

    // ---------- Listagem ----------
    $where = '';
    if ($filtrosAtivos) {
        $where = ' WHERE ' . implode(' AND ', array_map(fn($c) => "$c = ?", array_keys($filtrosAtivos)));
    }
    $linhas = q("SELECT * FROM $tabela$where ORDER BY {$d['ordem']}", array_values($filtrosAtivos))->fetchAll();

    $editar = isset($_GET['editar']) ? q("SELECT * FROM $tabela WHERE id = ?", [(int)$_GET['editar']])->fetch() : null;
    $valoresForm = $editar ?: [];
    if (!empty($_SESSION['crud_rascunho'])) {
        $valoresForm = array_merge($valoresForm, $_SESSION['crud_rascunho']);
        unset($_SESSION['crud_rascunho']);
    }
    $novo = !$editar && (isset($_GET['novo']) || !empty($valoresForm));

    $titulo = $d['titulo'];
    require __DIR__ . '/admin_header.php';
    ?>
    <?php if (!empty($d['ajuda'])): ?>
        <div class="ajuda"><div><?= $d['ajuda'] ?></div></div>
    <?php endif; ?>

    <div class="barra-acoes">
        <a class="botao botao-cheio" href="<?= e($base) ?>&novo=1#formulario"><?= e($d['botao_novo'] ?? 'Novo cadastro') ?></a>
        <?php if ($filtros): ?>
            <form class="filtros" method="get">
                <?php foreach ($filtros as $campo => [$rotulo, $opcoes]): ?>
                    <select name="<?= e($campo) ?>" onchange="this.form.submit()" aria-label="<?= e($rotulo) ?>">
                        <option value=""><?= e($rotulo) ?>: todos</option>
                        <?php foreach ($opcoes as $valor => $texto): ?>
                            <option value="<?= e($valor) ?>" <?= isset($filtrosAtivos[$campo]) && (string)$filtrosAtivos[$campo] === (string)$valor ? 'selected' : '' ?>><?= e($texto) ?></option>
                        <?php endforeach; ?>
                    </select>
                <?php endforeach; ?>
            </form>
        <?php endif; ?>
    </div>

    <div class="grade-2 grade-larga">
        <section class="cartao">
            <?php if (!$linhas): ?>
                <p class="suave"><?= e($d['vazio'] ?? 'Nada cadastrado ainda.') ?></p>
            <?php else: ?>
                <p class="suave contagem"><?= count($linhas) ?> registro(s)</p>
                <div class="rolagem">
                    <table class="tabela">
                        <thead><tr>
                            <?php foreach ($d['lista'] as $rotulo): ?><th><?= e(is_array($rotulo) ? $rotulo[0] : $rotulo) ?></th><?php endforeach; ?>
                            <th></th>
                        </tr></thead>
                        <tbody>
                        <?php foreach ($linhas as $l): ?>
                            <tr class="<?= isset($d['linha_classe']) ? e($d['linha_classe']($l)) : '' ?> <?= $editar && $editar['id'] == $l['id'] ? 'editando' : '' ?>">
                                <?php foreach ($d['lista'] as $campo => $rotulo): ?>
                                    <td><?= is_array($rotulo) ? $rotulo[1]($l) : crud_formatar($campos[$campo] ?? [], $l[$campo] ?? null) ?></td>
                                <?php endforeach; ?>
                                <td class="acoes">
                                    <a class="botao botao-pequeno" href="<?= e($base) ?>&editar=<?= $l['id'] ?>#formulario">Editar</a>
                                    <form method="post" onsubmit="return confirm('Excluir? Não dá para desfazer.')">
                                        <?= csrf_field() ?><input type="hidden" name="acao" value="excluir"><input type="hidden" name="id" value="<?= $l['id'] ?>">
                                        <button class="link-perigo">Excluir</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>

        <form method="post" enctype="multipart/form-data" class="cartao formulario <?= $editar || $novo ? 'em-foco' : '' ?>" id="formulario">
            <h2><?= $editar ? 'Editar' : 'Novo cadastro' ?><?= !empty($d['singular']) ? ': ' . e($d['singular']) : '' ?></h2>
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= (int)($editar['id'] ?? 0) ?>">
            <?php
            $linhaAberta = null;
            foreach ($campos as $nome => $c):
                $linha = $c['linha'] ?? null;
                if ($linhaAberta !== null && $linha !== $linhaAberta) { echo '</div>'; $linhaAberta = null; }
                if ($linha !== null && $linhaAberta === null) { echo '<div class="linha-campos">'; $linhaAberta = $linha; }
                echo crud_campo($nome, $c, $valoresForm, (bool)$editar);
            endforeach;
            if ($linhaAberta !== null) { echo '</div>'; }
            ?>
            <button class="botao botao-cheio"><?= $editar ? 'Salvar alterações' : 'Cadastrar' ?></button>
            <?php if ($editar || $novo): ?><a href="<?= e($base) ?>" class="link-secundario">Cancelar</a><?php endif; ?>
        </form>
    </div>
    <?php
    require __DIR__ . '/admin_footer.php';
}

// Lê e valida os campos enviados. Devolve [valores, erros]
function crud_ler_formulario(array $campos): array
{
    $valores = [];
    $erros = [];
    foreach ($campos as $nome => $c) {
        $tipo = $c['tipo'] ?? 'texto';
        $rotulo = $c['rotulo'];
        if ($tipo === 'imagem') {
            continue;
        }
        $v = post($nome);
        switch ($tipo) {
            case 'checkbox':
                $v = isset($_POST[$nome]) ? 1 : 0;
                break;
            case 'numero':
                $v = $v === '' ? '' : (int)$v;
                break;
            case 'data':
                if ($v !== '' && !DateTime::createFromFormat('Y-m-d', $v)) {
                    $erros[] = "$rotulo: data inválida.";
                }
                break;
            case 'datahora':
                if ($v !== '') {
                    $ts = strtotime(str_replace('T', ' ', $v));
                    $ts ? $v = date('Y-m-d H:i:s', $ts) : $erros[] = "$rotulo: data/hora inválida.";
                }
                break;
            case 'hora':
                if ($v !== '' && !preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $v)) {
                    $erros[] = "$rotulo: horário inválido.";
                }
                break;
            case 'select':
                if ($v !== '' && !array_key_exists($v, $c['opcoes'])) {
                    $erros[] = "$rotulo: opção inválida.";
                }
                break;
            case 'email':
                if ($v !== '' && !filter_var($v, FILTER_VALIDATE_EMAIL)) {
                    $erros[] = "$rotulo: e-mail inválido.";
                }
                break;
        }
        if (($c['obrigatorio'] ?? false) && $v === '') {
            $erros[] = "Preencha o campo \"$rotulo\".";
        }
        if (is_string($v) && isset($c['max'])) {
            $v = mb_substr($v, 0, $c['max']);
        }
        $valores[$nome] = $v === '' ? null : $v;
    }
    return [$valores, $erros];
}

function crud_campo(string $nome, array $c, array $valores, bool $editando): string
{
    $tipo = $c['tipo'] ?? 'texto';
    $valor = $valores[$nome] ?? (!$editando ? ($c['padrao'] ?? '') : '');
    $req = !empty($c['obrigatorio']) ? ' required' : '';
    $ph = isset($c['placeholder']) ? ' placeholder="' . e($c['placeholder']) . '"' : '';
    $max = isset($c['max']) ? ' maxlength="' . (int)$c['max'] . '"' : '';
    $rotulo = e($c['rotulo']) . (!empty($c['obrigatorio']) ? ' <span class="obrigatorio" title="Obrigatório">*</span>' : '');
    $dica = isset($c['dica']) ? '<small class="dica-campo">' . e($c['dica']) . '</small>' : '';
    $n = e($nome);

    switch ($tipo) {
        case 'textarea':
            $input = "<textarea name=\"$n\" rows=\"" . (int)($c['linhas'] ?? 4) . "\"$req$ph>" . e($valor) . '</textarea>';
            break;
        case 'select':
            $input = "<select name=\"$n\"$req>";
            if (empty($c['obrigatorio']) || isset($c['vazio'])) {
                $input .= '<option value="">' . e($c['vazio'] ?? '— selecione —') . '</option>';
            }
            foreach ($c['opcoes'] as $v => $texto) {
                $sel = (string)$valor === (string)$v ? ' selected' : '';
                $input .= '<option value="' . e($v) . "\"$sel>" . e($texto) . '</option>';
            }
            $input .= '</select>';
            break;
        case 'checkbox':
            $marcado = ($editando || isset($valores[$nome])) ? !empty($valor) : !empty($c['padrao']);
            return '<label class="check"><input type="checkbox" name="' . $n . '" value="1"' . ($marcado ? ' checked' : '') . '> '
                . e($c['rotulo']) . '</label>' . $dica;
        case 'imagem':
            $input = "<input type=\"file\" name=\"$n\" accept=\".jpg,.jpeg,.png,.webp\">";
            if ($editando && !empty($valores[$nome])) {
                $input .= '<img class="miniatura" src="../uploads/' . e($valores[$nome]) . '" alt="">'
                    . "<label class=\"check\"><input type=\"checkbox\" name=\"remover_$n\" value=\"1\"> Remover imagem atual</label>";
            }
            break;
        case 'datahora':
            $v = $valor ? date('Y-m-d\TH:i', strtotime(str_replace('T', ' ', $valor))) : '';
            $input = "<input type=\"datetime-local\" name=\"$n\" value=\"" . e($v) . "\"$req>";
            break;
        case 'hora':
            $input = "<input type=\"time\" name=\"$n\" value=\"" . e(substr((string)$valor, 0, 5)) . "\"$req>";
            break;
        default:
            $tipos = ['email' => 'email', 'numero' => 'number', 'data' => 'date'];
            $extra = $tipo === 'numero' ? (isset($c['min']) ? ' min="' . (int)$c['min'] . '"' : '') . (isset($c['maximo']) ? ' max="' . (int)$c['maximo'] . '"' : '') : '';
            $input = '<input type="' . ($tipos[$tipo] ?? 'text') . "\" name=\"$n\" value=\"" . e($valor) . "\"$req$ph$max$extra>";
    }
    return "<label>$rotulo$input</label>$dica";
}

function crud_formatar(array $c, $v): string
{
    if ($v === null || $v === '') {
        return ($c['tipo'] ?? '') === 'checkbox' ? '<span class="etiqueta etiqueta-inativo">Não</span>' : '<span class="suave">—</span>';
    }
    switch ($c['tipo'] ?? 'texto') {
        case 'select':
            return e($c['opcoes'][$v] ?? $v);
        case 'checkbox':
            return $v ? '<span class="etiqueta etiqueta-ativo">Sim</span>' : '<span class="etiqueta etiqueta-inativo">Não</span>';
        case 'data':
            return data_br($v);
        case 'datahora':
            return data_br($v, true);
        case 'hora':
            return hora_br($v);
        case 'imagem':
            return '<img class="miniatura-lista" src="../uploads/' . e($v) . '" alt="">';
        case 'textarea':
            return e(trecho($v, 70));
        default:
            return e($v);
    }
}
