<!DOCTYPE html>
<html lang="fr" class="h-full bg-gray-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Telemetry App' ?></title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="/assets/css/app.css">
    
    <!-- Alpine.js -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="h-full">
    <div x-data="{ sidebarOpen: false }" class="min-h-full">
        <!-- Navigation -->
        <nav class="bg-indigo-600">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 items-center justify-between">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <img class="h-8 w-8" src="/assets/img/logo.png" alt="Telemetry App">
                        </div>
                        <div class="hidden md:block">
                            <div class="ml-10 flex items-baseline space-x-4">
                                <a href="/" class="<?= $currentPage === 'dashboard' ? 'bg-indigo-700' : 'hover:bg-indigo-500' ?> text-white rounded-md px-3 py-2 text-sm font-medium">Dashboard</a>
                                <a href="/pilotes" class="<?= $currentPage === 'pilotes' ? 'bg-indigo-700' : 'hover:bg-indigo-500' ?> text-white rounded-md px-3 py-2 text-sm font-medium">Pilotes</a>
                                <a href="/motos" class="<?= $currentPage === 'motos' ? 'bg-indigo-700' : 'hover:bg-indigo-500' ?> text-white rounded-md px-3 py-2 text-sm font-medium">Motos</a>
                                <a href="/circuits" class="<?= $currentPage === 'circuits' ? 'bg-indigo-700' : 'hover:bg-indigo-500' ?> text-white rounded-md px-3 py-2 text-sm font-medium">Circuits</a>
                                <a href="/sessions" class="<?= $currentPage === 'sessions' ? 'bg-indigo-700' : 'hover:bg-indigo-500' ?> text-white rounded-md px-3 py-2 text-sm font-medium">Sessions</a>
                            </div>
                        </div>
                    </div>
                    <div class="hidden md:block">
                        <div class="ml-4 flex items-center md:ml-6">
                            <!-- Profile dropdown -->
                            <div class="relative ml-3" x-data="{ open: false }">
                                <div>
                                    <button @click="open = !open" type="button" class="flex max-w-xs items-center rounded-full bg-indigo-600 text-sm focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-indigo-600" id="user-menu-button">
                                        <span class="sr-only">Open user menu</span>
                                        <img class="h-8 w-8 rounded-full" src="https://ui-avatars.com/api/?name=<?= urlencode($user['username'] ?? 'User') ?>" alt="">
                                    </button>
                                </div>
                                <div x-show="open" @click.away="open = false" class="absolute right-0 z-10 mt-2 w-48 origin-top-right rounded-md bg-white py-1 shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none" role="menu">
                                    <a href="/profile" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem">Profile</a>
                                    <a href="/settings" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem">Settings</a>
                                    <form action="/logout" method="POST" class="block">
                                        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                                        <button type="submit" class="block w-full px-4 py-2 text-left text-sm text-gray-700 hover:bg-gray-100" role="menuitem">Logout</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="-mr-2 flex md:hidden">
                        <!-- Mobile menu button -->
                        <button @click="sidebarOpen = !sidebarOpen" type="button" class="inline-flex items-center justify-center rounded-md bg-indigo-600 p-2 text-indigo-200 hover:bg-indigo-500 hover:text-white focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-indigo-600">
                            <span class="sr-only">Open main menu</span>
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Mobile menu -->
            <div x-show="sidebarOpen" class="md:hidden">
                <div class="space-y-1 px-2 pb-3 pt-2 sm:px-3">
                    <a href="/" class="<?= $currentPage === 'dashboard' ? 'bg-indigo-700' : 'hover:bg-indigo-500' ?> text-white block rounded-md px-3 py-2 text-base font-medium">Dashboard</a>
                    <a href="/pilotes" class="<?= $currentPage === 'pilotes' ? 'bg-indigo-700' : 'hover:bg-indigo-500' ?> text-white block rounded-md px-3 py-2 text-base font-medium">Pilotes</a>
                    <a href="/motos" class="<?= $currentPage === 'motos' ? 'bg-indigo-700' : 'hover:bg-indigo-500' ?> text-white block rounded-md px-3 py-2 text-base font-medium">Motos</a>
                    <a href="/circuits" class="<?= $currentPage === 'circuits' ? 'bg-indigo-700' : 'hover:bg-indigo-500' ?> text-white block rounded-md px-3 py-2 text-base font-medium">Circuits</a>
                    <a href="/sessions" class="<?= $currentPage === 'sessions' ? 'bg-indigo-700' : 'hover:bg-indigo-500' ?> text-white block rounded-md px-3 py-2 text-base font-medium">Sessions</a>
                </div>
                <div class="border-t border-indigo-700 pb-3 pt-4">
                    <div class="flex items-center px-5">
                        <div class="flex-shrink-0">
                            <img class="h-10 w-10 rounded-full" src="https://ui-avatars.com/api/?name=<?= urlencode($user['username'] ?? 'User') ?>" alt="">
                        </div>
                        <div class="ml-3">
                            <div class="text-base font-medium text-white"><?= htmlspecialchars($user['username'] ?? '') ?></div>
                            <div class="text-sm font-medium text-indigo-300"><?= htmlspecialchars($user['email'] ?? '') ?></div>
                        </div>
                    </div>
                    <div class="mt-3 space-y-1 px-2">
                        <a href="/profile" class="block rounded-md px-3 py-2 text-base font-medium text-white hover:bg-indigo-500">Profile</a>
                        <a href="/settings" class="block rounded-md px-3 py-2 text-base font-medium text-white hover:bg-indigo-500">Settings</a>
                        <form action="/logout" method="POST">
                            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                            <button type="submit" class="block w-full text-left rounded-md px-3 py-2 text-base font-medium text-white hover:bg-indigo-500">Logout</button>
                        </form>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Main content -->
        <main>
            <div class="mx-auto max-w-7xl py-6 sm:px-6 lg:px-8">
                <?php if (isset($error)): ?>
                    <div class="rounded-md bg-red-50 p-4 mb-6">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-red-800"><?= htmlspecialchars($error) ?></h3>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (isset($success)): ?>
                    <div class="rounded-md bg-green-50 p-4 mb-6">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-green-800"><?= htmlspecialchars($success) ?></h3>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <?= $content ?>
            </div>
        </main>
    </div>

    <!-- Scripts -->
    <script src="/assets/js/app.js"></script>
</body>
</html> 