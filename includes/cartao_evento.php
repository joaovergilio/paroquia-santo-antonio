<?php // Cartão de um item da agenda. Espera a variável $ev (com c_titulo e c_nome do celebrante). ?>
<article class="cartao-evento tipo-<?= e($ev['tipo']) ?>">
    <a href="agenda.php?id=<?= $ev['id'] ?>" class="cartao-evento-link">
        <?php if ($ev['imagem']): ?>
            <img src="uploads/<?= e($ev['imagem']) ?>" alt="" loading="lazy">
        <?php else: ?>
            <div class="evento-data-grande">
                <strong><?= date('d', strtotime($ev['data_hora'])) ?></strong>
                <span><?= mb_substr(nome_mes((int)date('n', strtotime($ev['data_hora']))), 0, 3) ?></span>
            </div>
        <?php endif; ?>
        <div class="cartao-evento-corpo">
            <span class="etiqueta etiqueta-<?= e($ev['tipo']) ?>"><?= e(TIPOS_CELEBRACAO[$ev['tipo']] ?? '') ?></span>
            <h3><?= e($ev['titulo']) ?></h3>
            <p class="evento-quando"><?= e(data_extenso($ev['data_hora'])) ?> · <?= hora_br(date('H:i', strtotime($ev['data_hora']))) ?></p>
            <p class="suave"><?= e($ev['local']) ?><?= !empty($ev['c_nome']) ? ' · ' . e(nome_clero(['titulo' => $ev['c_titulo'], 'nome' => $ev['c_nome']])) : '' ?></p>
            <?php if ($ev['historia']): ?><span class="link-mais">Ler a história</span><?php endif; ?>
        </div>
    </a>
</article>
