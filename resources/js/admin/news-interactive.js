/**
 * News Interactive Features Module
 * Handles all interactive functionality for the news management system
 */

class NewsInteractiveManager {
    constructor() {
        this.searchTimeout = null;
        this.searchDelay = 500; // 500ms debounce
        this.currentFilters = {};
        this.uploadedFiles = new Map();
        this.selectedTopics = new Set();
        this.init();
    }

    init() {
        this.initSearchDebounce();
        this.initDynamicFilters();
        this.initImageUpload();
        this.initTopicMultiSelect();
        this.initDeleteConfirmation();
        this.bindEvents();
    }

    /**
     * Initialize real-time search with debounce
     */
    initSearchDebounce() {
        const searchInput = document.querySelector('input[name="search"]');
        if (!searchInput) return;

        searchInput.addEventListener('input', (e) => {
            clearTimeout(this.searchTimeout);

            this.searchTimeout = setTimeout(() => {
                this.performSearch(e.target.value);
            }, this.searchDelay);
        });

        // Show search indicator
        searchInput.addEventListener('input', () => {
            this.showSearchIndicator(true);
        });
    }

    /**
     * Perform search with current filters
     */
    async performSearch(query) {
        try {
            const params = new URLSearchParams(window.location.search);
            params.set('search', query);

            // Keep other filters
            const form = document.querySelector('form[method="GET"]');
            if (form) {
                const formData = new FormData(form);
                for (const [key, value] of formData.entries()) {
                    if (key !== 'search' && value) {
                        params.set(key, value);
                    }
                }
            }

            const response = await fetch(`${window.location.pathname}?${params.toString()}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'text/html'
                }
            });

            if (response.ok) {
                const html = await response.text();
                this.updateNewsGrid(html);
                this.updateURL(params);
            }
        } catch (error) {
            console.error('Search error:', error);
            this.showNotification('Erro ao realizar busca', 'error');
        } finally {
            this.showSearchIndicator(false);
        }
    }

    /**
     * Initialize dynamic filters with AJAX
     */
    initDynamicFilters() {
        const filterSelects = document.querySelectorAll('select[name="status"], select[name="topic"], select[name="featured"], select[name="sort"], select[name="direction"]');

        filterSelects.forEach(select => {
            select.addEventListener('change', (e) => {
                this.applyFilters();
            });
        });
    }

    /**
     * Apply filters dynamically
     */
    async applyFilters() {
        try {
            this.showFilterIndicator(true);

            const form = document.querySelector('form[method="GET"]');
            const formData = new FormData(form);
            const params = new URLSearchParams();

            // Build parameters from form
            for (const [key, value] of formData.entries()) {
                if (value && value !== 'all' && value !== '') {
                    params.set(key, value);
                }
            }

            const response = await fetch(`${window.location.pathname}?${params.toString()}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'text/html'
                }
            });

            if (response.ok) {
                const html = await response.text();
                this.updateNewsGrid(html);
                this.updateURL(params);
                this.updateStatistics(html);
            }
        } catch (error) {
            console.error('Filter error:', error);
            this.showNotification('Erro ao aplicar filtros', 'error');
        } finally {
            this.showFilterIndicator(false);
        }
    }

    /**
     * Initialize image upload with preview
     */
    initImageUpload() {
        // Featured image upload
        const featuredInput = document.getElementById('featured_image');
        if (featuredInput) {
            featuredInput.addEventListener('change', (e) => {
                this.handleFeaturedImageUpload(e);
            });
        }

        // Gallery images upload
        const galleryInput = document.getElementById('gallery_images');
        if (galleryInput) {
            galleryInput.addEventListener('change', (e) => {
                this.handleGalleryImagesUpload(e);
            });
        }

        // Drag and drop functionality
        this.initDragAndDrop();
    }

    /**
     * Handle featured image upload with preview
     */
    handleFeaturedImageUpload(event) {
        const file = event.target.files[0];
        if (!file) return;

        // Validate file
        if (!this.validateImageFile(file)) {
            event.target.value = '';
            return;
        }

        // Show preview
        this.showFeaturedImagePreview(file);
    }

    /**
     * Handle gallery images upload with preview
     */
    handleGalleryImagesUpload(event) {
        const files = Array.from(event.target.files);
        if (!files.length) return;

        files.forEach(file => {
            if (this.validateImageFile(file)) {
                this.addGalleryImagePreview(file);
            }
        });
    }

    /**
     * Validate image file
     */
    validateImageFile(file) {
        const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        const maxSize = 5 * 1024 * 1024; // 5MB

        if (!allowedTypes.includes(file.type)) {
            this.showNotification('Tipo de arquivo não permitido. Use: JPG, PNG, GIF ou WebP', 'error');
            return false;
        }

        if (file.size > maxSize) {
            this.showNotification('Arquivo muito grande. Tamanho máximo: 5MB', 'error');
            return false;
        }

        return true;
    }

    /**
     * Show featured image preview
     */
    showFeaturedImagePreview(file) {
        const previewContainer = document.getElementById('featured-image-preview');
        if (!previewContainer) return;

        const reader = new FileReader();
        reader.onload = (e) => {
            previewContainer.innerHTML = `
                <div class="relative">
                    <img src="${e.target.result}" alt="Preview" class="w-full h-48 object-cover rounded-lg">
                    <button type="button"
                            onclick="newsInteractive.removeFeaturedImage()"
                            class="absolute top-2 right-2 bg-red-500 hover:bg-red-600 text-white rounded-full p-1 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            `;
        };
        reader.readAsDataURL(file);
    }

    /**
     * Add gallery image preview
     */
    addGalleryImagePreview(file) {
        const galleryContainer = document.getElementById('gallery-images-preview');
        if (!galleryContainer) return;

        const fileId = Date.now() + Math.random();
        this.uploadedFiles.set(fileId, file);

        const reader = new FileReader();
        reader.onload = (e) => {
            const imageDiv = document.createElement('div');
            imageDiv.className = 'relative group';
            imageDiv.dataset.fileId = fileId;

            imageDiv.innerHTML = `
                <img src="${e.target.result}" alt="Gallery image" class="w-full h-32 object-cover rounded-lg">
                <button type="button"
                        onclick="newsInteractive.removeGalleryImage('${fileId}')"
                        class="absolute top-2 right-2 bg-red-500 hover:bg-red-600 text-white rounded-full p-1 opacity-0 group-hover:opacity-100 transition-opacity">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
                <div class="absolute inset-0 bg-blue-500 bg-opacity-20 opacity-0 group-hover:opacity-100 transition-opacity rounded-lg flex items-center justify-center">
                    <span class="text-white text-xs font-medium">Clique para remover</span>
                </div>
            `;

            galleryContainer.appendChild(imageDiv);
        };
        reader.readAsDataURL(file);
    }

    /**
     * Initialize drag and drop functionality
     */
    initDragAndDrop() {
        const dropZones = document.querySelectorAll('.drop-zone');

        dropZones.forEach(zone => {
            zone.addEventListener('dragover', (e) => {
                e.preventDefault();
                zone.classList.add('drag-over');
            });

            zone.addEventListener('dragleave', (e) => {
                e.preventDefault();
                zone.classList.remove('drag-over');
            });

            zone.addEventListener('drop', (e) => {
                e.preventDefault();
                zone.classList.remove('drag-over');

                const files = Array.from(e.dataTransfer.files);
                const isGallery = zone.dataset.type === 'gallery';

                if (isGallery) {
                    files.forEach(file => {
                        if (this.validateImageFile(file)) {
                            this.addGalleryImagePreview(file);
                        }
                    });
                } else if (files.length > 0) {
                    const file = files[0];
                    if (this.validateImageFile(file)) {
                        this.showFeaturedImagePreview(file);
                        // Update the input
                        const input = document.getElementById('featured_image');
                        if (input) {
                            const dt = new DataTransfer();
                            dt.items.add(file);
                            input.files = dt.files;
                        }
                    }
                }
            });
        });
    }

    /**
     * Initialize multi-select searchable topics
     */
    initTopicMultiSelect() {
        const topicSelect = document.getElementById('topics-multiselect');
        if (!topicSelect) return;

        // Create custom multi-select component
        this.createTopicMultiSelect(topicSelect);
    }

    /**
     * Create custom topic multi-select
     */
    createTopicMultiSelect(originalSelect) {
        const container = document.createElement('div');
        container.className = 'relative';

        // Hide original select
        originalSelect.style.display = 'none';
        originalSelect.parentNode.insertBefore(container, originalSelect);

        // Create search input
        const searchInput = document.createElement('input');
        searchInput.type = 'text';
        searchInput.placeholder = 'Buscar tópicos...';
        searchInput.className = 'w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500';

        // Create dropdown
        const dropdown = document.createElement('div');
        dropdown.className = 'absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg hidden max-h-60 overflow-y-auto';

        // Create selected items container
        const selectedContainer = document.createElement('div');
        selectedContainer.className = 'flex flex-wrap gap-2 mt-2';

        container.appendChild(searchInput);
        container.appendChild(dropdown);
        container.appendChild(selectedContainer);

        // Load topics
        this.loadTopicsForMultiSelect(dropdown, originalSelect);

        // Search functionality
        searchInput.addEventListener('input', (e) => {
            this.filterTopics(dropdown, e.target.value);
        });

        // Show/hide dropdown
        searchInput.addEventListener('focus', () => {
            dropdown.classList.remove('hidden');
        });

        document.addEventListener('click', (e) => {
            if (!container.contains(e.target)) {
                dropdown.classList.add('hidden');
            }
        });
    }

    /**
     * Load topics for multi-select
     */
    async loadTopicsForMultiSelect(dropdown, originalSelect) {
        try {
            const response = await fetch('/admin/news-topics/api', {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (response.ok) {
                const topics = await response.json();
                this.renderTopicOptions(dropdown, topics, originalSelect);
            }
        } catch (error) {
            console.error('Error loading topics:', error);
        }
    }

    /**
     * Render topic options
     */
    renderTopicOptions(dropdown, topics, originalSelect) {
        dropdown.innerHTML = '';

        topics.forEach(topic => {
            const option = document.createElement('div');
            option.className = 'px-3 py-2 hover:bg-gray-100 cursor-pointer flex items-center justify-between';
            option.dataset.topicId = topic.id;

            option.innerHTML = `
                <div class="flex items-center">
                    <div class="w-3 h-3 rounded-full mr-2" style="background-color: ${topic.color}"></div>
                    <span>${topic.name}</span>
                </div>
                <div class="text-xs text-gray-500">${topic.news_count || 0} notícias</div>
            `;

            option.addEventListener('click', () => {
                this.toggleTopicSelection(topic, originalSelect);
            });

            dropdown.appendChild(option);
        });
    }

    /**
     * Toggle topic selection
     */
    toggleTopicSelection(topic, originalSelect) {
        const isSelected = this.selectedTopics.has(topic.id);

        if (isSelected) {
            this.selectedTopics.delete(topic.id);
            this.removeTopicTag(topic.id);
        } else {
            this.selectedTopics.add(topic.id);
            this.addTopicTag(topic);
        }

        this.updateOriginalSelect(originalSelect);
    }

    /**
     * Add topic tag
     */
    addTopicTag(topic) {
        const selectedContainer = document.querySelector('.relative').lastElementChild;

        const tag = document.createElement('span');
        tag.className = 'inline-flex items-center px-2 py-1 rounded-md text-xs font-medium';
        tag.style.backgroundColor = topic.color + '20';
        tag.style.color = topic.color;
        tag.dataset.topicId = topic.id;

        tag.innerHTML = `
            ${topic.name}
            <button type="button" class="ml-1 hover:text-red-500" onclick="newsInteractive.removeTopicSelection('${topic.id}')">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        `;

        selectedContainer.appendChild(tag);
    }

    /**
     * Remove topic tag
     */
    removeTopicTag(topicId) {
        const tag = document.querySelector(`[data-topic-id="${topicId}"]`);
        if (tag) {
            tag.remove();
        }
    }

    /**
     * Initialize delete confirmation modals
     */
    initDeleteConfirmation() {
        // Create delete confirmation modal if it doesn't exist
        if (!document.getElementById('deleteModal')) {
            this.createDeleteModal();
        }
    }

    /**
     * Create delete confirmation modal
     */
    createDeleteModal() {
        const modalHTML = `
            <div id="deleteModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50" role="dialog" aria-modal="true">
                <div class="relative top-20 mx-auto p-5 border w-full max-w-md shadow-lg rounded-md bg-white">
                    <div class="mt-3 text-center">
                        <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-4">
                            <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                            </svg>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Confirmar Exclusão</h3>
                        <div class="mt-2 px-7 py-3">
                            <p class="text-sm text-gray-500" id="deleteMessage">
                                Tem certeza que deseja excluir esta notícia? Esta ação não pode ser desfeita.
                            </p>
                        </div>
                        <div class="flex justify-center space-x-3 mt-4">
                            <button type="button"
                                    onclick="newsInteractive.closeDeleteModal()"
                                    class="px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-700 rounded-md transition-colors duration-200">
                                Cancelar
                            </button>
                            <button type="button"
                                    id="confirmDeleteBtn"
                                    class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-md transition-colors duration-200">
                                Excluir
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;

        document.body.insertAdjacentHTML('beforeend', modalHTML);
    }

    /**
     * Show delete confirmation
     */
    showDeleteConfirmation(newsId, newsTitle) {
        const modal = document.getElementById('deleteModal');
        const message = document.getElementById('deleteMessage');
        const confirmBtn = document.getElementById('confirmDeleteBtn');

        message.innerHTML = `Tem certeza que deseja excluir a notícia "<strong>${newsTitle}</strong>"? Esta ação não pode ser desfeita.`;

        confirmBtn.onclick = () => this.performDelete(newsId);

        modal.classList.remove('hidden');
    }

    /**
     * Close delete modal
     */
    closeDeleteModal() {
        const modal = document.getElementById('deleteModal');
        modal.classList.add('hidden');
    }

    /**
     * Perform delete action
     */
    async performDelete(newsId) {
        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            const response = await fetch(`/admin/news/${newsId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (response.ok) {
                this.closeDeleteModal();
                this.showNotification('Notícia excluída com sucesso!', 'success');

                // Remove the news card from the grid
                const newsCard = document.querySelector(`[data-news-id="${newsId}"]`);
                if (newsCard) {
                    newsCard.remove();
                }

                // Refresh the page after a short delay
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            } else {
                throw new Error('Erro ao excluir notícia');
            }
        } catch (error) {
            console.error('Delete error:', error);
            this.showNotification('Erro ao excluir notícia', 'error');
        }
    }

    /**
     * Bind additional events
     */
    bindEvents() {
        // Close modals with Escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                const deleteModal = document.getElementById('deleteModal');
                if (deleteModal && !deleteModal.classList.contains('hidden')) {
                    this.closeDeleteModal();
                }
            }
        });

        // Form auto-save (draft functionality)
        const forms = document.querySelectorAll('form[data-auto-save]');
        forms.forEach(form => {
            this.initAutoSave(form);
        });
    }

    /**
     * Initialize auto-save functionality
     */
    initAutoSave(form) {
        let saveTimeout;
        const inputs = form.querySelectorAll('input, textarea, select');

        inputs.forEach(input => {
            input.addEventListener('input', () => {
                clearTimeout(saveTimeout);
                saveTimeout = setTimeout(() => {
                    this.autoSave(form);
                }, 2000); // Auto-save after 2 seconds of inactivity
            });
        });
    }

    /**
     * Auto-save form data
     */
    async autoSave(form) {
        try {
            const formData = new FormData(form);
            formData.append('auto_save', '1');

            const response = await fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (response.ok) {
                this.showAutoSaveIndicator();
            }
        } catch (error) {
            console.error('Auto-save error:', error);
        }
    }

    /**
     * Utility methods
     */
    showSearchIndicator(show) {
        const indicator = document.getElementById('search-indicator');
        if (indicator) {
            indicator.style.display = show ? 'block' : 'none';
        }
    }

    showFilterIndicator(show) {
        const indicator = document.getElementById('filter-indicator');
        if (indicator) {
            indicator.style.display = show ? 'block' : 'none';
        }
    }

    showAutoSaveIndicator() {
        const indicator = document.createElement('div');
        indicator.className = 'fixed top-4 right-4 bg-green-500 text-white px-3 py-1 rounded text-sm z-50';
        indicator.textContent = 'Salvo automaticamente';

        document.body.appendChild(indicator);

        setTimeout(() => {
            indicator.remove();
        }, 2000);
    }

    updateNewsGrid(html) {
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');
        const newGrid = doc.querySelector('.grid');
        const currentGrid = document.querySelector('.grid');

        if (newGrid && currentGrid) {
            currentGrid.innerHTML = newGrid.innerHTML;
        }
    }

    updateStatistics(html) {
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');
        const newStats = doc.querySelectorAll('.grid .bg-white');
        const currentStats = document.querySelectorAll('.grid .bg-white');

        newStats.forEach((stat, index) => {
            if (currentStats[index]) {
                currentStats[index].innerHTML = stat.innerHTML;
            }
        });
    }

    updateURL(params) {
        const newURL = `${window.location.pathname}?${params.toString()}`;
        window.history.pushState({}, '', newURL);
    }

    showNotification(message, type = 'info') {
        if (window.AdminErrorHandler) {
            window.AdminErrorHandler.showNotification(message, type);
        }
    }

    // Public methods for global access
    removeFeaturedImage() {
        const input = document.getElementById('featured_image');
        const preview = document.getElementById('featured-image-preview');

        if (input) input.value = '';
        if (preview) preview.innerHTML = '';
    }

    removeGalleryImage(fileId) {
        this.uploadedFiles.delete(fileId);
        const imageDiv = document.querySelector(`[data-file-id="${fileId}"]`);
        if (imageDiv) {
            imageDiv.remove();
        }
    }

    removeTopicSelection(topicId) {
        this.selectedTopics.delete(topicId);
        this.removeTopicTag(topicId);

        const originalSelect = document.getElementById('topics-multiselect');
        if (originalSelect) {
            this.updateOriginalSelect(originalSelect);
        }
    }

    updateOriginalSelect(originalSelect) {
        // Update the original select with selected topics
        Array.from(originalSelect.options).forEach(option => {
            option.selected = this.selectedTopics.has(parseInt(option.value));
        });
    }
}

// Initialize the News Interactive Manager
let newsInteractive;

document.addEventListener('DOMContentLoaded', function() {
    newsInteractive = new NewsInteractiveManager();
});

// Global functions for backward compatibility
function confirmDelete(newsId, newsTitle) {
    if (newsInteractive) {
        newsInteractive.showDeleteConfirmation(newsId, newsTitle);
    }
}

function closeDeleteModal() {
    if (newsInteractive) {
        newsInteractive.closeDeleteModal();
    }
}
