<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\VinylController;
use App\Http\Controllers\Admin\TrackController;
use App\Http\Controllers\Admin\VinylImageController;
use App\Http\Controllers\Admin\CatStyleShopController;
use App\Http\Controllers\Admin\MidiaStatusController;
use App\Http\Controllers\Admin\CoverStatusController;
use App\Http\Controllers\Admin\ReportsController;
use App\Http\Controllers\Admin\PaymentSettingsController;
use App\Http\Controllers\Admin\PosSalesController;
use App\Http\Controllers\Admin\SupplierController;
use App\Http\Controllers\YouTubeController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\MarketAnalysisController;
use App\Http\Controllers\Admin\NewsController;
use App\Http\Controllers\Admin\NewsTopicController;
use Illuminate\Support\Facades\Hash;


use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\ImageController;

// Rota inicial acessível por todos
Route::get('/', function () {
    return view('welcome');
})->name('home');


Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Two Factor Authentication routes
Route::get('/two-factor/verify', [LoginController::class, 'showTwoFactorForm'])->name('two-factor.verify');
Route::post('/two-factor/verify', [\App\Http\Controllers\Admin\TwoFactorController::class, 'verify'])->name('two-factor.verify.post');
Route::post('/two-factor/recovery', [\App\Http\Controllers\Admin\TwoFactorController::class, 'verifyRecovery'])->name('two-factor.recovery');

Route::post('/youtube/search', [YouTubeController::class, 'search'])->name('youtube.search');

// Log de erros do cliente (sem middleware para permitir logs de erro)
Route::post('/admin/log-client-error', [\App\Http\Controllers\Admin\ClientErrorLogController::class, 'logError'])->name('admin.log_client_error');

// Rotas administrativas
Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    // Dashboard administrativo
    Route::get('/', [DashboardController::class, 'index'])->name('admin.dashboard');

    // Two Factor Authentication management
    Route::prefix('two-factor')->name('admin.two-factor.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\TwoFactorController::class, 'show'])->name('show');
        Route::post('/enable', [\App\Http\Controllers\Admin\TwoFactorController::class, 'enable'])->name('enable');
        Route::post('/disable', [\App\Http\Controllers\Admin\TwoFactorController::class, 'disable'])->name('disable');
        Route::get('/recovery-codes', [\App\Http\Controllers\Admin\TwoFactorController::class, 'recoveryCodes'])->name('recovery-codes');
        Route::post('/recovery-codes/regenerate', [\App\Http\Controllers\Admin\TwoFactorController::class, 'regenerateRecoveryCodes'])->name('recovery-codes.regenerate');
    });

    // User Creation
    Route::post('/users', [App\Http\Controllers\Admin\UserController::class, 'store'])->name('admin.users.store');

    // Client Users CRUD
    Route::resource('clients', App\Http\Controllers\Admin\ClientController::class)->names('admin.clients');

    // Gerenciamento de Discos
Route::prefix('discos')->group(function () {
    // Listagem e operações básicas
    Route::get('/', [VinylController::class, 'index'])->name('admin.vinyls.index');
    Route::get('/adicionar', [VinylController::class, 'create'])->name('admin.vinyls.create');
    Route::post('/salvar', [VinylController::class, 'store'])->name('admin.vinyls.store');
    Route::get('{id}', [VinylController::class, 'show'])->name('admin.vinyls.show');
    Route::get('{id}/edit', [VinylController::class, 'edit'])->name('admin.vinyls.edit');
    Route::put('{id}', [VinylController::class, 'update'])->name('admin.vinyls.update');
    Route::delete('{id}', [VinylController::class, 'destroy'])->name('admin.vinyls.destroy');

    Route::get('/{id}/completar', [VinylController::class, 'complete'])->name('admin.vinyls.complete');
    Route::post('/{id}/completar', [VinylController::class, 'storeComplete'])->name('admin.vinyl.storeComplete');

    Route::get('/{id}/images', [VinylImageController::class, 'index'])->name('admin.vinyl.images');
    Route::post('/{id}/images', [VinylImageController::class, 'store'])->name('admin.vinyl.images.store');
    Route::delete('/{id}/images/{imageId}', [VinylImageController::class, 'destroy'])->name('admin.vinyl.images.destroy');
    Route::post('/update-field', [VinylController::class, 'updateField'])->name('admin.vinyls.updateField');

    Route::post('/{id}/fetch-discogs-image', [VinylController::class, 'fetchDiscogsImage'])->name('admin.vinyls.fetch-discogs-image');
    Route::post('/{id}/upload-image', [VinylController::class, 'uploadImage'])->name('admin.vinyls.upload-image');
    Route::delete('/{id}/remove-image', [VinylController::class, 'removeImage'])->name('admin.vinyls.remove-image');

    //faixas
    Route::get('/{id}/edit-tracks', [TrackController::class, 'editTracks'])->name('admin.vinyls.edit-tracks');
    Route::put('/{id}/update-tracks', [TrackController::class, 'updateTracks'])->name('admin.vinyls.update-tracks');


});

// YouTube API - acessível sem middleware admin
Route::match(['get', 'post'], '/youtube/search', [YouTubeController::class, 'search'])->name('admin.youtube.search')->withoutMiddleware(['admin']);

Route::get('/settings', [SettingsController::class, 'index'])->name('admin.settings');

// Alterar senha do usuário admin
Route::get('/alterar-senha', function () {
    return view('admin.change-password');
})->name('admin.change-password');

// Processar alteração de senha
Route::put('/alterar-senha', function (Illuminate\Http\Request $request) {
    $request->validate([
        'current_password' => ['required', 'current_password'],
        'password' => ['required', 'string', 'min:8', 'confirmed'],
    ]);

    $user = auth()->user();
    $user->update([
        'password' => Hash::make($request->password)
    ]);

    return back()->with('success', 'Senha alterada com sucesso!');
})->name('admin.update-password');

// Gerenciamento de categorias de disco
Route::prefix('categorias')->group(function () {
    Route::get('/', [CatStyleShopController::class, 'index'])->name('admin.cat-style-shop.index');
    Route::get('/create', [CatStyleShopController::class, 'create'])->name('admin.cat-style-shop.create');
    Route::post('/', [CatStyleShopController::class, 'store'])->name('admin.cat-style-shop.store');
    Route::get('/{catStyleShop}/edit', [CatStyleShopController::class, 'edit'])->name('admin.cat-style-shop.edit');
    Route::put('/{catStyleShop}', [CatStyleShopController::class, 'update'])->name('admin.cat-style-shop.update');
    Route::delete('/{catStyleShop}', [CatStyleShopController::class, 'destroy'])->name('admin.cat-style-shop.destroy');
});

// Gerenciamento de status de mídia
Route::prefix('midia-status')->group(function () {
    Route::get('/', [MidiaStatusController::class, 'index'])->name('admin.midia-status.index');
    Route::get('/create', [MidiaStatusController::class, 'create'])->name('admin.midia-status.create');
    Route::post('/', [MidiaStatusController::class, 'store'])->name('admin.midia-status.store');
    Route::get('/{midiaStatus}/edit', [MidiaStatusController::class, 'edit'])->name('admin.midia-status.edit');
    Route::put('/{midiaStatus}', [MidiaStatusController::class, 'update'])->name('admin.midia-status.update');
    Route::delete('/{midiaStatus}', [MidiaStatusController::class, 'destroy'])->name('admin.midia-status.destroy');
});

// Gerenciamento de status de capa
Route::prefix('cover-status')->group(function () {
    Route::get('/', [CoverStatusController::class, 'index'])->name('admin.cover-status.index');
    Route::get('/create', [CoverStatusController::class, 'create'])->name('admin.cover-status.create');
    Route::post('/', [CoverStatusController::class, 'store'])->name('admin.cover-status.store');
    Route::get('/{coverStatus}/edit', [CoverStatusController::class, 'edit'])->name('admin.cover-status.edit');
    Route::put('/{coverStatus}', [CoverStatusController::class, 'update'])->name('admin.cover-status.update');
    Route::delete('/{coverStatus}', [CoverStatusController::class, 'destroy'])->name('admin.cover-status.destroy');
});

// Relatórios
Route::prefix('relatorios')->group(function () {
    Route::get('/', [ReportsController::class, 'index'])->name('admin.reports.index');
    Route::get('/discos', [ReportsController::class, 'vinyl'])->name('admin.reports.vinyl');

    // Relatórios de clientes (com middleware adicional de validação)
    Route::middleware('validate.client.reports')->group(function () {
        Route::get('/clientes', [\App\Http\Controllers\Admin\ClientReportsController::class, 'index'])->name('admin.reports.clients.index');
        Route::get('/clientes/prospects', [\App\Http\Controllers\Admin\ClientReportsController::class, 'highValueProspects'])->name('admin.reports.clients.prospects');
        Route::get('/clientes/export', [\App\Http\Controllers\Admin\ClientReportsController::class, 'export'])->name('admin.reports.clients.export');
        Route::get('/clientes/{id}', [\App\Http\Controllers\Admin\ClientReportsController::class, 'show'])->name('admin.reports.clients.show');
        Route::put('/clientes/{id}/status', [\App\Http\Controllers\Admin\ClientReportsController::class, 'updateStatus'])->name('admin.reports.clients.update_status');
        Route::post('/clientes/{id}/send-abandoned-cart-email', [\App\Http\Controllers\Admin\ClientReportsController::class, 'sendAbandonedCartEmail'])->name('admin.reports.clients.send_abandoned_cart_email');
        Route::delete('/clientes/cache', [\App\Http\Controllers\Admin\ClientReportsController::class, 'clearCache'])->name('admin.reports.clients.clear_cache');
    });

    // Relatórios de carrinhos
    Route::get('/carrinhos', [ReportsController::class, 'carts'])->name('admin.reports.carts');
    Route::get('/carrinhos/{productId}', [ReportsController::class, 'cartDetails'])->name('admin.reports.cart_details');

    // Carrinhos abertos
});

// Playlists
Route::prefix('playlists')->group(function () {
    Route::get('/', [\App\Http\Controllers\Admin\PlaylistController::class, 'index'])->name('admin.playlists.index');
    Route::get('/create', [\App\Http\Controllers\Admin\PlaylistController::class, 'create'])->name('admin.playlists.create');
    Route::post('/', [\App\Http\Controllers\Admin\PlaylistController::class, 'store'])->name('admin.playlists.store');
    Route::get('/{playlist}', [\App\Http\Controllers\Admin\PlaylistController::class, 'show'])->name('admin.playlists.show');
    Route::get('/{playlist}/edit', [\App\Http\Controllers\Admin\PlaylistController::class, 'edit'])->name('admin.playlists.edit');
    Route::put('/{playlist}', [\App\Http\Controllers\Admin\PlaylistController::class, 'update'])->name('admin.playlists.update');
    Route::delete('/{playlist}', [\App\Http\Controllers\Admin\PlaylistController::class, 'destroy'])->name('admin.playlists.destroy');
    Route::patch('/{playlist}/toggle-status', [\App\Http\Controllers\Admin\PlaylistController::class, 'toggleStatus'])->name('admin.playlists.toggle-status');
});

// Relatórios adicionais (movidos para fora do grupo playlists)
Route::prefix('relatorios')->group(function () {
    Route::get('/carrinhos-abertos', [ReportsController::class, 'carts'])->name('admin.reports.open_carts');
    Route::get('/carrinhos-abertos/{productId}/details', [ReportsController::class, 'cartDetails'])->name('admin.reports.open_cart_details');
    Route::get('/carrinhos-abertos/items/{cartId}', [ReportsController::class, 'getCartItems'])->name('admin.reports.cart_items');

    // Relatórios de wishlist
    Route::get('/wishlist', [ReportsController::class, 'wishlists'])->name('admin.reports.wishlists');
    Route::get('/wishlist/{productId}', [ReportsController::class, 'wishlistDetails'])->name('admin.reports.wishlist_details');

    // Relatórios de wantlist
    Route::get('/wantlist', [ReportsController::class, 'wantlists'])->name('admin.reports.wantlists');
    Route::get('/wantlist/{vinylMasterId}', [ReportsController::class, 'wantlistDetails'])->name('admin.reports.wantlist_details');

    // Relatórios de visualizações
    Route::get('/visualizacoes', [ReportsController::class, 'views'])->name('admin.reports.views');
    Route::get('/visualizacoes/{vinylMasterId}', [ReportsController::class, 'viewDetails'])->name('admin.reports.view_details');
});

// Configurações de Pagamento
Route::prefix('payment')->group(function () {
    Route::get('/', [PaymentSettingsController::class, 'index'])->name('admin.payment.index');
    Route::get('/{id}/edit', [PaymentSettingsController::class, 'edit'])->name('admin.payment.edit');
    Route::put('/{id}', [PaymentSettingsController::class, 'update'])->name('admin.payment.update');
});

// PDV - Point of Sale (Vendas Diretas)
Route::prefix('pdv')->group(function () {
    Route::get('/', [PosSalesController::class, 'index'])->name('admin.pos.index');
    Route::get('/nova-venda', [PosSalesController::class, 'create'])->name('admin.pos.create');
    Route::post('/venda', [PosSalesController::class, 'store'])->name('admin.pos.store');
    Route::get('/venda/{posSale}', [PosSalesController::class, 'show'])->name('admin.pos.show');
    Route::get('/vendas', [PosSalesController::class, 'list'])->name('admin.pos.list');

    // API para autocompletar
    Route::get('/buscar-usuarios', [PosSalesController::class, 'searchUsers'])->name('admin.pos.search-users');
    Route::get('/buscar-discos', [PosSalesController::class, 'searchVinyls'])->name('admin.pos.search-vinyls');
});

// Gerenciamento de fornecedores
Route::prefix('fornecedores')->group(function () {
    Route::get('/', [SupplierController::class, 'index'])->name('admin.suppliers.index');
    Route::get('/create', [SupplierController::class, 'create'])->name('admin.suppliers.create');
    Route::post('/', [SupplierController::class, 'store'])->name('admin.suppliers.store');
    Route::get('/{supplier}/edit', [SupplierController::class, 'edit'])->name('admin.suppliers.edit');
    Route::put('/{supplier}', [SupplierController::class, 'update'])->name('admin.suppliers.update');
    Route::delete('/{supplier}', [SupplierController::class, 'destroy'])->name('admin.suppliers.destroy');
});

// Gerenciamento de Artistas
// Route::prefix('artists')->name('artists.')->group(function () {
//     Route::get('/', [ArtistsController::class, 'index'])->name('index');
//     Route::get('/create', [ArtistsController::class, 'create'])->name('create');
//     Route::post('/', [ArtistsController::class, 'store'])->name('store');
//     Route::get('/{artist}/edit', [ArtistsController::class, 'edit'])->name('edit');
//     Route::put('/{artist}', [ArtistsController::class, 'update'])->name('update');
//     Route::delete('/{artist}', [ArtistsController::class, 'destroy'])->name('destroy');
// });

// Rotas para gerenciar pedidos
Route::prefix('orders')->name('admin.orders.')->group(function () {
    Route::get('/', [\App\Http\Controllers\Admin\OrdersController::class, 'index'])->name('index');
    Route::get('/{order}', [\App\Http\Controllers\Admin\OrdersController::class, 'show'])->name('show');
    Route::put('/{order}/status', [\App\Http\Controllers\Admin\OrdersController::class, 'updateStatus'])->name('update-status');
    Route::put('/{order}/payment-method', [\App\Http\Controllers\Admin\OrdersController::class, 'updatePaymentMethod'])->name('update-payment-method');
    Route::post('/{order}/shipping-label', [\App\Http\Controllers\Admin\OrdersController::class, 'generateShippingLabel'])->name('generate-shipping-label');
    // Rota GET para redirecionar com mensagem de erro caso alguém tente acessar diretamente
    Route::get('/{order}/shipping-label', function(\App\Models\Order $order) {
        return redirect()->route('admin.orders.show', $order->id)
            ->with('error', 'Para gerar uma etiqueta de envio, use o botão "Gerar Etiqueta" na página do pedido.');
    });
    Route::get('/{order}/invoice', [\App\Http\Controllers\Admin\OrdersController::class, 'generateInvoice'])->name('invoice');
});

// Tracks (Faixas de áudio)
Route::post('vinyls/{vinyl}/tracks', [TrackController::class, 'store'])->name('admin.vinyls.tracks.store');
Route::put('vinyls/{vinyl}/tracks/reorder', [TrackController::class, 'reorder'])->name('admin.vinyls.tracks.reorder');
Route::put('tracks/{track}', [TrackController::class, 'update'])->name('admin.tracks.update');
Route::delete('tracks/{track}', [TrackController::class, 'destroy'])->name('admin.tracks.destroy');

// Categorias, Estilos e Lojas
Route::resource('categories', CatStyleShopController::class, ['as' => 'admin'])->parameters(['categories' => 'category']);
Route::resource('styles', CatStyleShopController::class, ['as' => 'admin'])->parameters(['styles' => 'style']);
Route::resource('shops', CatStyleShopController::class, ['as' => 'admin'])->parameters(['shops' => 'shop']);

// Equipamentos (comentado - controller não existe)
// Route::resource('equipment', EquipmentController::class, ['as' => 'admin']);
// Route::get('equipment/{equipment}/images', [EquipmentController::class, 'showImages'])->name('admin.equipment.images');
// Route::post('equipment/{equipment}/images', [EquipmentController::class, 'storeImages'])->name('admin.equipment.images.store');

// Suppliers (Fornecedores) - Usando prefixo 'admin' para evitar conflitos
// Removendo este resource já que temos uma definição manual para fornecedores acima
// Route::resource('suppliers', SupplierController::class);

// Rotas de API para funcionalidades de IA
Route::prefix('api')->group(function () {
    // Geração de descrição e tradução com IA
    Route::post('/vinyls/generate-description', [\App\Http\Controllers\Admin\AIController::class, 'generateDescription'])->name('admin.ai.generate-description');
    Route::post('/vinyls/translate-description', [\App\Http\Controllers\Admin\AIController::class, 'translateDescription'])->name('admin.ai.translate-description');
    Route::get('/ai/status', [\App\Http\Controllers\Admin\AIController::class, 'checkStatus'])->name('admin.ai.status');
});

// Media e Cover Status - Ambos já estão definidos acima com rotas individuais
// Removido Route::resource para evitar conflitos de nomes de rotas

// Área do desenvolvedor
// Route::middleware(['developer'])->prefix('developer')->group(function () {
//     // Identidade Visual (Logo e Favicon)
//     Route::get('/branding', [DeveloperController::class, 'showBranding'])->name('admin.developer.branding');
//     Route::post('/branding', [DeveloperController::class, 'updateBranding'])->name('admin.developer.branding.update');

//     // Informações da Loja
//     Route::get('/store', [DeveloperController::class, 'showStoreInfo'])->name('admin.developer.store');
//     Route::post('/store', [DeveloperController::class, 'updateStoreInfo'])->name('admin.developer.store.update');
// });

// Análise de Mercado - Discogs
Route::prefix('analise-mercado')->group(function () {
    Route::get('/', [MarketAnalysisController::class, 'index'])->name('admin.market-analysis.index');
    Route::get('/graficos', [MarketAnalysisController::class, 'charts'])->name('admin.market-analysis.charts');
    Route::get('/historico', [MarketAnalysisController::class, 'history'])->name('admin.market-analysis.history');
    Route::post('/forcar-analise', [MarketAnalysisController::class, 'forceAnalysis'])->name('admin.market-analysis.force');
    Route::get('/exportar', [MarketAnalysisController::class, 'exportCsv'])->name('admin.market-analysis.export');
    Route::post('/auto-collect', [MarketAnalysisController::class, 'autoCollect'])->name('admin.market-analysis.auto-collect');

    // Rotas de CRUD
    Route::post('/store', [MarketAnalysisController::class, 'store'])->name('admin.market-analysis.store');
    Route::put('/{marketAnalysis}', [MarketAnalysisController::class, 'update'])->name('admin.market-analysis.update');
    Route::delete('/{marketAnalysis}', [MarketAnalysisController::class, 'destroy'])->name('admin.market-analysis.destroy');

    // APIs para dados dinâmicos
    Route::get('/api/graficos', [MarketAnalysisController::class, 'apiChartData'])->name('admin.market-analysis.api.charts');
    Route::get('/api/stats', [MarketAnalysisController::class, 'apiStats'])->name('admin.market-analysis.api.stats');
});

// Gerenciamento de Notícias
Route::prefix('news')->name('admin.news.')->group(function () {
    Route::get('/', [NewsController::class, 'index'])->name('index');
    Route::get('/create', [NewsController::class, 'create'])->name('create');
    Route::post('/', [NewsController::class, 'store'])->name('store');
    Route::get('/{news}', [NewsController::class, 'show'])->name('show');
    Route::get('/{news}/edit', [NewsController::class, 'edit'])->name('edit');
    Route::put('/{news}', [NewsController::class, 'update'])->name('update');
    Route::delete('/{news}', [NewsController::class, 'destroy'])->name('destroy');
    Route::post('/generate-content', [NewsController::class, 'generateContent'])->name('generate-content');
});

// Gerenciamento de Tópicos de Notícias
Route::prefix('news-topics')->name('admin.news-topics.')->group(function () {
    Route::get('/', [NewsTopicController::class, 'index'])->name('index');
    Route::get('/api', [NewsTopicController::class, 'api'])->name('api');
    Route::get('/create', [NewsTopicController::class, 'create'])->name('create');
    Route::post('/', [NewsTopicController::class, 'store'])->name('store');
    Route::get('/{topic}/edit', [NewsTopicController::class, 'edit'])->name('edit');
    Route::put('/{topic}', [NewsTopicController::class, 'update'])->name('update');
    Route::delete('/{topic}', [NewsTopicController::class, 'destroy'])->name('destroy');
});

});

// Rotas acessíveis apenas para usuários autenticados (exceto clientes)
Route::middleware(['auth', 'admin'])->group(function () {
    // Additional admin routes can be added here if needed
});

Route::get('/media-externa/{path}', [ImageController::class, 'show'])->where('path', '.*')->name('media.show');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/vinyl/search-discogs', [VinylController::class, 'searchDiscogs'])->name('vinyl.searchDiscogs');
    Route::get('/vinyl/get-discogs-release/{releaseId}', [VinylController::class, 'getDiscogsRelease'])->name('vinyl.getDiscogsRelease');

    // Redirecionar as rotas antigas para as novas
    Route::post('/market-analysis/auto-collect', function() {
        return redirect()->route('admin.market-analysis.auto-collect');
    });
    Route::get('/market-analysis', function() {
        return redirect()->route('admin.market-analysis.index');
    });
});
