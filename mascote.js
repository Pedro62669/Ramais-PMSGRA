/* Mascote "Ico" do tour guiado — adaptado do protótipo em ~/Downloads/index.html.
   Catavento 100% SVG em código. Físicas de mola: persegue um alvo (mouse, ponto
   do tour ou canto da tela conforme o modo), olhos sempre seguem o cursor real. */
   (function () {
    const SVGNS = 'http://www.w3.org/2000/svg';

    const PETALAS_DEF = [
        { grande: false, cor: '#F9B216' },
        { grande: true,  cor: '#36B24B' },
        { grande: false, cor: '#02693B' },
        { grande: true,  cor: '#F4821D' },
        { grande: false, cor: '#F9B216' },
        { grande: true,  cor: '#36B24B' },
        { grande: false, cor: '#02693B' },
        { grande: true,  cor: '#F4821D' },
    ];
    const ORDEM_DESENHO = [0, 2, 4, 6, 1, 3, 5, 7];

    let elMascote, elCatavento, elSombra;
    let olhos, pupilas, brilhos, palpebras, boca;
    const petalas = [];

    let TAM = 100; // tamanho atual (px), varia entre modo "grande" e "mini"
    const TAM_GRANDE = 100;
    const TAM_MINI = 56;

    // modo: 'livre' (persegue o mouse real), 'guiando' (persegue alvoFixo), 'mini' (canto fixo)
    let modo = 'livre';
    let alvoFixo = { x: 0, y: 0 };
    let mouseX = window.innerWidth / 2;
    let mouseY = window.innerHeight / 2;

    let x = window.innerWidth / 2;
    let y = window.innerHeight / 2;
    let vx = 0, vy = 0;
    let spin = 0, spinVel = 0;
    let tempoParado = 0;
    let piscando = 0;
    let chegouCallback = null;
    let jaChegou = false;

    // Rastro de triângulos atrás do Ico quando ele corre (qualquer modo, exceto mini)
    const CORES_RASTRO = ['#36B24B', '#F4821D'];
    const LIMIAR_RASTRO = 1.2;
    let particulas = [];

    function criarParticula(posX, posY, velX, velY) {
        const p = document.createElement('div');
        p.className = 'tour-rastro-triangulo';
        p.style.borderBottomColor = CORES_RASTRO[Math.floor(Math.random() * CORES_RASTRO.length)];
        document.body.appendChild(p);

        particulas.push({
            el: p,
            x: posX - velX * 1.5,
            y: posY - velY * 1.5,
            vx: -velX * 0.12 + (Math.random() * 1.6 - 0.8),
            vy: -velY * 0.12 + (Math.random() * 1.6 - 0.8),
            vida: 1.0,
            escala: Math.random() * 0.5 + 0.7,
            rotacao: Math.random() * 360,
        });
    }

    function atualizarRastro() {
        for (let i = particulas.length - 1; i >= 0; i--) {
            const p = particulas[i];
            p.x += p.vx;
            p.y += p.vy;
            p.vida -= 0.045;
            p.rotacao += 3;

            if (p.vida <= 0) {
                p.el.remove();
                particulas.splice(i, 1);
            } else {
                p.el.style.transform = 'translate(' + (p.x - 8) + 'px, ' + (p.y - 7) + 'px) rotate(' + p.rotacao + 'deg) scale(' + (p.vida * p.escala) + ')';
                p.el.style.opacity = p.vida;
            }
        }
    }

    function montarDOM() {
        if (elMascote) return;

        elMascote = document.createElement('div');
        elMascote.id = 'tour-mascote';
        elMascote.innerHTML =
            '<svg viewBox="-70 -70 140 140">' +
                '<ellipse id="tour-sombra" cx="0" cy="78" rx="34" ry="7" fill="rgba(0,0,0,.12)"/>' +
                '<g id="tour-catavento"></g>' +
                '<g id="tour-rosto">' +
                    '<g class="tour-olho" transform="translate(-20,-4)">' +
                        '<circle r="14" fill="#fff" stroke="#333" stroke-width="3"/>' +
                        '<circle class="tour-pupila" r="5.5" fill="#222"/>' +
                        '<circle class="tour-brilho" r="2" fill="#fff"/>' +
                        '<path class="tour-palpebra" d="M -14 0 A 14 14 0 0 1 14 0 L -14 0 Z" fill="#333" transform="scale(1,0)"/>' +
                    '</g>' +
                    '<g class="tour-olho" transform="translate(20,-4)">' +
                        '<circle r="14" fill="#fff" stroke="#333" stroke-width="3"/>' +
                        '<circle class="tour-pupila" r="5.5" fill="#222"/>' +
                        '<circle class="tour-brilho" r="2" fill="#fff"/>' +
                        '<path class="tour-palpebra" d="M -14 0 A 14 14 0 0 1 14 0 L -14 0 Z" fill="#333" transform="scale(1,0)"/>' +
                    '</g>' +
                    '<path id="tour-boca" d="M -8 16 Q 0 24 8 16" fill="none" stroke="#333" stroke-width="3" stroke-linecap="round"/>' +
                '</g>' +
            '</svg>';
        document.body.appendChild(elMascote);

        elCatavento = elMascote.querySelector('#tour-catavento');
        elSombra = elMascote.querySelector('#tour-sombra');
        olhos = elMascote.querySelectorAll('.tour-olho');
        pupilas = elMascote.querySelectorAll('.tour-pupila');
        brilhos = elMascote.querySelectorAll('.tour-brilho');
        palpebras = elMascote.querySelectorAll('.tour-palpebra');
        boca = elMascote.querySelector('#tour-boca');

        for (const i of ORDEM_DESENHO) {
            const def = PETALAS_DEF[i];
            const g = document.createElementNS(SVGNS, 'g');
            const p = document.createElementNS(SVGNS, 'path');
            p.setAttribute('d', def.grande
                ? 'M 6 -62 L 20 -25 L -20 -29 Z'
                : 'M 4 -46 L 14 -21 L -14 -24 Z');
            p.setAttribute('fill', '#fff');
            p.setAttribute('stroke', def.cor);
            p.setAttribute('stroke-width', def.grande ? 10 : 8.5);
            p.setAttribute('stroke-linejoin', 'round');
            g.appendChild(p);
            elCatavento.appendChild(g);
            petalas[i] = { g, ang: i * 45 };
        }

        document.addEventListener('mousemove', function (e) {
            mouseX = e.clientX;
            mouseY = e.clientY;
        });

        requestAnimationFrame(animar);
    }

    function cantoMini() {
        return {
            x: window.innerWidth - TAM_MINI / 2 - 28,
            y: window.innerHeight - TAM_MINI / 2 - 28,
        };
    }

    function animar(t) {
        let destX, destY;
        if (modo === 'livre') {
            destX = mouseX;
            destY = mouseY;
        } else if (modo === 'mini') {
            const c = cantoMini();
            destX = c.x;
            destY = c.y;
        } else {
            destX = alvoFixo.x;
            destY = alvoFixo.y;
        }

        const distParada = modo === 'livre' ? 110 : 4;
        // No modo guiado/mini o Ico corre mais — o balão do tour só abre
        // quando ele chega, então a viagem precisa ser rápida.
        const rapido = modo !== 'livre';
        const forcaMax = rapido ? 1.5 : 0.9;
        const rampa = rapido ? 200 : 300;
        const atrito = rapido ? 0.87 : 0.90;

        const dx = destX - x;
        const dy = destY - y;
        const dist = Math.hypot(dx, dy);

        if (dist > distParada) {
            const forca = Math.min(forcaMax, (dist - distParada) / rampa) * forcaMax;
            vx += (dx / dist) * forca;
            vy += (dy / dist) * forca;
        }

        // Chegada: no modo guiado avisa um pouco antes de assentar,
        // para o balão abrir sem espera perceptível.
        const limiarChegada = modo === 'guiando' ? 36 : distParada;
        if (dist <= limiarChegada) {
            if (!jaChegou) {
                jaChegou = true;
                if (chegouCallback) { const cb = chegouCallback; chegouCallback = null; cb(); }
            }
        } else {
            jaChegou = false;
        }
        vx *= atrito;
        vy *= atrito;
        x += vx;
        y += vy;

        const metade = TAM / 2;
        x = Math.max(metade, Math.min(window.innerWidth - metade, x));
        y = Math.max(metade, Math.min(window.innerHeight - metade, y));

        const vel = Math.hypot(vx, vy);

        if (modo !== 'mini' && vel > LIMIAR_RASTRO && Math.random() < 0.6) {
            criarParticula(x, y, vx, vy);
        }
        atualizarRastro();

        spinVel += vel * 0.25;
        spinVel *= 0.93;
        spin += spinVel;
        elCatavento.setAttribute('transform', 'rotate(' + spin + ')');

        tempoParado = vel < 0.3 ? tempoParado + 1 : 0;
        const idle = Math.min(1, tempoParado / 60);
        const bob = Math.sin(t / 500) * 6 * idle;
        const respira = idle * Math.sin(t / 500) * 0.03;

        for (let i = 0; i < 8; i++) {
            const p = 1 + idle * 0.06 * Math.sin(t / 300 + i * 0.8);
            petalas[i].g.setAttribute('transform', 'rotate(' + petalas[i].ang + ') scale(' + p + ')');
        }

        const inclinacao = Math.max(-18, Math.min(18, vx * 2.2));
        const estica = Math.min(0.22, vel * 0.018);

        elMascote.style.transform =
            'translate(' + (x - metade) + 'px, ' + (y - metade + bob) + 'px) ' +
            'rotate(' + inclinacao + 'deg) ' +
            'scale(' + (1 + estica + respira) + ', ' + (1 - estica + respira) + ')';

        const s = 1 - bob / 40;
        elSombra.setAttribute('rx', 34 * s);
        elSombra.setAttribute('opacity', s);

        for (let i = 0; i < olhos.length; i++) {
            const r = olhos[i].getBoundingClientRect();
            const cx = r.left + r.width / 2;
            const cy = r.top + r.height / 2;
            const ang = Math.atan2(mouseY - cy, mouseX - cx);
            const d = Math.min(6, Math.hypot(mouseX - cx, mouseY - cy) / 15);
            const px = Math.cos(ang) * d;
            const py = Math.sin(ang) * d;
            pupilas[i].setAttribute('transform', 'translate(' + px + ',' + py + ')');
            brilhos[i].setAttribute('transform', 'translate(' + (px - 1.8) + ',' + (py - 1.8) + ')');
        }

        for (const p of palpebras) p.setAttribute('transform', 'scale(1,' + piscando + ')');

        const sorriso = 16 + Math.min(8, vel * 1.2);
        boca.setAttribute('d', 'M -8 16 Q 0 ' + (sorriso + 8) + ' 8 16');

        requestAnimationFrame(animar);
    }

    function piscar() {
        let fase = 0;
        const anim = setInterval(function () {
            fase += 0.34;
            piscando = fase <= 1 ? fase : Math.max(0, 2 - fase);
            if (fase >= 2) { piscando = 0; clearInterval(anim); }
        }, 25);
        setTimeout(piscar, 2500 + Math.random() * 3000);
    }

    function aplicarTamanho(novoTam) {
        TAM = novoTam;
        elMascote.style.width = TAM + 'px';
        elMascote.style.height = TAM + 'px';
    }

    window.TourMascote = {
        mostrar: function () {
            montarDOM();
            elMascote.classList.add('show');
            setTimeout(piscar, 2000);
        },

        seguirMouse: function () {
            modo = 'livre';
            aplicarTamanho(TAM_GRANDE);
            elMascote.classList.remove('mini');
        },

        irPara: function (px, py, callback) {
            modo = 'guiando';
            aplicarTamanho(TAM_GRANDE);
            alvoFixo = { x: px, y: py };
            jaChegou = false;
            chegouCallback = callback || null;
            elMascote.classList.remove('mini');
        },

        virarMini: function () {
            modo = 'mini';
            aplicarTamanho(TAM_MINI);
            chegouCallback = null;
            elMascote.classList.add('mini');
        },

        estaMini: function () {
            return modo === 'mini';
        },

        posicaoCorner: cantoMini,

        aoClicar: function (callback) {
            montarDOM();
            elMascote.addEventListener('click', callback);
        },
    };
})();
