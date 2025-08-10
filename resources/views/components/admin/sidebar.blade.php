<aside id="logo-sidebar" class="fixed top-0 left-0 z-40 w-66 h-screen pt-20 transition-transform -translate-x-full bg-white border-r border-gray-200 sm:translate-x-0" aria-label="Sidebar">
     <div class="h-full px-3 pb-4 overflow-y-auto bg-white ">
        <ul class="space-y-2 font-medium">
           <li>
              <a href="{{ route('admin.dashboard') }}" class="flex items-center p-2 text-gray-900 rounded-lg hover:bg-gray-100 group  {{ request()->routeIs('admin.dashboard') ? 'bg-zinc-200' : '' }}">

                 <span class="ms-3">inicio</span>
              </a>
           </li>
           <li>
              <a href="{{ route('admin.vinyls.index') }}" class="flex items-center p-2 text-gray-900 rounded-lg  hover:bg-gray-100 group {{ request()->routeIs('admin.vinyls.*') ? 'bg-zinc-200' : '' }}">

                 <span class="flex-1 ms-3 whitespace-nowrap">Discos</span>
                 <span class="inline-flex items-center justify-center px-2 ms-3 text-sm font-medium text-gray-800 bg-gray-100 rounded-full">principal</span>
              </a>
           </li>
           <li>
              <a href="{{ route('admin.settings') }}" class="flex items-center p-2 text-gray-900 rounded-lg  hover:bg-gray-100 {{ request()->routeIs('admin.settings') ? 'bg-zinc-200' : '' }} group">

                 <span class="flex-1 ms-3 whitespace-nowrap">Configurações</span>

              </a>
           </li>
           <li>
              <a href="{{ route('admin.orders.index') }}" class="flex items-center p-2 text-gray-900 rounded-lg  hover:bg-gray-100  group {{ request()->routeIs('admin.orders.*') ? 'bg-zinc-200' : '' }}">

                 <span class="flex-1 ms-3 whitespace-nowrap">Pedidos Online</span>
              </a>
           </li>
           <li>
              <a href="{{ route('admin.pos.index') }}" class="flex items-center p-2 text-gray-900 rounded-lg  hover:bg-gray-100  group {{ request()->routeIs('admin.pos.*') ? 'bg-zinc-200' : '' }}">
                 <span class="flex-1 ms-3 whitespace-nowrap">Ponto de Venda</span>
              </a>
           </li>
           <li>
              <a href="{{ route('admin.reports.index') }}" class="flex items-center p-2 text-gray-900 rounded-lg  hover:bg-gray-100  group {{ request()->routeIs('admin.reports.*') ? 'bg-zinc-200' : '' }}">

                 <span class="flex-1 ms-3 whitespace-nowrap">Relatórios</span>
              </a>
           </li>


           <li>
              <a href="{{ route('admin.market-analysis.index') }}" class="flex items-center p-2 text-gray-900 rounded-lg  hover:bg-gray-100  group {{ request()->routeIs('admin.market-analysis.*') ? 'bg-zinc-200' : '' }}">

                 <span class="flex-1 ms-3 whitespace-nowrap">Análise de Mercado</span>
                 <span class="inline-flex items-center justify-center px-2 ms-3 text-sm font-medium text-gray-800 bg-green-100 rounded-full">Discogs</span>
              </a>
           </li>





         <li>
            <a href="{{ route('admin.playlists.index') }}" class="flex items-center p-2 text-gray-900 rounded-lg  hover:bg-gray-100  group {{ request()->routeIs('admin.playlists.*') ? 'bg-zinc-200' : '' }}">

               <span class="flex-1 ms-3 whitespace-nowrap">Playlists</span>
            </a>
         </li>
        </ul>
     </div>
  </aside>
