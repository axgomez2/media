/**
 * AI Content Generation Module
 * Handles AI-powered content generation for news articles
 */

class AIContentManager {
    constructor() {
        this.currentField = null;
        this.modal = null;
        this.isGenerating = false;
        this.init();
    }

    init() {
        this.setupModal();
        this.bindEvents();
    }

    setupModal() {
        // Create modal if it doesn't exist
        if (!document.getElementById('aiModal')) {
            this.createModal();
        }
        this.modal = document.getElementById('aiModal');
    }

    createModal() {
        const modalHTML = `
            <div id="aiModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50" role="dialog" aria-modal="true" aria-labelledby="ai-modal-title">
                <div class="relative top-20 mx-auto p-5 border w-full max-w-md shadow-lg rounded-md bg-white">
                    <div class="mt-3">
                        <div class="flex items-center justify-between mb-4">
                            <h3 id="ai-modal-title" class="text-lg font-medium text-gray-900">Gerar Conteúdo com IA</h3>
                            <button type="button"
                                    onclick="aiContentManager.closeModal()"
                                    class="text-gray-400 hover:text-gray-600 focus:outline-none focus:ring-2 focus:ring-purple-500 rounded-md p-1"
                                    aria-label="Fechar modal">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>

                        <form id="aiForm" onsubmit="aiContentManager.generateContent(event)">
                            <div class="mb-4">
                                <label for="ai_prompt" class="block text-sm font-medium text-gray-700 mb-2">
                                    Prompt <span class="text-red-500">*</span>
                                </label>
                                <textarea id="ai_prompt"
                                          rows="3"
                                          class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500"
                                          placeholder="Descreva o que você quer gerar..."
                                          required></textarea>
                                <p class="mt-1 text-xs text-gray-500">Seja específico para obter melhores resultados</p>
                            </div>

                            <div class="mb-4">
                                <label for="ai_type" class="block text-sm font-medium text-gray-700 mb-2">
                                    Tipo de Conteúdo
                                </label>
                                <select id="ai_type"
                                        class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500">
                                    <option value="title">Título</option>
                                    <option value="excerpt">Resumo</option>
                                    <option value="content">Conteúdo</option>
                                    <option value="keywords">Palavras-chave</option>
                                    <option value="meta_description">Meta Descrição</option>
                                    <option value="meta_keywords">Meta Keywords</option>
                                </select>
                            </div>

                            <div class="flex justify-end space-x-3">
                                <button type="button"
                                        onclick="aiContentManager.closeModal()"
                                        class="px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-700 rounded-md transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-gray-500">
                                    Cancelar
                                </button>
                                <button type="submit"
                                        id="aiGenerateBtn"
                                        class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-md transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-purple-500 disabled:opacity-50 disabled:cursor-not-allowed">
                                    <span class="ai-btn-text">Gerar</span>
                                    <span class="ai-btn-loading hidden">
                                        <svg class="animate-spin -ml-1 mr-3 h-4 w-4 text-white inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        Gerando...
                                    </span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        `;

        document.body.insertAdjacentHTML('beforeend', modalHTML);
    }

    bindEvents() {
        // Close modal when clicking outside
        document.addEventListener('click', (e) => {
            if (e.target && e.target.id === 'aiModal') {
                this.closeModal();
            }
        });

        // Close modal with Escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.modal && !this.modal.classList.contains('hidden')) {
                this.closeModal();
            }
        });
    }

    openModal(fieldName) {
        if (this.isGenerating) {
            this.showToast('Aguarde a geração atual terminar', 'warning');
            return;
        }

        this.currentField = fieldName;

        // Set the content type based on field
        const typeSelect = document.getElementById('ai_type');
        if (typeSelect) {
            typeSelect.value = fieldName;
        }

        // Show modal
        this.modal.classList.remove('hidden');

        // Focus on prompt field
        const promptField = document.getElementById('ai_prompt');
        if (promptField) {
            setTimeout(() => promptField.focus(), 100);
        }

        // Set placeholder based on field type
        this.setPromptPlaceholder(fieldName);
    }

    closeModal() {
        if (this.isGenerating) {
            if (!confirm('A geração está em andamento. Deseja cancelar?')) {
                return;
            }
        }

        this.modal.classList.add('hidden');
        this.resetForm();
        this.currentField = null;
    }

    setPromptPlaceholder(fieldType) {
        const promptField = document.getElementById('ai_prompt');
        if (!promptField) return;

        const placeholders = {
            title: 'Ex: Artigo sobre tecnologia sustentável para empresas',
            excerpt: 'Ex: Resumo sobre os benefícios da energia solar',
            content: 'Ex: Artigo completo sobre inteligência artificial no marketing',
            keywords: 'Ex: Palavras-chave para artigo sobre marketing digital',
            meta_description: 'Ex: Meta descrição para artigo sobre e-commerce',
            meta_keywords: 'Ex: Palavras-chave específicas para meta tags'
        };

        promptField.placeholder = placeholders[fieldType] || 'Descreva o que você quer gerar...';
    }

    async generateContent(event) {
        event.preventDefault();

        if (this.isGenerating) return;

        const prompt = document.getElementById('ai_prompt').value.trim();
        const type = document.getElementById('ai_type').value;

        if (!prompt) {
            this.showToast('Por favor, insira um prompt', 'error');
            return;
        }

        this.setLoadingState(true);

        try {
            const response = await this.makeAPICall(prompt, type);

            if (response.success) {
                this.insertGeneratedContent(response.content);
                this.closeModal();
                this.showToast('Conteúdo gerado com sucesso!', 'success');
            } else {
                this.showToast(response.message || 'Erro ao gerar conteúdo', 'error');
            }
        } catch (error) {
            console.error('AI Generation Error:', error);
            this.handleError(error);
        } finally {
            this.setLoadingState(false);
        }
    }

    async makeAPICall(prompt, type) {
        const formData = new FormData();
        formData.append('prompt', prompt);
        formData.append('type', type);

        // Get CSRF token
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        if (csrfToken) {
            formData.append('_token', csrfToken);
        }

        const response = await fetch(this.getAPIEndpoint(), {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }

        return await response.json();
    }

    getAPIEndpoint() {
        // Try to get the route from a meta tag or use a default
        const routeMeta = document.querySelector('meta[name="ai-content-route"]');
        if (routeMeta) {
            return routeMeta.getAttribute('content');
        }

        // Fallback to constructing the route
        return '/admin/news/generate-content';
    }

    insertGeneratedContent(content) {
        const targetField = document.getElementById(this.currentField);
        if (!targetField) {
            this.showToast('Campo de destino não encontrado', 'error');
            return;
        }

        // Insert content and trigger change event
        targetField.value = content;
        targetField.dispatchEvent(new Event('input', { bubbles: true }));
        targetField.focus();

        // If it's a textarea, adjust height if needed
        if (targetField.tagName === 'TEXTAREA') {
            this.adjustTextareaHeight(targetField);
        }
    }

    adjustTextareaHeight(textarea) {
        textarea.style.height = 'auto';
        textarea.style.height = textarea.scrollHeight + 'px';
    }

    setLoadingState(loading) {
        this.isGenerating = loading;

        const btn = document.getElementById('aiGenerateBtn');
        const btnText = btn?.querySelector('.ai-btn-text');
        const btnLoading = btn?.querySelector('.ai-btn-loading');

        if (loading) {
            btnText?.classList.add('hidden');
            btnLoading?.classList.remove('hidden');
            if (btn) btn.disabled = true;
        } else {
            btnText?.classList.remove('hidden');
            btnLoading?.classList.add('hidden');
            if (btn) btn.disabled = false;
        }
    }

    resetForm() {
        const form = document.getElementById('aiForm');
        if (form) {
            form.reset();
        }
        this.setLoadingState(false);
    }

    handleError(error) {
        let message = 'Erro ao conectar com o serviço de IA';

        if (error.message.includes('404')) {
            message = 'Serviço de IA não encontrado';
        } else if (error.message.includes('500')) {
            message = 'Erro interno do servidor';
        } else if (error.message.includes('429')) {
            message = 'Muitas solicitações. Tente novamente em alguns minutos';
        } else if (error.message.includes('timeout')) {
            message = 'Tempo limite excedido. Tente novamente';
        }

        this.showToast(message, 'error');
    }

    showToast(message, type = 'info') {
        // Create a toast notification
        const toast = this.createToast(message, type);
        document.body.appendChild(toast);

        // Show toast with animation
        setTimeout(() => {
            toast.classList.remove('translate-x-full');
        }, 100);

        // Auto remove after 5 seconds
        setTimeout(() => {
            this.removeToast(toast);
        }, 5000);
    }

    createToast(message, type) {
        const colors = {
            success: 'bg-green-500',
            error: 'bg-red-500',
            warning: 'bg-yellow-500',
            info: 'bg-blue-500'
        };

        const icons = {
            success: '✓',
            error: '✕',
            warning: '⚠',
            info: 'ℹ'
        };

        const toast = document.createElement('div');
        toast.className = `fixed top-4 right-4 ${colors[type]} text-white px-6 py-3 rounded-lg shadow-lg transform translate-x-full transition-transform duration-300 z-50 max-w-sm`;

        toast.innerHTML = `
            <div class="flex items-center">
                <span class="mr-2 font-bold">${icons[type]}</span>
                <span class="flex-1">${message}</span>
                <button onclick="aiContentManager.removeToast(this.parentElement.parentElement)"
                        class="ml-4 text-white hover:text-gray-200 focus:outline-none">
                    ✕
                </button>
            </div>
        `;

        return toast;
    }

    removeToast(toast) {
        if (toast && toast.parentElement) {
            toast.classList.add('translate-x-full');
            setTimeout(() => {
                if (toast.parentElement) {
                    toast.parentElement.removeChild(toast);
                }
            }, 300);
        }
    }
}

// Initialize the AI Content Manager
let aiContentManager;

document.addEventListener('DOMContentLoaded', function() {
    aiContentManager = new AIContentManager();
});

// Global functions for backward compatibility
function openAIModal(field) {
    if (aiContentManager) {
        aiContentManager.openModal(field);
    }
}

function closeAIModal() {
    if (aiContentManager) {
        aiContentManager.closeModal();
    }
}

function generateAIContent(event) {
    if (aiContentManager) {
        aiContentManager.generateContent(event);
    }
}
