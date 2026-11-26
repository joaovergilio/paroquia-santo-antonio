-- Paróquia Santo Antônio de Pádua - Laurentino/SC
-- Banco de dados MySQL / MariaDB
-- Execute no phpMyAdmin (Importar) ou: mysql -u root < database/schema.sql

CREATE DATABASE IF NOT EXISTS paroquia_santo_antonio
  DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE paroquia_santo_antonio;

CREATE TABLE usuario (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  senha_hash VARCHAR(255) NOT NULL,
  perfil ENUM('admin','secretaria','tesouraria') NOT NULL DEFAULT 'secretaria',
  ativo TINYINT(1) NOT NULL DEFAULT 1,
  criado_em DATETIME NOT NULL
) ENGINE=InnoDB;

-- Dados da paróquia editáveis no painel (endereço, telefone, PIX...)
CREATE TABLE configuracao (
  chave VARCHAR(50) PRIMARY KEY,
  valor TEXT NOT NULL
) ENGINE=InnoDB;

-- Padres, diáconos e religiosos que celebram na paróquia
CREATE TABLE clero (
  id INT AUTO_INCREMENT PRIMARY KEY,
  titulo VARCHAR(20) NOT NULL DEFAULT 'Pe.',
  nome VARCHAR(120) NOT NULL,
  funcao VARCHAR(80) NULL,
  biografia TEXT NULL,
  ordenacao DATE NULL,
  foto VARCHAR(255) NULL,
  ordem INT NULL,
  ativo TINYINT(1) NOT NULL DEFAULT 1,
  criado_em DATETIME NOT NULL
) ENGINE=InnoDB;

-- Quadro de horários fixos (se repetem toda semana)
CREATE TABLE horario (
  id INT AUTO_INCREMENT PRIMARY KEY,
  dia_semana TINYINT NOT NULL,           -- 0 = domingo ... 6 = sábado
  hora TIME NOT NULL,
  tipo VARCHAR(40) NOT NULL,
  local VARCHAR(120) NULL,
  celebrante_id INT NULL,
  observacao VARCHAR(200) NULL,
  ativo TINYINT(1) NOT NULL DEFAULT 1,
  criado_em DATETIME NOT NULL,
  FOREIGN KEY (celebrante_id) REFERENCES clero(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Agenda: missas especiais, datas comemorativas, festas e eventos
CREATE TABLE celebracao (
  id INT AUTO_INCREMENT PRIMARY KEY,
  titulo VARCHAR(150) NOT NULL,
  tipo ENUM('celebracao','comemorativa','festa','evento','sacramento') NOT NULL DEFAULT 'celebracao',
  data_hora DATETIME NOT NULL,
  local VARCHAR(150) NULL,
  celebrante_id INT NULL,
  descricao TEXT NULL,
  historia TEXT NULL,
  imagem VARCHAR(255) NULL,
  criado_em DATETIME NOT NULL,
  FOREIGN KEY (celebrante_id) REFERENCES clero(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE santo (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(120) NOT NULL,
  dia TINYINT NOT NULL,
  mes TINYINT NOT NULL,
  titulo VARCHAR(120) NULL,
  padroeiro VARCHAR(200) NULL,
  resumo VARCHAR(300) NULL,
  historia TEXT NULL,
  oracao TEXT NULL,
  imagem VARCHAR(255) NULL,
  criado_em DATETIME NOT NULL
) ENGINE=InnoDB;

-- Movimentos, pastorais e grupos da paróquia
CREATE TABLE movimento (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(120) NOT NULL,
  publico VARCHAR(120) NULL,
  descricao TEXT NULL,
  encontros VARCHAR(200) NULL,
  coordenador VARCHAR(120) NULL,
  imagem VARCHAR(255) NULL,
  aceita_inscricao TINYINT(1) NOT NULL DEFAULT 1,
  ativo TINYINT(1) NOT NULL DEFAULT 1,
  ordem INT NULL,
  criado_em DATETIME NOT NULL
) ENGINE=InnoDB;

CREATE TABLE inscricao (
  id INT AUTO_INCREMENT PRIMARY KEY,
  movimento_id INT NOT NULL,
  nome VARCHAR(120) NOT NULL,
  nascimento DATE NULL,
  telefone VARCHAR(20) NOT NULL,
  email VARCHAR(150) NULL,
  comunidade VARCHAR(120) NULL,
  mensagem TEXT NULL,
  status ENUM('nova','contatada','confirmada','cancelada') NOT NULL DEFAULT 'nova',
  criado_em DATETIME NOT NULL,
  FOREIGN KEY (movimento_id) REFERENCES movimento(id)
) ENGINE=InnoDB;

CREATE TABLE dizimista (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(120) NOT NULL,
  telefone VARCHAR(20) NULL,
  email VARCHAR(150) NULL,
  endereco VARCHAR(200) NULL,
  comunidade VARCHAR(120) NULL,
  nascimento DATE NULL,
  observacao VARCHAR(255) NULL,
  origem ENUM('site','secretaria') NOT NULL DEFAULT 'secretaria',
  status ENUM('novo','ativo','inativo') NOT NULL DEFAULT 'ativo',
  criado_em DATETIME NOT NULL
) ENGINE=InnoDB;

CREATE TABLE lancamento (
  id INT AUTO_INCREMENT PRIMARY KEY,
  tipo ENUM('receita','despesa') NOT NULL,
  categoria VARCHAR(60) NOT NULL,
  valor DECIMAL(10,2) NOT NULL,
  descricao VARCHAR(255) NOT NULL,
  data DATE NOT NULL,
  comprovante VARCHAR(255) NULL,
  estorno_de INT NULL,
  usuario_id INT NOT NULL,
  criado_em DATETIME NOT NULL,
  FOREIGN KEY (estorno_de) REFERENCES lancamento(id),
  FOREIGN KEY (usuario_id) REFERENCES usuario(id)
) ENGINE=InnoDB;

CREATE TABLE fechamento_mensal (
  id INT AUTO_INCREMENT PRIMARY KEY,
  mes INT NOT NULL,
  ano INT NOT NULL,
  total_receitas DECIMAL(12,2) NOT NULL,
  total_despesas DECIMAL(12,2) NOT NULL,
  usuario_id INT NOT NULL,
  publicado_em DATETIME NOT NULL,
  UNIQUE (mes, ano),
  FOREIGN KEY (usuario_id) REFERENCES usuario(id)
) ENGINE=InnoDB;

-- =================================================================
-- Dados iniciais (exemplos: ajuste tudo pelo painel)
-- =================================================================

-- Usuário padrão: admin@paroquia.org.br / senha: admin123  (troque no primeiro acesso)
INSERT INTO usuario (nome, email, senha_hash, perfil, ativo, criado_em) VALUES
('Administrador', 'admin@paroquia.org.br', '$2y$10$nKimgvVthYARC7Oq6oulzeuwgfy.aR.on1zaK0b.5.djCNZpxB.J2', 'admin', 1, NOW());

INSERT INTO clero (titulo, nome, funcao, biografia, ordem, ativo, criado_em) VALUES
('Pe.', 'Nome do Pároco', 'Pároco', 'Escreva aqui uma breve apresentação do pároco: onde nasceu, quando foi ordenado, por onde passou e há quanto tempo está na paróquia.', 1, 1, NOW()),
('Pe.', 'Nome do Vigário', 'Vigário paroquial', 'Apresentação do vigário paroquial. Se a paróquia não tiver vigário, desmarque "Ativo" no painel.', 2, 1, NOW());

INSERT INTO horario (dia_semana, hora, tipo, local, celebrante_id, observacao, ativo, criado_em) VALUES
(0, '08:00', 'Missa', 'Igreja Matriz', 1, NULL, 1, NOW()),
(0, '10:00', 'Missa', 'Igreja Matriz', 2, 'Missa com as crianças da catequese', 1, NOW()),
(0, '19:00', 'Missa', 'Igreja Matriz', 1, NULL, 1, NOW()),
(2, '19:00', 'Missa', 'Igreja Matriz', 2, NULL, 1, NOW()),
(3, '19:00', 'Terço', 'Igreja Matriz', NULL, 'Terço dos homens', 1, NOW()),
(4, '18:00', 'Adoração ao Santíssimo', 'Igreja Matriz', 1, 'Seguida de missa às 19h', 1, NOW()),
(4, '19:00', 'Missa', 'Igreja Matriz', 1, NULL, 1, NOW()),
(5, '19:00', 'Missa', 'Igreja Matriz', 2, 'Missa com bênção da saúde', 1, NOW()),
(6, '15:00', 'Confissões', 'Igreja Matriz', 1, 'Até as 17h', 1, NOW()),
(6, '19:00', 'Missa', 'Igreja Matriz', 2, 'Missa vespertina do domingo', 1, NOW()),
(2, '15:00', 'Novena', 'Igreja Matriz', NULL, 'Novena perpétua de Santo Antônio', 1, NOW());

INSERT INTO configuracao (chave, valor) VALUES ('instalado_em', NOW());

-- Agenda: datas comemorativas com a história de cada uma
INSERT INTO celebracao (titulo, tipo, data_hora, local, celebrante_id, descricao, historia, criado_em) VALUES
('Solenidade de Cristo Rei', 'comemorativa', '2026-11-22 08:00:00', 'Igreja Matriz', 1,
 'Último domingo do ano litúrgico.',
 'A Solenidade de Nosso Senhor Jesus Cristo, Rei do Universo, foi instituída pelo Papa Pio XI em 1925, na encíclica Quas Primas, num tempo marcado por regimes totalitários e pelo afastamento da fé na vida pública. O Papa quis lembrar que o Reino de Cristo não é deste mundo: é um reino de verdade, justiça, amor e paz.\n\nCom a reforma litúrgica de 1969, a festa passou para o último domingo do Tempo Comum, encerrando o ano litúrgico. Assim a Igreja termina o ano contemplando Cristo como o fim e a plenitude de toda a história.', NOW()),
('Dia de Finados', 'comemorativa', '2026-11-02 08:00:00', 'Cemitério Municipal', 1,
 'Missa em memória de todos os fiéis falecidos.',
 'A Comemoração de Todos os Fiéis Defuntos é celebrada no dia 2 de novembro, logo após a Solenidade de Todos os Santos. O costume de rezar pelos mortos num dia próprio foi difundido por Santo Odilão, abade do mosteiro de Cluny, na França, por volta do ano 998, e depois se espalhou por toda a Igreja.\n\nA Igreja reza pelos que partiram porque acredita na comunhão dos santos e na ressurreição. Neste dia as famílias visitam os túmulos, levam flores, acendem velas e participam da Missa, entregando seus entes queridos à misericórdia de Deus.', NOW()),
('1º Domingo do Advento', 'comemorativa', '2026-11-29 08:00:00', 'Igreja Matriz', 1,
 'Início do novo ano litúrgico e do tempo de preparação para o Natal.',
 'A palavra Advento vem do latim adventus, que significa "chegada". São as quatro semanas que antecedem o Natal, e com elas começa um novo ano litúrgico. O Advento tem um duplo sentido: preparamos a celebração do nascimento de Jesus e renovamos a esperança na sua vinda gloriosa no fim dos tempos.\n\nA coroa do Advento, com quatro velas acesas uma a cada domingo, nasceu entre os cristãos da Alemanha e tornou-se um dos símbolos mais conhecidos desse tempo. A cor litúrgica é o roxo, sinal de conversão e espera; no terceiro domingo, o Domingo Gaudete ("Alegrai-vos"), pode-se usar o rosa.', NOW()),
('Solenidade da Imaculada Conceição', 'comemorativa', '2026-12-08 19:00:00', 'Igreja Matriz', 2,
 'Missa solene em honra de Nossa Senhora.',
 'A Igreja celebra que Maria, por uma graça singular de Deus e em vista dos méritos de Jesus Cristo, foi preservada do pecado original desde o primeiro instante da sua concepção. Essa verdade de fé foi proclamada como dogma pelo Papa Pio IX em 8 de dezembro de 1854, na bula Ineffabilis Deus.\n\nQuatro anos depois, em 1858, Nossa Senhora apareceu a Santa Bernadete em Lourdes, na França, e se apresentou dizendo: "Eu sou a Imaculada Conceição". A festa é celebrada no meio do Advento, lembrando que Maria é a primeira a acolher o Salvador.', NOW()),
('Missa da Noite de Natal', 'comemorativa', '2026-12-24 20:00:00', 'Igreja Matriz', 1,
 'Missa da vigília do Natal do Senhor. Traga sua família!',
 'No Natal celebramos o nascimento de Jesus em Belém, o Deus que se fez homem para nos salvar. A data de 25 de dezembro já era celebrada em Roma no século IV: o registro mais antigo é do calendário romano de 354.\n\nA tradicional "Missa do Galo" recebe esse nome porque, segundo a tradição popular, o galo teria sido o primeiro a anunciar o nascimento do Menino Jesus. O presépio, que hoje enfeita igrejas e casas, foi montado pela primeira vez por São Francisco de Assis em Greccio, na Itália, no Natal de 1223.', NOW()),
('Natal do Senhor', 'comemorativa', '2026-12-25 09:00:00', 'Igreja Matriz', 2,
 'Missa do dia de Natal.',
 'O dia de Natal abre o Tempo do Natal, que vai até a festa do Batismo do Senhor. A Igreja celebra o mistério da Encarnação: "O Verbo se fez carne e habitou entre nós" (João 1,14).\n\nNa liturgia, o Natal é celebrado durante oito dias como se fossem um único grande dia, a Oitava de Natal, que termina em 1º de janeiro com a Solenidade de Santa Maria, Mãe de Deus.', NOW()),
('Missa em Ação de Graças pelo ano', 'celebracao', '2026-12-31 19:00:00', 'Igreja Matriz', 1,
 'Agradecemos a Deus pelas graças recebidas durante o ano.', NULL, NOW()),
('Santa Maria, Mãe de Deus e Dia Mundial da Paz', 'comemorativa', '2027-01-01 09:00:00', 'Igreja Matriz', 1,
 'Primeiro dia do ano, dedicado a Nossa Senhora e à oração pela paz.',
 'No primeiro dia do ano a Igreja celebra Maria como Mãe de Deus (Theotokos), título proclamado no Concílio de Éfeso, no ano 431. É a festa mariana mais antiga e encerra a Oitava do Natal.\n\nEm 1968, o Papa São Paulo VI instituiu neste mesmo dia o Dia Mundial da Paz. Desde então, todos os anos o Papa publica uma mensagem com um tema para a reflexão e a oração pela paz no mundo.', NOW()),
('Quarta-feira de Cinzas', 'comemorativa', '2027-02-10 19:00:00', 'Igreja Matriz', 1,
 'Missa com imposição das cinzas. Início da Quaresma. Dia de jejum e abstinência.',
 'A Quarta-feira de Cinzas abre a Quaresma, os quarenta dias de preparação para a Páscoa, que lembram os quarenta dias de Jesus no deserto. As cinzas são feitas com os ramos abençoados no Domingo de Ramos do ano anterior.\n\nAo impor as cinzas, o sacerdote diz: "Convertei-vos e crede no Evangelho" ou "Lembra-te de que és pó e ao pó voltarás". É um convite à conversão por meio da oração, do jejum e da caridade. No Brasil, desde 1964 a Quaresma é também o tempo da Campanha da Fraternidade, promovida pela CNBB.', NOW()),
('Domingo de Ramos', 'comemorativa', '2027-03-21 08:00:00', 'Igreja Matriz', 1,
 'Bênção dos ramos e procissão. Início da Semana Santa.',
 'O Domingo de Ramos e da Paixão do Senhor recorda a entrada de Jesus em Jerusalém, aclamado pelo povo com ramos e mantos estendidos pelo caminho: "Hosana ao Filho de Davi!".\n\nA celebração começa com a bênção dos ramos e a procissão, em clima de festa, e logo depois a liturgia muda de tom com a leitura da Paixão. Assim, numa só celebração, a Igreja contempla a glória e a cruz, e abre a Semana Santa.', NOW()),
('Celebração da Paixão do Senhor (Sexta-feira Santa)', 'comemorativa', '2027-03-26 15:00:00', 'Igreja Matriz', 1,
 'Celebração às 15h, hora da morte de Jesus. Dia de jejum e abstinência.',
 'Na Sexta-feira Santa a Igreja não celebra Missa: é o único dia do ano em que isso acontece. Às 15 horas, hora em que Jesus morreu, realiza-se a Celebração da Paixão do Senhor, com a leitura da Paixão segundo São João, a grande oração universal, a adoração da Cruz e a comunhão com as hóstias consagradas na Quinta-feira Santa.\n\nEm muitas comunidades também se reza a Via-Sacra, percorrendo as estações do caminho de Jesus até o Calvário.', NOW()),
('Vigília Pascal', 'comemorativa', '2027-03-27 20:00:00', 'Igreja Matriz', 1,
 'A mãe de todas as vigílias. Bênção do fogo novo e do círio pascal.',
 'Santo Agostinho chamou a Vigília Pascal de "a mãe de todas as santas vigílias". É a celebração mais importante do ano: começa no escuro, com a bênção do fogo novo e o acendimento do círio pascal, sinal de Cristo ressuscitado, luz do mundo.\n\nSegue-se o canto do Exsultet (Precônio Pascal), as leituras que percorrem a história da salvação, o Glória com o toque dos sinos, a renovação das promessas do Batismo e a Eucaristia.', NOW()),
('Domingo de Páscoa', 'comemorativa', '2027-03-28 08:00:00', 'Igreja Matriz', 2,
 'Cristo ressuscitou, aleluia!',
 'A Páscoa é a maior festa do cristianismo: a ressurreição de Jesus. A palavra vem do hebraico Pessach, "passagem", que lembra a libertação do povo de Israel da escravidão no Egito. Em Cristo, ela se torna a passagem da morte para a vida.\n\nO Concílio de Niceia, em 325, estabeleceu que a Páscoa é celebrada no domingo seguinte à primeira lua cheia depois do início da primavera no hemisfério norte; por isso a data muda todo ano. A alegria pascal se prolonga por cinquenta dias, até Pentecostes.', NOW()),
('Solenidade de Pentecostes', 'comemorativa', '2027-05-16 19:00:00', 'Igreja Matriz', 1,
 'Encerramento do Tempo Pascal.',
 'Cinquenta dias depois da Páscoa, a Igreja celebra a vinda do Espírito Santo sobre os apóstolos e Maria, reunidos no Cenáculo, em Jerusalém (Atos 2). Línguas como de fogo pousaram sobre cada um, e eles começaram a anunciar Jesus a pessoas de muitas nações, que os entendiam em sua própria língua.\n\nPor isso Pentecostes é considerado o "aniversário da Igreja". A cor litúrgica é o vermelho, lembrando o fogo do Espírito.', NOW()),
('Corpus Christi', 'comemorativa', '2027-05-27 09:00:00', 'Igreja Matriz', 1,
 'Missa seguida de procissão com o Santíssimo Sacramento pelas ruas enfeitadas com tapetes.',
 'A Solenidade do Santíssimo Corpo e Sangue de Cristo celebra a presença real de Jesus na Eucaristia. A festa nasceu a partir das visões de Santa Juliana de Cornillon, religiosa de Liège, na Bélgica, e foi estendida a toda a Igreja pelo Papa Urbano IV em 1264, com a bula Transiturus. São Tomás de Aquino compôs os hinos da festa, entre eles o Tantum Ergo.\n\nNo Brasil é tradição enfeitar as ruas com tapetes coloridos feitos de serragem, flores, café e outros materiais, por onde passa a procissão com o Santíssimo.', NOW()),
('Abertura da Trezena de Santo Antônio', 'festa', '2027-06-01 19:00:00', 'Igreja Matriz', 1,
 'Treze dias de oração preparando a festa do nosso padroeiro.',
 'A trezena é uma devoção típica de Santo Antônio: são treze dias de oração antes da festa, em 13 de junho, lembrando o dia da sua morte. O número também recorda os treze milagres atribuídos ao santo no conhecido responsório "Si quaeris miracula".\n\nEm cada noite da trezena a comunidade reza, ouve a Palavra e pede a intercessão do padroeiro, preparando o coração para a grande festa da paróquia.', NOW()),
('Festa de Santo Antônio, nosso padroeiro', 'festa', '2027-06-13 10:00:00', 'Igreja Matriz', 1,
 'Missa solene, procissão, bênção e distribuição dos pães de Santo Antônio. Depois, almoço festivo no salão paroquial.',
 'Santo Antônio nasceu em Lisboa, por volta de 1195, com o nome de Fernando de Bulhões. Foi cônego agostiniano e, em 1220, entrou para a Ordem dos Frades Menores, de São Francisco, tomando o nome de Antônio. Grande pregador, percorreu a Itália e o sul da França anunciando o Evangelho e defendendo os pobres. Morreu perto de Pádua, na Itália, em 13 de junho de 1231, com cerca de 36 anos.\n\nFoi canonizado menos de um ano depois, em 1232, pelo Papa Gregório IX, e declarado Doutor da Igreja em 1946 pelo Papa Pio XII. A tradição do "pão de Santo Antônio" lembra a sua caridade com os pobres: os pães abençoados na festa são levados para casa como sinal de que nunca falte o alimento na mesa.', NOW());

-- Santos (a data é o dia da festa no calendário)
INSERT INTO santo (nome, dia, mes, titulo, padroeiro, resumo, historia, oracao, criado_em) VALUES
('Santo Antônio de Pádua', 13, 6, 'Doutor da Igreja · Padroeiro da paróquia', 'dos pobres, das coisas perdidas e dos casamentos',
 'Frade franciscano nascido em Lisboa, grande pregador e amigo dos pobres. É o padroeiro da nossa paróquia.',
 'Fernando de Bulhões nasceu em Lisboa, Portugal, por volta de 1195, numa família nobre. Aos 15 anos entrou para os cônegos regulares de Santo Agostinho, onde estudou profundamente as Escrituras. Em 1220, impressionado com o testemunho de cinco frades franciscanos martirizados no Marrocos, deixou os agostinianos, entrou para a Ordem de São Francisco e passou a se chamar Antônio.\n\nQuis ser missionário na África, mas adoeceu e o navio que o trazia de volta foi desviado por uma tempestade até a Sicília. Na Itália, descobriram por acaso o seu dom para a pregação, e o próprio São Francisco o encarregou de ensinar teologia aos frades. Pregou com enorme sucesso no norte da Itália e no sul da França, sempre defendendo os pobres e combatendo a usura.\n\nMorreu em 13 de junho de 1231, perto de Pádua, com cerca de 36 anos. Foi canonizado em 1232, uma das canonizações mais rápidas da história, e declarado Doutor da Igreja em 1946 pelo Papa Pio XII, com o título de "Doutor Evangélico". No Brasil é popularmente conhecido como o santo casamenteiro e o protetor de quem procura algo perdido.',
 'Glorioso Santo Antônio, amigo dos pobres e dos que sofrem, que recebestes de Deus o dom de pregar com sabedoria e de socorrer os necessitados, intercedei por nós e por nossa paróquia. Ajudai-nos a encontrar o que perdemos, sobretudo a fé, a esperança e a caridade, e alcançai-nos a graça de que hoje precisamos (fazer o pedido). Amém.', NOW()),
('Santa Maria, Mãe de Deus', 1, 1, 'Solenidade', 'de toda a Igreja',
 'Primeira solenidade do ano, que celebra Maria como Mãe de Jesus, verdadeiro Deus e verdadeiro homem.',
 'O título de Mãe de Deus (em grego, Theotokos) foi proclamado solenemente pelo Concílio de Éfeso, no ano 431. Ao afirmar que Maria é Mãe de Deus, a Igreja professa que Jesus, nascido dela, é uma só pessoa, verdadeiramente Deus e verdadeiramente homem.\n\nA solenidade encerra a Oitava do Natal e abre o ano civil sob a proteção de Nossa Senhora. Desde 1968 também é o Dia Mundial da Paz.',
 'Santa Maria, Mãe de Deus, rogai por nós, pecadores, agora e na hora da nossa morte. Amém.', NOW()),
('São Sebastião', 20, 1, 'Mártir', 'contra a fome, a peste e a guerra; dos soldados',
 'Soldado romano que deu a vida por Cristo no tempo do imperador Diocleciano.',
 'Sebastião viveu entre os séculos III e IV e, segundo a tradição, era um oficial da guarda do imperador em Roma. Usava sua posição para visitar e animar os cristãos presos durante as perseguições.\n\nDenunciado como cristão, foi condenado a ser amarrado a uma árvore e atingido por flechas. Deixado como morto, foi socorrido e curado por uma cristã chamada Irene. Voltou a se apresentar ao imperador para defender os cristãos e então foi martirizado. É muito venerado no Brasil, padroeiro da cidade do Rio de Janeiro e de muitas comunidades do interior.',
 'Glorioso São Sebastião, que destes a vida por amor a Cristo, livrai-nos da peste, da fome e da guerra, e dai-nos coragem para testemunhar a fé. Amém.', NOW()),
('São José', 19, 3, 'Esposo de Maria · Padroeiro da Igreja', 'da Igreja universal, das famílias e dos trabalhadores',
 'Homem justo, esposo da Virgem Maria e pai adotivo de Jesus, que cuidou da Sagrada Família em silêncio e fidelidade.',
 'São José era carpinteiro em Nazaré, descendente do rei Davi. Os Evangelhos o chamam de "homem justo": acolheu Maria como esposa após o anúncio do anjo, protegeu o Menino Jesus na fuga para o Egito e o educou no trabalho e na fé.\n\nEm 1870 o Papa Pio IX o proclamou Padroeiro da Igreja universal. Em 1955 Pio XII instituiu a festa de São José Operário, em 1º de maio. Em 2020 o Papa Francisco dedicou a ele a carta apostólica Patris Corde e um ano especial.',
 'São José, guarda fiel da Sagrada Família, protegei a nossa família e a nossa paróquia. Dai trabalho a quem precisa e ensinai-nos a cumprir a vontade de Deus. Amém.', NOW()),
('São Jorge', 23, 4, 'Mártir', 'dos soldados e escoteiros',
 'Soldado cristão martirizado no início do século IV, símbolo da luta do bem contra o mal.',
 'Segundo a tradição, Jorge nasceu na Capadócia, na atual Turquia, e foi soldado do exército romano. Quando o imperador Diocleciano começou a perseguir os cristãos, por volta de 303, Jorge declarou publicamente a sua fé e recusou-se a oferecer sacrifícios aos deuses. Foi torturado e decapitado.\n\nA imagem de São Jorge montado a cavalo vencendo o dragão surgiu na Idade Média e representa a vitória da fé sobre o mal. É padroeiro de vários países, como a Inglaterra e a Geórgia.',
 'São Jorge, soldado fiel de Cristo, dai-nos coragem para vencer o mal e permanecer firmes na fé. Amém.', NOW()),
('Nossa Senhora de Fátima', 13, 5, 'Aparição de Nossa Senhora', 'de Portugal',
 'Em 1917 Nossa Senhora apareceu a três pastorinhos em Fátima, Portugal, pedindo oração do terço e conversão.',
 'Entre 13 de maio e 13 de outubro de 1917, Nossa Senhora apareceu seis vezes a três crianças, Lúcia, Francisco e Jacinta, na Cova da Iria, em Fátima, Portugal. Ela pediu a oração diária do terço pela paz no mundo, a penitência e a consagração ao seu Imaculado Coração.\n\nNa última aparição, cerca de 70 mil pessoas testemunharam o chamado "milagre do sol". Francisco e Jacinta foram canonizados pelo Papa Francisco em 13 de maio de 2017, no centenário das aparições.',
 'Nossa Senhora de Fátima, rogai por nós e pela paz no mundo. Ó meu Jesus, perdoai-nos, livrai-nos do fogo do inferno, levai as almas todas para o céu e socorrei principalmente as que mais precisarem. Amém.', NOW()),
('Santa Rita de Cássia', 22, 5, 'Religiosa agostiniana', 'das causas impossíveis',
 'Esposa, mãe, viúva e religiosa, exemplo de paciência e perdão. Conhecida como a santa das causas impossíveis.',
 'Rita nasceu em 1381 em Roccaporena, perto de Cássia, na Itália. Desejava ser religiosa, mas casou-se a pedido dos pais com um homem de temperamento violento. Com paciência e oração, conseguiu a conversão do marido, que depois foi assassinado. Rita perdoou os assassinos e rezou para que os filhos não se vingassem.\n\nViúva e sem os filhos, que morreram jovens, entrou para o mosteiro agostiniano de Cássia. Ali recebeu na testa uma chaga semelhante a um espinho da coroa de Cristo, que carregou por 15 anos. Morreu em 22 de maio de 1457 e foi canonizada em 1900 pelo Papa Leão XIII.',
 'Santa Rita de Cássia, advogada das causas impossíveis, intercedei por nós junto a Deus e alcançai-nos a graça de que tanto precisamos (fazer o pedido). Amém.', NOW()),
('São João Batista', 24, 6, 'Precursor do Senhor', 'dos batizados',
 'Primo de Jesus, preparou o povo para a sua vinda e o batizou no rio Jordão.',
 'Filho de Isabel e Zacarias, João nasceu seis meses antes de Jesus. É um dos poucos santos de quem a Igreja celebra o nascimento, porque já no ventre da mãe "estremeceu de alegria" ao encontro de Maria grávida de Jesus.\n\nViveu no deserto, pregava a conversão e batizava no rio Jordão, onde também batizou Jesus, apontando-o como "o Cordeiro de Deus que tira o pecado do mundo". Foi preso e decapitado por ordem de Herodes Antipas, por denunciar o seu casamento ilegítimo. As festas juninas de São João são uma das tradições mais populares do Brasil.',
 'São João Batista, que preparastes os caminhos do Senhor, ajudai-nos a viver a conversão e a apontar Jesus aos nossos irmãos. Amém.', NOW()),
('São Pedro e São Paulo', 29, 6, 'Apóstolos', 'da Igreja de Roma; São Pedro dos pescadores',
 'As duas colunas da Igreja: Pedro, a rocha escolhida por Jesus, e Paulo, o apóstolo dos povos.',
 'Pedro era pescador na Galileia e foi escolhido por Jesus como líder dos apóstolos: "Tu és Pedro, e sobre esta pedra edificarei a minha Igreja". Paulo, antes chamado Saulo, perseguia os cristãos até se converter num encontro com Cristo ressuscitado no caminho de Damasco; tornou-se o grande missionário dos povos e escreveu boa parte do Novo Testamento.\n\nOs dois foram martirizados em Roma durante a perseguição de Nero, por volta do ano 64-67: Pedro crucificado de cabeça para baixo e Paulo decapitado. No Brasil, no domingo mais próximo a esta data, celebra-se o Dia do Papa.',
 'São Pedro e São Paulo, colunas da Igreja, intercedei pelo Papa, pelos bispos e por todo o povo de Deus. Amém.', NOW()),
('Santa Paulina', 9, 7, 'Primeira santa do Brasil', 'dos doentes e de Santa Catarina',
 'Imigrante italiana que viveu em Nova Trento (SC) e fundou a Congregação das Irmãzinhas da Imaculada Conceição.',
 'Amábile Lúcia Visintainer nasceu em 1865 em Vigolo Vattaro, no Trentino, Itália, e veio com a família para o Brasil em 1875, instalando-se em Vígolo, no atual município de Nova Trento, em Santa Catarina. Desde jovem se dedicou à catequese, à limpeza da capela e ao cuidado dos doentes.\n\nEm 1890, com uma amiga, começou a cuidar de uma senhora com câncer num pequeno casebre, dando origem à Congregação das Irmãzinhas da Imaculada Conceição, e passou a se chamar Madre Paulina do Coração Agonizante de Jesus. Sofreu muitas humilhações, mas aceitou tudo com humildade. Morreu em São Paulo em 9 de julho de 1942 e foi canonizada pelo Papa João Paulo II em 19 de maio de 2002. O seu santuário, em Nova Trento, recebe romeiros de todo o país.',
 'Santa Paulina, que vivestes na nossa terra catarinense servindo aos doentes e aos pobres, intercedei por nós e pelos enfermos da nossa comunidade. Amém.', NOW()),
('São Bento', 11, 7, 'Abade · Padroeiro da Europa', 'contra os perigos e as tentações',
 'Pai do monaquismo no Ocidente, autor da Regra que ensina "reza e trabalha".',
 'Bento nasceu em Núrsia, na Itália, por volta do ano 480. Estudou em Roma, mas, desiludido com a vida corrompida da cidade, retirou-se para viver como eremita em Subiaco. Discípulos se juntaram a ele e, por volta de 529, fundou o mosteiro de Monte Cassino.\n\nEscreveu a Regra de São Bento, resumida no lema "Ora et labora" (reza e trabalha), que orientou milhares de mosteiros e ajudou a preservar a fé e a cultura na Europa. A medalha de São Bento é usada como sinal de proteção contra o mal. O Papa Paulo VI o proclamou Padroeiro da Europa em 1964.',
 'São Bento, pai dos monges, livrai-nos dos perigos e das tentações e ensinai-nos a rezar e a trabalhar para a glória de Deus. Amém.', NOW()),
('São Joaquim e Sant\'Ana', 26, 7, 'Pais de Nossa Senhora', 'dos avós',
 'Os pais da Virgem Maria e avós de Jesus. No Brasil, a data é o Dia dos Avós.',
 'Os nomes de Joaquim e Ana não aparecem na Bíblia, mas chegaram até nós por uma tradição muito antiga, registrada no chamado Protoevangelho de Tiago, do século II. Segundo ela, o casal era piedoso e sofria por não ter filhos, até que Deus atendeu suas orações com o nascimento de Maria.\n\nA devoção a Sant\'Ana é muito antiga, e as imagens a mostram ensinando a menina Maria a ler as Escrituras. Por serem os avós de Jesus, no Brasil o dia 26 de julho é celebrado como o Dia dos Avós.',
 'São Joaquim e Sant\'Ana, abençoai os nossos avós e todas as famílias da nossa comunidade. Amém.', NOW()),
('São João Maria Vianney', 4, 8, 'O Cura d\'Ars', 'dos párocos e sacerdotes',
 'Padre humilde de uma pequena aldeia francesa que passava até 16 horas por dia no confessionário. No Brasil, 4 de agosto é o Dia do Padre.',
 'João Maria Vianney nasceu em 1786 perto de Lyon, na França. Teve muita dificuldade nos estudos e quase não foi ordenado. Em 1818 foi enviado para Ars, uma pequena aldeia de cerca de 230 habitantes, onde a fé estava esquecida.\n\nCom oração, penitência, pregação simples e muito tempo no confessionário, transformou a aldeia, que passou a receber milhares de peregrinos de toda a França. Morreu em 4 de agosto de 1859 e foi canonizado em 1925. Pio XI o proclamou padroeiro dos párocos. No dia da sua festa, rezamos e agradecemos pelos nossos padres.',
 'São João Maria Vianney, padroeiro dos párocos, intercedei pelos sacerdotes da nossa paróquia e despertai novas vocações. Amém.', NOW()),
('Santa Clara de Assis', 11, 8, 'Virgem · Fundadora das Clarissas', 'da televisão',
 'Seguidora de São Francisco, fundou a ordem das Irmãs Pobres, hoje chamadas Clarissas.',
 'Clara nasceu em Assis, na Itália, em 1194, numa família nobre. Aos 18 anos, tocada pela pregação de São Francisco, fugiu de casa na noite do Domingo de Ramos de 1212 para consagrar a vida a Deus na pobreza. Fundou com outras irmãs o mosteiro de São Damião, origem da Ordem das Clarissas.\n\nViveu 41 anos em clausura, oração e pobreza radical. Conta-se que, já doente e sem poder ir à Missa, via a celebração projetada na parede do seu quarto; por isso, em 1958, Pio XII a declarou padroeira da televisão. Morreu em 1253 e foi canonizada dois anos depois.',
 'Santa Clara, que amastes a pobreza e a oração, iluminai as nossas famílias e ajudai-nos a buscar o que é essencial. Amém.', NOW()),
('São Miguel, São Gabriel e São Rafael', 29, 9, 'Arcanjos', 'São Miguel: dos policiais e protetor contra o mal',
 'Os três arcanjos citados pelo nome na Bíblia: Miguel, o defensor; Gabriel, o mensageiro; Rafael, o que cura.',
 'Miguel, cujo nome significa "Quem é como Deus?", aparece no livro de Daniel e no Apocalipse como o chefe dos anjos que combate o dragão. Gabriel ("Força de Deus") é o anjo que anunciou a Zacarias o nascimento de João Batista e a Maria o nascimento de Jesus. Rafael ("Deus cura") acompanha o jovem Tobias em viagem e cura a cegueira de seu pai, no livro de Tobias.\n\nDesde 1969 a Igreja celebra os três arcanjos juntos neste dia.',
 'São Miguel Arcanjo, defendei-nos no combate; sede o nosso refúgio contra as maldades e ciladas do demônio. Amém.', NOW()),
('Santa Teresinha do Menino Jesus', 1, 10, 'Doutora da Igreja', 'das missões',
 'Jovem carmelita francesa que ensinou a "pequena via": fazer as pequenas coisas com grande amor.',
 'Teresa Martin nasceu em Alençon, na França, em 1873. Com apenas 15 anos, conseguiu permissão para entrar no Carmelo de Lisieux. Ali viveu uma vida escondida, descobrindo a sua "pequena via": confiar em Deus como uma criança e transformar cada gesto simples em ato de amor.\n\nMorreu de tuberculose aos 24 anos, em 1897. A sua autobiografia, "História de uma Alma", tornou-se um dos livros espirituais mais lidos do mundo. Foi canonizada em 1925, proclamada padroeira das missões em 1927, mesmo sem nunca ter saído do convento, e Doutora da Igreja por João Paulo II em 1997.',
 'Santa Teresinha, ensinai-nos a pequena via da confiança e do amor, e deixai cair sobre nós uma chuva de rosas, as graças de Deus. Amém.', NOW()),
('São Francisco de Assis', 4, 10, 'Fundador dos Franciscanos', 'da ecologia e dos animais',
 'O "pobrezinho de Assis", que deixou tudo para seguir Jesus. Foi na sua ordem que Santo Antônio se tornou frade.',
 'Francisco nasceu em Assis, na Itália, por volta de 1181, filho de um rico comerciante de tecidos. Depois de uma juventude de festas e de ter sido prisioneiro de guerra, ouviu Cristo lhe dizer na igrejinha de São Damião: "Francisco, vai e reconstrói a minha Igreja". Renunciou publicamente à herança e passou a viver na pobreza, servindo aos leprosos e pregando o Evangelho.\n\nFundou a Ordem dos Frades Menores, à qual pertenceu Santo Antônio, nosso padroeiro. Compôs o Cântico das Criaturas, montou o primeiro presépio em 1223 e recebeu os estigmas em 1224. Morreu em 3 de outubro de 1226 e foi canonizado dois anos depois. Em 1979 João Paulo II o declarou padroeiro da ecologia.',
 'Senhor, fazei de mim um instrumento de vossa paz. Onde houver ódio, que eu leve o amor; onde houver ofensa, que eu leve o perdão; onde houver discórdia, que eu leve a união. Amém.', NOW()),
('Nossa Senhora Aparecida', 12, 10, 'Padroeira do Brasil', 'do Brasil',
 'Imagem de Nossa Senhora da Conceição encontrada por pescadores no rio Paraíba do Sul em 1717.',
 'Em outubro de 1717, os pescadores Domingos Garcia, João Alves e Filipe Pedroso lançaram as redes no rio Paraíba do Sul, em São Paulo, para conseguir peixes para uma visita do governador. Primeiro pescaram o corpo de uma pequena imagem de Nossa Senhora da Conceição, depois a cabeça. Em seguida, as redes se encheram de peixes.\n\nA devoção cresceu rapidamente e a imagem, escurecida pelo tempo na água, recebeu o nome de Aparecida. Em 1930 o Papa Pio XI a proclamou Padroeira do Brasil, e desde 1980 o dia 12 de outubro é feriado nacional. O Santuário Nacional de Aparecida é um dos maiores santuários marianos do mundo.',
 'Ó Senhora Aparecida, Mãe e Padroeira do Brasil, abençoai nossas famílias, nossa paróquia e nossa pátria. Amém.', NOW()),
('São Judas Tadeu', 28, 10, 'Apóstolo', 'das causas difíceis e desesperadas',
 'Um dos doze apóstolos, primo de Jesus segundo a tradição e autor de uma das cartas do Novo Testamento.',
 'Judas Tadeu foi um dos doze apóstolos escolhidos por Jesus. Não deve ser confundido com Judas Iscariotes, o traidor. Na Última Ceia, perguntou a Jesus por que ele se manifestaria aos discípulos e não ao mundo (João 14,22). É atribuída a ele a Carta de Judas, no Novo Testamento.\n\nSegundo a tradição, pregou o Evangelho na Mesopotâmia e na Pérsia junto com São Simão, com quem divide a festa e o martírio. É invocado como o padroeiro das causas difíceis e desesperadas.',
 'São Judas Tadeu, apóstolo fiel de Jesus, socorrei-nos nas causas difíceis e alcançai-nos a graça que pedimos com fé (fazer o pedido). Amém.', NOW()),
('Imaculada Conceição', 8, 12, 'Solenidade de Nossa Senhora', 'do Brasil (título oficial da Padroeira)',
 'Maria foi preservada do pecado original desde a sua concepção. Dogma proclamado em 1854.',
 'A Igreja crê que Maria, por uma graça especial de Deus e pelos méritos de Jesus Cristo, foi concebida sem a mancha do pecado original. Essa fé, presente desde os primeiros séculos, foi definida como dogma pelo Papa Pio IX em 8 de dezembro de 1854, na bula Ineffabilis Deus.\n\nEm 1858, em Lourdes, Nossa Senhora apresentou-se a Santa Bernadete dizendo: "Eu sou a Imaculada Conceição". A imagem de Nossa Senhora Aparecida é uma imagem da Imaculada Conceição.',
 'Ó Maria concebida sem pecado, rogai por nós que recorremos a vós. Amém.', NOW()),
('Santa Luzia', 13, 12, 'Virgem e mártir', 'dos olhos e da visão',
 'Jovem de Siracusa, na Sicília, que consagrou a vida a Cristo e foi martirizada por volta do ano 304.',
 'Luzia nasceu em Siracusa, na Sicília, numa família rica, no fim do século III. Depois de rezar no túmulo de Santa Águeda pela cura da mãe, decidiu consagrar-se a Deus e distribuir seus bens aos pobres. O jovem a quem havia sido prometida em casamento a denunciou como cristã.\n\nFoi martirizada durante a perseguição de Diocleciano, por volta do ano 304. O seu nome vem do latim lux, "luz", e por isso é invocada como protetora dos olhos. É muito querida em Santa Catarina, onde muitas comunidades realizam a sua festa em dezembro.',
 'Santa Luzia, protegei a luz dos nossos olhos e dai-nos uma fé que enxergue a presença de Deus em todas as coisas. Amém.', NOW());

-- Movimentos, pastorais e grupos
INSERT INTO movimento (nome, publico, descricao, encontros, coordenador, aceita_inscricao, ativo, ordem, criado_em) VALUES
('Grupo de Jovens', 'Jovens de 15 a 29 anos', 'Encontros de oração, formação, música e amizade. Os jovens também ajudam nas celebrações, nas festas da paróquia e em ações sociais.', 'Sábados, às 20h, no salão paroquial', 'A definir', 1, 1, 1, NOW()),
('Movimento de Irmãos', 'Adultos e casais', 'Movimento de leigos que vive a fraternidade cristã por meio de encontros, oração e partilha da Palavra, fortalecendo a fé e a vida em comunidade.', 'Encontros mensais (consulte a secretaria)', 'A definir', 1, 1, 2, NOW()),
('Catequese', 'Crianças, adolescentes e adultos', 'Preparação para a Primeira Eucaristia, a Crisma e catequese de adultos. Inscrições para o novo ano são feitas no início do ano.', 'Sábados pela manhã, durante o ano letivo', 'A definir', 1, 1, 3, NOW()),
('Coroinhas e Acólitos', 'Crianças e adolescentes a partir de 9 anos', 'Servem ao altar nas missas e celebrações, aprendendo sobre a liturgia e vivendo a amizade com Jesus.', 'Formação quinzenal', 'A definir', 1, 1, 4, NOW()),
('Ministério de Música', 'Cantores e músicos', 'Anima as celebrações com o canto litúrgico. Não é preciso ser profissional: basta ter vontade e disponibilidade para os ensaios.', 'Ensaios semanais', 'A definir', 1, 1, 5, NOW()),
('Encontro de Casais com Cristo (ECC)', 'Casais', 'Ajuda os casais a renovar o amor, o diálogo e a fé, fortalecendo a família como Igreja doméstica.', 'Encontros anuais e reuniões periódicas', 'A definir', 1, 1, 6, NOW()),
('Pastoral do Dízimo', 'Todos os paroquianos', 'Acolhe os dizimistas, organiza a devolução do dízimo e conscientiza a comunidade sobre a partilha.', 'Reuniões mensais', 'A definir', 1, 1, 7, NOW()),
('Legião de Maria', 'Adultos', 'Grupo de oração mariana que realiza visitas a doentes, famílias e pessoas afastadas da Igreja.', 'Reuniões semanais', 'A definir', 1, 1, 8, NOW()),
('Pastoral da Criança', 'Gestantes e famílias com crianças até 6 anos', 'Acompanha o desenvolvimento das crianças e orienta as famílias sobre saúde, nutrição e educação, com apoio de voluntários.', 'Celebração da Vida mensal', 'A definir', 1, 1, 9, NOW()),
('Ministros Extraordinários da Comunhão', 'Adultos indicados pela comunidade', 'Ajudam na distribuição da comunhão nas missas e a levam aos doentes e idosos em suas casas.', 'Formação conforme orientação do pároco', 'A definir', 0, 1, 10, NOW());
