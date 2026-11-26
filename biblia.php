<?php
require_once __DIR__ . '/includes/bootstrap.php';

$titulo = 'Bíblia Sagrada';
require __DIR__ . '/includes/site_header.php';
?>
<section class="secao">
    <div class="container biblia" data-biblia>
        <h1>Bíblia Sagrada</h1>
        <p class="introducao">Bíblia católica completa, com os 73 livros. Escolha o livro e o capítulo, ou digite uma referência
            como <em>Jo 3,16</em>, <em>Sl 23</em> ou <em>Lucas 15</em>.</p>

        <div class="biblia-controles cartao">
            <form class="biblia-referencia" data-biblia-busca>
                <label>Ir para
                    <input type="search" placeholder="Ex.: Jo 3,16" data-biblia-ref autocomplete="off">
                </label>
                <button class="botao botao-cheio">Abrir</button>
            </form>
            <div class="biblia-selects">
                <label>Livro
                    <select data-biblia-livro></select>
                </label>
                <label>Capítulo
                    <select data-biblia-capitulo></select>
                </label>
            </div>
            <div class="biblia-fonte" role="group" aria-label="Tamanho da letra">
                <button type="button" class="chip" data-fonte="-1" aria-label="Diminuir letra">A−</button>
                <button type="button" class="chip" data-fonte="1" aria-label="Aumentar letra">A+</button>
            </div>
        </div>

        <article class="cartao biblia-texto" aria-live="polite">
            <header class="biblia-cabecalho">
                <button type="button" class="botao botao-pequeno" data-biblia-anterior>Anterior</button>
                <h2 data-biblia-titulo>Carregando…</h2>
                <button type="button" class="botao botao-pequeno" data-biblia-proximo>Próximo</button>
            </header>
            <div data-biblia-versos class="versos"></div>
            <p class="alerta alerta-erro" data-biblia-erro hidden></p>
        </article>
        <p class="suave creditos-biblia">Tradução do Pe. Manuel de Matos Soares (1956), a partir da Vulgata. Toque em um versículo para marcá-lo e copiar o link.</p>
    </div>
</section>
<?php require __DIR__ . '/includes/site_footer.php'; ?>
