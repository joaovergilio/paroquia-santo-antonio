<?php
// Configurações técnicas do sistema. Os dados da paróquia (endereço, telefone, PIX...)
// são editados pelo painel, em "Dados da paróquia".

define('DB_HOST', 'localhost');
define('DB_NAME', 'paroquia_santo_antonio');
define('DB_USER', 'root');
define('DB_PASS', '');

// Uploads (fotos de padres, santos, eventos e comprovantes)
define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('UPLOAD_MAX', 5 * 1024 * 1024); // 5 MB

// Valores usados enquanto não forem alterados no painel
const CONFIG_PADRAO = [
    'paroquia_nome' => 'Paróquia Santo Antônio de Pádua',
    'igreja_nome' => 'Igreja Matriz Santo Antônio de Pádua',
    'cidade' => 'Laurentino/SC',
    'diocese' => 'Diocese de Rio do Sul',
    'endereco' => '',
    'telefone' => '(47) 0000-0000',
    'whatsapp' => '',
    'email' => 'secretaria@paroquia.org.br',
    'secretaria_horario' => 'Segunda a sexta, das 8h às 12h e das 13h30 às 17h30',
    'boas_vindas' => 'Seja bem-vindo à nossa comunidade! Aqui você encontra os horários de missa, a agenda da paróquia, os movimentos e pastorais e pode contribuir com o dízimo.',
    'pix_chave' => '',
    'pix_titular' => 'Paroquia Santo Antonio',
    'pix_cidade' => 'Laurentino',
    'banco_info' => '',
];

date_default_timezone_set('America/Sao_Paulo');
