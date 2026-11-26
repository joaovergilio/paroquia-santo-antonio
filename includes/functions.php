<?php
// Funções auxiliares usadas em todo o sistema

const DIAS_SEMANA = ['Domingo', 'Segunda-feira', 'Terça-feira', 'Quarta-feira', 'Quinta-feira', 'Sexta-feira', 'Sábado'];

// Tipos do quadro de horários fixos (semanais)
const TIPOS_HORARIO = ['Missa', 'Confissões', 'Adoração ao Santíssimo', 'Terço', 'Novena', 'Batizados', 'Atendimento do padre'];

// Tipos de itens da agenda
const TIPOS_CELEBRACAO = [
    'celebracao' => 'Missa / celebração especial',
    'comemorativa' => 'Data comemorativa',
    'festa' => 'Festa da comunidade',
    'evento' => 'Evento / encontro',
    'sacramento' => 'Sacramentos',
];

function e($valor): string
{
    return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8');
}

function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}

function post(string $campo, $padrao = '')
{
    return isset($_POST[$campo]) && !is_array($_POST[$campo]) ? trim((string)$_POST[$campo]) : $padrao;
}

function agora(): string
{
    return date('Y-m-d H:i:s');
}

// ---------- Dados da paróquia (editáveis no painel) ----------
function cfg(string $chave): string
{
    static $valores = null;
    if ($valores === null) {
        $valores = CONFIG_PADRAO;
        foreach (q('SELECT chave, valor FROM configuracao')->fetchAll() as $c) {
            $valores[$c['chave']] = $c['valor'];
        }
    }
    return (string)($valores[$chave] ?? '');
}

// ---------- Mensagens de feedback (flash) ----------
function flash(string $tipo, string $msg): void
{
    $_SESSION['flash'][] = [$tipo, $msg];
}

function mostrar_flashes(): string
{
    $html = '';
    foreach ($_SESSION['flash'] ?? [] as [$tipo, $msg]) {
        $html .= '<div class="alerta alerta-' . e($tipo) . '">' . e($msg) . '</div>';
    }
    unset($_SESSION['flash']);
    return $html;
}

// ---------- Proteção CSRF ----------
function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(16));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . csrf_token() . '">';
}

function csrf_check(): void
{
    if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) {
        http_response_code(400);
        exit('Requisição inválida (o formulário expirou). Volte, recarregue a página e tente novamente.');
    }
}

// ---------- Formatação ----------
function moeda($valor): string
{
    return 'R$ ' . number_format((float)$valor, 2, ',', '.');
}

function data_br(?string $data, bool $hora = false): string
{
    if (!$data) {
        return '';
    }
    return date($hora ? 'd/m/Y H:i' : 'd/m/Y', strtotime($data));
}

function hora_br(?string $hora): string
{
    if (!$hora) {
        return '';
    }
    [$h, $m] = array_map('intval', explode(':', $hora));
    return $m ? sprintf('%dh%02d', $h, $m) : $h . 'h';
}

function nome_mes(int $mes): string
{
    $nomes = [1 => 'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho',
        'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'];
    return $nomes[$mes] ?? '';
}

// "Sábado, 28 de novembro" (com o ano se for diferente do atual)
function data_extenso(string $data, bool $comDiaSemana = true): string
{
    $ts = strtotime($data);
    $texto = (int)date('j', $ts) . ' de ' . mb_strtolower(nome_mes((int)date('n', $ts)));
    if (date('Y', $ts) !== date('Y')) {
        $texto .= ' de ' . date('Y', $ts);
    }
    return $comDiaSemana ? DIAS_SEMANA[(int)date('w', $ts)] . ', ' . $texto : $texto;
}

// "Hoje", "Amanhã" ou a data por extenso
function quando_amigavel(string $dataHora): string
{
    $dia = date('Y-m-d', strtotime($dataHora));
    if ($dia === date('Y-m-d')) {
        return 'Hoje';
    }
    if ($dia === date('Y-m-d', strtotime('+1 day'))) {
        return 'Amanhã';
    }
    return data_extenso($dataHora);
}

// Aceita "1.234,56", "1234,56" ou "1234.56"
function parse_valor(string $texto): float
{
    $texto = str_replace(['R$', ' '], '', $texto);
    if (strpos($texto, ',') !== false) {
        $texto = str_replace(['.', ','], ['', '.'], $texto);
    }
    return round((float)$texto, 2);
}

function trecho(?string $texto, int $tamanho = 160): string
{
    $texto = trim(preg_replace('/\s+/', ' ', (string)$texto));
    return mb_strlen($texto) > $tamanho ? rtrim(mb_substr($texto, 0, $tamanho)) . '…' : $texto;
}

// Parágrafos a partir de texto simples (linhas em branco separam parágrafos)
function paragrafos(?string $texto): string
{
    $html = '';
    foreach (preg_split('/\n\s*\n/', trim((string)$texto)) as $p) {
        if ($p !== '') {
            $html .= '<p>' . nl2br(e(trim($p))) . '</p>';
        }
    }
    return $html;
}

// ---------- Clero ----------
function nome_clero(?array $c): string
{
    if (!$c || empty($c['nome'])) {
        return '';
    }
    return trim(($c['titulo'] ?? '') . ' ' . $c['nome']);
}

function clero_opcoes(): array
{
    $op = [];
    foreach (q('SELECT id, titulo, nome FROM clero WHERE ativo = 1 ORDER BY ordem, nome')->fetchAll() as $c) {
        $op[$c['id']] = nome_clero($c);
    }
    return $op;
}

// ---------- Agenda: próximas missas / celebrações ----------
// Junta os horários fixos da semana com a agenda especial e devolve em ordem de data.
function proximos_horarios(int $limite = 6, ?string $tipo = 'Missa', ?int $cleroId = null, int $dias = 7): array
{
    $lista = [];
    $fixos = q('SELECT h.*, c.titulo AS c_titulo, c.nome AS c_nome FROM horario h
                LEFT JOIN clero c ON c.id = h.celebrante_id WHERE h.ativo = 1')->fetchAll();
    for ($d = 0; $d <= $dias; $d++) {
        $dia = date('Y-m-d', strtotime("+$d day"));
        $semana = (int)date('w', strtotime($dia));
        foreach ($fixos as $h) {
            if ((int)$h['dia_semana'] !== $semana || ($tipo && $h['tipo'] !== $tipo)
                || ($cleroId && (int)$h['celebrante_id'] !== $cleroId)) {
                continue;
            }
            $quando = $dia . ' ' . substr($h['hora'], 0, 5) . ':00';
            if ($quando < agora()) {
                continue;
            }
            $lista[] = ['quando' => $quando, 'titulo' => $h['tipo'], 'local' => $h['local'], 'observacao' => $h['observacao'],
                'celebrante' => nome_clero(['titulo' => $h['c_titulo'], 'nome' => $h['c_nome']]), 'celebrante_id' => $h['celebrante_id'], 'id' => null];
        }
    }
    $sql = 'SELECT a.*, c.titulo AS c_titulo, c.nome AS c_nome FROM celebracao a LEFT JOIN clero c ON c.id = a.celebrante_id
            WHERE a.data_hora BETWEEN ? AND ?';
    $params = [agora(), date('Y-m-d 23:59:59', strtotime("+$dias day"))];
    if ($tipo === 'Missa') {
        $sql .= " AND a.tipo IN ('celebracao', 'comemorativa', 'festa')";
    }
    if ($cleroId) {
        $sql .= ' AND a.celebrante_id = ?';
        $params[] = $cleroId;
    }
    foreach (q($sql, $params)->fetchAll() as $a) {
        $lista[] = ['quando' => $a['data_hora'], 'titulo' => $a['titulo'], 'local' => $a['local'], 'observacao' => '',
            'celebrante' => nome_clero(['titulo' => $a['c_titulo'], 'nome' => $a['c_nome']]), 'celebrante_id' => $a['celebrante_id'], 'id' => $a['id']];
    }
    usort($lista, fn($a, $b) => strcmp($a['quando'], $b['quando']));
    return array_slice($lista, 0, $limite);
}

// ---------- Santos ----------
function santo_do_dia(): ?array
{
    return q('SELECT * FROM santo WHERE dia = ? AND mes = ? ORDER BY id LIMIT 1', [(int)date('j'), (int)date('n')])->fetch() ?: null;
}

function proximo_santo(): ?array
{
    $hoje = (int)date('n') * 100 + (int)date('j');
    $s = q('SELECT * FROM santo WHERE mes * 100 + dia > ? ORDER BY mes, dia LIMIT 1', [$hoje])->fetch();
    return $s ?: (q('SELECT * FROM santo ORDER BY mes, dia LIMIT 1')->fetch() ?: null);
}

// ---------- Bíblia ----------
// Lê um versículo dos arquivos em assets/biblia (livro = número de 1 a 73)
function versiculo(int $livro, int $capitulo, int $verso): ?array
{
    $arquivo = __DIR__ . sprintf('/../assets/biblia/%02d.json', $livro);
    $indice = json_decode((string)@file_get_contents(__DIR__ . '/../assets/biblia/livros.json'), true);
    $caps = is_file($arquivo) ? json_decode(file_get_contents($arquivo), true) : null;
    foreach ($caps[$capitulo - 1] ?? [] as [$n, $texto]) {
        if ($n === $verso) {
            return ['texto' => trim($texto, " \"“”"), 'referencia' => ($indice[$livro - 1]['nome'] ?? '') . " $capitulo,$verso",
                'link' => "biblia.php#$livro/$capitulo/$verso"];
        }
    }
    return null;
}

function versiculo_do_dia(): ?array
{
    $refs = [[50, 3, 16], [23, 22, 1], [52, 8, 28], [57, 4, 13], [24, 3, 5], [29, 41, 10], [47, 11, 28], [50, 14, 6],
        [23, 26, 1], [53, 13, 4], [47, 5, 9], [50, 15, 12], [30, 29, 11], [23, 118, 105], [65, 11, 1], [69, 4, 8],
        [47, 6, 33], [49, 1, 37], [52, 12, 12], [23, 45, 11], [50, 11, 25], [47, 28, 20], [56, 2, 8], [66, 1, 5]];
    [$l, $c, $v] = $refs[(int)date('z') % count($refs)];
    return versiculo($l, $c, $v);
}

// ---------- Financeiro ----------
// Um mês publicado na transparência não aceita mais alterações
function mes_publicado(string $data): bool
{
    $ts = strtotime($data);
    return (bool)q('SELECT 1 FROM fechamento_mensal WHERE mes = ? AND ano = ?', [(int)date('n', $ts), (int)date('Y', $ts)])->fetchColumn();
}

// ---------- Upload de arquivos ----------
// Devolve o nome salvo, null se nenhum arquivo foi enviado, ou lança exceção se inválido
function upload_arquivo(string $campo, array $extensoes = ['jpg', 'jpeg', 'png', 'webp']): ?string
{
    if (empty($_FILES[$campo]) || $_FILES[$campo]['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    $arq = $_FILES[$campo];
    if ($arq['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Falha no envio do arquivo.');
    }
    if ($arq['size'] > UPLOAD_MAX) {
        throw new RuntimeException('Arquivo maior que 5 MB.');
    }
    $ext = strtolower(pathinfo($arq['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $extensoes, true)) {
        throw new RuntimeException('Tipo de arquivo não permitido. Use: ' . implode(', ', $extensoes));
    }
    $mimesValidos = ['jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png',
        'webp' => 'image/webp', 'pdf' => 'application/pdf'];
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($arq['tmp_name']);
    if (($mimesValidos[$ext] ?? '') !== $mime) {
        throw new RuntimeException('O conteúdo do arquivo não corresponde à extensão.');
    }
    $nome = date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    if (!move_uploaded_file($arq['tmp_name'], UPLOAD_DIR . $nome)) {
        throw new RuntimeException('Não foi possível salvar o arquivo.');
    }
    return $nome;
}

function apagar_upload(?string $nome): void
{
    if ($nome && basename($nome) === $nome && is_file(UPLOAD_DIR . $nome)) {
        unlink(UPLOAD_DIR . $nome);
    }
}
