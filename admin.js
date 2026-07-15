// --- TOGGLE DE SETOR NO FORMULÁRIO DE EMAIL ---

function toggleSetorOptions() {
	const setorSelect = document.getElementById('setor_email_novo');
	const novoSetorInput = document.getElementById('novo_setor_input');

	if (setorSelect.value) {
		novoSetorInput.value = '';
		novoSetorInput.style.borderColor = '#e2e8f0';
		setorSelect.style.borderColor = '#2e7d32';
	}

	if (novoSetorInput.value.trim()) {
		setorSelect.value = '';
		setorSelect.style.borderColor = '#e2e8f0';
		novoSetorInput.style.borderColor = '#2e7d32';
	}
}

document.addEventListener('DOMContentLoaded', function() {
	const formNovoEmail = document.getElementById('form-novo-email');
	if (formNovoEmail) {
		formNovoEmail.addEventListener('submit', function(e) {
			const setorSelect = document.getElementById('setor_email_novo');
			const novoSetorInput = document.getElementById('novo_setor_input');

			if (!setorSelect.value && !novoSetorInput.value.trim()) {
				e.preventDefault();
				alert('⚠️ Por favor, selecione um setor existente OU digite um nome para criar um novo setor.');
				return false;
			}

			if (novoSetorInput.value.trim()) {
				setorSelect.value = '';
			}
		});
	}
});

// --- TABS ---

function showTab(tabName, button) {
	document.querySelectorAll('.tab-content').forEach(content => {
		content.classList.remove('active');
	});

	document.querySelectorAll('.tab').forEach(tab => {
		tab.classList.remove('active');
	});

	document.getElementById('tab-' + tabName).classList.add('active');

	if (button) {
		button.classList.add('active');
	} else {
		document.querySelector(`.tab[data-tab="${tabName}"]`).classList.add('active');
	}
}

// --- RESTAURAR SCROLL ---

(function() {
	const savedRowId = sessionStorage.getItem('adminScrollToRow');
	if (savedRowId !== null) {
		sessionStorage.removeItem('adminScrollToRow');

		function scrollToRow() {
			const row = document.getElementById(savedRowId);
			if (row) {
				const rowTop = row.getBoundingClientRect().top + window.pageYOffset;
				const offset = 150;
				window.scrollTo({ top: rowTop - offset, behavior: 'instant' });

				row.style.transition = 'background-color 0.3s';
				const originalBg = row.style.backgroundColor;
				row.style.backgroundColor = '#fff3cd';
				setTimeout(function() { row.style.backgroundColor = originalBg; }, 1000);
				return true;
			}
			return false;
		}

		let attempts = 0;
		const maxAttempts = 10;

		function tryScroll() {
			attempts++;
			if (scrollToRow()) return;
			if (attempts < maxAttempts) setTimeout(tryScroll, 100);
		}

		if (document.readyState === 'complete') {
			setTimeout(tryScroll, 50);
		} else {
			document.addEventListener('DOMContentLoaded', function() { setTimeout(tryScroll, 100); });
			window.addEventListener('load', function() { setTimeout(tryScroll, 50); });
		}
		return;
	}

	const savedScrollPosition = sessionStorage.getItem('adminScrollPosition');
	if (savedScrollPosition !== null) {
		sessionStorage.removeItem('adminScrollPosition');
		const scrollPos = parseInt(savedScrollPosition, 10);

		function restoreScroll() {
			window.scrollTo({ top: scrollPos, behavior: 'instant' });
		}

		if (document.readyState === 'complete') {
			setTimeout(restoreScroll, 50);
		} else {
			window.addEventListener('load', function() { setTimeout(restoreScroll, 50); });
			if (document.readyState === 'loading') {
				document.addEventListener('DOMContentLoaded', function() { setTimeout(restoreScroll, 100); });
			} else {
				setTimeout(restoreScroll, 100);
			}
		}
	}
})();

// --- DOM READY ---

document.addEventListener('DOMContentLoaded', function() {

	// --- MODAL DE TRANSFERÊNCIA ---

	function abrirModalTransferir(id, tabelaOrigem, contato) {
		const modal = document.getElementById('modalTransferir');
		if (modal) {
			document.getElementById('ramalId').value = id;
			document.getElementById('tabelaOrigem').value = tabelaOrigem;
			document.getElementById('modalInfo').textContent = 'Transferir: ' + contato;
			document.getElementById('tabelaDestino').value = '';
			modal.style.display = 'block';
		}
	}

	function fecharModalTransferir() {
		const modal = document.getElementById('modalTransferir');
		if (modal) modal.style.display = 'none';
	}

	document.querySelectorAll('button.transfer').forEach(botao => {
		botao.addEventListener('click', function() {
			abrirModalTransferir(
				this.getAttribute('data-id'),
				this.getAttribute('data-tabela'),
				this.getAttribute('data-contato')
			);
		});
	});

	const modal = document.getElementById('modalTransferir');
	if (modal) {
		modal.querySelector('.close').addEventListener('click', fecharModalTransferir);
		modal.addEventListener('click', e => { if (e.target === modal) fecharModalTransferir(); });
	}
	document.addEventListener('keydown', e => { if (e.key === 'Escape') fecharModalTransferir(); });
	window.fecharModalTransferir = fecharModalTransferir;

	// --- PRÉ-PREENCHIMENTO DO FORMULÁRIO DE CADASTRO ---

	document.querySelectorAll('button.usar').forEach(btn => {
		btn.addEventListener('click', function() {
			const subSetor    = this.dataset.subSetor;
			const tabela      = this.dataset.tabela;
			const campoSub    = document.getElementById('sub_setor');
			const selectSetor = document.getElementById('create_tabela');
			const badge       = document.getElementById('badge-subsetor');
			const badgeTexto  = document.getElementById('badge-subsetor-texto');

			if (campoSub)    campoSub.value    = subSetor;
			if (selectSetor) selectSetor.value = tabela;

			if (badge && badgeTexto) {
				if (subSetor) {
					badgeTexto.textContent = subSetor;
					badge.style.display = 'inline-flex';
				} else {
					badge.style.display = 'none';
				}
			}

			const section = document.getElementById('section-novo-ramal');
			if (section) {
				section.scrollIntoView({ behavior: 'smooth', block: 'start' });
				setTimeout(() => {
					const ramalInput = document.getElementById('ramal');
					if (ramalInput) ramalInput.focus();
				}, 500);
			}
		});
	});

	window.limparSubSetorForm = function() {
		const campo = document.getElementById('sub_setor');
		const badge = document.getElementById('badge-subsetor');
		if (campo) campo.value = '';
		if (badge) badge.style.display = 'none';
	};

	// --- ATUALIZAÇÃO DE STATUS VIA API ---

	const tabelaRegistros = document.querySelector('table tbody');
	if (tabelaRegistros) {
		tabelaRegistros.addEventListener('change', async function(e) {
			if (!e.target.classList.contains('status-checkbox-row')) return;

			const checkbox = e.target;
			const id = checkbox.dataset.id;
			const tabela = checkbox.dataset.tabela;
			const field = checkbox.dataset.field;
			const value = checkbox.checked;
			const row = checkbox.closest('tr');

			updateRowUI(row, field, value);

			try {
				const response = await fetch('./api.php', {
					method: 'POST',
					headers: { 'Content-Type': 'application/json' },
					body: JSON.stringify({ id, tabela, field, value, csrf_token: window.csrfToken })
				});

				const result = await response.json();

				if (!response.ok || !result.success) {
					throw new Error(result.message || 'Erro desconhecido na API.');
				}

				showToast('✓ Status atualizado com sucesso!');

			} catch (error) {
				console.error('Erro ao atualizar status:', error);
				showToast('⚠️ Erro: ' + error.message, true);
				checkbox.checked = !value;
				updateRowUI(row, field, !value);
			}
		});
	}

	function updateRowUI(row, changedField, isChecked) {
		const checkboxes = row.querySelectorAll('.status-checkbox-row');

		checkboxes.forEach(cb => {
			const fieldName = cb.dataset.field;

			row.classList.remove(`${fieldName}-row`);
			if (isChecked && fieldName === changedField) {
				row.classList.add(`${fieldName}-row`);
			}

			if (fieldName !== changedField) {
				if (isChecked) {
					cb.checked = false;
					cb.disabled = true;
				} else {
					cb.disabled = false;
				}
			}
		});

		if (row.classList.contains('oculto-row')) {
			row.classList.add('oculto-row');
		} else {
			row.classList.remove('oculto-row');
		}
	}

	// --- CHECKBOXES DO FORMULÁRIO DE CRIAÇÃO ---

	const formCheckboxes = document.querySelectorAll('.status-checkbox[data-group="status"]');
	formCheckboxes.forEach(checkbox => {
		checkbox.addEventListener('change', function() {
			if (this.checked) {
				formCheckboxes.forEach(other => {
					if (other !== this) other.disabled = true;
				});
			} else {
				formCheckboxes.forEach(other => other.disabled = false);
			}
		});
	});

	document.querySelectorAll('tr[id^="row-"]').forEach(row => {
		const checkedBox = row.querySelector('.status-checkbox-row:checked');
		if (checkedBox) {
			updateRowUI(row, checkedBox.dataset.field, true);
		}
	});
});

// --- TOAST DE FEEDBACK ---

let toastTimeout;
function showToast(message, isError = false) {
	let toast = document.getElementById('toast-notification');
	if (!toast) {
		toast = document.createElement('div');
		toast.id = 'toast-notification';
		document.body.appendChild(toast);
		Object.assign(toast.style, {
			position: 'fixed',
			bottom: '20px',
			left: '50%',
			transform: 'translateX(-50%)',
			padding: '12px 24px',
			borderRadius: '8px',
			color: 'white',
			fontWeight: '500',
			zIndex: '10000',
			transition: 'opacity 0.3s, transform 0.3s',
			opacity: '0',
			boxShadow: '0 4px 15px rgba(0,0,0,0.2)',
			pointerEvents: 'none'
		});
	}

	toast.textContent = message;
	toast.style.background = isError
		? 'linear-gradient(135deg, #d32f2f, #b71c1c)'
		: 'linear-gradient(135deg, #2e7d32, #1b5e20)';

	toast.style.opacity = '1';
	toast.style.transform = 'translateX(-50%) translateY(0)';

	clearTimeout(toastTimeout);
	toastTimeout = setTimeout(() => {
		toast.style.opacity = '0';
		toast.style.transform = 'translateX(-50%) translateY(20px)';
	}, 3000);
}
