# Design do Sistema de Notícias/Blog

## Visão Geral

O sistema de notícias/blog será implementado como uma funcionalidade completa dentro do painel administrativo existente, seguindo os padrões já estabelecidos no projeto. A implementação utilizará Tailwind CSS para estilização, componentes Flowbite para elementos de interface e JavaScript vanilla para interatividade.

## Arquitetura

### Estrutura de Arquivos

```
app/
├── Http/Controllers/Admin/
│   ├── NewsController.php (já existe - precisa completar)
│   └── NewsTopicController.php (novo)
├── Models/
│   ├── News.php (já existe - completo)
│   └── NewsTopic.php (já existe - completo)
├── Services/
│   └── AIContentService.php (novo)
└── Http/Requests/
    ├── StoreNewsRequest.php (novo)
    └── UpdateNewsRequest.php (novo)

resources/views/admin/news/
├── index.blade.php (novo)
├── create.blade.php (novo)
├── edit.blade.php (novo)
├── show.blade.php (novo)
└── topics/
    ├── index.blade.php (novo)
    ├── create.blade.php (novo)
    └── edit.blade.php (novo)

database/migrations/
├── 2025_08_10_170635_create_news_table.php (já existe)
└── 2025_08_10_170733_create_news_topics_table.php (já existe)
```

### Rotas

```php
// Dentro do grupo admin middleware
Route::prefix('news')->name('news.')->group(function () {
    Route::get('/', [NewsController::class, 'index'])->name('index');
    Route::get('/create', [NewsController::class, 'create'])->name('create');
    Route::post('/', [NewsController::class, 'store'])->name('store');
    Route::get('/{news}', [NewsController::class, 'show'])->name('show');
    Route::get('/{news}/edit', [NewsController::class, 'edit'])->name('edit');
    Route::put('/{news}', [NewsController::class, 'update'])->name('update');
    Route::delete('/{news}', [NewsController::class, 'destroy'])->name('destroy');
    Route::post('/generate-content', [NewsController::class, 'generateContent'])->name('generate-content');
});

Route::prefix('news-topics')->name('news-topics.')->group(function () {
    Route::get('/', [NewsTopicController::class, 'index'])->name('index');
    Route::get('/create', [NewsTopicController::class, 'create'])->name('create');
    Route::post('/', [NewsTopicController::class, 'store'])->name('store');
    Route::get('/{topic}/edit', [NewsTopicController::class, 'edit'])->name('edit');
    Route::put('/{topic}', [NewsTopicController::class, 'update'])->name('update');
    Route::delete('/{topic}', [NewsTopicController::class, 'destroy'])->name('destroy');
});
```

## Componentes e Interfaces

### 1. Interface de Listagem (index.blade.php)

**Layout:**
- Header com título "Notícias" e botão "Nova Notícia"
- Barra de filtros com:
  - Campo de busca (título/conteúdo)
  - Filtro por status (todos, rascunho, publicado, arquivado)
  - Filtro por tópico
  - Filtro por destaque
- Cards de estatísticas (total, publicadas, rascunhos, em destaque)
- Grid responsivo de notícias com:
  - Imagem de destaque (thumbnail)
  - Título e excerpt
  - Status badge
  - Tópicos (tags coloridas)
  - Data de publicação
  - Ações (visualizar, editar, excluir)
- Paginação

**Componentes Flowbite:**
- Buttons para ações
- Badges para status e tópicos
- Cards para layout
- Dropdown para filtros
- Modal para confirmação de exclusão

### 2. Interface de Criação/Edição (create.blade.php / edit.blade.php)

**Layout:**
- Formulário em duas colunas:
  - Coluna principal (70%):
    - Campo título com botão IA
    - Editor de conteúdo (textarea) com botão IA
    - Campo excerpt com botão IA
  - Sidebar (30%):
    - Upload de imagem de destaque
    - Galeria de imagens
    - Seleção de tópicos
    - Configurações de publicação
    - Meta dados SEO

**Funcionalidades IA:**
- Modal para geração de conteúdo
- Campos: prompt, tipo de conteúdo
- Botões para inserir conteúdo gerado
- Loading states durante geração

**Componentes Flowbite:**
- Form inputs e textareas
- File upload component
- Multi-select para tópicos
- Toggle switches
- Modal para IA
- Buttons com loading states

### 3. Interface de Visualização (show.blade.php)

**Layout:**
- Header com título e ações (editar, excluir)
- Metadados (autor, data, status, visualizações)
- Imagem de destaque
- Conteúdo formatado
- Galeria de imagens
- Tópicos associados
- Notícias relacionadas (sidebar)

### 4. Gerenciamento de Tópicos

**Interface simples:**
- Listagem em tabela
- Formulário inline para criação
- Edição modal
- Campos: nome, cor, descrição, status

## Modelos de Dados

### News Model (já implementado)
- Relacionamentos: author (User)
- Scopes: published, featured, search, byTopic
- Accessors: featured_image_url, gallery_image_urls, status_formatted, reading_time
- Auto-geração de slug

### NewsTopic Model (já implementado)
- Auto-geração de slug
- Scope para tópicos ativos

## Tratamento de Erros

### Validação
- Form Requests personalizados
- Validação de imagens (tipo, tamanho)
- Validação de slug único
- Validação de tópicos existentes

### Upload de Arquivos
- Validação de tipo e tamanho
- Armazenamento em storage/app/public/news/
- Limpeza de arquivos órfãos
- Tratamento de erros de upload

### IA Integration
- Fallback gracioso quando API falha
- Timeout handling
- Rate limiting
- Error messages user-friendly

## Estratégia de Testes

### Testes Unitários
- Models: scopes, accessors, relationships
- Services: AIContentService
- Form Requests: validação

### Testes de Feature
- CRUD operations
- File uploads
- Search e filtros
- IA content generation

### Testes de Interface
- JavaScript functionality
- Form submissions
- Modal interactions
- Responsive design

## Serviço de IA (AIContentService)

### Estrutura
```php
class AIContentService
{
    public function generateContent(string $prompt, string $type): string
    {
        // Implementação com OpenAI API ou similar
        // Tipos: title, excerpt, content, keywords
    }
    
    private function buildPrompt(string $userPrompt, string $type): string
    {
        // Templates específicos por tipo
    }
    
    private function handleApiError(\Exception $e): void
    {
        // Log e tratamento de erros
    }
}
```

### Configuração
- API key em .env
- Rate limiting
- Timeout configuration
- Fallback messages

## Interface JavaScript

### Funcionalidades
1. **Search em tempo real** - debounced input
2. **Filtros dinâmicos** - AJAX requests
3. **Upload de imagens** - preview e progress
4. **Modal de IA** - form handling e loading states
5. **Editor de conteúdo** - basic formatting
6. **Multi-select tópicos** - searchable dropdown

### Implementação Vanilla JS
```javascript
// Estrutura modular
const NewsManager = {
    search: {
        init() { /* search functionality */ },
        debounce() { /* debounced search */ }
    },
    ai: {
        openModal() { /* AI modal */ },
        generateContent() { /* API call */ }
    },
    upload: {
        handleFiles() { /* file upload */ },
        showPreview() { /* image preview */ }
    }
};
```

## Integração com Layout Existente

### Sidebar Navigation
- Adicionar item "Notícias" no menu admin
- Submenu: "Todas as Notícias", "Nova Notícia", "Tópicos"

### Breadcrumbs
- Seguir padrão existente
- Admin > Notícias > [Ação]

### Notifications
- Usar sistema de toast existente
- Success/error messages
- Progress indicators

## Performance e Otimização

### Database
- Índices já definidos nas migrations
- Eager loading para relacionamentos
- Paginação eficiente

### Frontend
- Lazy loading para imagens
- Debounced search
- Cached API responses
- Minified assets

### Storage
- Otimização de imagens
- CDN ready structure
- Cleanup de arquivos órfãos

## Segurança

### Validação
- CSRF protection
- File type validation
- Size limits
- Sanitização de conteúdo

### Autorização
- Admin middleware
- Role-based access
- File access control

### Upload Security
- Whitelist de extensões
- Scan de malware (futuro)
- Storage fora do webroot
