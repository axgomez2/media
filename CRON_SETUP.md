# Configuração do Cron Job - Análise de Mercado

## Configuração no Servidor

Para configurar o cron job da análise de mercado no servidor, adicione as seguintes linhas ao crontab:

### 1. Editar o crontab
```bash
crontab -e
```

### 2. Adicionar a linha do cron job
```bash
# Análise de mercado Discogs - executar diariamente às 6:00
0 6 * * * cd /caminho/para/painel-admin && php artisan schedule:run >> /dev/null 2>&1
```

### 3. Configuração alternativa (executar comando direto)
```bash
# Análise de mercado Discogs - executar diariamente às 6:00
0 6 * * * cd /caminho/para/painel-admin && php artisan market:analyze >> /dev/null 2>&1
```

## Verificação

Para verificar se o cron job está funcionando:

### 1. Verificar se o cron está listado
```bash
crontab -l
```

### 2. Testar o comando manualmente
```bash
cd /caminho/para/painel-admin
php artisan market:analyze
```

### 3. Verificar logs do Laravel
```bash
tail -f storage/logs/laravel.log
```

## Configuração da API do Discogs

Certifique-se de que a chave da API do Discogs está configurada no arquivo `.env`:

```env
DISCOGS_TOKEN=sua_chave_aqui
```

## Configuração no config/services.php

Adicione a configuração do Discogs no arquivo `config/services.php`:

```php
'discogs' => [
    'token' => env('DISCOGS_TOKEN'),
],
```

## Notas Importantes

1. **Limite de API**: O Discogs tem limite de requisições por minuto. O sistema inclui delays para não sobrecarregar a API.

2. **Tempo de Execução**: A análise pode demorar alguns minutos para ser concluída, dependendo da quantidade de dados.

3. **Armazenamento**: Os dados são armazenados na tabela `market_analysis` e podem ser visualizados no painel administrativo.

4. **Timezone**: O cron job está configurado para executar no timezone "America/Sao_Paulo".

## Monitoramento

Para monitorar a execução do cron job:

1. **Logs do Laravel**: Verificar `storage/logs/laravel.log`
2. **Logs do Sistema**: Verificar `/var/log/cron.log` (Linux)
3. **Painel Admin**: Acessar a seção "Análise de Mercado" no painel

## Troubleshooting

### Cron job não está executando:
- Verificar se o cron service está rodando: `systemctl status cron`
- Verificar permissões do usuário
- Verificar se o PHP está no PATH

### Erro de permissão:
```bash
chmod +x /caminho/para/painel-admin/artisan
```

### Erro de configuração:
- Verificar se as variáveis de ambiente estão corretas
- Verificar se as dependências estão instaladas
- Executar `composer install` se necessário 
