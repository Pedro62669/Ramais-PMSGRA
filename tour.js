/* Orquestração do tour guiado do Ico para a Consulta de Ramais.
   Usa a API window.TourMascote (mascote.js). Este arquivo é específico deste site:
   define os passos, os balões de fala e o destaque de cada elemento. Os estilos do
   balão ficam aqui (injetados) para não poluir o mascote.css compartilhado. */
(function () {
    'use strict';

    const STORAGE_KEY = 'ramais_tour_visto_v1';

    const PASSOS = [
        {
            sel: '.tabs-navigation',
            titulo: 'Categorias de ramais',
            texto: 'Comece por aqui: alterne entre Ramais Externos, Centro Administrativo e Telefones Externos.',
        },
        {
            sel: '#busca-input',
            titulo: 'Busca rápida',
            texto: 'Digite um nome, número de ramal ou descrição para filtrar a lista na hora.',
        },
        {
            sel: '#setor-select',
            titulo: 'Filtrar por setor',
            texto: 'Quer afunilar? Escolha um setor específico para ver só o que interessa.',
        },
        {
            sel: '.results-container',
            titulo: 'Resultados',
            texto: 'Os ramais aparecem aqui. Clique nos títulos das colunas para ordenar, e use "Copiar" ou "Ligar".',
        },
        {
            sel: '#abrir-sugestao',
            titulo: 'Sugestões',
            texto: 'Achou algo pra melhorar? Manda pra gente por aqui. É rapidinho!',
        },
        {
            sel: '.header-actions a[href*="emails"]',
            titulo: 'E-mails',
            texto: 'Precisa dos e-mails dos setores? É só clicar aqui, ao lado das Sugestões.',
        },
    ];

    let balaoEl, tituloEl, textoEl, progressoEl, btnProximo, btnPular;
    let backdropEl, introEl, menuEl;
    let elDestacado = null;
    let emAndamento = false;

    function injetarEstilos() {
        if (document.getElementById('tour-estilos')) return;
        const style = document.createElement('style');
        style.id = 'tour-estilos';
        style.textContent =
            '#tour-balao{position:fixed;left:0;top:0;max-width:280px;background:#fff;color:#1f2937;' +
            'border-radius:14px;padding:16px 18px;box-shadow:0 12px 32px rgba(0,0,0,.22);' +
            'z-index:100003;opacity:0;transform:translateY(8px) scale(.96);pointer-events:none;' +
            'transition:opacity .2s ease,transform .2s ease;font-family:Inter,system-ui,sans-serif;' +
            'border:2px solid #2e7d32;}' +
            '#tour-balao.mostrar{opacity:1;transform:translateY(0) scale(1);pointer-events:auto;}' +
            '#tour-balao::before{content:"";position:absolute;top:24px;width:0;height:0;' +
            'border-top:9px solid transparent;border-bottom:9px solid transparent;}' +
            '#tour-balao[data-arrow="left"]::before{left:-11px;border-right:11px solid #2e7d32;}' +
            '#tour-balao[data-arrow="right"]::before{right:-11px;border-left:11px solid #2e7d32;}' +
            '#tour-balao h4{margin:0 0 6px;font-size:15px;font-weight:700;color:#2e7d32;}' +
            '#tour-balao p{margin:0 0 14px;font-size:13.5px;line-height:1.5;color:#374151;}' +
            '#tour-balao .tour-balao-rodape{display:flex;align-items:center;justify-content:space-between;gap:12px;}' +
            '#tour-balao .tour-progresso{font-size:12px;color:#9ca3af;font-weight:600;}' +
            '#tour-balao .tour-acoes{display:flex;align-items:center;gap:10px;}' +
            '#tour-balao .tour-pular{background:none;border:none;color:#9ca3af;font-size:12.5px;' +
            'cursor:pointer;padding:4px;text-decoration:underline;}' +
            '#tour-balao .tour-proximo{background:linear-gradient(135deg,#2e7d32 0%,#1b5e20 100%);' +
            'color:#fff;border:none;padding:8px 16px;font-size:13px;font-weight:600;border-radius:8px;' +
            'cursor:pointer;box-shadow:0 2px 8px rgba(46,125,50,.3);}' +
            '#tour-balao .tour-proximo:hover{filter:brightness(1.06);}' +
            /* Fundo escurecido durante a apresentação do Gonçalinho */
            '#tour-backdrop{position:fixed;inset:0;background:rgba(15,23,42,.5);' +
            'z-index:100000;opacity:0;pointer-events:none;transition:opacity .35s ease;}' +
            '#tour-backdrop.mostrar{opacity:1;pointer-events:auto;}' +
            /* Efeito de "crescer" do Ico (escala no SVG, que a animação não sobrescreve) */
            '#tour-mascote svg{transition:transform .5s cubic-bezier(.34,1.56,.64,1);}' +
            '#tour-mascote.goncalinho-grande svg{transform:scale(1.75);}' +
            /* No modo "seguir", o Ico precisa ser clicável (o mascote.css só deixa no mini) */
            '#tour-mascote.tour-seguindo{pointer-events:auto;cursor:pointer;}' +
            /* Menu de opções ao clicar no Ico mini */
            '#tour-menu{position:fixed;z-index:100005;background:#fff;border:2px solid #2e7d32;' +
            'border-radius:12px;box-shadow:0 12px 32px rgba(0,0,0,.22);padding:6px;display:none;' +
            'flex-direction:column;gap:2px;font-family:Inter,system-ui,sans-serif;' +
            'opacity:0;transform:translateY(6px);transition:opacity .15s ease,transform .15s ease;}' +
            '#tour-menu.mostrar{display:flex;opacity:1;transform:translateY(0);}' +
            '#tour-menu button{background:none;border:none;text-align:left;padding:9px 14px;' +
            'border-radius:8px;font-size:13.5px;font-weight:600;color:#2d3748;cursor:pointer;' +
            'white-space:nowrap;font-family:inherit;}' +
            '#tour-menu button:hover{background:#edf2f7;color:#2e7d32;}' +
            /* Balão de apresentação, centralizado */
            '#tour-intro{position:fixed;left:50%;transform:translateX(-50%);max-width:360px;' +
            'width:calc(100% - 40px);background:#fff;color:#1f2937;border-radius:16px;' +
            'padding:22px 24px;box-shadow:0 18px 48px rgba(0,0,0,.3);z-index:100003;' +
            'text-align:center;opacity:0;pointer-events:none;transition:opacity .25s ease;' +
            'font-family:Inter,system-ui,sans-serif;border:2px solid #2e7d32;}' +
            '#tour-intro.mostrar{opacity:1;pointer-events:auto;}' +
            '#tour-intro h3{margin:0 0 10px;font-size:19px;font-weight:700;color:#2e7d32;}' +
            '#tour-intro p{margin:0 0 18px;font-size:14.5px;line-height:1.55;color:#374151;}' +
            '#tour-intro .tour-intro-acoes{display:flex;gap:10px;justify-content:center;align-items:center;}' +
            '#tour-intro .tour-comecar{background:linear-gradient(135deg,#2e7d32 0%,#1b5e20 100%);' +
            'color:#fff;border:none;padding:10px 22px;font-size:14px;font-weight:600;border-radius:9px;' +
            'cursor:pointer;box-shadow:0 2px 8px rgba(46,125,50,.3);}' +
            '#tour-intro .tour-comecar:hover{filter:brightness(1.06);}' +
            '#tour-intro .tour-pular-intro{background:none;border:none;color:#9ca3af;' +
            'font-size:13px;cursor:pointer;text-decoration:underline;padding:6px 10px;}' +
            '.tour-destaque{position:relative;z-index:100001;outline:3px solid #F9B216;' +
            'outline-offset:4px;border-radius:10px;box-shadow:0 0 0 9999px rgba(15,23,42,.35);' +
            'transition:outline-color .2s ease;}';
        document.head.appendChild(style);
    }

    function criarBalao() {
        if (balaoEl) return;
        balaoEl = document.createElement('div');
        balaoEl.id = 'tour-balao';
        balaoEl.setAttribute('data-arrow', 'left');
        balaoEl.innerHTML =
            '<h4></h4>' +
            '<p></p>' +
            '<div class="tour-balao-rodape">' +
                '<span class="tour-progresso"></span>' +
                '<div class="tour-acoes">' +
                    '<button type="button" class="tour-pular">Pular</button>' +
                    '<button type="button" class="tour-proximo">Próximo</button>' +
                '</div>' +
            '</div>';
        document.body.appendChild(balaoEl);

        tituloEl = balaoEl.querySelector('h4');
        textoEl = balaoEl.querySelector('p');
        progressoEl = balaoEl.querySelector('.tour-progresso');
        btnProximo = balaoEl.querySelector('.tour-proximo');
        btnPular = balaoEl.querySelector('.tour-pular');
    }

    function criarApresentacao() {
        if (backdropEl) return;

        backdropEl = document.createElement('div');
        backdropEl.id = 'tour-backdrop';
        document.body.appendChild(backdropEl);

        introEl = document.createElement('div');
        introEl.id = 'tour-intro';
        introEl.innerHTML =
            '<h3>Oi! Eu sou o Gonçalinho 👋</h3>' +
            '<p>Bem-vindo à <strong>Consulta de Ramais</strong> da Prefeitura de São Gonçalo do Rio Abaixo! ' +
            'Aqui você encontra rapidinho os telefones e ramais de cada setor. ' +
            'Bora que eu te mostro como funciona?</p>' +
            '<div class="tour-intro-acoes">' +
                '<button type="button" class="tour-pular-intro">Agora não</button>' +
                '<button type="button" class="tour-comecar">Começar tour</button>' +
            '</div>';
        document.body.appendChild(introEl);
    }

    function crescerIco(grande) {
        const el = document.getElementById('tour-mascote');
        if (el) el.classList.toggle('goncalinho-grande', !!grande);
    }

    // Abertura: fundo escurece, Ico vai ao centro e cresce, se apresenta como
    // Gonçalinho e explica o sistema. Ao concluir, tudo volta ao normal e segue o tour.
    function apresentar(aoConcluir) {
        criarApresentacao();

        requestAnimationFrame(function () {
            backdropEl.classList.add('mostrar');
        });
        crescerIco(true);

        const W = window.innerWidth;
        const H = window.innerHeight;
        const cx = Math.round(W / 2);
        const cy = Math.max(150, Math.round(H * 0.40));

        window.TourMascote.irPara(cx, cy, function () {
            const alt = introEl.offsetHeight || 200;
            let top = cy + 110;
            top = Math.min(top, H - alt - 16);
            top = Math.max(16, top);
            introEl.style.top = top + 'px';

            const comecar = introEl.querySelector('.tour-comecar');
            const pular = introEl.querySelector('.tour-pular-intro');
            comecar.onclick = function () {
                fecharApresentacao();
                aoConcluir();
            };
            pular.onclick = function () {
                fecharApresentacao();
                finalizar();
            };

            introEl.classList.add('mostrar');
        });
    }

    function fecharApresentacao() {
        crescerIco(false);
        if (introEl) introEl.classList.remove('mostrar');
        if (backdropEl) backdropEl.classList.remove('mostrar');
    }

    function limparDestaque() {
        if (elDestacado) {
            elDestacado.classList.remove('tour-destaque');
            elDestacado = null;
        }
    }

    function esconderBalao() {
        if (balaoEl) balaoEl.classList.remove('mostrar');
    }

    // Escolhe onde o Ico deve parar em relação ao elemento destacado. Prefere parar
    // logo ABAIXO (neste layout quase sempre há espaço), depois ACIMA e, por último,
    // ao LADO. Assim ele nunca fica espremido num canto — ex.: o botão "Sugestões"
    // no canto superior direito, que era o caso problemático.
    function calcularAlvo(r) {
        const M = 90;    // margem p/ o Ico (~100px) caber inteiro na tela
        const GAP = 44;  // distância entre o Ico e o elemento
        const W = window.innerWidth;
        const H = window.innerHeight;
        const cx = r.left + r.width / 2;
        const cy = r.top + r.height / 2;

        let x, y;
        if (H - r.bottom >= 150) {
            x = cx;
            y = r.bottom + GAP;
        } else if (r.top >= 150) {
            x = cx;
            y = r.top - GAP;
        } else {
            y = cy;
            x = (r.left >= W / 2) ? r.left - GAP : r.right + GAP;
        }

        return {
            x: Math.max(M, Math.min(W - M, x)),
            y: Math.max(M, Math.min(H - M, y)),
        };
    }

    function posicionarBalao(alvoX, alvoY) {
        balaoEl.style.visibility = 'hidden';
        balaoEl.classList.add('mostrar');
        const larg = balaoEl.offsetWidth;
        const alt = balaoEl.offsetHeight;
        balaoEl.classList.remove('mostrar');

        let left = alvoX + 60;
        let arrow = 'left';
        if (left + larg > window.innerWidth - 12) {
            left = alvoX - 60 - larg;
            arrow = 'right';
        }
        left = Math.max(12, left);
        let top = Math.max(12, Math.min(window.innerHeight - alt - 12, alvoY - 32));

        balaoEl.style.left = left + 'px';
        balaoEl.style.top = top + 'px';
        balaoEl.setAttribute('data-arrow', arrow);
        balaoEl.style.visibility = '';
    }

    function mostrarPasso(indice) {
        esconderBalao();
        limparDestaque();

        if (indice >= PASSOS.length) {
            finalizar();
            return;
        }

        const passo = PASSOS[indice];
        const el = document.querySelector(passo.sel);
        if (!el) {
            mostrarPasso(indice + 1);
            return;
        }

        el.scrollIntoView({ behavior: 'smooth', block: 'center' });

        setTimeout(function () {
            const r = el.getBoundingClientRect();
            el.classList.add('tour-destaque');
            elDestacado = el;

            const alvo = calcularAlvo(r);
            const alvoX = alvo.x;
            const alvoY = alvo.y;

            window.TourMascote.irPara(alvoX, alvoY, function () {
                tituloEl.textContent = passo.titulo;
                textoEl.textContent = passo.texto;
                progressoEl.textContent = (indice + 1) + ' de ' + PASSOS.length;
                btnProximo.textContent = (indice + 1 >= PASSOS.length) ? 'Concluir' : 'Próximo';

                btnProximo.onclick = function () { mostrarPasso(indice + 1); };
                btnPular.onclick = finalizar;

                posicionarBalao(alvoX, alvoY);
                balaoEl.classList.add('mostrar');
            });
        }, 420);
    }

    function iniciarTour() {
        if (emAndamento) return;
        emAndamento = true;
        injetarEstilos();
        criarBalao();
        window.TourMascote.mostrar();
        apresentar(function () {
            mostrarPasso(0);
        });
    }

    function finalizar() {
        emAndamento = false;
        fecharMenu();
        fecharApresentacao();
        esconderBalao();
        limparDestaque();
        try { localStorage.setItem(STORAGE_KEY, '1'); } catch (e) {}
        window.TourMascote.virarMini();
    }

    function entrarSeguir() {
        window.TourMascote.seguirMouse();
        const el = document.getElementById('tour-mascote');
        if (el) el.classList.add('tour-seguindo');
    }

    function sairSeguir() {
        const el = document.getElementById('tour-mascote');
        if (el) el.classList.remove('tour-seguindo');
        window.TourMascote.virarMini();
    }

    function criarMenu() {
        if (menuEl) return;
        menuEl = document.createElement('div');
        menuEl.id = 'tour-menu';
        menuEl.innerHTML =
            '<button type="button" data-acao="tour">▶ Reiniciar tour</button>' +
            '<button type="button" data-acao="seguir">🖱️ Seguir o mouse</button>';
        document.body.appendChild(menuEl);

        menuEl.addEventListener('click', function (e) {
            const btn = e.target.closest('button');
            if (!btn) return;
            fecharMenu();
            if (btn.getAttribute('data-acao') === 'tour') {
                iniciarTour();
            } else {
                entrarSeguir();
            }
        });
    }

    function abrirMenu() {
        criarMenu();
        const c = window.TourMascote.posicaoCorner();
        menuEl.style.right = Math.max(12, window.innerWidth - (c.x + 28)) + 'px';
        menuEl.style.bottom = (window.innerHeight - c.y + 40) + 'px';
        menuEl.classList.add('mostrar');
    }

    function fecharMenu() {
        if (menuEl) menuEl.classList.remove('mostrar');
    }

    function menuAberto() {
        return menuEl && menuEl.classList.contains('mostrar');
    }

    // Clique no Ico: no modo mini abre um menu (Reiniciar tour / Seguir);
    // no modo seguir, volta pro canto.
    function aoClicarIco() {
        if (emAndamento) return; // durante o tour, ignora
        if (window.TourMascote.estaMini()) {
            menuAberto() ? fecharMenu() : abrirMenu();
        } else {
            sairSeguir();
        }
    }

    function init() {
        if (!window.TourMascote) return;

        injetarEstilos();
        window.TourMascote.aoClicar(aoClicarIco);

        // Fecha o menu ao clicar fora dele (e fora do próprio Ico)
        document.addEventListener('click', function (e) {
            if (!menuAberto()) return;
            const ico = document.getElementById('tour-mascote');
            if (menuEl.contains(e.target)) return;
            if (ico && ico.contains(e.target)) return;
            fecharMenu();
        });

        let jaViu = false;
        try { jaViu = !!localStorage.getItem(STORAGE_KEY); } catch (e) {}

        if (jaViu) {
            window.TourMascote.mostrar();
            window.TourMascote.virarMini();
        } else {
            iniciarTour();
        }
    }

    if (document.readyState === 'complete') {
        init();
    } else {
        window.addEventListener('load', init);
    }
})();
