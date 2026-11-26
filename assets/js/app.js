// Paróquia Santo Antônio de Pádua - comportamentos da interface

function $(sel, raiz) { return (raiz || document).querySelector(sel); }
function $$(sel, raiz) { return Array.prototype.slice.call((raiz || document).querySelectorAll(sel)); }
function semAcento(t) { return (t || '').normalize('NFD').replace(/[̀-ͯ]/g, ''); }
function lerLocal(chave) { try { return localStorage.getItem(chave); } catch (e) { return null; } }
function gravarLocal(chave, valor) { try { localStorage.setItem(chave, valor); } catch (e) { /* sem armazenamento */ } }

// Menu no celular
$$('[data-toggle-menu]').forEach(function (botao) {
    botao.addEventListener('click', function () { $('#menu-lateral').classList.toggle('aberto'); });
});

// Converte "1.234,56" em número
function lerValor(texto) {
    texto = (texto || '').replace(/[R$\s]/g, '');
    if (texto.indexOf(',') >= 0) texto = texto.replace(/\./g, '').replace(',', '.');
    return parseFloat(texto) || 0;
}

// Máscara de telefone (47) 99999-9999
$$('[data-mascara-telefone]').forEach(function (campo) {
    campo.addEventListener('input', function () {
        var d = campo.value.replace(/\D/g, '').slice(0, 11);
        if (d.length > 10) d = d.replace(/(\d{2})(\d{5})(\d{0,4})/, '($1) $2-$3');
        else if (d.length > 6) d = d.replace(/(\d{2})(\d{4})(\d{0,4})/, '($1) $2-$3');
        else if (d.length > 2) d = d.replace(/(\d{2})(\d+)/, '($1) $2');
        campo.value = d;
    });
});

// Horários: filtro por tipo (Missa, Confissões...)
var filtroHorarios = $('[data-filtro-horarios]');
if (filtroHorarios) {
    filtroHorarios.addEventListener('click', function (ev) {
        var botao = ev.target.closest('[data-tipo]');
        if (!botao) return;
        $$('.chip', filtroHorarios).forEach(function (c) { c.classList.toggle('ativo', c === botao); });
        var tipo = botao.dataset.tipo;
        $$('.horario-item').forEach(function (item) { item.hidden = tipo !== '' && item.dataset.tipo !== tipo; });
        $$('.dia-coluna').forEach(function (col) {
            col.classList.toggle('filtrado-vazio', !$$('.horario-item', col).some(function (i) { return !i.hidden; }));
        });
    });
}

// Santos: busca
var buscaSantos = $('[data-busca-santos]');
if (buscaSantos) {
    buscaSantos.addEventListener('input', function () {
        var termo = semAcento(buscaSantos.value.trim().toLowerCase());
        var algum = false;
        $$('[data-grupo]').forEach(function (grupo) {
            var visiveis = 0;
            $$('.santo-card', grupo).forEach(function (card) {
                var ok = termo === '' || semAcento(card.dataset.texto).indexOf(termo) >= 0;
                card.hidden = !ok;
                if (ok) visiveis++;
            });
            grupo.hidden = visiveis === 0;
            if (visiveis) algum = true;
        });
        $('[data-sem-resultado]').hidden = algum;
    });
}

// Movimentos: "Quero participar" já escolhe o movimento no formulário
$$('[data-escolher-movimento]').forEach(function (link) {
    link.addEventListener('click', function (ev) {
        var sel = $('[data-select-movimento]');
        if (!sel) return;
        ev.preventDefault();
        sel.value = link.dataset.escolherMovimento;
        $('#inscricao').scrollIntoView({ behavior: 'smooth' });
        setTimeout(function () { var n = $('#inscricao input[name=nome]'); if (n) n.focus({ preventScroll: true }); }, 400);
    });
});

// Financeiro: troca as categorias conforme entrada/saída
var formLanc = $('[data-lancamento]');
if (formLanc) {
    var selCat = $('[data-categorias]', formLanc);
    $$('input[name=tipo]', formLanc).forEach(function (radio) {
        radio.addEventListener('change', function () {
            selCat.innerHTML = '';
            JSON.parse(selCat.dataset[radio.value]).forEach(function (nome) {
                var op = document.createElement('option');
                op.textContent = nome;
                selCat.appendChild(op);
            });
        });
    });
}

// ---------------------------------------------------------------
// PIX: gera o código "copia e cola" (padrão BR Code do Banco Central) e o QR Code
// ---------------------------------------------------------------
var pixCartao = $('[data-pix]');
if (pixCartao && pixCartao.dataset.chave) {
    var campoValor = $('[data-pix-valor]', pixCartao);
    var campoCodigo = $('[data-pix-codigo]', pixCartao);
    var areaQr = $('[data-pix-qr]', pixCartao);

    var tlv = function (id, valor) { return id + String(valor.length).padStart(2, '0') + valor; };
    var limpar = function (texto, max) {
        return semAcento(texto).replace(/[^A-Za-z0-9 ]/g, '').trim().toUpperCase().slice(0, max);
    };
    var crc16 = function (texto) {
        var crc = 0xFFFF;
        for (var i = 0; i < texto.length; i++) {
            crc ^= texto.charCodeAt(i) << 8;
            for (var b = 0; b < 8; b++) crc = (crc & 0x8000) ? ((crc << 1) ^ 0x1021) : (crc << 1);
            crc &= 0xFFFF;
        }
        return crc.toString(16).toUpperCase().padStart(4, '0');
    };

    var gerarPix = function () {
        var finalidade = ($('input[name=finalidade]:checked', pixCartao) || {}).value || 'Dízimo';
        var valor = lerValor(campoValor.value);
        var conta = tlv('00', 'br.gov.bcb.pix') + tlv('01', pixCartao.dataset.chave.trim());
        var descricao = limpar(finalidade, 40);
        if ((conta + tlv('02', descricao)).length <= 99) conta += tlv('02', descricao);
        var codigo = tlv('00', '01') + tlv('26', conta) + tlv('52', '0000') + tlv('53', '986')
            + (valor > 0 ? tlv('54', valor.toFixed(2)) : '')
            + tlv('58', 'BR') + tlv('59', limpar(pixCartao.dataset.nome, 25) || 'PAROQUIA')
            + tlv('60', limpar(pixCartao.dataset.cidade, 15) || 'BRASIL') + tlv('62', tlv('05', '***')) + '6304';
        codigo += crc16(codigo);
        campoCodigo.value = codigo;
        areaQr.innerHTML = '';
        if (window.QRCode) {
            new QRCode(areaQr, { text: codigo, width: 220, height: 220, correctLevel: QRCode.CorrectLevel.M });
        } else {
            areaQr.innerHTML = '<p class="suave">Use o código copia e cola abaixo.</p>';
        }
    };

    campoValor.addEventListener('input', gerarPix);
    $$('input[name=finalidade]', pixCartao).forEach(function (r) { r.addEventListener('change', gerarPix); });
    $$('[data-pix-rapido]', pixCartao).forEach(function (b) {
        b.addEventListener('click', function () { campoValor.value = b.dataset.pixRapido + ',00'; gerarPix(); });
    });
    $('[data-pix-copiar]', pixCartao).addEventListener('click', function (ev) {
        var botao = ev.currentTarget;
        var ok = function () { botao.textContent = 'Código copiado ✓'; setTimeout(function () { botao.textContent = 'Copiar código PIX'; }, 2500); };
        campoCodigo.select();
        if (navigator.clipboard) navigator.clipboard.writeText(campoCodigo.value).then(ok, function () { document.execCommand('copy'); ok(); });
        else { document.execCommand('copy'); ok(); }
    });
    window.addEventListener('load', gerarPix);
}

// ---------------------------------------------------------------
// Bíblia online (arquivos em assets/biblia: livros.json + um arquivo por livro)
// ---------------------------------------------------------------
var biblia = $('[data-biblia]');
if (biblia) {
    var selLivro = $('[data-biblia-livro]', biblia);
    var selCap = $('[data-biblia-capitulo]', biblia);
    var tituloCap = $('[data-biblia-titulo]', biblia);
    var areaVersos = $('[data-biblia-versos]', biblia);
    var areaErro = $('[data-biblia-erro]', biblia);
    var livros = [];
    var cache = {};
    var atual = { livro: 50, cap: 1, verso: 0 };
    var fonte = parseFloat(lerLocal('biblia-fonte')) || 1.1;

    var aplicarFonte = function () { areaVersos.style.fontSize = fonte + 'rem'; };
    aplicarFonte();
    $$('[data-fonte]', biblia).forEach(function (b) {
        b.addEventListener('click', function () {
            fonte = Math.min(1.8, Math.max(0.9, fonte + parseInt(b.dataset.fonte, 10) * 0.1));
            gravarLocal('biblia-fonte', fonte.toFixed(1));
            aplicarFonte();
        });
    });

    var erro = function (msg) { areaErro.textContent = msg; areaErro.hidden = !msg; };

    var carregarLivro = function (id) {
        if (cache[id]) return Promise.resolve(cache[id]);
        return fetch('assets/biblia/' + String(id).padStart(2, '0') + '.json')
            .then(function (r) { if (!r.ok) throw new Error(); return r.json(); })
            .then(function (dados) { cache[id] = dados; return dados; });
    };

    var preencherCapitulos = function () {
        var livro = livros[atual.livro - 1];
        selCap.innerHTML = '';
        for (var c = 1; c <= livro.capitulos; c++) {
            var op = document.createElement('option');
            op.value = c; op.textContent = c;
            selCap.appendChild(op);
        }
    };

    var abrir = function (livro, cap, verso, atualizarHash) {
        livro = Math.min(Math.max(1, livro), livros.length);
        cap = Math.min(Math.max(1, cap), livros[livro - 1].capitulos);
        var mudouLivro = livro !== atual.livro || !selCap.options.length;
        atual = { livro: livro, cap: cap, verso: verso || 0 };
        selLivro.value = livro;
        if (mudouLivro) preencherCapitulos();
        selCap.value = cap;
        tituloCap.textContent = livros[livro - 1].nome + ' ' + cap;
        erro('');
        if (atualizarHash !== false) history.replaceState(null, '', '#' + livro + '/' + cap + (verso ? '/' + verso : ''));
        carregarLivro(livro).then(function (dados) {
            areaVersos.innerHTML = '';
            (dados[cap - 1] || []).forEach(function (v) {
                var p = document.createElement('p');
                p.id = 'v' + v[0];
                p.className = 'verso' + (v[0] === atual.verso ? ' marcado' : '');
                var num = document.createElement('sup');
                num.textContent = v[0];
                p.appendChild(num);
                p.appendChild(document.createTextNode(' ' + v[1]));
                areaVersos.appendChild(p);
            });
            if (atual.verso) {
                var alvo = $('#v' + atual.verso, areaVersos);
                if (alvo) alvo.scrollIntoView({ block: 'center' });
            }
        }).catch(function () { erro('Não foi possível carregar este livro. Tente novamente.'); });
    };

    var lerHash = function () {
        var p = location.hash.replace('#', '').split('/').map(function (n) { return parseInt(n, 10) || 0; });
        return p[0] ? p : null;
    };

    // Entende referências como "Jo 3,16", "1 Cor 13", "Salmos 23:1", "lucas 15"
    var interpretar = function (texto) {
        var m = texto.trim().toLowerCase().match(/^([1-3]?\s*[^\d,:.]+?)\s*(\d+)?(?:\s*[,:.]\s*(\d+))?$/);
        if (!m) return null;
        var nome = m[1].replace(/\s+/g, '');
        var nomeSem = semAcento(nome);
        var achar = function (teste) { for (var i = 0; i < livros.length; i++) if (teste(livros[i])) return livros[i]; return null; };
        var livro = achar(function (l) { return l.sigla.toLowerCase() === nome; })
            || achar(function (l) { return semAcento(l.sigla.toLowerCase()) === nomeSem; })
            || achar(function (l) { return semAcento(l.nome.toLowerCase().replace(/\s+/g, '')) === nomeSem; })
            || achar(function (l) { return semAcento(l.nome.toLowerCase().replace(/\s+/g, '')).indexOf(nomeSem) === 0; });
        if (!livro) return null;
        return [livro.id, parseInt(m[2], 10) || 1, parseInt(m[3], 10) || 0];
    };

    fetch('assets/biblia/livros.json').then(function (r) { return r.json(); }).then(function (lista) {
        livros = lista;
        ['AT', 'NT'].forEach(function (t) {
            var grupo = document.createElement('optgroup');
            grupo.label = t === 'AT' ? 'Antigo Testamento' : 'Novo Testamento';
            livros.filter(function (l) { return l.testamento === t; }).forEach(function (l) {
                var op = document.createElement('option');
                op.value = l.id; op.textContent = l.nome;
                grupo.appendChild(op);
            });
            selLivro.appendChild(grupo);
        });
        var h = lerHash();
        atual.livro = 0;
        abrir(h ? h[0] : 50, h ? h[1] || 1 : 1, h ? h[2] : 0, !!h);
    }).catch(function () { erro('Não foi possível carregar a Bíblia.'); });

    selLivro.addEventListener('change', function () { abrir(parseInt(selLivro.value, 10), 1); });
    selCap.addEventListener('change', function () { abrir(atual.livro, parseInt(selCap.value, 10)); });
    $('[data-biblia-anterior]', biblia).addEventListener('click', function () {
        if (atual.cap > 1) abrir(atual.livro, atual.cap - 1);
        else if (atual.livro > 1) abrir(atual.livro - 1, livros[atual.livro - 2].capitulos);
        window.scrollTo({ top: biblia.offsetTop, behavior: 'smooth' });
    });
    $('[data-biblia-proximo]', biblia).addEventListener('click', function () {
        if (atual.cap < livros[atual.livro - 1].capitulos) abrir(atual.livro, atual.cap + 1);
        else if (atual.livro < livros.length) abrir(atual.livro + 1, 1);
        window.scrollTo({ top: biblia.offsetTop, behavior: 'smooth' });
    });
    $('[data-biblia-busca]', biblia).addEventListener('submit', function (ev) {
        ev.preventDefault();
        var ref = interpretar($('[data-biblia-ref]', biblia).value);
        if (ref) abrir(ref[0], ref[1], ref[2]);
        else erro('Referência não encontrada. Tente algo como "Jo 3,16" ou "Salmos 23".');
    });
    areaVersos.addEventListener('click', function (ev) {
        var p = ev.target.closest('.verso');
        if (!p) return;
        var n = parseInt(p.id.slice(1), 10);
        var marcar = !p.classList.contains('marcado');
        $$('.verso.marcado', areaVersos).forEach(function (x) { x.classList.remove('marcado'); });
        p.classList.toggle('marcado', marcar);
        atual.verso = marcar ? n : 0;
        history.replaceState(null, '', '#' + atual.livro + '/' + atual.cap + (marcar ? '/' + n : ''));
        if (marcar && navigator.clipboard) {
            navigator.clipboard.writeText(livros[atual.livro - 1].nome + ' ' + atual.cap + ',' + n + ' - ' + p.textContent.replace(/^\d+\s/, '') + '\n' + location.href).catch(function () {});
        }
    });
    window.addEventListener('hashchange', function () {
        var h = lerHash();
        if (h && (h[0] !== atual.livro || h[1] !== atual.cap)) abrir(h[0], h[1] || 1, h[2], false);
    });
}
