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
use App\Models\Product;
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
        // Buscar todos os produtos que estão em carrinhos ativos
        $cartItems = DB::table('cart_items')
            ->join('carts', 'cart_items.cart_id', '=', 'carts.id')
            ->join('products', 'cart_items.product_id', '=', 'products.id')
            ->leftJoin('users', 'carts.user_id', '=', 'users.id')
            ->select(
                'products.id as product_id',
                'products.name as title',
                DB::raw('COUNT(DISTINCT carts.id) as cart_count'),
                DB::raw('SUM(1) as total_quantity')
            )
            ->where('carts.status', 'active')
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('total_quantity')
            ->get();

        return view('admin.reports.products_in_carts', compact('cartItems'));
    }
    
    /**
     * Exibe detalhes de um produto específico em carrinhos
     */
    public function cartDetails($productId)
    {
        // Buscar informações do produto
        $product = Product::findOrFail($productId);
            
        // Buscar usuários que têm este produto no carrinho
        $cartUsers = DB::table('cart_items')
            ->join('carts', 'cart_items.cart_id', '=', 'carts.id')
            ->leftJoin('users', 'carts.user_id', '=', 'users.id')
            ->select(
                'users.id as user_id',
                'users.name',
                'users.email',
                DB::raw('1 as quantity'), // Assumindo que quantidade é sempre 1
                'carts.created_at',
                'carts.updated_at'
            )
            ->where('cart_items.product_id', $productId)
            ->where('carts.status', 'active')
            ->orderBy('carts.updated_at', 'desc')
            ->get();

        return view('admin.reports.cart_details', compact('product', 'cartUsers'));
    }
    
    /**
     * Exibe relatório de produtos em wishlists
     */
    public function wishlists()
    {
        // Buscar todos os produtos que estão em wishlists
        $wishlistItems = DB::table('wishlists')
            ->join('products', 'wishlists.product_id', '=', 'products.id')
            ->select(
                'products.id as product_id',
                'products.name as title',
                DB::raw('COUNT(DISTINCT wishlists.user_id) as user_count')
            )
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('user_count')
            ->get();

        return view('admin.reports.wishlists', compact('wishlistItems'));
    }
    
    /**
     * Exibe detalhes de um produto específico em wishlists
     */
    public function wishlistDetails($productId)
    {
        // Buscar informações do produto
        $product = Product::findOrFail($productId);
            
        // Buscar usuários que têm este produto na wishlist
        $wishlistUsers = DB::table('wishlists')
            ->join('users', 'wishlists.user_id', '=', 'users.id')
            ->select(
                'users.id as user_id',
                'users.name',
                'users.email',
                'wishlists.created_at'
            )
            ->where('wishlists.product_id', $productId)
            ->orderBy('wishlists.created_at', 'desc')
            ->get();

        return view('admin.reports.wishlist_details', compact('product', 'wishlistUsers'));
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
    
    /**
     * Exibe todos os carrinhos ativos no sistema
     */
    public function openCarts()
    {
        // Buscar todos os carrinhos ativos com seus respectivos usuários
        $carts = Cart::with(['user'])
            ->where('status', 'active')
            ->orderBy('updated_at', 'desc')
            ->get();
            
        // Para cada carrinho, buscar a quantidade de itens e valor total
        foreach ($carts as $cart) {
            $cart->items_count = CartItem::where('cart_id', $cart->id)->count();
            $cart->total_value = 0; // Inicializa o valor total
            
            // Itens do carrinho com produtos associados para calcular valor total
            $cartItems = CartItem::where('cart_id', $cart->id)
                ->with('product')
                ->get();
                
            foreach ($cartItems as $item) {
                if ($item->product) {
                    $cart->total_value += $item->product->price;
                }
            }
        }

        return view('admin.reports.carts', compact('carts'));
    }
    
    /**
     * Retorna os itens de um carrinho específico para exibição em modal
     */
    public function getCartItems($cartId)
    {
        $cart = Cart::with('user')->findOrFail($cartId);
        
        $items = CartItem::with('product')
            ->where('cart_id', $cartId)
            ->get();
            
        $totalValue = 0;
        foreach ($items as $item) {
            if ($item->product) {
                $totalValue += $item->product->price;
            }
        }
        
        return response()->json([
            'success' => true,
            'cart' => $cart,
            'items' => $items,
            'totalValue' => $totalValue,
            'html' => view('admin.reports.partials.cart_items_modal', compact('cart', 'items', 'totalValue'))->render()
        ]);
    }
}
