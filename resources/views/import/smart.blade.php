<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            🧠 Smart Import Pegawai (AI-Powered)
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            @if (!$serviceHealthy)
                <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
                    <strong class="text-red-700">⚠️ Python Service Offline</strong>
                    <p class="text-red-600 text-sm mt-1">
                        Smart Import service tidak tersedia. Jalankan: <code class="bg-red-100 px-1 rounded">cd
                            python_service && uvicorn app.main:app --port 8002</code>
                    </p>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg" id="smart-importer">
                <div class="p-6">
                    <!-- Step Indicator -->
                    <div class="mb-8">
                        <div class="flex items-center justify-center space-x-4">
                            <template x-for="(stepLabel, index) in ['Upload', 'Mapping', 'Validate', 'Import']"
                                :key="index">
                                <div class="flex items-center">
                                    <div class="flex items-center justify-center w-10 h-10 rounded-full text-sm font-semibold transition-colors"
                                        :class="step > index ? 'bg-green-500 text-white' : (step === index ?
                                            'bg-primary-600 text-white' : 'bg-gray-200 text-gray-500')">
                                        <span x-text="index + 1"></span>
                                    </div>
                                    <span class="ml-2 text-sm font-medium" x-text="stepLabel"
                                        :class="step === index ? 'text-primary-600' : 'text-gray-500'"></span>
                                    <template x-if="index < 3">
                                        <div class="w-12 h-0.5 mx-2"
                                            :class="step > index ? 'bg-green-500' : 'bg-gray-200'"></div>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Step 0: Upload -->
                    <div x-show="step === 0" x-transition>
                        <div class="border-2 border-dashed border-gray-300 rounded-xl p-12 text-center hover:border-primary-500 hover:bg-gray-50 transition cursor-pointer"
                            @click="$refs.fileInput.click()" @drop.prevent="handleDrop($event)"
                            @dragover.prevent="isDragging = true" @dragleave="isDragging = false"
                            :class="{ 'border-primary-500 bg-primary-50': isDragging }">
                            <div class="text-6xl mb-4">📁</div>
                            <h3 class="text-xl font-medium text-gray-700">Drop file Excel di sini</h3>
                            <p class="text-gray-500 mt-2">atau <span class="text-primary-600 underline">klik untuk
                                    browse</span></p>
                            <p class="text-xs text-gray-400 mt-4">Format: .xlsx, .xls, .csv (Max 50MB)</p>

                            <div x-show="selectedFile" class="mt-4 text-primary-600 font-medium">
                                📄 <span x-text="selectedFile?.name"></span>
                            </div>
                        </div>

                        <input type="file" x-ref="fileInput" @change="handleFileSelect($event)"
                            accept=".xlsx,.xls,.csv" class="hidden">

                        <div class="mt-6 text-center">
                            <button @click="uploadFile" :disabled="!selectedFile || uploading"
                                class="px-8 py-3 bg-primary-600 text-white rounded-lg font-medium hover:bg-primary-700 disabled:bg-gray-300 disabled:cursor-not-allowed transition">
                                <span x-show="!uploading">🚀 Analisis File</span>
                                <span x-show="uploading" class="flex items-center">
                                    <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none"
                                        viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10"
                                            stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor"
                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                        </path>
                                    </svg>
                                    Menganalisis...
                                </span>
                            </button>
                        </div>
                    </div>

                    <!-- Step 1: Mapping -->
                    <div x-show="step === 1" x-transition class="space-y-6">
                        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                            <h3 class="font-semibold text-green-800">✨ Kolom Terdeteksi AI</h3>
                            <p class="text-sm text-green-600 mt-1">
                                Sistem telah menganalisa <span x-text="totalRows"></span> baris. Koreksi mapping jika
                                diperlukan.
                            </p>
                        </div>

                        <div class="space-y-4">
                            <template x-for="col in detectedColumns" :key="col.standard_name">
                                <div class="flex items-center justify-between p-4 border rounded-lg"
                                    :class="{
                                        'border-green-300 bg-green-50': col.confidence === 'high',
                                        'border-yellow-300 bg-yellow-50': col.confidence === 'medium',
                                        'border-red-300 bg-red-50': col.confidence === 'low'
                                    }">
                                    <div class="flex-1">
                                        <div class="flex items-center">
                                            <span class="font-medium text-gray-800"
                                                x-text="getFieldLabel(col.standard_name)"></span>
                                            <span class="ml-2 px-2 py-0.5 text-xs rounded-full"
                                                :class="{
                                                    'bg-green-200 text-green-800': col.confidence === 'high',
                                                    'bg-yellow-200 text-yellow-800': col.confidence === 'medium',
                                                    'bg-red-200 text-red-800': col.confidence === 'low'
                                                }">
                                                <span
                                                    x-text="col.confidence === 'high' ? '✓ Auto' : (col.confidence === 'medium' ? '⚠ Review' : '❓ Check')"></span>
                                                (<span x-text="Math.round(col.similarity_score * 100)"></span>%)
                                            </span>
                                        </div>
                                        <p class="text-xs text-gray-500 mt-1">
                                            Contoh: <span x-text="col.sample_values?.slice(0, 2).join(', ')"></span>
                                        </p>
                                    </div>
                                    <div class="ml-4">
                                        <select x-model="mapping[col.standard_name]"
                                            class="border-gray-300 rounded-lg shadow-sm focus:ring-primary-500 focus:border-primary-500">
                                            <option value="">-- Pilih Kolom --</option>
                                            <template x-for="header in availableHeaders" :key="header">
                                                <option :value="header" x-text="header"></option>
                                            </template>
                                        </select>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <div class="flex justify-between">
                            <button @click="step = 0"
                                class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                                ← Kembali
                            </button>
                            <button @click="validateData" :disabled="!isMappingValid || validating"
                                class="px-6 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 disabled:bg-gray-300">
                                <span x-show="!validating">Validasi Data →</span>
                                <span x-show="validating">Memvalidasi...</span>
                            </button>
                        </div>
                    </div>

                    <!-- Step 2: Validation Results -->
                    <div x-show="step === 2" x-transition class="space-y-6">
                        <!-- Summary Cards -->
                        <div class="grid grid-cols-3 gap-4">
                            <div class="p-4 rounded-lg text-center"
                                :class="validationResult?.error_count > 0 ? 'bg-red-50' : 'bg-green-50'">
                                <div class="text-3xl font-bold"
                                    :class="validationResult?.error_count > 0 ? 'text-red-600' : 'text-green-600'"
                                    x-text="validationResult?.error_count || 0"></div>
                                <div class="text-sm text-gray-600">Error Kritis</div>
                            </div>
                            <div class="p-4 bg-yellow-50 rounded-lg text-center">
                                <div class="text-3xl font-bold text-yellow-600"
                                    x-text="validationResult?.warning_count || 0"></div>
                                <div class="text-sm text-gray-600">Peringatan</div>
                            </div>
                            <div class="p-4 bg-green-50 rounded-lg text-center">
                                <div class="text-3xl font-bold text-green-600"
                                    x-text="validationResult?.valid_rows || 0"></div>
                                <div class="text-sm text-gray-600">Data Valid</div>
                            </div>
                        </div>

                        <!-- Errors -->
                        <template x-if="validationResult?.errors?.length > 0">
                            <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                                <h4 class="font-semibold text-red-800 mb-3">❌ Error yang harus diperbaiki:</h4>
                                <div class="max-h-48 overflow-y-auto space-y-2">
                                    <template x-for="err in validationResult.errors.slice(0, 10)"
                                        :key="err.row_number + err.column">
                                        <div class="text-sm text-red-700 flex justify-between items-start">
                                            <span>Baris <span x-text="err.row_number"></span>: <span
                                                    x-text="err.message"></span></span>
                                            <span class="text-xs text-red-500 ml-2" x-text="err.suggestion"></span>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </template>

                        <!-- Preview -->
                        <div class="border rounded-lg overflow-hidden">
                            <h4 class="font-semibold p-3 bg-gray-50 border-b">Preview Data (5 baris)</h4>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200 text-sm">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <template x-for="(val, key) in (validationResult?.preview?.[0] || {})"
                                                :key="key">
                                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase"
                                                    x-text="key"></th>
                                            </template>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200">
                                        <template x-for="(row, idx) in validationResult?.preview || []"
                                            :key="idx">
                                            <tr>
                                                <template x-for="(val, key) in row" :key="key + idx">
                                                    <td class="px-4 py-2 whitespace-nowrap" x-text="val"></td>
                                                </template>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="flex justify-between">
                            <button @click="step = 1"
                                class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                                ← Koreksi Mapping
                            </button>
                            <div class="space-x-3">
                                <template x-if="validationResult?.error_count > 0">
                                    <button @click="processImport(true)"
                                        class="px-6 py-2 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600">
                                        Import Tanpa Error (<span x-text="validationResult?.error_count"></span>
                                        dilewat)
                                    </button>
                                </template>
                                <button @click="processImport(false)"
                                    :disabled="validationResult?.error_count > 0 || importing"
                                    class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 disabled:bg-gray-300">
                                    <span x-show="!importing">✓ Import ke Database</span>
                                    <span x-show="importing">Memproses...</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Step 3: Complete -->
                    <div x-show="step === 3" x-transition class="text-center py-8">
                        <div class="text-7xl mb-4">✅</div>
                        <h3 class="text-2xl font-bold text-green-600 mb-2">Import Berhasil!</h3>

                        <div class="grid grid-cols-4 gap-4 max-w-2xl mx-auto my-8">
                            <div class="p-4 bg-gray-50 rounded-lg">
                                <div class="text-2xl font-bold text-gray-700" x-text="importResult?.total || 0"></div>
                                <div class="text-sm text-gray-500">Total</div>
                            </div>
                            <div class="p-4 bg-green-50 rounded-lg">
                                <div class="text-2xl font-bold text-green-600" x-text="importResult?.imported || 0">
                                </div>
                                <div class="text-sm text-gray-500">Baru</div>
                            </div>
                            <div class="p-4 bg-blue-50 rounded-lg">
                                <div class="text-2xl font-bold text-blue-600" x-text="importResult?.updated || 0">
                                </div>
                                <div class="text-sm text-gray-500">Update</div>
                            </div>
                            <div class="p-4 bg-purple-50 rounded-lg">
                                <div class="text-2xl font-bold text-purple-600"
                                    x-text="importResult?.new_accounts || 0"></div>
                                <div class="text-sm text-gray-500">Akun Baru</div>
                            </div>
                        </div>

                        <div class="space-x-3">
                            <button @click="reset"
                                class="px-6 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700">
                                Import File Lain
                            </button>
                            <a href="{{ route('employees.index') }}"
                                class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 inline-block">
                                Lihat Data Pegawai
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('smartImporter', () => ({
                step: 0,
                isDragging: false,
                selectedFile: null,
                uploading: false,
                validating: false,
                importing: false,

                jobId: null,
                totalRows: 0,
                detectedColumns: [],
                availableHeaders: [],
                mapping: {},
                validationResult: null,
                importResult: null,

                fieldLabels: {
                    'nip': 'NIP',
                    'nama': 'Nama Lengkap',
                    'tanggal_lahir': 'Tanggal Lahir',
                    'golongan': 'Golongan/Pangkat',
                    'jabatan': 'Jabatan Fungsional',
                    'jabatan_struktural': 'Jabatan Struktural',
                    'fakultas': 'Fakultas/Unit',
                    'status': 'Status Kepegawaian',
                    'email': 'Email',
                    'telepon': 'No. Telepon/WA',
                    'pendidikan': 'Pendidikan'
                },

                get isMappingValid() {
                    return this.mapping['nip'] && this.mapping['nama'];
                },

                getFieldLabel(field) {
                    return this.fieldLabels[field] || field;
                },

                handleFileSelect(event) {
                    this.selectedFile = event.target.files[0];
                },

                handleDrop(event) {
                    this.isDragging = false;
                    const files = event.dataTransfer.files;
                    if (files.length) {
                        this.selectedFile = files[0];
                    }
                },

                async uploadFile() {
                    if (!this.selectedFile) return;

                    this.uploading = true;
                    const formData = new FormData();
                    formData.append('file', this.selectedFile);

                    try {
                        const response = await fetch('/smart-import/upload', {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector(
                                    'meta[name="csrf-token"]').content
                            }
                        });

                        const data = await response.json();

                        if (data.error) {
                            alert('Error: ' + data.error);
                            return;
                        }

                        this.jobId = data.job_id;
                        this.totalRows = data.total_rows;
                        this.detectedColumns = data.detected_columns;
                        this.availableHeaders = data.available_headers;

                        // Initialize mapping from detected
                        this.detectedColumns.forEach(col => {
                            this.mapping[col.standard_name] = col.detected_header;
                        });

                        this.step = 1;

                    } catch (error) {
                        alert('Error uploading file: ' + error.message);
                    } finally {
                        this.uploading = false;
                    }
                },

                async validateData() {
                    this.validating = true;

                    try {
                        // First update mapping
                        await fetch('/smart-import/mapping', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector(
                                    'meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({
                                job_id: this.jobId,
                                mapping: this.mapping
                            })
                        });

                        // Then validate
                        const response = await fetch(`/smart-import/validate/${this.jobId}`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector(
                                    'meta[name="csrf-token"]').content
                            }
                        });

                        this.validationResult = await response.json();
                        this.step = 2;

                    } catch (error) {
                        alert('Error validating: ' + error.message);
                    } finally {
                        this.validating = false;
                    }
                },

                async processImport(skipErrors = false) {
                    this.importing = true;

                    try {
                        const response = await fetch('/smart-import/process', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector(
                                    'meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({
                                job_id: this.jobId,
                                skip_errors: skipErrors
                            })
                        });

                        this.importResult = await response.json();
                        this.step = 3;

                    } catch (error) {
                        alert('Error importing: ' + error.message);
                    } finally {
                        this.importing = false;
                    }
                },

                reset() {
                    this.step = 0;
                    this.selectedFile = null;
                    this.jobId = null;
                    this.detectedColumns = [];
                    this.mapping = {};
                    this.validationResult = null;
                    this.importResult = null;
                }
            }));
        });
    </script>

    <div x-data="smartImporter">
        <!-- Component content goes here (already rendered above) -->
    </div>
</x-app-layout>
