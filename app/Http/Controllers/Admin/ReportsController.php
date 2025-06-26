<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VinylSec;
use App\Models\VinylMaster;
use App\Models\VinylView;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Wishlist;
use App\Models\Wantlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportsController extends Controller
{
    public function index()
    {
        // Dashboard geral com links para os diversos relatórios
        
        // Estatísticas rápidas para exibir no dashboard
        $cartItemsCount = CartItem::count();
        $wishlistItemsCount = Wishlist::count();
        $wantlistItemsCount = Wantlist::count();
        $viewsCount = DB::table('vinyl_views')->count();
        
        return view('admin.reports.index', compact(
            'cartItemsCount',
            'wishlistItemsCount',
            'wantlistItemsCount',
            'viewsCount'
        ));
    }

    public function vinyl()
    {
        // Estatísticas gerais de estoque
        $totalDiscs = VinylSec::count();
        $availableDiscs = VinylSec::where('in_stock', true)->count();
        $unavailableDiscs = $totalDiscs - $availableDiscs;
        
        // Valores totais
        $totalBuyValue = VinylSec::sum('buy_price');
        $totalSellValue = VinylSec::sum('price');
        $potentialProfit = $totalSellValue - $totalBuyValue;
        
        // Dados para a lista de discos
        $discs = VinylSec::with(['vinylMaster', 'vinylMaster.artists', 'supplier', 'midiaStatus', 'coverStatus'])
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Dados agrupados por fornecedor com supplier_id não nulo
        $supplierStats = VinylSec::select(
                DB::raw('IFNULL(supplier_id, 0) as supplier_id'), 
                DB::raw('COUNT(*) as total_discs'),
                DB::raw('SUM(buy_price) as total_buy'),
                DB::raw('SUM(price) as total_sell'),
                DB::raw('SUM(case when in_stock = 1 then 1 else 0 end) as available')
            )
            ->groupBy(DB::raw('IFNULL(supplier_id, 0)'))
            ->get();
            
        // Enriquecendo dados com nomes dos fornecedores
        $supplierStats->map(function($item) {
            if ($item->supplier_id == 0) {
                $item->supplier_name = 'Origem Desconhecida';
            } else {
                $supplier = Supplier::find($item->supplier_id);
                $item->supplier_name = $supplier ? $supplier->name : 'Fornecedor Inválido';
            }
            return $item;
        });

        return view('admin.reports.vinyl', compact(
            'totalDiscs', 
            'availableDiscs', 
            'unavailableDiscs', 
            'totalBuyValue', 
            'totalSellValue', 
            'potentialProfit',
            'discs',
            'supplierStats'
        ));
    }
    
    /**
     * Exibe relatório de discos em carrinhos de compras
     */
    public function carts()
    {
        // Buscar todos os discos que estão em carrinhos ativos
        $cartItems = DB::table('cart_items')
            ->join('carts', 'cart_items.cart_id', '=', 'carts.id')
            ->join('vinyl_masters', 'cart_items.vinyl_master_id', '=', 'vinyl_masters.id')
            ->leftJoin('users', 'carts.user_id', '=', 'users.id')
            ->select(
                'vinyl_masters.id as master_id',
                'vinyl_masters.title',
                DB::raw('COUNT(DISTINCT carts.id) as cart_count'),
                DB::raw('SUM(cart_items.quantity) as total_quantity')
            )
            ->where('carts.status', 'active')
            ->groupBy('vinyl_masters.id', 'vinyl_masters.title')
            ->orderByDesc('total_quantity')
            ->get();

        return view('admin.reports.carts', compact('cartItems'));
    }
    
    /**
     * Exibe detalhes de um disco específico em carrinhos
     */
    public function cartDetails($vinylMasterId)
    {
        // Buscar informações do disco
        $vinyl = VinylMaster::with('artists')
            ->findOrFail($vinylMasterId);
            
        // Buscar usuários que têm este disco no carrinho
        $cartUsers = DB::table('cart_items')
            ->join('carts', 'cart_items.cart_id', '=', 'carts.id')
            ->leftJoin('users', 'carts.user_id', '=', 'users.id')
            ->select(
                'users.id as user_id',
                'users.name',
                'users.email',
                'cart_items.quantity',
                'carts.created_at',
                'carts.updated_at'
            )
            ->where('cart_items.vinyl_master_id', $vinylMasterId)
            ->where('carts.status', 'active')
            ->orderBy('carts.updated_at', 'desc')
            ->get();

        return view('admin.reports.cart_details', compact('vinyl', 'cartUsers'));
    }
    
    /**
     * Exibe relatório de discos em wishlists
     */
    public function wishlists()
    {
        // Buscar todos os discos que estão em wishlists
        $wishlistItems = DB::table('wishlists')
            ->join('vinyl_masters', 'wishlists.vinyl_master_id', '=', 'vinyl_masters.id')
            ->select(
                'vinyl_masters.id as master_id',
                'vinyl_masters.title',
                DB::raw('COUNT(DISTINCT wishlists.user_id) as user_count')
            )
            ->groupBy('vinyl_masters.id', 'vinyl_masters.title')
            ->orderByDesc('user_count')
            ->get();

        return view('admin.reports.wishlists', compact('wishlistItems'));
    }
    
    /**
     * Exibe detalhes de um disco específico em wishlists
     */
    public function wishlistDetails($vinylMasterId)
    {
        // Buscar informações do disco
        $vinyl = VinylMaster::with('artists')
            ->findOrFail($vinylMasterId);
            
        // Buscar usuários que têm este disco na wishlist
        $wishlistUsers = DB::table('wishlists')
            ->join('users', 'wishlists.user_id', '=', 'users.id')
            ->select(
                'users.id as user_id',
                'users.name',
                'users.email',
                'wishlists.created_at'
            )
            ->where('wishlists.vinyl_master_id', $vinylMasterId)
            ->orderBy('wishlists.created_at', 'desc')
            ->get();

        return view('admin.reports.wishlist_details', compact('vinyl', 'wishlistUsers'));
    }
    
    /**
     * Exibe relatório de discos em wantlists
     */
    public function wantlists()
    {
        // Buscar todos os discos que estão em wantlists
        $wantlistItems = DB::table('wantlists')
            ->join('vinyl_masters', 'wantlists.vinyl_master_id', '=', 'vinyl_masters.id')
            ->select(
                'vinyl_masters.id as master_id',
                'vinyl_masters.title',
                DB::raw('COUNT(DISTINCT wantlists.user_id) as user_count')
            )
            ->groupBy('vinyl_masters.id', 'vinyl_masters.title')
            ->orderByDesc('user_count')
            ->get();

        return view('admin.reports.wantlists', compact('wantlistItems'));
    }
    
    /**
     * Exibe detalhes de um disco específico em wantlists
     */
    public function wantlistDetails($vinylMasterId)
    {
        // Buscar informações do disco
        $vinyl = VinylMaster::with('artists')
            ->findOrFail($vinylMasterId);
            
        // Buscar usuários que têm este disco na wantlist
        $wantlistUsers = DB::table('wantlists')
            ->join('users', 'wantlists.user_id', '=', 'users.id')
            ->select(
                'users.id as user_id',
                'users.name',
                'users.email',
                'wantlists.created_at'
            )
            ->where('wantlists.vinyl_master_id', $vinylMasterId)
            ->orderBy('wantlists.created_at', 'desc')
            ->get();

        return view('admin.reports.wantlist_details', compact('vinyl', 'wantlistUsers'));
    }
    
    /**
     * Exibe relatório de visualizações de discos
     */
    public function views()
    {
        // Buscar estatísticas de visualizações por disco
        $vinylViews = DB::table('vinyl_views')
            ->join('vinyl_masters', 'vinyl_views.vinyl_master_id', '=', 'vinyl_masters.id')
            ->select(
                'vinyl_masters.id as master_id',
                'vinyl_masters.title',
                DB::raw('COUNT(*) as view_count'),
                DB::raw('COUNT(DISTINCT vinyl_views.user_uuid) as unique_users'),
                DB::raw('COUNT(DISTINCT vinyl_views.ip_address) as unique_ips'),
                DB::raw('MAX(vinyl_views.viewed_at) as last_viewed')
            )
            ->groupBy('vinyl_masters.id', 'vinyl_masters.title')
            ->orderByDesc('view_count')
            ->get();

        return view('admin.reports.views', compact('vinylViews'));
    }
    
    /**
     * Exibe detalhes de visualizações de um disco específico
     */
    public function viewDetails($vinylMasterId)
    {
        // Buscar informações do disco
        $vinyl = VinylMaster::with('artists')
            ->findOrFail($vinylMasterId);
            
        // Buscar visualizações detalhadas usando o modelo Eloquent
        $views = VinylView::with('user')
            ->where('vinyl_master_id', $vinylMasterId)
            ->orderBy('viewed_at', 'desc')
            ->get();
            
        // Estatísticas de visualizações
        $viewStats = [
            'total' => $views->count(),
            'unique_users' => $views->whereNotNull('user_uuid')->unique('user_uuid')->count(),
            'unique_ips' => $views->unique('ip_address')->count(),
            'by_country' => $views->groupBy('country')->map->count(),
            'by_region' => $views->groupBy('region')->map->count(),
        ];

        return view('admin.reports.view_details', compact('vinyl', 'views', 'viewStats'));
    }
}
