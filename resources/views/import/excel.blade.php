<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            📥 Import Data Pegawai
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <p class="text-gray-600 mb-6">
                        Upload file Excel untuk mengimport/memperbarui data pegawai. Bisa diulang kapan saja - data yang
                        sudah ada akan di-update.
                    </p>

                    {{-- Report Section --}}
                    @if (session('report'))
                        @php $r = session('report'); @endphp
                        <div
                            class="mb-6 p-6 rounded-lg {{ $r['failed'] > 0 ? 'bg-amber-50 border border-amber-200' : 'bg-green-50 border border-green-200' }}">
                            <h3
                                class="text-lg font-semibold {{ $r['failed'] > 0 ? 'text-amber-800' : 'text-green-800' }} mb-4">
                                ✅ Import Selesai!
                            </h3>

                            <div class="grid grid-cols-3 gap-4 mb-4">
                                <div class="bg-white p-4 rounded-lg text-center border">
                                    <div class="text-3xl font-bold text-green-600">{{ $r['imported'] }}</div>
                                    <div class="text-sm text-gray-500">Data Baru</div>
                                </div>
                                <div class="bg-white p-4 rounded-lg text-center border">
                                    <div class="text-3xl font-bold text-blue-600">{{ $r['updated'] }}</div>
                                    <div class="text-sm text-gray-500">Data Diperbarui</div>
                                </div>
                                <div class="bg-white p-4 rounded-lg text-center border">
                                    <div class="text-3xl font-bold text-red-600">{{ $r['failed'] }}</div>
                                    <div class="text-sm text-gray-500">Gagal</div>
                                </div>
                            </div>

                            @if ($r['failed'] > 0 && count($r['failed_details'] ?? []) > 0)
                                <div class="bg-red-50 border border-red-200 rounded-lg p-4 mt-4">
                                    <strong class="text-red-700">⚠️ Beberapa baris gagal:</strong>
                                    <div class="mt-2 max-h-40 overflow-y-auto">
                                        @foreach ($r['failed_details'] as $error)
                                            <div class="text-sm text-red-600 py-1 border-b border-red-100">
                                                Baris {{ $error['row'] }} (NIP: {{ $error['nip'] }}):
                                                {{ $error['error'] }}
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif

                    {{-- Error Section --}}
                    @if ($errors->any())
                        <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
                            <strong class="text-red-700">❌ Error:</strong>
                            <p class="text-red-600">{{ $errors->first() }}</p>
                        </div>
                    @endif

                    {{-- Upload Form --}}
                    <form method="POST" action="{{ route('import.employees') }}" enctype="multipart/form-data"
                        id="uploadForm">
                        @csrf

                        <div class="border-2 border-dashed border-gray-300 rounded-lg p-12 text-center hover:border-primary-500 hover:bg-gray-50 transition cursor-pointer"
                            onclick="document.getElementById('fileInput').click()" ondrop="dropHandler(event)"
                            ondragover="dragOverHandler(event)" ondragleave="dragLeaveHandler(event)" id="dropZone">
                            <div class="text-5xl mb-4">📁</div>
                            <h3 class="text-lg font-medium text-gray-700">Klik atau Drag & Drop file Excel</h3>
                            <p class="text-sm text-gray-500 mt-2">Format: .xlsx, .xls, atau .csv (Max 10MB)</p>
                            <div class="mt-4 text-primary-600 font-medium" id="fileName"></div>
                        </div>

                        <input type="file" name="file" id="fileInput" accept=".xlsx,.xls,.csv" class="hidden"
                            required onchange="handleFileSelect(this)">

                        <div class="mt-6 flex justify-between items-center">
                            <a href="{{ route('import.template') }}"
                                class="text-primary-600 hover:text-primary-700 text-sm flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                </svg>
                                Download Template
                            </a>

                            <button type="submit" id="submitBtn" disabled
                                class="px-6 py-3 bg-primary-600 text-white rounded-lg font-medium hover:bg-primary-700 disabled:bg-gray-300 disabled:cursor-not-allowed transition">
                                🚀 Mulai Import
                            </button>
                        </div>
                    </form>

                    {{-- Info Section --}}
                    <div class="mt-8 p-4 bg-gray-50 rounded-lg border border-gray-200">
                        <h4 class="font-medium text-gray-700 mb-3">📋 Catatan Penting:</h4>
                        <ul class="text-sm text-gray-600 space-y-2">
                            <li>• Kolom wajib: <strong>NIP</strong> dan <strong>Nama</strong></li>
                            <li>• Kolom opsional: Tanggal Lahir, Jabatan, Golongan, Tugas Tambahan, Status</li>
                            <li>• Format Tanggal: DD/MM/YYYY atau YYYY-MM-DD</li>
                            <li>• Jika NIP sudah ada → Data akan <strong>diperbarui</strong> (update)</li>
                            <li>• Jika NIP baru → Data akan <strong>ditambahkan</strong> + otomatis buat akun login</li>
                            <li>• Password default: Tanggal lahir (DDMMYYYY) atau "12345678"</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function handleFileSelect(input) {
            const fileName = input.files[0]?.name || '';
            document.getElementById('fileName').textContent = fileName ? `📄 ${fileName}` : '';
            document.getElementById('submitBtn').disabled = !input.files[0];

            if (fileName) {
                document.getElementById('dropZone').classList.add('border-primary-500', 'bg-primary-50');
            }
        }

        function dragOverHandler(ev) {
            ev.preventDefault();
            ev.currentTarget.classList.add('border-primary-500', 'bg-primary-50');
        }

        function dragLeaveHandler(ev) {
            ev.currentTarget.classList.remove('border-primary-500', 'bg-primary-50');
        }

        function dropHandler(ev) {
            ev.preventDefault();
            ev.currentTarget.classList.remove('border-primary-500', 'bg-primary-50');

            const files = ev.dataTransfer.files;
            if (files.length) {
                document.getElementById('fileInput').files = files;
                handleFileSelect(document.getElementById('fileInput'));
            }
        }
    </script>
</x-app-layout>
