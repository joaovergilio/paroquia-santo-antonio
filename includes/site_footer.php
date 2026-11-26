</main>
<footer class="site-rodape">
    <div class="container rodape-grade">
        <div>
            <strong class="rodape-marca"><?= cruz('rodape-cruz') ?> <?= e(cfg('paroquia_nome')) ?></strong>
            <p><?= e(cfg('igreja_nome')) ?><br><?= e(cfg('diocese')) ?></p>
        </div>
        <div>
            <strong>Endereço</strong>
            <p><?= e(cfg('endereco')) ?><br><?= e(cfg('cidade')) ?></p>
        </div>
        <div>
            <strong>Secretaria paroquial</strong>
            <p><?= e(cfg('secretaria_horario')) ?><br>
                <?= e(cfg('telefone')) ?><?= cfg('whatsapp') ? ' · WhatsApp ' . e(cfg('whatsapp')) : '' ?><br>
                <?= e(cfg('email')) ?></p>
        </div>
    </div>
    <p class="container creditos">
        <a href="admin/login.php">Área restrita</a> · Projeto de extensão · Sistemas de Informação · UNIDAVI
    </p>
</footer>
<script src="assets/js/app.js"></script>
</body>
</html>
