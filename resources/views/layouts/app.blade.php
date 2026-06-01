<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Применяем тему до загрузки CSS, чтоб не мерцало при переключении -->
    <script>
        (function() {
            const theme = localStorage.getItem('theme') || 'light';
            document.documentElement.setAttribute('data-theme', theme);
            if (theme === 'dark') document.documentElement.classList.add('dark');
        })();
    </script>

    <title>@yield('title') - LongiHealth Analytics</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark" style="background-color: var(--bg-navbar);" >
        <div class="container">
            <a class="navbar-brand fw-bold" href="/">
                <i class="fas fa-heartbeat"></i> LongiHealth Analytics
            </a>
            <div class="ms-auto d-flex align-items-center gap-3">
                    <button id="theme-toggle" class="text-white" title="Переключить тему">
                            
                    </button>
                <span class="text-white">Главный администратор</span>
                <a href="#" class="btn btn-outline-light btn-sm">Выйти</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        @yield('content')
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</body>
</html>