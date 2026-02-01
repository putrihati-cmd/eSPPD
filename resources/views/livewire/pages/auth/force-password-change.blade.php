<div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4">
    <div class="max-w-md w-full space-y-8 bg-white p-8 rounded-xl shadow-lg">
        <div class="text-center">
            <h2 class="text-2xl font-bold text-gray-900">Keamanan Akun</h2>
            <p class="mt-2 text-sm text-gray-600">
                Anda menggunakan password default. Silakan buat password baru untuk melanjutkan.
            </p>
        </div>
        <form wire:submit="updatePassword" class="mt-8 space-y-6">
            <div>
                <label class="block text-sm font-medium text-gray-700">Password Saat Ini</label>
                <input wire:model.defer="current_password" type="password" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-teal-500 focus:border-teal-500" required>
                @error('current_password') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Password Baru</label>
                <input wire:model.defer="new_password" type="password" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-teal-500 focus:border-teal-500" required>
                @error('new_password') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Konfirmasi Password</label>
                <input wire:model.defer="new_password_confirmation" type="password" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-teal-500 focus:border-teal-500" required>
            </div>
            <div class="text-xs text-gray-500">
                <p>Password harus mengandung:</p>
                <ul class="list-disc ml-4">
                    <li>Huruf besar (A-Z)</li>
                    <li>Huruf kecil (a-z)</li>
                    <li>Angka (0-9)</li>
                    <li>Simbol (@$!%*#?&)</li>
                </ul>
            </div>
            <button type="submit" wire:loading.attr="disabled" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-teal-600 hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500">
                <span wire:loading.remove>Simpan & Lanjutkan</span>
                <span wire:loading>Menyimpan...</span>
            </button>
        </form>
        <div class="text-center mt-4">
            <button wire:click="logout" class="text-sm text-gray-500 hover:text-gray-700">
                Logout (Nanti saja)
            </button>
        </div>
    </div>
</div>
