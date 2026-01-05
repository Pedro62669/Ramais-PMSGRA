/**
 * Script principal para busca e interações
 * Otimizado para performance e reutilização
 */

(function() {
    'use strict';
    
    // Cache de elementos DOM
    const elements = {
        buscaInput: null,
        setorSelect: null,
        contentContainer: null
    };
    
    let debounceTimer = null;
    const DEBOUNCE_DELAY = 400;
    
    /**
     * Inicializa a aplicação quando o DOM estiver pronto
     */
    function init() {
        elements.buscaInput = document.getElementById('busca-input');
        elements.setorSelect = document.getElementById('setor-select');
        elements.contentContainer = document.getElementById('content-container');
        
        if (!elements.buscaInput || !elements.setorSelect || !elements.contentContainer) {
            console.warn('Elementos necessários não encontrados');
            return;
        }
        
        setupEventListeners();
        setupServiceWorker();
        setupPWAInstall();
        optimizeTouchDevices();
    }
    
    /**
     * Configura os event listeners
     */
    function setupEventListeners() {
        // Busca com debounce
        elements.buscaInput.addEventListener('keyup', handleSearchInput);
        
        // Busca ao mudar setor
        elements.setorSelect.addEventListener('change', () => performSearch(1));
        
        // Delegação de eventos para paginação e cópia
        document.addEventListener('click', handleDocumentClick);
        
        // Prevenir zoom em input no iOS
        if (/iPad|iPhone|iPod/.test(navigator.userAgent)) {
            elements.buscaInput.addEventListener('focus', function() {
                this.style.fontSize = '16px';
            });
            
            elements.buscaInput.addEventListener('blur', function() {
                this.style.fontSize = '';
            });
        }
    }
    
    /**
     * Manipula o input de busca com debounce
     */
    function handleSearchInput() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            performSearch(1);
        }, DEBOUNCE_DELAY);
    }
    
    /**
     * Executa a busca com os parâmetros atuais
     * @param {number} page - Número da página (padrão: 1)
     */
    function performSearch(page = 1) {
        const termoBusca = elements.buscaInput.value.trim();
        const setorBusca = elements.setorSelect.value;
        
        // Obter o tipo atual da URL ou padrão 'interno'
        const urlParams = new URLSearchParams(window.location.search);
        const tipoRamal = urlParams.get('tipo') || 'interno';
        
        elements.contentContainer.classList.add('loading');
        
        const params = new URLSearchParams({
            busca: termoBusca,
            setor: setorBusca,
            tipo: tipoRamal,
            pagina: page,
            ajax: 1
        });
        
        const url = window.location.pathname + '?' + params.toString();
        window.history.pushState({ path: url }, '', url);
        
        fetch(url)
            .then(response => {
                if (!response.ok) throw new Error('Erro na resposta do servidor');
                return response.text();
            })
            .then(html => {
                elements.contentContainer.innerHTML = html;
                elements.contentContainer.classList.remove('loading');
            })
            .catch(error => {
                console.error('Erro na busca:', error);
                elements.contentContainer.innerHTML = '<p class="no-results">Ocorreu um erro ao buscar. Tente novamente.</p>';
                elements.contentContainer.classList.remove('loading');
            });
    }
    
    /**
     * Manipula cliques no documento (delegação de eventos)
     */
    function handleDocumentClick(e) {
        // Paginação AJAX
        const pageLink = e.target.closest('.pagination a');
        if (pageLink) {
            e.preventDefault();
            const url = new URL(pageLink.href);
            const page = parseInt(url.searchParams.get('pagina') || '1', 10);
            performSearch(page);
            return;
        }
        
        // Cópia de ramal
        const copyButton = e.target.closest('.btn-copiar');
        if (copyButton) {
            handleCopyRamal(copyButton);
        }
    }
    
    /**
     * Copia o ramal para a área de transferência
     * @param {HTMLElement} button - Botão que foi clicado
     */
    function handleCopyRamal(button) {
        const ramal = button.getAttribute('data-ramal') || '';
        const originalText = button.innerText;
        
        button.innerText = 'Copiado!';
        button.classList.add('copiado');
        
        // Usar Clipboard API com fallback
        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(ramal)
                .then(() => {
                    resetCopyButton(button, originalText);
                })
                .catch(() => {
                    fallbackCopyTextToClipboard(ramal, button, originalText);
                });
        } else {
            fallbackCopyTextToClipboard(ramal, button, originalText);
        }
    }
    
    /**
     * Reseta o botão de copiar após um tempo
     */
    function resetCopyButton(button, originalText) {
        setTimeout(() => {
            button.innerText = originalText;
            button.classList.remove('copiado');
        }, 1500);
    }
    
    /**
     * Função fallback para copiar texto (navegadores antigos)
     */
    function fallbackCopyTextToClipboard(text, button, originalText) {
        const textArea = document.createElement('textarea');
        textArea.value = text;
        textArea.style.cssText = 'position:fixed;top:0;left:0;opacity:0;';
        
        document.body.appendChild(textArea);
        textArea.focus();
        textArea.select();
        
        try {
            const successful = document.execCommand('copy');
            if (successful) {
                resetCopyButton(button, originalText);
            } else {
                alert('Não foi possível copiar o ramal.');
                button.innerText = originalText;
                button.classList.remove('copiado');
            }
        } catch (err) {
            alert('Não foi possível copiar o ramal.');
            button.innerText = originalText;
            button.classList.remove('copiado');
        }
        
        document.body.removeChild(textArea);
    }
    
    /**
     * Configura o Service Worker
     */
    function setupServiceWorker() {
        if ('serviceWorker' in navigator) {
            const swPath = (window.basePath || '') + '/service-worker.js';
            navigator.serviceWorker.register(swPath)
                .then(() => console.log('Service Worker registrado em:', swPath))
                .catch((e) => console.warn('Falha ao registrar SW', e));
        }
    }
    
    /**
     * Configura o prompt de instalação PWA
     */
    function setupPWAInstall() {
        let deferredPrompt;
        
        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            deferredPrompt = e;
            
            const div = document.createElement('div');
            div.id = 'installPrompt';
            div.style.cssText = 'position:fixed;bottom:20px;left:20px;background:#fafafa;padding:15px;border:1px solid #ccc;border-radius:8px;box-shadow:0 2px 6px rgba(0,0,0,0.2);';
            div.innerHTML = `
                <p>Deseja adicionar o <b>Ramais PMSGRA</b> na sua área de trabalho?</p>
                <button id="installBtn">Adicionar</button>
                <button onclick="this.parentElement.remove()">Agora não</button>
            `;
            document.body.appendChild(div);
            
            document.getElementById('installBtn').addEventListener('click', () => {
                div.remove();
                deferredPrompt.prompt();
                deferredPrompt.userChoice.then((choice) => {
                    if (choice.outcome === 'accepted') {
                        console.log('Usuário aceitou instalar');
                    }
                    deferredPrompt = null;
                });
            });
        });
    }
    
    /**
     * Otimiza para dispositivos touch
     */
    function optimizeTouchDevices() {
        if ('ontouchstart' in window) {
            const buttons = document.querySelectorAll('.btn-copiar, .pagination a');
            buttons.forEach(button => {
                button.style.minHeight = '44px';
                button.style.minWidth = '44px';
            });
        }
    }
    
    // Inicializar quando o DOM estiver pronto
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();

