# 🤖 Instalação do Ollama para IA Local

## 📋 **Opções Disponíveis**

### **Opção 1: IA Local com Ollama (Recomendado para desenvolvimento)**
### **Opção 2: Fallback sem IA (Já implementado)**
### **Opção 3: API Externa (OpenAI, Anthropic, etc.)**

---

## 🖥️ **Instalação no Windows**

### **Método 1: Instalador Oficial**
1. Acesse: https://ollama.ai/download
2. Baixe o instalador para Windows
3. Execute como administrador
4. Ollama será instalado como serviço

### **Método 2: Via PowerShell**
```powershell
# Baixar e instalar
iwr -useb https://ollama.ai/install.ps1 | iex

# Ou via Chocolatey
choco install ollama
```

### **Configuração Windows**
```powershell
# Iniciar serviço
ollama serve

# Baixar modelo recomendado
ollama pull llama3.2:3b

# Testar
ollama run llama3.2:3b "Olá, como você está?"
```

---

## 🐧 **Instalação no VPS Linux**

### **Ubuntu/Debian**
```bash
# Instalação automática
curl -fsSL https://ollama.ai/install.sh | sh

# Ou manual
wget https://ollama.ai/download/ollama-linux-amd64
sudo mv ollama-linux-amd64 /usr/local/bin/ollama
sudo chmod +x /usr/local/bin/ollama
```

### **CentOS/RHEL/Rocky Linux**
```bash
# Instalação
curl -fsSL https://ollama.ai/install.sh | sh

# Ou via RPM
wget https://github.com/ollama/ollama/releases/download/v0.1.17/ollama-0.1.17-1.x86_64.rpm
sudo rpm -i ollama-0.1.17-1.x86_64.rpm
```

### **Configurar como Serviço (VPS)**
```bash
# Criar usuário para ollama
sudo useradd -r -s /bin/false -m -d /usr/share/ollama ollama

# Criar arquivo de serviço
sudo tee /etc/systemd/system/ollama.service > /dev/null <<EOF
[Unit]
Description=Ollama Service
After=network-online.target

[Service]
ExecStart=/usr/local/bin/ollama serve
User=ollama
Group=ollama
Restart=always
RestartSec=3
Environment="OLLAMA_HOST=0.0.0.0"

[Install]
WantedBy=default.target
EOF

# Habilitar e iniciar
sudo systemctl daemon-reload
sudo systemctl enable ollama
sudo systemctl start ollama

# Verificar status
sudo systemctl status ollama
```

### **Baixar Modelos**
```bash
# Modelo leve (3B parâmetros) - Recomendado
ollama pull llama3.2:3b

# Modelo médio (7B parâmetros)
ollama pull llama3.2:7b

# Modelo para código
ollama pull codellama:7b

# Listar modelos instalados
ollama list
```

---

## ⚙️ **Configuração no Laravel**

### **Arquivo .env**
```env
# Ollama Configuration
OLLAMA_URL=http://localhost:11434
OLLAMA_MODEL=llama3.2:3b

# Para VPS (se Ollama estiver em outro servidor)
OLLAMA_URL=http://seu-vps-ip:11434
```

### **Configuração de Rede (VPS)**
```bash
# Permitir conexões externas
sudo ufw allow 11434

# Ou configurar nginx proxy
sudo tee /etc/nginx/sites-available/ollama > /dev/null <<EOF
server {
    listen 80;
    server_name ollama.seudominio.com;
    
    location / {
        proxy_pass http://localhost:11434;
        proxy_set_header Host \$host;
        proxy_set_header X-Real-IP \$remote_addr;
    }
}
EOF

sudo ln -s /etc/nginx/sites-available/ollama /etc/nginx/sites-enabled/
sudo nginx -t && sudo systemctl reload nginx
```

---

## 🔄 **Alternativas sem IA Local**

### **1. Sistema Atual (Fallback)**
✅ **Já implementado** - Gera descrições usando templates quando Ollama não está disponível

### **2. API Externa - OpenAI**
```php
// Em config/services.php (já existe)
'openai' => [
    'api_key' => env('OPENAI_API_KEY'),
    'model' => env('OPENAI_MODEL', 'gpt-3.5-turbo'),
],
```

```env
# No .env
OPENAI_API_KEY=sk-sua-chave-aqui
OPENAI_MODEL=gpt-3.5-turbo
```

### **3. API Gratuita - Hugging Face**
```env
HUGGINGFACE_API_KEY=hf_sua-chave-aqui
HUGGINGFACE_MODEL=microsoft/DialoGPT-medium
```

---

## 🧪 **Testar Instalação**

### **Teste Local**
```bash
# Verificar se está rodando
curl http://localhost:11434/api/tags

# Testar geração
curl http://localhost:11434/api/generate -d '{
  "model": "llama3.2:3b",
  "prompt": "Descreva um disco de vinil de rock dos anos 80",
  "stream": false
}'
```

### **Teste no Laravel**
```bash
# No terminal do projeto
php artisan tinker

# Executar
$service = app(\App\Services\AIDescriptionService::class);
$result = $service->generateDescription([
    'artists' => 'Pink Floyd',
    'title' => 'The Wall',
    'year' => '1979'
]);
dd($result);
```

---

## 📊 **Requisitos de Sistema**

### **Mínimo**
- **RAM**: 4GB
- **CPU**: 2 cores
- **Disco**: 5GB livres
- **Modelo**: llama3.2:3b (2GB)

### **Recomendado**
- **RAM**: 8GB+
- **CPU**: 4+ cores
- **Disco**: 10GB+ livres
- **Modelo**: llama3.2:7b (4GB)

---

## 🚀 **Status Atual**

✅ **Sistema funciona SEM Ollama** - Usa templates como fallback
✅ **Pronto para Ollama** - Detecta automaticamente se está disponível
✅ **Configuração flexível** - Suporta local e remoto

**Recomendação**: Teste primeiro sem Ollama, depois instale quando necessário.
