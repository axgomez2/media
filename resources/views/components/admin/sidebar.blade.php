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
              <a href="{{ route('admin.two-factor.show') }}" class="flex items-center p-2 text-gray-900 rounded-lg  hover:bg-gray-100 {{ request()->routeIs('admin.two-factor.*') ? 'bg-zinc-200' : '' }} group">
                 <svg class="w-5 h-5 text-gray-500 transition duration-75 group-hover:text-gray-900" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"></path>
                 </svg>
                 <span class="flex-1 ms-3 whitespace-nowrap">Segurança (2FA)</span>
                 @auth
                    @if(auth()->user()->hasTwoFactorEnabled())
                       <span class="inline-flex items-center justify-center px-2 ms-3 text-sm font-medium text-white bg-green-500 rounded-full">Ativo</span>
                    @else
                       <span class="inline-flex items-center justify-center px-2 ms-3 text-sm font-medium text-gray-800 bg-yellow-100 rounded-full">Inativo</span>
                    @endif
                 @endauth
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
         <li>
            <a href="{{ route('admin.news.index') }}" class="flex items-center p-2 text-gray-900 rounded-lg  hover:bg-gray-100  group {{ request()->routeIs('admin.news.*') && !request()->routeIs('admin.news-topics.*') ? 'bg-zinc-200' : '' }}">
               <svg class="w-5 h-5 text-gray-500 transition duration-75 group-hover:text-gray-900" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                  <path d="M5 5V.13a2.96 2.96 0 0 0-1.293.749L.879 3.707A2.96 2.96 0 0 0 .13 5H5Z"/>
                  <path d="M6.737 11.061a2.961 2.961 0 0 1 .81-1.515l6.117-6.116A4.839 4.839 0 0 1 16 2.141V2a1.97 1.97 0 0 0-1.933-2H7v5a2 2 0 0 1-2 2H0v11a1.969 1.969 0 0 0 1.933 2h12.134A1.97 1.97 0 0 0 16 18v-3.093l-1.546 1.546c-.413.413-.94.695-1.513.81l-3.4.679a2.947 2.947 0 0 1-1.85-.227 2.96 2.96 0 0 1-1.635-3.257l.681-3.397Z"/>
                  <path d="M8.961 16a.93.93 0 0 0 .189-.019l3.4-.679a.961.961 0 0 0 .49-.263l6.118-6.117a2.884 2.884 0 0 0-4.079-4.078l-6.117 6.117a.96.96 0 0 0-.263.491l-.679 3.4A.961.961 0 0 0 8.961 16Zm7.477-9.8a.958.958 0 0 1 .68-.281.961.961 0 0 1 .682 1.644l-.315.315-1.36-1.36.313-.318Zm-5.911 5.911 4.236-4.236 1.359 1.359-4.236 4.237-1.7.339.341-1.699Z"/>
               </svg>
               <span class="flex-1 ms-3 whitespace-nowrap">Notícias</span>
            </a>
         </li>
         <li>
            <a href="{{ route('admin.news-topics.index') }}" class="flex items-center p-2 text-gray-900 rounded-lg  hover:bg-gray-100  group {{ request()->routeIs('admin.news-topics.*') ? 'bg-zinc-200' : '' }}">
               <svg class="w-5 h-5 text-gray-500 transition duration-75 group-hover:text-gray-900" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                  <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z"/>
               </svg>
               <span class="flex-1 ms-3 whitespace-nowrap">Tópicos</span>
            </a>
         </li>
        </ul>
     </div>
  </aside>
