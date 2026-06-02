<?php
?>

<!DOCTYPE html>
<html lang="en">

<?php include 'head.php'; ?>

<body>
    <?php include 'header.php'; ?>
    <div class="tw-pt-16 tw-flex-1 tw-h-full tw-w-full tw-relative tw-overflow-y-auto tw-overflow-x-hidden">
        <div id="login-container"
            class="tw-min-h-full tw-flex tw-items-center tw-justify-center tw-bg-background tw-px-4 sm:tw-px-6 lg:tw-px-8 tw-py-12 tw-relative tw-overflow-hidden">
            <div
                class="tw-absolute tw-top-1/4 tw-left-1/4 tw-w-72 tw-h-72 tw-bg-accent tw-rounded-full tw-mix-blend-multiply tw-filter tw-blur-2xl tw-opacity-60">
            </div>
            <div
                class="tw-absolute tw-bottom-1/4 tw-right-1/4 tw-w-80 tw-h-80 tw-bg-secondary tw-rounded-full tw-mix-blend-multiply tw-filter tw-blur-2xl tw-opacity-40">
            </div>

            <div class="tw-max-w-md tw-w-full tw-space-y-8 glass-panel tw-p-10 tw-rounded-3xl tw-z-10 tw-relative">
                <div>
                    <h2 class="tw-mt-2 tw-text-center tw-text-3xl tw-font-extrabold tw-text-primary">
                        Selamat Datang Kembali
                    </h2>
                    <p class="tw-mt-2 tw-text-center tw-text-sm tw-text-gray-600">
                        Silakan masuk ke akun DOREMI Anda
                    </p>
                </div>
                <form class="tw-mt-8 tw-w-full tw-space-y-6" onsubmit="login(event)">
                    <div class="tw-rounded-md tw-shadow-sm tw-space-y-4">
                        <div class="tw-w-full">
                            <label for="username" class="tw-sr-only">Username</label>
                            <div class="tw-relative tw-w-full">
                                <i class="iconsax tw-text-2xl z-[9999] tw-left-0 tw-pl-3 tw-absolute tw-pointer-events-none tw-text-gray-400"
                                    icon-name="user-1"></i>
                                <input id="username" name="username" type="text" required
                                    class="tw-appearance-none tw-rounded-xl tw-relative tw-block tw-w-[90%] tw-px-3 tw-py-3 tw-ps-10 tw-border tw-border-gray-300 tw-placeholder-gray-500 tw-text-gray-900 focus:tw-outline-none focus:tw-ring-secondary focus:tw-border-secondary focus:tw-z-10 sm:tw-text-sm tw-transition-colors"
                                    placeholder="Username" />
                            </div>
                        </div>
                        <div>
                            <label for="password" class="tw-sr-only">Password</label>
                            <div class="tw-relative">
                                <div
                                    class="tw-absolute tw-inset-y-0 tw-left-0 tw-pl-3 tw-flex tw-items-center tw-pointer-events-none">
                                    <i class="iconsax tw-text-2xl tw-text-gray-400" icon-name="lock-1"></i>
                                </div>
                                <input id="password" name="password" type="password" required
                                    class="tw-appearance-none tw-rounded-xl tw-relative tw-block tw-w-[90%] tw-px-3 tw-py-3 tw-pl-10 tw-border tw-border-gray-300 tw-placeholder-gray-500 tw-text-gray-900 focus:tw-outline-none focus:tw-ring-secondary focus:tw-border-secondary focus:tw-z-10 sm:tw-text-sm tw-transition-colors"
                                    placeholder="Password" />
                            </div>
                        </div>
                    </div>

                    <div class="tw-flex tw-items-center tw-justify-between">
                        <div class="tw-flex tw-items-center">
                            <input id="remember-me" name="remember-me" type="checkbox"
                                class="tw-h-4 tw-w-4 tw-text-secondary focus:tw-ring-primary tw-border-gray-300 tw-rounded" />
                            <label for="remember-me" class="tw-ml-2 tw-block tw-text-sm tw-text-gray-900">
                                Ingat saya
                            </label>
                        </div>
                        <div class="tw-text-sm">
                            <a href="#" class="tw-font-medium tw-text-secondary hover:tw-text-primary">
                                Lupa password?
                            </a>
                        </div>
                    </div>

                    <div>
                        <button type="submit"
                            class="tw-group tw-relative tw-w-full tw-flex tw-justify-center tw-py-3 tw-px-4 tw-border tw-border-transparent tw-text-sm tw-font-medium tw-rounded-xl tw-text-white tw-bg-primary hover:tw-bg-opacity-90 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-offset-2 focus:tw-ring-primary tw-shadow-lg tw-transition-all tw-transform hover:-tw-translate-y-0.5">
                            Masuk
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>

</html>