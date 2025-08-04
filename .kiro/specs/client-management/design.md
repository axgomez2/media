# Design Document - Sistema de Gestão de Clientes

## Overview

O sistema de gestão de clientes será implementado como uma extensão do painel administrativo existente, seguindo os padrões visuais e arquiteturais já estabelecidos. O sistema consistirá em múltiplas views interconectadas que permitirão visualização, busca, filtragem e gestão completa dos dados dos clientes.

## Architecture

### MVC Pattern
- **Models**: ClientUser, Address, Order, CartItem, WishlistItem
- **Controllers**: ClientReportsController para gerenciar todas as operações
- **Views**: Blade templates seguindo o padrão x-admin-layout existente

### Database Schema
```sql
-- Tabela principal já existente
client_users (id UUID, name, email, password, google_id, email_verified_at, created_at, updated_at)

-- Tabela de endereços já existente  
addresses (id, user_id UUID, street, number, complement, neighborhood, city, state, zip_code, is_default, created_at, updated_at)

-- Relacionamentos com tabelas existentes
orders (user_id references client_users.id)
cart_items (user_id references client_users.id)  
wishlists (user_id references client_users.id)
```

## Components and Interfaces

### 1. Reports Index Enhancement
**File**: `resources/views/admin/reports/index.blade.php`
- Adicionar novo card "Relatório de Clientes" 
- Ícone: usuários/pessoas
- Cor: Indigo (#6366f1)
- Contador dinâmico de clientes totais

### 2. Client Reports Controller
**File**: `app/Http/Controllers/Admin/ClientReportsController.php`

**Methods**:
```php
public function index()           // Lista de clientes com filtros
public function show($id)        // Detalhes completos do cliente  
public function export()         // Exportação CSV
public function updateStatus()   // Ativar/desativar cliente
```

### 3. Client List View
**File**: `resources/views/admin/reports/clients/index.blade.php`

**Features**:
- Tabela responsiva com paginação
- Busca em tempo real por nome/email
- Filtros: status verificação, período cadastro
- Badges visuais para status
- Botões de ação: visualizar, editar status, exportar

**Columns**:
- Avatar/Inicial do nome
- Nome completo
- Email
- Data de cadastro
- Status verificação (badge)
- Último login
- Total de pedidos
- Ações

### 4. Client Detail View  
**File**: `resources/views/admin/reports/clients/show.blade.php`

**Sections**:
- **Header**: Nome, email, avatar, status
- **Informações Pessoais**: Dados básicos, datas importantes
- **Endereços**: Lista de endereços com indicação do padrão
- **Estatísticas**: Cards com métricas do cliente
- **Pedidos**: Tabela com histórico de compras
- **Favoritos**: Grid com discos favoritados
- **Carrinho**: Itens atuais no carrinho

### 5. Client Statistics Cards
**Components**: Reutilizar padrão dos cards existentes

**Metrics**:
- Total de clientes
- Novos no mês
- Taxa de verificação de email
- Valor médio por cliente
- Clientes ativos (login últimos 30 dias)

## Data Models

### ClientUser Model Enhancement
```php
// Relacionamentos
public function addresses()      // hasMany Address
public function orders()         // hasMany Order  
public function cartItems()      // hasMany CartItem
public function wishlists()      // hasMany Wishlist
public function wantlists()      // hasMany Wantlist

// Scopes
public function scopeVerified()  // Email verificado
public function scopeActive()    // Login últimos 30 dias
public function scopeWithOrders() // Que fizeram pedidos

// Accessors
public function getAvatarAttribute() // Gerar avatar baseado no nome
public function getFullAddressAttribute() // Endereço completo padrão
```

### Address Model (Novo)
```php
protected $fillable = ['user_id', 'street', 'number', 'complement', 'neighborhood', 'city', 'state', 'zip_code', 'is_default'];

public function user() // belongsTo ClientUser
public function getFullAddressAttribute() // Endereço formatado
```

## Error Handling

### Validation Rules
- **Busca**: Sanitização de input, prevenção SQL injection
- **Filtros**: Validação de datas e valores numéricos
- **Status Update**: Verificação de permissões

### Error Responses
- **404**: Cliente não encontrado
- **403**: Sem permissão para operação
- **422**: Dados de validação inválidos
- **500**: Erro interno do servidor

### User Feedback
- Mensagens de sucesso para operações
- Alertas para ações destrutivas
- Loading states para operações demoradas
- Tooltips explicativos

## Testing Strategy

### Unit Tests
- **Models**: Relacionamentos, scopes, accessors
- **Controllers**: Métodos de busca, filtros, validações
- **Services**: Lógica de negócio, cálculos estatísticos

### Integration Tests
- **Views**: Renderização correta dos dados
- **Routes**: Acesso e permissões
- **Database**: Queries e performance

### Feature Tests
- **User Journey**: Fluxo completo de gestão de cliente
- **Filters**: Combinações de filtros e busca
- **Export**: Geração e download de relatórios

### Performance Tests
- **Large Dataset**: Teste com milhares de clientes
- **Concurrent Access**: Múltiplos administradores simultâneos
- **Query Optimization**: Índices e eager loading

## Security Considerations

### Access Control
- Middleware de autenticação admin obrigatório
- Verificação de permissões por operação
- Logs de auditoria para alterações sensíveis

### Data Protection
- Mascaramento de dados sensíveis em logs
- Criptografia de dados pessoais
- Conformidade com LGPD

### Input Validation
- Sanitização de todos os inputs
- Validação de tipos e formatos
- Prevenção de XSS e CSRF

## UI/UX Design Patterns

### Visual Consistency
- Seguir padrão de cores existente
- Usar componentes Tailwind CSS estabelecidos
- Manter hierarquia visual consistente

### Responsive Design
- Mobile-first approach
- Tabelas responsivas com scroll horizontal
- Cards empilháveis em telas pequenas

### Accessibility
- Labels apropriados para screen readers
- Contraste adequado de cores
- Navegação por teclado funcional

### Loading States
- Skeleton screens para carregamento
- Progress bars para operações longas
- Spinners para ações rápidas
