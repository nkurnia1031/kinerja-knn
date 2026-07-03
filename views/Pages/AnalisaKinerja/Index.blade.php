@php
    use app\Fungsi;
@endphp
@extends('Layout.html')
@section('js')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const { createApp } = Vue

        app = createApp({
            data() {
                const periodeList = @json($data['periodeList'] ?? []);
                const activePeriode = periodeList.find(p => p.status === 'aktif')?.id || '';
                return {
                    canManage: '{{ $Session["admin"]->role ?? "" }}' === 'admin',
                    // Base configuration
                    baseURL: 'AnalisaKinerja',
                    
                    // Parameter KNN
                    knnParams: {
                        k: 3,
                        metode_jarak: 'euclidean',
                        normalisasi: 'min_max',
                        nama_analisa: '',
                        periode_id: activePeriode
                    },
                    
                    // Data
                    kriteria: [],
                    dataTraining: [],
                    hasilKlasifikasi: [],
                    riwayatPenilaian: [],
                    statistik: {
                        total_sangat_baik: 0,
                        total_baik: 0,
                        total_cukup: 0,
                        total_kurang: 0
                    },
                    
                    // Daftar analisa sebelumnya
                    daftarAnalisa: [],
                    
                    // State
                    selectedKaryawan: null,
                    selectedAnalisaId: null,
                    showDetail: false,
                    activeTab: 'overview',
                    isLoading: false,
                    isLoadingDetail: false,
                    isUpdatingTraining: false,
                    error: null,
                    periodeList: periodeList,
                    
                    // Charts
                    distributionChart: null,
                    radarChart: null,
                    
                    // Detail perhitungan
                    detailPerhitungan: null
                }
            },
            computed: {
                totalBobot() {
                    return this.kriteria.reduce((sum, k) => sum + parseFloat(k.bobot || 0), 0);
                },
                totalDataTesting() {
                    return this.hasilKlasifikasi.length;
                },
                totalDataTraining() {
                    return this.dataTraining.length;
                },
                selectedPeriodeLabel() {
                    if (!this.knnParams.periode_id) {
                        return 'Periode Aktif';
                    }

                    const periode = this.periodeList.find(p => String(p.id) === String(this.knnParams.periode_id));
                    return periode ? `${periode.nama_periode} (${periode.status})` : 'Periode Terpilih';
                }
            },
            mounted() {
                this.loadDaftarAnalisa();
                this.previewAnalisaReal();
                this.loadRiwayatPenilaian();
            },
            methods: {
                // ========================
                // API CALLS
                // ========================
                
                /**
                 * Preview analisa dengan data real tanpa menyimpan
                 */
                async previewAnalisaReal() {
                    this.isLoading = true;
                    this.error = null;
                    
                    try {
                        const response = await axiosInstance.post('AnalisaKinerja-Api?action=preview', {
                            nilai_k: this.knnParams.k,
                            metode_jarak: this.knnParams.metode_jarak,
                            normalisasi: this.knnParams.normalisasi,
                            periode_id: this.knnParams.periode_id || ''
                        });
                        
                        if (response.data.status) {
                            const data = response.data.data;
                            this.kriteria = data.kriteria || [];
                            this.dataTraining = (data.data_training || []).map(t => ({
                                ...t,
                                nilai_kriteria: t.nilai_kriteria || t.nilai || []
                            }));
                            this.dataTraining =collect(  this.dataTraining ).sortBy('id');
                            this.hasilKlasifikasi = data.hasil || [];
                            this.statistik = data.statistik;
                            
                            this.$nextTick(() => {
                                this.renderDistributionChart();
                            });
                            
                            new Toast({
                                message: 'Preview analisa dari data aktual berhasil dimuat',
                                type: 'success'
                            });
                        } else {
                            this.error = response.data.message;
                            new Toast({
                                message: response.data.message,
                                type: 'danger'
                            });
                        }
                    } catch (err) {
                        this.error = 'Gagal memproses analisa: ' + (err.response?.data?.message || err.message);
                        new Toast({
                            message: this.error,
                            type: 'danger'
                        });
                    } finally {
                        this.isLoading = false;
                    }
                },
                async prosesAnalisaMock() {
                    return this.previewAnalisaReal();
                },
                
                /**
                 * Proses analisa baru dan simpan ke database
                 */
                async prosesAnalisaBaru() {
                    if (!this.knnParams.nama_analisa) {
                        this.knnParams.nama_analisa = 'Analisa KNN ' + new Date().toLocaleString('id-ID');
                    }
                    
                    this.isLoading = true;
                    this.error = null;
                    
                    try {
                        const response = await axiosInstance.post('AnalisaKinerja-Api?action=proses', {
                            nama_analisa: this.knnParams.nama_analisa,
                            nilai_k: this.knnParams.k,
                            metode_jarak: this.knnParams.metode_jarak,
                            normalisasi: this.knnParams.normalisasi,
                            periode_id: this.knnParams.periode_id || '',
                            simpan_detail: true
                        });
                        
                        if (response.data.status) {
                            const data = response.data.data;
                            this.hasilKlasifikasi = data.hasil;
                            this.statistik = data.statistik;
                            this.selectedAnalisaId = data.analisa_id;
                            
                            this.$nextTick(() => {
                                this.renderDistributionChart();
                            });
                            
                            this.loadDaftarAnalisa();
                            this.loadRiwayatPenilaian();
                            
                            new Toast({
                                message: 'Analisa berhasil disimpan dengan kode: ' + data.kode_analisa,
                                type: 'success'
                            });
                        } else {
                            this.error = response.data.message;
                            new Toast({
                                message: response.data.message,
                                type: 'danger'
                            });
                        }
                    } catch (err) {
                        this.error = 'Gagal menyimpan analisa: ' + (err.response?.data?.message || err.message);
                        new Toast({
                            message: this.error,
                            type: 'danger'
                        });
                    } finally {
                        this.isLoading = false;
                    }
                },
                
                /**
                 * Load daftar analisa sebelumnya
                 */
                async loadDaftarAnalisa() {
                    try {
                        const response = await axiosInstance.get('AnalisaKinerja-Api?action=daftar&limit=20');
                        
                        if (response.data.status) {
                            this.daftarAnalisa = response.data.data.data || [];
                        }
                    } catch (err) {
                        console.error('Gagal load daftar analisa:', err);
                    }
                },
                async loadRiwayatPenilaian() {
                    try {
                        const response = await axiosInstance.get(`AnalisaKinerja-Api?action=riwayat-penilaian&periode_id=${this.knnParams.periode_id || ''}`);
                        if (response.data.status) {
                            this.riwayatPenilaian = response.data.data.data || [];
                        }
                    } catch (err) {
                        console.error('Gagal load riwayat penilaian:', err);
                    }
                },
                async addToTraining(item) {
                    const klasifikasi = item.klasifikasi || '';
                    if (!klasifikasi) {
                        new Toast({ message: 'Klasifikasi penilaian belum tersedia', type: 'danger' });
                        return;
                    }
                    this.isUpdatingTraining = true;
                    try {
                        const response = await axiosInstance.post('KlasifikasiKinerja-Api?action=add-to-training', {
                            penilaian_id: item.id,
                            klasifikasi: klasifikasi
                        });
                        if (response.data.status) {
                            await this.previewAnalisaReal();
                            await this.loadRiwayatPenilaian();
                            new Toast({ message: response.data.message, type: 'success' });
                        } else {
                            new Toast({ message: response.data.message, type: 'danger' });
                        }
                    } catch (err) {
                        new Toast({ message: 'Gagal menambahkan data training', type: 'danger' });
                    } finally {
                        this.isUpdatingTraining = false;
                    }
                },
                async removeFromTraining(item) {
                    this.isUpdatingTraining = true;
                    try {
                        const response = await axiosInstance.post('KlasifikasiKinerja-Api?action=remove-from-training', {
                            penilaian_id: item.id
                        });
                        if (response.data.status) {
                            await this.previewAnalisaReal();
                            await this.loadRiwayatPenilaian();
                            new Toast({ message: response.data.message, type: 'success' });
                        } else {
                            new Toast({ message: response.data.message, type: 'danger' });
                        }
                    } catch (err) {
                        new Toast({ message: 'Gagal menghapus data training', type: 'danger' });
                    } finally {
                        this.isUpdatingTraining = false;
                    }
                },
                
                /**
                 * Load hasil analisa sebelumnya dari database
                 */
                async loadHasilAnalisa(analisaId) {
                    this.isLoading = true;
                    this.error = null;
                    this.selectedAnalisaId = analisaId;
                    
                    try {
                        const response = await axiosInstance.get(`AnalisaKinerja-Api?action=hasil&id=${analisaId}`);
                        
                        if (response.data.status) {
                            const data = response.data.data;
                            
                            this.kriteria = data.kriteria;
                            this.dataTraining = collect(data.data_training).sortBy('id');
                            
                            this.hasilKlasifikasi = data.hasil_klasifikasi || [];
                            
                            // Set params from analisa
                            this.knnParams.k = data.analisa.nilai_k;
                            this.knnParams.metode_jarak = data.analisa.metode_jarak;
                            this.knnParams.normalisasi = data.analisa.normalisasi;
                            this.knnParams.nama_analisa = data.analisa.nama_analisa;
                            
                            this.statistik = {
                                total_sangat_baik: data.analisa.total_sangat_baik || 0,
                                total_baik: data.analisa.total_baik || 0,
                                total_cukup: data.analisa.total_cukup || 0,
                                total_kurang: data.analisa.total_kurang || 0
                            };
                            
                            this.$nextTick(() => {
                                this.renderDistributionChart();
                            });
                            
                            new Toast({
                                message: 'Berhasil memuat analisa: ' + data.analisa.kode_analisa,
                                type: 'success'
                            });
                        } else {
                            this.error = response.data.message;
                            new Toast({
                                message: response.data.message,
                                type: 'danger'
                            });
                        }
                    } catch (err) {
                        this.error = 'Gagal memuat analisa: ' + (err.response?.data?.message || err.message);
                        new Toast({
                            message: this.error,
                            type: 'danger'
                        });
                    } finally {
                        this.isLoading = false;
                    }
                },
                
                /**
                 * Lihat detail perhitungan untuk satu karyawan
                 */
                async lihatDetail(hasil) {
                    this.selectedKaryawan = hasil;
                    this.showDetail = true;
                    this.detailPerhitungan = this.buildPreviewDetailPerhitungan(hasil);
                    
                    // Load detail dari file JSON terpilih
                    const detailId = hasil.result_id || hasil.db_id || null;
                    if (detailId) {
                        this.isLoadingDetail = true;
                        try {
                            const response = await axiosInstance.get(`AnalisaKinerja-Api?action=detail&id=${detailId}`);
                            
                            if (response.data.status) {
                                this.detailPerhitungan = response.data.data;
                            }
                        } catch (err) {
                            console.error('Gagal load detail:', err);
                        } finally {
                            this.isLoadingDetail = false;
                        }
                    }
                    
                    this.$nextTick(() => {
                        this.renderRadarChart();
                        $('#modalDetail').modal('show');
                    });
                },
                
                /**
                 * Hapus analisa
                 */
                async hapusAnalisa(analisaId) {
                    if (!confirm('Apakah Anda yakin ingin menghapus analisa ini?')) {
                        return;
                    }
                    
                    try {
                        const response = await axiosInstance.delete(`AnalisaKinerja-Api?action=hapus&id=${analisaId}`);
                        
                        if (response.data.status) {
                            if (this.selectedAnalisaId === analisaId) {
                                this.selectedAnalisaId = null;
                            }
                            this.loadDaftarAnalisa();
                            new Toast({
                                message: 'Analisa berhasil dihapus',
                                type: 'success'
                            });
                        } else {
                            new Toast({
                                message: response.data.message,
                                type: 'danger'
                            });
                        }
                    } catch (err) {
                        new Toast({
                            message: 'Gagal menghapus analisa',
                            type: 'danger'
                        });
                    }
                },
                
                // ========================
                // UI METHODS
                // ========================
                
                setActiveTab(tab) {
                    this.activeTab = tab;
                    if (tab === 'overview') {
                        this.$nextTick(() => this.renderDistributionChart());
                    }
                },
                
                tutupDetail() {
                    this.showDetail = false;
                    this.selectedKaryawan = null;
                    this.detailPerhitungan = null;
                    $('#modalDetail').modal('hide');
                },
                
                ubahParameterK() {
                    // Re-process dengan parameter baru
                    this.previewAnalisaReal();
                },
                
                // ========================
                // CHART METHODS
                // ========================
                
                renderDistributionChart() {
                    if (this.distributionChart) this.distributionChart.destroy();
                    
                    const ctx = document.getElementById('distributionChart');
                    if (ctx) {
                        this.distributionChart = new Chart(ctx, {
                            type: 'doughnut',
                            data: {
                                labels: ['Sangat Baik', 'Baik', 'Cukup', 'Kurang'],
                                datasets: [{
                                    data: [
                                        this.statistik.total_sangat_baik,
                                        this.statistik.total_baik,
                                        this.statistik.total_cukup,
                                        this.statistik.total_kurang
                                    ],
                                    backgroundColor: ['#1cc88a', '#4e73df', '#f6c23e', '#e74a3b']
                                }]
                            },
                            options: {
                                responsive: true,
                                plugins: {
                                    legend: { position: 'bottom' }
                                }
                            }
                        });
                    }
                },
                
                renderRadarChart() {
                    if (this.radarChart) this.radarChart.destroy();
                    
                    const ctx = document.getElementById('radarDetailChart');
                    if (ctx && this.selectedKaryawan) {
                        const labels = this.kriteria.map(k => k.kode);
                        const nilai = this.selectedKaryawan.karyawan.nilai;
                        
                        this.radarChart = new Chart(ctx, {
                            type: 'radar',
                            data: {
                                labels: labels,
                                datasets: [{
                                    label: this.selectedKaryawan.karyawan.nama,
                                    data: nilai,
                                    backgroundColor: 'rgba(78, 115, 223, 0.2)',
                                    borderColor: '#4e73df',
                                    pointBackgroundColor: '#4e73df'
                                }]
                            },
                            options: {
                                responsive: true,
                                scales: {
                                    r: {
                                        beginAtZero: true,
                                        max: 4,
                                        ticks: { stepSize: 1 }
                                    }
                                }
                            }
                        });
                    }
                },
                
                // ========================
                // HELPER METHODS
                // ========================
                
                getKlasifikasiClass(klasifikasi) {
                    const mapping = {
                        'Sangat Baik': 'success',
                        'Baik': 'primary',
                        'Cukup': 'warning',
                        'Kurang': 'danger'
                    };
                    return mapping[klasifikasi] || 'secondary';
                },
                
                getNilaiClass(nilai) {
                    if (nilai >= 4) return 'success';
                    if (nilai >= 3) return 'primary';
                    if (nilai >= 2) return 'warning';
                    return 'danger';
                },

                formatMathNumber(value, decimals = 4) {
                    const number = Number(value);
                    return Number.isFinite(number) ? number.toFixed(decimals) : value;
                },

                getDistanceCollapseId(item, index, section = 'default') {
                    return `distance-detail-${section}-${item.training_id || item.id || 'row'}-${index}`;
                },

                buildPreviewDistanceBreakdown(testingValues = [], trainingValues = []) {
                    const terms = [];
                    let sumSquared = 0;
                    const total = Math.max(
                        testingValues.length || 0,
                        trainingValues.length || 0,
                        this.kriteria.length || 0
                    );

                    for (let index = 0; index < total; index++) {
                        const testing = Number(testingValues[index] || 0);
                        const training = Number(trainingValues[index] || 0);
                        const selisih = testing - training;
                        const selisihKuadrat = selisih ** 2;
                        sumSquared += selisihKuadrat;

                        terms.push({
                            kode: this.kriteria[index]?.kode || `C${index + 1}`,
                            nama: this.kriteria[index]?.nama || `Kriteria ${index + 1}`,
                            testing,
                            training,
                            selisih,
                            selisih_kuadrat: selisihKuadrat,
                        });
                    }

                    return {
                        terms,
                        sum_squared: sumSquared,
                        sqrt_result: Math.sqrt(sumSquared),
                    };
                },

                buildPreviewDetailRows(items = [], testingValues = []) {
                    return (items || []).map((item, index) => ({
                        training_id: item.id || item.training_id || null,
                        training_order: item.training_order || (index + 1),
                        nama_training: item.nama || item.nama_training || '',
                        periode_training: item.nama_periode || item.periode_training || '-',
                        klasifikasi_training: item.klasifikasi || item.klasifikasi_training || '',
                        nilai_jarak: item.distance ?? item.nilai_jarak ?? 0,
                        ranking: item.ranking || null,
                        is_k_nearest: !!item.is_k_nearest,
                        detail_matematis: this.buildPreviewDistanceBreakdown(
                            testingValues,
                            item.nilai_training || item.nilai || []
                        ),
                    }));
                },

                buildPreviewOriginalDistanceOrder(hasil) {
                    const sortedMap = new Map(
                        (hasil?.all_distances || []).map(item => [
                            String(item.id || item.training_id || ''),
                            item,
                        ])
                    );

                    return (this.dataTraining || []).map((training, index) => {
                        const key = String(training.id || training.training_id || '');
                        const matched = sortedMap.get(key) || {};

                        return {
                            ...matched,
                            id: matched.id || matched.training_id || training.id || null,
                            nama: matched.nama || matched.nama_training || training.nama || '',
                            nama_periode: matched.nama_periode || matched.periode_training || training.nama_periode || '-',
                            nilai_training: matched.nilai_training || matched.nilai || training.nilai_kriteria || training.nilai || [],
                            klasifikasi: matched.klasifikasi || matched.klasifikasi_training || training.klasifikasi || '',
                            training_order: matched.training_order || (index + 1),
                        };
                    });
                },

                buildPreviewDetailPerhitungan(hasil) {
                    const testingValues = hasil?.karyawan?.nilai || [];
                    const originalItems = this.buildPreviewOriginalDistanceOrder(hasil);
                    const sortedItems = hasil?.all_distances || [];

                    const detailJarakUrutanAwal = this.buildPreviewDetailRows(originalItems, testingValues);
                    const detailJarakTerurut = this.buildPreviewDetailRows(sortedItems, testingValues);

                    const kNearest = (hasil?.k_nearest || []).map((item, index) => ({
                        training_id: item.id || item.training_id || null,
                        nama_training: item.nama || item.nama_training || '',
                        periode_training: item.nama_periode || item.periode_training || '-',
                        klasifikasi_training: item.klasifikasi || item.klasifikasi_training || '',
                        nilai_jarak: item.distance ?? item.nilai_jarak ?? 0,
                        ranking: item.ranking || (index + 1),
                    }));

                    return {
                        hasil,
                        kriteria: this.kriteria,
                        detail_jarak: detailJarakTerurut,
                        detail_jarak_terurut: detailJarakTerurut,
                        detail_jarak_urutan_awal: detailJarakUrutanAwal,
                        k_nearest: kNearest,
                    };
                },
                
                formatDistance(d) {
                    const number = Number(d);
                    return Number.isFinite(number)
                        ? number.toLocaleString('id-ID', {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        })
                        : d;
                },
                
                hitungRataRata(nilai) {
                    if (!nilai || nilai.length === 0) return '0.00';
                    return (nilai.reduce((a, b) => a + b, 0) / nilai.length).toFixed(2);
                },
                
                formatTanggal(tanggal) {
                    if (!tanggal) return '-';
                    return new Date(tanggal).toLocaleDateString('id-ID', {
                        day: '2-digit',
                        month: 'short',
                        year: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit'
                    });
                },
                
                getStatusClass(status) {
                    const mapping = {
                        'selesai': 'success',
                        'proses': 'warning',
                        'gagal': 'danger'
                    };
                    return mapping[status] || 'secondary';
                },
                
                exportJSON() {
                    const dataExport = {
                        parameter: this.knnParams,
                        kriteria: this.kriteria,
                        data_training: this.dataTraining,
                        statistik: this.statistik,
                        hasil_klasifikasi: this.hasilKlasifikasi,
                        exported_at: new Date().toISOString()
                    };
                    
                    const dataStr = JSON.stringify(dataExport, null, 2);
                    const dataUri = 'data:application/json;charset=utf-8,'+ encodeURIComponent(dataStr);
                    const linkElement = document.createElement('a');
                    linkElement.setAttribute('href', dataUri);
                    linkElement.setAttribute('download', 'knn_analysis_data.json');
                    linkElement.click();
                }
            }
        }).mount('#app')
    </script>
@endsection
@section('css')
    <style>
        .chart-container {
            position: relative;
            height: 280px;
        }
        .methodology-card {
            border-left: 4px solid #4e73df;
        }
        .formula-box {
            background: #f8f9fc;
            border: 1px solid #e3e6f0;
            border-radius: 8px;
            padding: 20px;
            font-family: 'Courier New', monospace;
        }
        .formula-box .formula {
            font-size: 1.1rem;
            color: #2c3e50;
            text-align: center;
            margin: 15px 0;
        }
        .step-number {
            width: 32px;
            height: 32px;
            background: #4e73df;
            color: white;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-right: 10px;
        }
        .distance-table td, .distance-table th {
            vertical-align: middle;
            font-size: 0.85rem;
        }
        .k-nearest-highlight {
            background-color: #d4edda !important;
        }
        .nav-tabs .nav-link.active {
            background-color: #4e73df;
            color: white;
            border-color: #4e73df;
        }
        .nav-tabs .nav-link {
            color: #4e73df;
        }
        .criteria-badge {
            font-size: 0.75rem;
            padding: 4px 8px;
        }
        .analisa-card {
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .analisa-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
        }
        .analisa-card.active {
            border-color: #4e73df !important;
            border-width: 2px;
        }
        .loading-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255,255,255,0.8);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10;
        }
        .param-control {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .param-control input, .param-control select {
            width: auto;
        }
        .math-stack {
            display: inline-flex;
            flex-direction: column;
            align-items: center;
            line-height: 1.05;
        }
        .math-stack .divider {
            width: 100%;
            border-top: 1px solid #6c757d;
            margin: 2px 0;
        }
        .math-expression {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 6px;
            font-size: 0.8rem;
        }
        .math-term-list {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
    </style>
@endsection
@section('modal')
    {{-- Modal Detail KNN Calculation --}}
    
@endsection
@section('isi')
    <div class="row" id="app">
        @include('Pages.AnalisaKinerja.Partials.DetailModal')
        @include('Pages.AnalisaKinerja.Partials.Header')
        @include('Pages.AnalisaKinerja.Partials.ParameterPanel')
        @include('Pages.AnalisaKinerja.Partials.AnalysisHistory')
        @include('Pages.AnalisaKinerja.Partials.TabNavigation')

        {{-- Loading Indicator --}}
        <div class="col-12 text-center py-5" v-if="isLoading">
            <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
                <span class="sr-only">Loading...</span>
            </div>
            <p class="mt-3 text-muted">Memproses analisa KNN...</p>
        </div>

        {{-- Error Alert --}}
        <div class="col-12 mb-3" v-if="error && !isLoading">
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle mr-2"></i>
                @{{ error }}
            </div>
        </div>

        @include('Pages.AnalisaKinerja.Partials.OverviewTab')
        @include('Pages.AnalisaKinerja.Partials.MethodologyTab')
        @include('Pages.AnalisaKinerja.Partials.KriteriaTab')
        @include('Pages.AnalisaKinerja.Partials.TrainingTab')
        @include('Pages.AnalisaKinerja.Partials.HasilTab')
    </div>
@endsection
