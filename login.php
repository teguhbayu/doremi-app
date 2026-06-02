<?php
?>

<!DOCTYPE html>
<html lang="en">

<?php include 'head.php'; ?>

<body>
    <?php include 'header.php'; ?>
    <div class="pt-16 flex-1 h-full w-full relative overflow-y-auto overflow-x-hidden">
        <div id="login-container"
            class="min-h-full flex items-center justify-center bg-background px-4 sm:px-6 lg:px-8 py-12 relative overflow-hidden">
            <div
                class="absolute top-1/4 left-1/4 w-72 h-72 bg-accent rounded-full mix-blend-multiply filter blur-2xl opacity-60">
            </div>
            <div
                class="absolute bottom-1/4 right-1/4 w-80 h-80 bg-secondary rounded-full mix-blend-multiply filter blur-2xl opacity-40">
            </div>

            <div class="max-w-md w-full space-y-8 glass-panel p-10 rounded-3xl z-10 relative">
                <div>
                    <h2 class="mt-2 text-center text-3xl font-extrabold text-primary">
                        Selamat Datang Kembali
                    </h2>
                    <p class="mt-2 text-center text-sm text-gray-600">
                        Silakan masuk ke akun DOREMI Anda
                    </p>
                </div>
                <form class="mt-8 space-y-6" onsubmit="login(event)">
                    <div class="rounded-md shadow-sm space-y-4">
                        <div>
                            <label for="username" class="sr-only">Username</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="iconsax text-2xl text-gray-400" icon-name="user-1"></i>
                                </div>
                                <input id="username" name="username" type="text" required
                                    class="appearance-none rounded-xl relative block w-full px-3 py-3 pl-10 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-secondary focus:border-secondary focus:z-10 sm:text-sm transition-colors"
                                    placeholder="Username" />
                            </div>
                        </div>
                        <div>
                            <label for="password" class="sr-only">Password</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="iconsax text-2xl text-gray-400" icon-name="lock-1"></i>
                                </div>
                                <input id="password" name="password" type="password" required
                                    class="appearance-none rounded-xl relative block w-full px-3 py-3 pl-10 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-secondary focus:border-secondary focus:z-10 sm:text-sm transition-colors"
                                    placeholder="Password" />
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <input id="remember-me" name="remember-me" type="checkbox"
                                class="h-4 w-4 text-secondary focus:ring-primary border-gray-300 rounded" />
                            <label for="remember-me" class="ml-2 block text-sm text-gray-900">
                                Ingat saya
                            </label>
                        </div>
                        <div class="text-sm">
                            <a href="#" class="font-medium text-secondary hover:text-primary">
                                Lupa password?
                            </a>
                        </div>
                    </div>

                    <div>
                        <button type="submit"
                            class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-xl text-white bg-primary hover:bg-opacity-90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary shadow-lg transition-all transform hover:-translate-y-0.5">
                            Masuk
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>

</html>