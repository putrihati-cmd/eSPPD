<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Profile') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- Account Information -->
            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="max-w-xl">
                    <livewire:profile.update-profile-information-form />
                </div>
            </div>

            <!-- Employee Biodata Section (NEW) -->
            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <header class="mb-6">
                    <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                        📋 {{ __('Data Kepegawaian') }}
                    </h2>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        {{ __('Informasi lengkap data kepegawaian Anda.') }}
                    </p>
                </header>

                @if(auth()->user()->employee)
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- NIP -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                NIP
                            </label>
                            <p class="text-base font-mono text-gray-900 dark:text-gray-100">
                                {{ auth()->user()->employee->nip ?? '-' }}
                            </p>
                        </div>

                        <!-- Position / Jabatan -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Jabatan (Position)
                            </label>
                            <p class="text-base text-gray-900 dark:text-gray-100">
                                {{ auth()->user()->employee->position ?? '-' }}
                            </p>
                        </div>

                        <!-- Rank / Pangkat -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Pangkat
                            </label>
                            <p class="text-base text-gray-900 dark:text-gray-100">
                                {{ auth()->user()->employee->rank ?? '-' }}
                            </p>
                        </div>

                        <!-- Grade / Golongan -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Golongan
                            </label>
                            <p class="text-base text-gray-900 dark:text-gray-100">
                                {{ auth()->user()->employee->grade ?? '-' }}
                            </p>
                        </div>

                        <!-- Employment Status -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Status Kepegawaian
                            </label>
                            <p class="text-base text-gray-900 dark:text-gray-100">
                                <span @class([
                                    'inline-flex items-center px-3 py-1 rounded-full text-sm font-medium',
                                    'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' => auth()->user()->employee->employment_status === 'PNS',
                                    'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' => auth()->user()->employee->employment_status === 'PPPK',
                                    'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' => auth()->user()->employee->employment_status === 'Honorer',
                                    'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200' => !auth()->user()->employee->employment_status,
                                ])>
                                    {{ auth()->user()->employee->employment_status ?? '-' }}
                                </span>
                            </p>
                        </div>

                        <!-- Unit / Fakultas -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Unit / Fakultas
                            </label>
                            <p class="text-base text-gray-900 dark:text-gray-100">
                                {{ auth()->user()->employee->unit?->name ?? '-' }}
                            </p>
                        </div>

                        <!-- Organization -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Organisasi
                            </label>
                            <p class="text-base text-gray-900 dark:text-gray-100">
                                {{ auth()->user()->employee->organization?->name ?? '-' }}
                            </p>
                        </div>

                        <!-- Phone -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Nomor Telepon
                            </label>
                            <p class="text-base text-gray-900 dark:text-gray-100">
                                @if(auth()->user()->employee->phone)
                                    <a href="tel:{{ auth()->user()->employee->phone }}"
                                       class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                                        {{ auth()->user()->employee->phone }}
                                    </a>
                                @else
                                    -
                                @endif
                            </p>
                        </div>

                        <!-- Birth Date -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Tanggal Lahir
                            </label>
                            <p class="text-base text-gray-900 dark:text-gray-100">
                                {{ auth()->user()->employee->birth_date?->format('d/m/Y') ?? '-' }}
                            </p>
                        </div>

                        <!-- Bank Name -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Nama Bank
                            </label>
                            <p class="text-base text-gray-900 dark:text-gray-100">
                                {{ auth()->user()->employee->bank_name ?? '-' }}
                            </p>
                        </div>

                        <!-- Bank Account -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Nomor Rekening
                            </label>
                            <p class="text-base font-mono text-gray-900 dark:text-gray-100">
                                {{ auth()->user()->employee->bank_account ?? '-' }}
                            </p>
                        </div>

                        <!-- Bank Account Name -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Nama Pemilik Rekening
                            </label>
                            <p class="text-base text-gray-900 dark:text-gray-100">
                                {{ auth()->user()->employee->bank_account_name ?? '-' }}
                            </p>
                        </div>

                        <!-- Status -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Status
                            </label>
                            <p class="text-base text-gray-900 dark:text-gray-100">
                                @if(auth()->user()->employee->is_active)
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                        ✅ Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                        ❌ Tidak Aktif
                                    </span>
                                @endif
                            </p>
                        </div>
                    </div>

                    <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            💡 Data kepegawaian dikelola oleh administrator. Untuk perubahan data, hubungi bagian kepegawaian.
                        </p>
                    </div>
                @else
                    <div class="rounded-lg bg-yellow-50 dark:bg-yellow-900/20 p-4 border border-yellow-200 dark:border-yellow-800">
                        <p class="text-sm text-yellow-800 dark:text-yellow-200">
                            ⚠️ Data kepegawaian belum tersedia. Silakan hubungi administrator.
                        </p>
                    </div>
                @endif
            </div>

            <!-- Update Password -->
            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="max-w-xl">
                    <livewire:profile.update-password-form />
                </div>
            </div>

            <!-- Delete Account -->
            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="max-w-xl">
                    <livewire:profile.delete-user-form />
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
