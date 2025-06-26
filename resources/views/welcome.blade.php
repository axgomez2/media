<!doctype html>
<html>

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src='https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4'></script>

  <!-- google font -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link
    href="https://fonts.googleapis.com/css2?family=Inter:wght@100..900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap"
    rel="stylesheet">

  <style>
    body {
      font-family: Poppins, sans-serif;
    }
  </style>
</head>

<body class="max-w-[1920px] mx-auto">

  <div class="bg-white text-slate-900 text-base">

    <!-- content -->
    <div class="min-h-[calc(100vh-70px)] flex items-center justify-center">
      <div class="p-4">
        <div class="flex justify-center">
          <img src="images/borken.png" class="w-66 h-66 object-contain " alt='not-found-img' />
        </div>
        <div class="mt-8 text-center max-w-xl mx-auto">
          <h1 class="text-6xl font-bold mb-6">404</h1>
          <h2 class="text-2xl font-semibold mb-4">Oops! pagina nao encontrada</h2>
          <p class="text-slate-600">Desculpe, a pagina que voce esta procurando nao existe ou pode ter sido movida. Por favor, verifique a URL.</p>
          <a href="javascript:void(0)">
            <button
              class="cursor-pointer px-5 py-2.5 rounded-full font-medium tracking-wide text-white border border-blue-700 bg-blue-700 hover:bg-blue-800 transition-all mt-6">Voltar
              para Home</button>
          </a>
        </div>
      </div>
    </div>

  </div>

  <script>

    document.addEventListener('DOMContentLoaded', () => {
      // Navbar js
      var toggleOpen = document.getElementById('toggleOpen');
      var toggleClose = document.getElementById('toggleClose');
      var collapseMenu = document.getElementById('collapseMenu');

      function handleClick() {
        if (collapseMenu.style.display === 'block') {
          collapseMenu.style.display = 'none';
        } else {
          collapseMenu.style.display = 'block';
        }
      }

      toggleOpen.addEventListener('click', handleClick);
      toggleClose.addEventListener('click', handleClick);

    });

  </script>
</body>

</html>