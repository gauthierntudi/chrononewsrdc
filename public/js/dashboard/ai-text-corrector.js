class AITextCorrector {
    constructor(apiBase = '/api/v1') {
        this.apiBase = apiBase;
        this.enabled = false;
    }

    async loadStatus() {
        try {
            const res = await fetch(`${this.apiBase}/ai/status`, {
                credentials: 'same-origin',
                headers: { Accept: 'application/json' },
            });
            const data = await res.json();
            this.enabled = !!data.enabled;
            return data;
        } catch (error) {
            console.warn('Assistant IA indisponible', error);
            this.enabled = false;
            return { enabled: false };
        }
    }

    async callAI(action, text) {
        if (!this.enabled) {
            throw new Error('Assistant IA non configuré');
        }

        const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
        const res = await fetch(`${this.apiBase}/ai/process`, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf,
            },
            body: JSON.stringify({ action, text }),
        });

        const data = await res.json().catch(() => ({}));
        if (!res.ok || !data.success) {
            throw new Error(data.message || `Erreur IA (${res.status})`);
        }

        return data.text || '';
    }

    correctText(text) {
        return this.callAI('correct', text);
    }

    rewriteText(text) {
        return this.callAI('rewrite', text);
    }

    improveText(text) {
        return this.callAI('improve', text);
    }
}

class AITextCorrectorUI {
    constructor(corrector) {
        this.corrector = corrector;
        this.attachedFields = new Map();
        this.originalTexts = new Map();
        this.init();
    }

    init() {
        this.createStyles();
    }

    createStyles() {
        if (document.getElementById('ai-corrector-styles')) return;

        const style = document.createElement('style');
        style.id = 'ai-corrector-styles';
        style.textContent = `
            .ai-toolbar {
                display: flex;
                flex-wrap: wrap;
                gap: 0.5rem;
                margin-bottom: 0.5rem;
                padding: 0.5rem;
                background: #f9fafb;
                border-radius: 6px;
                border: 1px solid #e5e7eb;
                align-items: center;
            }
            .ai-btn {
                padding: 0;
                width: 36px;
                height: 36px;
                border: 2px solid #d1d5db;
                background: white;
                border-radius: 50%;
                cursor: pointer;
                transition: all 0.2s ease;
                display: flex;
                align-items: center;
                justify-content: center;
                color: #374151;
            }
            .ai-btn:hover:not(:disabled) { transform: scale(1.08); box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
            .ai-btn:disabled { opacity: 0.45; cursor: not-allowed; }
            .ai-btn.loading { position: relative; color: transparent; }
            .ai-btn.loading::after {
                content: '';
                position: absolute;
                width: 14px; height: 14px;
                top: 50%; left: 50%;
                margin: -7px 0 0 -7px;
                border: 2px solid #f3f4f6;
                border-top-color: #1E5EFF;
                border-radius: 50%;
                animation: ai-spin 0.6s linear infinite;
            }
            @keyframes ai-spin { to { transform: rotate(360deg); } }
            .ai-btn-correct { border-color: #10b981; color: #059669; }
            .ai-btn-correct:hover:not(:disabled), .ai-btn-correct.active { background: #10b981; border-color: #10b981; color: #fff; }
            .ai-btn-rewrite { border-color: #1E5EFF; color: #1E5EFF; }
            .ai-btn-rewrite:hover:not(:disabled), .ai-btn-rewrite.active { background: #1E5EFF; border-color: #1E5EFF; color: #fff; }
            .ai-btn-improve { border-color: #f59e0b; color: #d97706; }
            .ai-btn-improve:hover:not(:disabled), .ai-btn-improve.active { background: #f59e0b; border-color: #f59e0b; color: #fff; }
            .ai-btn-restore { border-color: #6b7280; color: #6b7280; }
            .ai-btn-restore:hover:not(:disabled) { background: #6b7280; border-color: #6b7280; color: #fff; }
            .ai-btn-restore:disabled { opacity: 0.3; }
            .ai-toolbar-label {
                display: flex;
                align-items: center;
                gap: 0.25rem;
                font-size: 0.75rem;
                color: #6b7280;
                font-weight: 600;
                padding-right: 0.5rem;
                border-right: 1px solid #e5e7eb;
                margin-right: 0.25rem;
            }
            .ai-toolbar .lucide-icon { width: 1rem; height: 1rem; }
        `;
        document.head.appendChild(style);
    }

    attachToInput(inputElement, options = {}) {
        if (this.attachedFields.has(inputElement)) return;
        const toolbar = this.createToolbar(inputElement, options);
        inputElement.parentElement.insertBefore(toolbar, inputElement);
        this.attachedFields.set(inputElement, toolbar);
        this.saveOriginalText(inputElement, options);
        this.refreshToolbarIcons(toolbar);
    }

    attachToTextarea(textareaElement, options = {}) {
        if (this.attachedFields.has(textareaElement)) return;
        const toolbar = this.createToolbar(textareaElement, options);
        textareaElement.parentElement.insertBefore(toolbar, textareaElement);
        this.attachedFields.set(textareaElement, toolbar);
        this.saveOriginalText(textareaElement, options);
        this.refreshToolbarIcons(toolbar);
    }

    attachToQuill(quillInstance, options = {}) {
        const editorElement = quillInstance.root.parentElement;
        if (this.attachedFields.has(editorElement)) return;
        const toolbar = this.createToolbar(editorElement, { ...options, isQuill: true, quillInstance });
        editorElement.parentElement.insertBefore(toolbar, editorElement);
        this.attachedFields.set(editorElement, toolbar);
        this.saveOriginalText(editorElement, { ...options, isQuill: true, quillInstance });
        this.refreshToolbarIcons(toolbar);
    }

    createToolbar(targetElement, options = {}) {
        const toolbar = document.createElement('div');
        toolbar.className = 'ai-toolbar';

        const label = document.createElement('div');
        label.className = 'ai-toolbar-label';
        label.innerHTML = '<i data-lucide="sparkles" class="lucide-icon" aria-hidden="true"></i> Assistant IA';
        toolbar.appendChild(label);

        const actions = [
            { action: 'correct', label: 'Corriger', icon: 'zap', class: 'ai-btn-correct' },
            { action: 'rewrite', label: 'Réécrire', icon: 'wand-sparkles', class: 'ai-btn-rewrite' },
            { action: 'improve', label: 'Améliorer', icon: 'lightbulb', class: 'ai-btn-improve' },
            { action: 'restore', label: 'Texte original', icon: 'rotate-ccw', class: 'ai-btn-restore' },
        ];

        actions.forEach(({ action, label: actionLabel, icon, class: btnClass }) => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = `ai-btn ${btnClass}`;
            btn.title = actionLabel;
            btn.innerHTML = `<i data-lucide="${icon}" class="lucide-icon" aria-hidden="true"></i>`;
            btn.dataset.action = action;
            if (action === 'restore') btn.disabled = true;
            btn.addEventListener('click', () => this.handleAction(action, targetElement, options, btn));
            toolbar.appendChild(btn);
        });

        return toolbar;
    }

    refreshToolbarIcons(toolbar) {
        if (window.lucide?.createIcons) {
            window.lucide.createIcons({ root: toolbar, nameAttr: 'data-lucide' });
        }
    }

    saveOriginalText(targetElement, options = {}) {
        const text = options.isQuill
            ? options.quillInstance.getText().trim()
            : targetElement.value.trim();
        if (text) this.originalTexts.set(targetElement, text);
    }

    updateRestoreButtonState(targetElement) {
        const toolbar = this.attachedFields.get(targetElement);
        const restoreBtn = toolbar?.querySelector('.ai-btn-restore');
        if (restoreBtn) restoreBtn.disabled = !this.originalTexts.has(targetElement);
    }

    toast(message, type = 'info') {
        if (window.DashboardUtils?.showToast) {
            window.DashboardUtils.showToast(message, type);
            return;
        }
        if (typeof iziToast !== 'undefined') {
            iziToast.show({ message, color: type === 'error' ? 'red' : type === 'success' ? 'green' : 'blue', position: 'topRight' });
        }
    }

    async handleAction(action, targetElement, options, button) {
        const { isQuill, quillInstance } = options;

        if (action === 'restore') {
            const originalText = this.originalTexts.get(targetElement);
            if (!originalText) {
                this.toast('Aucun texte original disponible', 'warning');
                return;
            }
            if (isQuill) quillEditorSetText(quillInstance, originalText);
            else targetElement.value = originalText;
            button.parentElement.querySelectorAll('.ai-btn').forEach((btn) => btn.classList.remove('active'));
            this.toast('Texte original restauré', 'success');
            return;
        }

        const currentText = isQuill ? quillInstance.getText().trim() : targetElement.value.trim();
        if (!currentText) {
            this.toast('Le champ est vide', 'warning');
            return;
        }

        if (!this.originalTexts.has(targetElement)) {
            this.originalTexts.set(targetElement, currentText);
            this.updateRestoreButtonState(targetElement);
        }

        const allButtons = button.parentElement.querySelectorAll('.ai-btn');
        allButtons.forEach((btn) => { btn.disabled = true; });
        button.classList.add('loading');

        try {
            let result;
            if (action === 'correct') result = await this.corrector.correctText(currentText);
            else if (action === 'rewrite') result = await this.corrector.rewriteText(currentText);
            else result = await this.corrector.improveText(currentText);

            if (result) {
                if (isQuill) quillEditorSetText(quillInstance, result);
                else targetElement.value = result;
                allButtons.forEach((btn) => btn.classList.remove('active'));
                button.classList.add('active');
                this.toast('Texte traité avec succès', 'success');
            }
        } catch (error) {
            console.error(error);
            this.toast(error.message || 'Erreur lors du traitement du texte', 'error');
        } finally {
            button.classList.remove('loading');
            allButtons.forEach((btn) => {
                if (btn.dataset.action === 'restore') {
                    btn.disabled = !this.originalTexts.has(targetElement);
                } else {
                    btn.disabled = false;
                }
            });
        }
    }
}

function quillEditorSetText(quillInstance, text) {
    quillInstance.setText(text);
}

let aiCorrectorInstance = null;
let aiCorrectorUI = null;

async function initAICorrector(apiBase = '/api/v1') {
    if (!aiCorrectorInstance) {
        aiCorrectorInstance = new AITextCorrector(apiBase);
        await aiCorrectorInstance.loadStatus();
        aiCorrectorUI = new AITextCorrectorUI(aiCorrectorInstance);
    }
    return { corrector: aiCorrectorInstance, ui: aiCorrectorUI, enabled: aiCorrectorInstance.enabled };
}

window.initAICorrector = initAICorrector;
