# Implementação - Salvamento de Tracks e Notas Editáveis

## Funcionalidades Implementadas

### 1. Campo de Notas Editável
- ✅ Substituído display estático por textarea editável
- ✅ Permite editar notas originais do Discogs
- ✅ Permite adicionar informações adicionais
- ✅ Dados são enviados via formulário hidden field

### 2. Salvamento de YouTube URLs nas Tracks
- ✅ Campo `youtube_url` já existe na tabela tracks
- ✅ Campo adicionado ao fillable do modelo Track
- ✅ VinylService modificado para processar youtube_url
- ✅ VinylController modificado para processar tracks editadas

## Modificações Realizadas

### 1. VinylController.php - Método store()
```php
// Processar notas editadas do formulário
$editedNotes = $request->input('notes');
if (!empty($editedNotes)) {
    $releaseData['notes'] = $editedNotes;
}

// Processar tracks editadas do formulário
$editedTracks = $request->input('tracks');
if (!empty($editedTracks) && is_array($editedTracks)) {
    // Mesclar dados das tracks editadas com as originais do Discogs
    $originalTracklist = $releaseData['tracklist'] ?? [];
    $updatedTracklist = [];

    foreach ($editedTracks as $index => $editedTrack) {
        // Usar dados editados se disponíveis, senão usar dados originais
        $originalTrack = $originalTracklist[$index] ?? [];
        
        $updatedTracklist[] = [
            'title' => !empty($editedTrack['name']) ? $editedTrack['name'] : ($originalTrack['title'] ?? ''),
            'duration' => !empty($editedTrack['duration']) ? $editedTrack['duration'] : ($originalTrack['duration'] ?? ''),
            'youtube_url' => $editedTrack['youtube_url'] ?? null,
            'position' => $originalTrack['position'] ?? ($index + 1),
            'type_' => $originalTrack['type_'] ?? null,
            'extraartists' => $originalTrack['extraartists'] ?? []
        ];
    }

    $releaseData['tracklist'] = $updatedTracklist;
}
```

### 2. VinylService.php - Método createOrUpdateTracks()
```php
// Normalizar o título da faixa
$title = trim($trackData['title']);
$duration = !empty($trackData['duration']) ? trim($trackData['duration']) : null;
$youtubeUrl = !empty($trackData['youtube_url']) ? trim($trackData['youtube_url']) : null;
$position = $position + 1; // Posição iniciando em 1

// Criar a faixa com campos adicionais
Track::updateOrCreate(
    [
        'vinyl_master_id' => $vinylMaster->id,
        'position' => $position,
    ],
    [
        'name' => $title,
        'duration' => $duration,
        'duration_seconds' => $durationSeconds,
        'youtube_url' => $youtubeUrl, // ✅ Agora salva a URL do YouTube
        'extra_info' => $extraInfoJson,
    ]
);
```

### 3. Track.php - Modelo atualizado
```php
protected $fillable = [
    'vinyl_master_id', 
    'name', 
    'duration', 
    'duration_seconds', 
    'youtube_url',      // ✅ Adicionado
    'position', 
    'extra_info'        // ✅ Adicionado
];
```

### 4. selected-release.blade.php - Interface atualizada

#### Campo de Notas Editável:
```php
<div x-data="{ notes: '{{ addslashes($release['notes'] ?? '') }}' }">
    <h4 class="text-lg font-semibold mb-2 text-gray-900">Notas e Descrição</h4>
    <div class="mb-4">
        <textarea x-model="notes" 
                  name="notes" 
                  rows="4" 
                  class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500" 
                  placeholder="Adicione notas ou descrição sobre este disco...">{{ $release['notes'] ?? '' }}</textarea>
        <p class="mt-1 text-xs text-gray-500">Você pode editar as notas originais do Discogs ou adicionar informações adicionais.</p>
    </div>
</div>
```

#### Formulário com Campos Hidden:
```php
<form action="{{ route('admin.vinyls.store') }}" method="POST" class="inline-block" id="save-vinyl-form">
    @csrf
    <input type="hidden" name="release_id" value="{{ $release['id'] }}">
    <input type="hidden" name="selected_cover_index" x-bind:value="selectedCoverIndex">
    
    <!-- Campo hidden para notas editadas -->
    <input type="hidden" name="notes" x-bind:value="document.querySelector('textarea[name=notes]')?.value || ''">
    
    <!-- Campos hidden para tracks editadas (serão populados via JavaScript) -->
    <div id="tracks-hidden-fields"></div>

    <button type="submit" @click="prepareTracksData($event)">
        <span>Salvar disco</span>
    </button>
</form>
```

#### JavaScript para Preparar Dados das Tracks:
```javascript
function prepareTracksData(event) {
    const hiddenFieldsContainer = document.getElementById('tracks-hidden-fields');
    if (!hiddenFieldsContainer) return;
    
    // Limpar campos existentes
    hiddenFieldsContainer.innerHTML = '';
    
    // Coletar dados de todas as tracks editáveis
    const trackInputs = document.querySelectorAll('input[name*="tracks["][name*="][name]"]');
    
    trackInputs.forEach((nameInput, index) => {
        const trackIndex = nameInput.name.match(/tracks\[(\d+)\]/)?.[1];
        if (trackIndex === undefined) return;
        
        // Encontrar os campos relacionados a esta track
        const durationInput = document.querySelector(`input[name="tracks[${trackIndex}][duration]"]`);
        const youtubeInput = document.querySelector(`input[name="tracks[${trackIndex}][youtube_url]"]`);
        
        // Criar campos hidden para esta track
        if (nameInput.value.trim()) {
            // Nome, duração e YouTube URL
            // ... código para criar campos hidden
        }
    });
}
```

## Fluxo de Dados

### 1. Entrada de Dados
1. Usuário edita nomes das faixas nos campos de input
2. Usuário busca e seleciona vídeos do YouTube
3. Usuário edita as notas na textarea
4. Usuário clica em "Salvar disco"

### 2. Processamento no Frontend
1. JavaScript `prepareTracksData()` coleta todos os dados editados
2. Cria campos hidden no formulário com os dados das tracks
3. Campo de notas é automaticamente incluído via Alpine.js binding
4. Formulário é submetido com todos os dados

### 3. Processamento no Backend
1. `VinylController::store()` recebe os dados
2. Processa notas editadas e substitui as originais
3. Processa tracks editadas e mescla com dados originais do Discogs
4. `VinylService::createOrUpdateTracks()` salva as tracks com YouTube URLs
5. Dados são persistidos no banco de dados

## Estrutura de Dados das Tracks

### Dados Originais do Discogs:
```php
[
    'title' => 'Nome da Faixa',
    'duration' => '3:45',
    'position' => '1',
    'type_' => 'track',
    'extraartists' => [...]
]
```

### Dados Editados pelo Usuário:
```php
[
    'name' => 'Nome Editado da Faixa',
    'duration' => '3:45',
    'youtube_url' => 'https://www.youtube.com/watch?v=...'
]
```

### Dados Finais Salvos:
```php
[
    'title' => 'Nome Editado da Faixa',  // Prioriza dados editados
    'duration' => '3:45',
    'youtube_url' => 'https://www.youtube.com/watch?v=...',  // Novo campo
    'position' => '1',
    'type_' => 'track',
    'extraartists' => [...]
]
```

## Benefícios da Implementação

1. **Flexibilidade**: Usuário pode editar nomes das faixas e adicionar URLs do YouTube
2. **Preservação**: Dados originais do Discogs são preservados quando não editados
3. **Usabilidade**: Interface intuitiva com busca automática no YouTube
4. **Consistência**: Dados são validados e processados de forma consistente
5. **Extensibilidade**: Estrutura permite adicionar mais campos editáveis facilmente

## Como Testar

1. **Teste de Notas**:
   - Busque um disco no Discogs
   - Edite o campo de notas
   - Salve o disco
   - Verifique se as notas foram salvas corretamente

2. **Teste de Tracks com YouTube**:
   - Busque um disco com faixas
   - Edite o nome de uma faixa
   - Busque um vídeo no YouTube para a faixa
   - Salve o disco
   - Verifique se o nome editado e a URL do YouTube foram salvos

3. **Teste de Dados Mesclados**:
   - Edite apenas algumas faixas
   - Verifique se as faixas não editadas mantêm os dados originais
   - Verifique se as faixas editadas têm os novos dados
