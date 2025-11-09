# 🧾 Formatação do Invoice - Nota de Venda

## ✅ Alterações Implementadas

### **1. Cabeçalho do Invoice (3 Colunas)**

O cabeçalho foi reformatado para layout profissional em 3 colunas:

```
┌──────────────┬─────────────────────────┬──────────────────┐
│   LOGO       │    DADOS DA EMPRESA     │  NOTA DE VENDA   │
│  (Esquerda)  │       (Centro)          │    (Direita)     │
└──────────────┴─────────────────────────┴──────────────────┘
```

---

## 📋 Estrutura do Cabeçalho

### **Coluna 1: Logo (Esquerda)**
- Logo da empresa
- Tamanho: 80px x 60px (menor e proporcional)
- Alinhamento: Esquerda

### **Coluna 2: Dados da Empresa (Centro)**
```
RDV DISCOS DE VINIL
CNPJ: 61.850.546/0001-26
Telefone: (11) 94715-9293
Rua Montevidéu, 174 - Santo André - SP
CEP: 09220-360
```

### **Coluna 3: Informações do Invoice (Direita)**
```
NOTA DE VENDA
#INV-00123
05/11/2025 19:30
```

---

## 🎨 Estilos CSS

### **Grid Layout**
```css
.company-header {
    display: grid;
    grid-template-columns: 1fr 2fr 1fr;  /* Logo | Empresa | Invoice */
    gap: 15px;
    align-items: center;
    border-bottom: 2px solid #000;
}
```

### **Logo**
```css
.logo {
    max-width: 80px;   /* Reduzido de 120px */
    max-height: 60px;  /* Reduzido de 80px */
}
```

### **Dados da Empresa**
```css
.company-info h1 {
    font-size: 16px;
    font-weight: bold;
}

.company-info p {
    font-size: 10px;
    margin: 3px 0;
}
```

### **Informações do Invoice**
```css
.invoice-info {
    text-align: right;
}

.invoice-number {
    font-size: 13px;
    font-weight: bold;
}

.invoice-date {
    font-size: 10px;
}
```

---

## 🌎 Configuração de Timezone para Brasil

### **Arquivo: `.env`**

Adicione as seguintes configurações:

```env
# Timezone do Brasil (Horário de Brasília)
APP_TIMEZONE=America/Sao_Paulo

# Localização em Português BR
APP_LOCALE=pt_BR
APP_FALLBACK_LOCALE=pt_BR
APP_FAKER_LOCALE=pt_BR
```

### **Timezones Disponíveis no Brasil:**

| Timezone | Região | UTC Offset |
|----------|--------|------------|
| `America/Sao_Paulo` | Brasília (padrão) | UTC-3 |
| `America/Manaus` | Amazonas | UTC-4 |
| `America/Noronha` | Fernando de Noronha | UTC-2 |
| `America/Rio_Branco` | Acre | UTC-5 |

**Recomendado:** `America/Sao_Paulo` (cobre a maior parte do Brasil incluindo SP)

---

## ⚙️ Arquivo: `config/app.php`

Modificado para aceitar variável de ambiente:

```php
'timezone' => env('APP_TIMEZONE', 'UTC'),
```

**Antes:**
```php
'timezone' => 'UTC',  // Fixo
```

**Depois:**
```php
'timezone' => env('APP_TIMEZONE', 'UTC'),  // Dinâmico
```

---

## 📅 Formatos de Data

Com timezone configurado, as datas serão exibidas no horário de Brasília:

### **No Invoice:**
```blade
{{ $sale->created_at->format('d/m/Y H:i') }}
```

**Saída:** `05/11/2025 19:30`

### **Outros Formatos Úteis:**

```php
// Data completa por extenso
{{ $sale->created_at->translatedFormat('d \d\e F \d\e Y à\s H:i') }}
// Saída: 05 de novembro de 2025 às 19:30

// Data curta
{{ $sale->created_at->format('d/m/Y') }}
// Saída: 05/11/2025

// Horário
{{ $sale->created_at->format('H:i:s') }}
// Saída: 19:30:45

// Data e hora brasileira
{{ $sale->created_at->format('d/m/Y \à\s H:i') }}
// Saída: 05/11/2025 às 19:30
```

---

## 🔄 Aplicar as Configurações

Após modificar o `.env`, limpe o cache do Laravel:

```bash
cd c:\Users\dj_al\Herd\painel-admin

# Limpar cache de configuração
php artisan config:clear

# Limpar cache geral
php artisan cache:clear

# Recriar cache de configuração (opcional)
php artisan config:cache
```

---

## 🧪 Como Testar

### **1. Acessar a venda:**
```
http://painel-admin.test/admin/pos/{id}
```

### **2. Clicar em "Imprimir"**

### **3. Verificar:**
- ✅ Logo menor à esquerda
- ✅ Dados da empresa no centro
- ✅ Número do invoice à direita
- ✅ Data e hora no fuso horário de Brasília
- ✅ Layout em 3 colunas alinhado

---

## 📂 Arquivos Modificados

### **1. `resources/views/admin/pos/show.blade.php`**
- Estilos CSS do cabeçalho (grid 3 colunas)
- HTML do cabeçalho com dados corretos
- Logo redimensionado

### **2. `.env`**
- `APP_TIMEZONE=America/Sao_Paulo`
- `APP_LOCALE=pt_BR`
- `APP_FALLBACK_LOCALE=pt_BR`
- `APP_FAKER_LOCALE=pt_BR`

### **3. `config/app.php`**
- `'timezone' => env('APP_TIMEZONE', 'UTC')`

---

## 🖨️ Preview do Invoice

```
┌─────────────────────────────────────────────────────────────────┐
│                                                                 │
│  [LOGO]     RDV DISCOS DE VINIL               NOTA DE VENDA     │
│             CNPJ: 61.850.546/0001-26          #INV-00123        │
│             Tel: (11) 94715-9293              05/11/2025 19:30  │
│             Rua Montevidéu, 174                                 │
│             Santo André - SP                                    │
│             CEP: 09220-360                                      │
│                                                                 │
├─────────────────────────────────────────────────────────────────┤
│  DADOS DO CLIENTE                                               │
│  Nome: João Silva                                               │
│  Email: joao@example.com                                        │
│  ...                                                            │
├─────────────────────────────────────────────────────────────────┤
│  ITENS DA VENDA                                                 │
│  ...                                                            │
└─────────────────────────────────────────────────────────────────┘
```

---

## 🎯 Dados da Empresa

Conforme solicitado, os dados no invoice são:

- **Nome:** RDV DISCOS DE VINIL
- **CNPJ:** 61.850.546/0001-26
- **Telefone:** (11) 94715-9293
- **Endereço:** Rua Montevidéu, 174 - Santo André - SP
- **CEP:** 09220-360

---

## 🚀 Próximos Passos

1. ✅ Limpar cache do Laravel
2. ✅ Testar impressão do invoice
3. ✅ Verificar timezone nas datas
4. ⏳ Adicionar logo.png em `public/images/logo.png`
5. ⏳ Ajustar cores/fontes se necessário

---

## 📝 Observações

### **Importante sobre o Logo:**

Certifique-se de ter o arquivo do logo no local correto:
```
public/images/logo.png
```

Se não existir, a impressão mostrará erro de imagem. Coloque um logo adequado neste caminho.

### **Formato Recomendado do Logo:**
- **Tipo:** PNG com fundo transparente
- **Tamanho:** 200x150px (será redimensionado para 80x60px)
- **Resolução:** 300 DPI (para boa qualidade na impressão)

---

## 🌐 Timezone: Entendendo a Diferença

### **Antes (UTC):**
- Hora do servidor: 22:30 (UTC)
- Exibido no invoice: 22:30

### **Depois (America/Sao_Paulo):**
- Hora do servidor: 22:30 (UTC)
- Convertido para: 19:30 (Brasília, UTC-3)
- Exibido no invoice: 19:30 ✅

**Conclusão:** Todas as datas agora serão exibidas no horário de Brasília!

---

**Data de Implementação:** 09/11/2025  
**Arquivos Modificados:** 3  
**Status:** ✅ Implementado - Aguardando teste
