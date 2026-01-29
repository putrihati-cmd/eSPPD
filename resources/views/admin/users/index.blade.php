@php
    $title = 'Manajemen User';
@endphp

<x-layouts.app :title="$title">
    <div class="container-fluid px-4 py-6">
        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-bold text-slate-800">{{ $title }}</h1>
                <p class="text-slate-500 mt-1">Kelola user dan reset password</p>
            </div>

            {{-- Search --}}
            <form method="GET" class="flex gap-2">
                <input type="text" name="search"
                    class="px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500"
                    placeholder="Cari NIP atau Nama..." value="{{ request('search') }}">
                <button type="submit" class="px-4 py-2 bg-teal-600 text-white rounded-lg hover:bg-teal-700 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </button>
            </form>
        </div>

        {{-- Alerts --}}
        @if (session('success'))
            <div class="mb-4 p-4 bg-emerald-50 border border-emerald-200 rounded-lg">
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-emerald-600 mt-0.5" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div class="flex-1">
                        <p class="text-emerald-800 font-medium">{!! session('success') !!}</p>

                        @if (session('copy_message'))
                            <div class="mt-3">
                                <p class="text-sm text-emerald-700 mb-2">Pesan untuk user (copy ke WA):</p>
                                <textarea id="waMessage" readonly class="w-full p-3 text-sm bg-white border border-emerald-300 rounded-lg resize-none"
                                    rows="5">{{ session('copy_message') }}</textarea>
                                <button onclick="copyToClipboard()"
                                    class="mt-2 px-3 py-1.5 text-sm bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition">
                                    📋 Copy Pesan
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        @if (session('error'))
            <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg flex items-center gap-3">
                <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <p class="text-red-800">{{ session('error') }}</p>
            </div>
        @endif

        {{-- Table --}}
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-slate-50 border-b border-slate-200">
                        <tr>
                            <th
                                class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">
                                No</th>
                            <th
                                class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">
                                NIP</th>
                            <th
                                class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">
                                Nama</th>
                            <th
                                class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">
                                Role</th>
                            <th
                                class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">
                                Status Password</th>
                            <th
                                class="px-4 py-3 text-center text-xs font-semibold text-slate-600 uppercase tracking-wider">
                                Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($users as $index => $user)
                            <tr class="hover:bg-slate-50 transition">
                                <td class="px-4 py-3 text-sm text-slate-600">{{ $users->firstItem() + $index }}</td>
                                <td class="px-4 py-3">
                                    <code
                                        class="text-sm bg-slate-100 px-2 py-1 rounded">{{ $user->employee?->nip ?? '-' }}</code>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="w-8 h-8 rounded-full bg-teal-100 flex items-center justify-center text-teal-700 font-semibold text-sm">
                                            {{ $user->initials }}
                                        </div>
                                        <div>
                                            <p class="font-medium text-slate-800">{{ $user->name }}</p>
                                            <p class="text-xs text-slate-500">{{ $user->email }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    @php
                                        $roleColors = [
                                            'superadmin' => 'bg-red-100 text-red-700',
                                            'admin' => 'bg-orange-100 text-orange-700',
                                            'approver' => 'bg-blue-100 text-blue-700',
                                            'finance' => 'bg-purple-100 text-purple-700',
                                        ];
                                        $roleColor = $roleColors[$user->role] ?? 'bg-slate-100 text-slate-700';
                                    @endphp
                                    <span class="px-2 py-1 text-xs font-medium rounded-full {{ $roleColor }}">
                                        {{ $user->roleModel?->label ?? ucfirst($user->role ?? 'User') }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    @if ($user->is_password_reset ?? true)
                                        <span
                                            class="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium rounded-full bg-emerald-100 text-emerald-700">
                                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd"
                                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                            Sudah Diganti
                                        </span>
                                    @else
                                        <span
                                            class="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium rounded-full bg-amber-100 text-amber-700">
                                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd"
                                                    d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                            Default
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <button
                                        onclick="confirmReset('{{ $user->employee?->nip ?? '-' }}', '{{ $user->name }}', {{ $user->id }})"
                                        class="px-3 py-1.5 text-sm bg-amber-500 text-white rounded-lg hover:bg-amber-600 transition disabled:opacity-50 disabled:cursor-not-allowed"
                                        @if ($user->id === auth()->id()) disabled title="Tidak bisa reset password sendiri" @endif>
                                        🔄 Reset Password
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-12 text-center">
                                    <div class="flex flex-col items-center">
                                        <svg class="w-12 h-12 text-slate-300 mb-3" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
                                        </svg>
                                        <p class="text-slate-500">Tidak ada user ditemukan</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if ($users->hasPages())
                <div class="px-4 py-3 border-t border-slate-200 bg-slate-50">
                    {{ $users->links() }}
                </div>
            @endif
        </div>
    </div>

    {{-- Modal Konfirmasi Reset --}}
    <div id="resetModal" class="fixed inset-0 z-50 hidden">
        <div class="fixed inset-0 bg-black/50" onclick="closeResetModal()"></div>
        <div class="fixed inset-0 flex items-center justify-center p-4">
            <div class="bg-white rounded-xl shadow-xl max-w-md w-full" onclick="event.stopPropagation()">
                <div class="p-4 border-b border-slate-200 bg-amber-50 rounded-t-xl">
                    <h3 class="text-lg font-semibold text-amber-800">Konfirmasi Reset Password</h3>
                </div>
                <div class="p-6">
                    <p class="text-slate-600 mb-4">Anda akan mereset password untuk:</p>
                    <div class="p-4 bg-slate-50 rounded-lg mb-4">
                        <p class="text-sm"><strong>Nama:</strong> <span id="resetName" class="text-slate-800"></span>
                        </p>
                        <p class="text-sm"><strong>NIP:</strong> <span id="resetNip"
                                class="text-slate-800 font-mono"></span></p>
                    </div>
                    <div class="p-4 bg-blue-50 rounded-lg border border-blue-200">
                        <p class="text-sm text-blue-800">
                            <strong>Info:</strong> Password akan direset ke <strong>Tanggal Lahir
                                (DDMMYYYY)</strong>.<br>
                            User wajib ganti password saat login berikutnya.
                        </p>
                    </div>
                </div>
                <div class="p-4 border-t border-slate-200 flex justify-end gap-3">
                    <button onclick="closeResetModal()"
                        class="px-4 py-2 text-slate-600 hover:bg-slate-100 rounded-lg transition">
                        Batal
                    </button>
                    <form id="resetForm" method="POST">
                        @csrf
                        @method('PATCH')
                        <button type="submit"
                            class="px-4 py-2 bg-amber-500 text-white rounded-lg hover:bg-amber-600 transition">
                            Ya, Reset Password
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function confirmReset(nip, name, userId) {
            document.getElementById('resetNip').textContent = nip;
            document.getElementById('resetName').textContent = name;
            document.getElementById('resetForm').action = '/admin/users/' + userId + '/reset-password';
            document.getElementById('resetModal').classList.remove('hidden');
        }

        function closeResetModal() {
            document.getElementById('resetModal').classList.add('hidden');
        }

        function copyToClipboard() {
            var copyText = document.getElementById("waMessage");
            copyText.select();
            copyText.setSelectionRange(0, 99999);
            navigator.clipboard.writeText(copyText.value);
            alert("Pesan berhasil dicopy! Silakan paste ke WhatsApp user.");
        }

        // Close modal on Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') closeResetModal();
        });
    </script>
</x-layouts.app>
