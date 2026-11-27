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
        <nav class="rodape-links" aria-label="Páginas do site">
            <strong>No site</strong>
            <p>
                <a href="horarios.php">Horários</a>
                <a href="agenda.php">Agenda</a>
                <a href="movimentos.php">Movimentos</a>
                <a href="clero.php">Nossos padres</a>
                <a href="santos.php">Santos</a>
                <a href="biblia.php">Bíblia</a>
                <a href="transparencia.php">Transparência</a>
                <a href="dizimo.php">Dízimo e doações</a>
            </p>
        </nav>
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
