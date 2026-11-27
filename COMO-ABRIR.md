# Projeto Paróquia Santo Antônio de Pádua: onde está tudo

## Links

| O quê | Link |
| --- | --- |
| Código no GitHub | https://github.com/joaovergilio/paroquia-santo-antonio |
| Documento (Planejamento e Idealização) | https://claude.ai/artifact/9Gms9CAAt2ycsTwWVR79iE |
| Slide da apresentação | https://claude.ai/artifact/XSqZXahf7iN6jcyzhJ4p2B |

O documento e o slide ficam na conta do claude.ai e abrem de qualquer PC depois de entrar na conta.
Para os colegas verem, use o botão **Share** em cada um.

## Login do painel

- E-mail: `admin@paroquia.org.br`
- Senha: `admin123`

## Abrir em outro PC

1. Instale o XAMPP (https://www.apachefriends.org) e o Git (https://git-scm.com).
2. Abra o painel do XAMPP e clique em **Start** no **Apache** e no **MySQL**.
3. Abra o Prompt de Comando e rode:
   ```
   cd C:\xampp\htdocs
   git clone https://github.com/joaovergilio/paroquia-santo-antonio.git
   ```
4. Acesse http://localhost/phpmyadmin, clique em **Importar**, escolha
   `C:\xampp\htdocs\paroquia-santo-antonio\database\schema.sql` e clique em **Importar** no fim da página.
5. Abra http://localhost/paroquia-santo-antonio/
6. Painel: link "Área restrita" no rodapé do site, com o login acima.

**Apache não inicia ou aparece erro 404?** Outro programa está usando a porta 80 (no Windows, costuma ser o IIS).
Abra `C:\xampp\apache\conf\httpd.conf`, troque `Listen 80` por `Listen 8080` e `ServerName localhost:80` por
`ServerName localhost:8080`, reinicie o Apache e acesse http://localhost:8080/paroquia-santo-antonio/

## Salvar alterações no GitHub

```
cd C:\xampp\htdocs\paroquia-santo-antonio
git add -A
git commit -m "descreva o que mudou"
git push
```

## Pegar a versão mais nova em outro PC

```
cd C:\xampp\htdocs\paroquia-santo-antonio
git pull
```

## Entrega no Classroom

- Documento: clique no nome do documento → **Export** → Word ou PDF.
- Slide: **Share** → **Export** → PowerPoint ou PDF.
- Repositório: o link do GitHub acima.

## Antes de apresentar, trocar no painel

- **Padres:** nomes reais (hoje está "Pe. Nome do Pároco").
- **Dados da paróquia:** endereço, telefone e chave PIX.
- **Usuários:** trocar a senha `admin123`.
