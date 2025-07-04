<nav class="fixed top-0 z-50 w-full bg-white border-b border-gray-200">
    <div class="px-3 py-3 lg:px-5 lg:pl-3">
      <div class="flex items-center justify-between">
        <div class="flex items-center justify-start rtl:justify-end">
          <button data-drawer-target="logo-sidebar" data-drawer-toggle="logo-sidebar" aria-controls="logo-sidebar" type="button" class="inline-flex items-center p-2 text-sm text-gray-500 rounded-lg sm:hidden hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-200  ">
              <span class="sr-only">Open sidebar</span>
              <svg class="w-6 h-6" aria-hidden="true" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                 <path clip-rule="evenodd" fill-rule="evenodd" d="M2 4.75A.75.75 0 012.75 4h14.5a.75.75 0 010 1.5H2.75A.75.75 0 012 4.75zm0 10.5a.75.75 0 01.75-.75h7.5a.75.75 0 010 1.5h-7.5a.75.75 0 01-.75-.75zM2 10a.75.75 0 01.75-.75h14.5a.75.75 0 010 1.5H2.75A.75.75 0 012 10z"></path>
              </svg>
           </button>
          <a href="https://flowbite.com" class="flex ms-2 md:me-24">
            <img src="{{ asset('images/borken.png') }}" class="h-8 me-3" alt="Logo" />
            <span class="self-center text-xl font-semibold sm:text-2xl whitespace-nowrap">Painel de admin do ale</span>
          </a>
        </div>
        <div class="flex items-center">
            <div class="flex items-center ms-3">
              <div>
                <button type="button" class="flex text-sm bg-gray-800 rounded-full focus:ring-4 focus:ring-gray-300 " aria-expanded="false" data-dropdown-toggle="dropdown-user">
                  <span class="sr-only">Open user menu</span>
                  <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-gray-500 ">
                    <span class="text-xl font-bold text-white ">
                      {{ strtoupper(auth()->user()->name[0]) }}
                    </span>
                  </span>
                </button>
              </div>
              <div class="z-50 hidden my-4 text-base list-none bg-white divide-y divide-gray-100 rounded-sm shadow-sm " id="dropdown-user">
                <div class="px-4 py-3" role="none">
                  <p class="text-sm text-gray-900" role="none">
                    {{ auth()->user()->name }}
                  </p>
                  <p class="text-sm font-medium text-gray-900 truncate" role="none">
                    {{ auth()->user()->email }}
                  </p>
                </div>
                <ul class="py-1" role="none">
                  {{-- <li>
                    <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 " role="menuitem">Dashboard</a>
                  </li>
                  <li>
                    <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 " role="menuitem">Settings</a>
                  </li>
                  <li>
                    <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 " role="menuitem">Earnings</a>
                  </li> --}}
                  <li>
                    <a href="{{ route('admin.change-password') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem">
                      🔐 Alterar Senha
                    </a>
                  </li>
                  <li>
                    <form method="POST" action="{{ route('logout') }}" class="px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 ">
                        @csrf
                        <button type="submit" class="">

                            Sair
                        </button>
                    </form>
                  </li>
                </ul>
              </div>
            </div>
          </div>
      </div>
    </div>
  </nav>


  <div class="border-t border-zinc-800 pt-4">
    <div class="flex items-center text-sm">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <span>{{ auth()->user()->name }}</span>
    </div>

</div>
