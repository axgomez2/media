/**
 * Sistema de tratamento de erros para o painel administrativo
 */

class AdminErrorHandler {
    constructor() {
        this.init();
    }

    init() {
        // Interceptar erros globais de JavaScript
        window.addEventListener('error', (event) => {
            this.logError('JavaScript Error', {
                message: event.message,
                filename: event.filename,
                lineno: event.lineno,
                colno: event.colno,
                stack: event.error?.stack
            });
        });

        // Interceptar promises rejeitadas não tratadas
        window.addEventListener('unhandledrejection', (event) => {
            this.logError('Unhandled Promise Rejection', {
                reason: event.reason,
                stack: event.reason?.stack
            });
        });

        // Interceptar erros de AJAX
        this.setupAjaxErrorHandling();
    }

    setupAjaxErrorHandling() {
        // Para jQuery se estiver disponível
        if (typeof $ !== 'undefined') {
            $(document).ajaxError((event, xhr, settings, thrownError) => {
                this.handleAjaxError(xhr, settings, thrownError);
            });
        }

        // Para fetch API
        const originalFetch = window.fetch;
        window.fetch = async (...args) => {
            try {
                const response = await originalFetch(...args);
                if (!response.ok) {
                    this.handleFetchError(response, args[0]);
                }
                return response;
            } catch (error) {
                this.handleFetchError(null, args[0], error);
                throw error;
            }
        };
    }

    handleAjaxError(xhr, settings, thrownError) {
        const errorData = {
            status: xhr.status,
            statusText: xhr.statusText,
            url: settings.url,
            method: settings.type || 'GET',
            error: thrownError
        };

        this.logError('AJAX Error', errorData);
        this.showUserFriendlyError(xhr.status, xhr.responseJSON?.message);
    }

    handleFetchError(response, url, error = null) {
        const errorData = {
            status: response?.status,
            statusText: response?.statusText,
            url: url,
            error: error?.message
        };

        this.logError('Fetch Error', errorData);
        this.showUserFriendlyError(response?.status, error?.message);
    }

    logError(type, data) {
        // Log no console para desenvolvimento
        console.error(`[${type}]`, data);

        // Enviar para o servidor se não estivermos em desenvolvimento
        if (window.location.hostname !== 'localhost' && window.location.hostname !== '127.0.0.1') {
            this.sendErrorToServer(type, data);
        }
    }

    async sendErrorToServer(type, data) {
        try {
            await fetch('/admin/log-client-error', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                },
                body: JSON.stringify({
                    type: type,
                    data: data,
                    url: window.location.href,
                    userAgent: navigator.userAgent,
                    timestamp: new Date().toISOString()
                })
            });
        } catch (error) {
            console.error('Falha ao enviar erro para o servidor:', error);
        }
    }

    showUserFriendlyError(status, message = null) {
        let userMessage = message;

        if (!userMessage) {
            switch (status) {
                case 400:
                    userMessage = 'Dados inválidos enviados. Verifique as informações e tente novamente.';
                    break;
                case 401:
                    userMessage = 'Sua sessão expirou. Faça login novamente.';
                    break;
                case 403:
                    userMessage = 'Você não tem permissão para realizar esta ação.';
                    break;
                case 404:
                    userMessage = 'Recurso não encontrado.';
                    break;
                case 422:
                    userMessage = 'Dados de validação inválidos. Verifique os campos e tente novamente.';
                    break;
                case 429:
                    userMessage = 'Muitas tentativas. Aguarde alguns minutos antes de tentar novamente.';
                    break;
                case 500:
                    userMessage = 'Erro interno do servidor. Nossa equipe foi notificada.';
                    break;
                case 503:
                    userMessage = 'Serviço temporariamente indisponível. Tente novamente em alguns minutos.';
                    break;
                default:
                    userMessage = 'Ocorreu um erro inesperado. Tente novamente.';
            }
        }

        this.showNotification(userMessage, 'error');
    }

    showNotification(message, type = 'info') {
        // Criar elemento de notificação
        const notification = document.createElement('div');
        notification.className = `fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg max-w-sm transition-all duration-300 transform translate-x-full`;

        // Definir classes baseadas no tipo
        const typeClasses = {
            success: 'bg-green-50 border border-green-200 text-green-800',
            error: 'bg-red-50 border border-red-200 text-red-800',
            warning: 'bg-yellow-50 border border-yellow-200 text-yellow-800',
            info: 'bg-blue-50 border border-blue-200 text-blue-800'
        };

        notification.className += ` ${typeClasses[type] || typeClasses.info}`;

        // Definir ícones baseados no tipo
        const icons = {
            success: '<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>',
            error: '<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>',
            warning: '<path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>',
            info: '<path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>'
        };

        notification.innerHTML = `
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        ${icons[type] || icons.info}
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium">${message}</p>
                </div>
                <div class="ml-auto pl-3">
                    <button type="button" class="inline-flex rounded-md p-1.5 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-offset-2" onclick="this.parentElement.parentElement.parentElement.remove()">
                        <span class="sr-only">Fechar</span>
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z" />
                        </svg>
                    </button>
                </div>
            </div>
        `;

        // Adicionar ao DOM
        document.body.appendChild(notification);

        // Animar entrada
        setTimeout(() => {
            notification.classList.remove('translate-x-full');
        }, 100);

        // Auto remover após 5 segundos
        setTimeout(() => {
            notification.classList.add('translate-x-full');
            setTimeout(() => {
                if (notification.parentElement) {
                    notification.remove();
                }
            }, 300);
        }, 5000);
    }

    // Método público para mostrar notificações
    static showNotification(message, type = 'info') {
        if (window.adminErrorHandler) {
            window.adminErrorHandler.showNotification(message, type);
        }
    }

    // Método público para tratar erros de formulário
    static handleFormError(form, errors) {
        // Limpar erros anteriores
        form.querySelectorAll('.text-red-600').forEach(el => el.remove());
        form.querySelectorAll('.border-red-300').forEach(el => {
            el.classList.remove('border-red-300');
            el.classList.add('border-gray-300');
        });

        // Adicionar novos erros
        Object.keys(errors).forEach(field => {
            const input = form.querySelector(`[name="${field}"]`);
            if (input) {
                input.classList.remove('border-gray-300');
                input.classList.add('border-red-300');

                const errorDiv = document.createElement('div');
                errorDiv.className = 'mt-1 text-sm text-red-600 flex items-center';
                errorDiv.innerHTML = `
                    <svg class="w-4 h-4 mr-1 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                    </svg>
                    <span>${errors[field][0]}</span>
                `;
                input.parentElement.appendChild(errorDiv);
            }
        });
    }
}

// Inicializar quando o DOM estiver pronto
document.addEventListener('DOMContentLoaded', () => {
    window.adminErrorHandler = new AdminErrorHandler();
});

// Exportar para uso em outros módulos
window.AdminErrorHandler = AdminErrorHandler;
