# Paróquia Santo Antônio de Pádua · Laurentino/SC

Site e painel de gestão da Igreja Matriz Santo Antônio de Pádua.

Projeto de Extensão · Programação Web I · Sistemas de Informação · UNIDAVI
Prof. Sandro Alencar Fernandes
Equipe: João Vergilio, Guilherme Losi e Kayke Souza

## O que o sistema faz

**Site público**
- **Início**: próximas missas, santo do dia, palavra do dia, agenda e movimentos
- **Horários**: quadro semanal de missas, confissões, adoração, terço etc., com celebrante e filtro por tipo, mais as celebrações especiais dos próximos 60 dias
- **Agenda**: missas especiais, datas comemorativas (com a história de cada data), festas e eventos
- **Santos**: história, padroado e oração de cada santo, busca e destaque do santo do dia
- **Nossos padres**: apresentação de cada sacerdote e quando ele vai celebrar
- **Movimentos**: grupos e pastorais (Grupo de Jovens, Movimento de Irmãos, Catequese...) com ficha de inscrição
- **Bíblia**: Bíblia católica completa (73 livros), com busca por referência (ex.: `Jo 3,16`)
- **Transparência**: entradas e saídas de cada mês publicado, por categoria
- **Dízimo e doações**: PIX com QR Code e "copia e cola" gerados com valor e finalidade, e cadastro de dizimista

**Painel** (`/admin`), feito para ser usado pela secretaria sem conhecimento técnico
- Horários fixos, agenda e datas comemorativas, padres, santos, movimentos
- Inscrições nos movimentos e cadastro de dizimistas (com situação: nova, contatada, confirmada...)
- Entradas e saídas com comprovante, estorno e publicação mensal na transparência
- Dados da paróquia (endereço, telefone, chave PIX...) editáveis sem mexer no código
- Usuários com perfis: Administrador, Secretaria e Tesouraria

## Como rodar (XAMPP)

1. Clone dentro do htdocs: `cd C:\xampp\htdocs` e `git clone https://github.com/joaovergilio/paroquia-santo-antonio.git`
2. Inicie **Apache** e **MySQL** no painel do XAMPP.
3. No phpMyAdmin (`http://localhost/phpmyadmin`), vá em **Importar** e envie `database/schema.sql`.
4. Acesse `http://localhost/paroquia-santo-antonio/`.

**Login inicial:** `admin@paroquia.org.br` / `admin123` (troque em Usuários no primeiro acesso).

**Apache não inicia ou `localhost` dá 404?** Outro programa está usando a porta 80 (no Windows, costuma ser o IIS ou o Skype).
Em `C:\xampp\apache\conf\httpd.conf`, troque `Listen 80` por `Listen 8080` e `ServerName localhost:80` por `ServerName localhost:8080`,
reinicie o Apache e acesse `http://localhost:8080/paroquia-santo-antonio/`. O sistema funciona em qualquer porta.

## Antes de publicar

- Em **Dados da paróquia**: endereço, telefone, e-mail, horário da secretaria e **chave PIX** (faça um PIX de teste de R$ 1,00)
- Em **Padres**: nome, foto e apresentação reais (os cadastrados são exemplos)
- Em **Horários fixos**: os horários reais (os cadastrados são exemplos)
- Em **Movimentos**: coordenadores e dias dos encontros

## Estrutura

```
paroquia-santo-antonio/
├── index.php, horarios.php, agenda.php, santos.php, clero.php,
│   movimentos.php, biblia.php, transparencia.php, dizimo.php      site público
├── admin/            painel
├── includes/         conexão, funções, autenticação, layouts e crud.php
│                     (tela de cadastro genérica usada pelo painel)
├── config/config.php banco de dados
├── database/schema.sql
├── assets/css, assets/js, assets/biblia (um arquivo JSON por livro)
└── uploads/          fotos e comprovantes
```

Para criar uma nova tela de cadastro no painel, copie `admin/movimentos.php`: basta descrever a tabela e os campos.

## Segurança

- Senhas com `password_hash`; consultas com PDO e prepared statements
- Token CSRF em todos os formulários e campo anti-robô nos formulários públicos
- Saída escapada com `htmlspecialchars` (função `e()`)
- Upload valida extensão, tipo real e tamanho (5 MB); a pasta `uploads` não executa PHP
- Dados de dizimistas e inscritos nunca aparecem no site; formulários pedem consentimento (LGPD)

## Créditos

Texto bíblico: tradução do Pe. Manuel de Matos Soares (1956), base de dados do projeto `biblia-db` (GitHub).
