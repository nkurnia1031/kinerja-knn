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
                data = dataAwal();
                data.baseURL = 'KlasifikasiKinerja';
                data.canManage = '{{ $Session["admin"]->role ?? "" }}' === 'admin';
                data.kriteria = @json($data['kriteria'] ?? []);
                data.dataTraining = [];
                data.hasilKlasifikasi = [];
                data.k = 3;
                data.selectedKaryawan = null;
                data.distributionChart = null;
                data.barChart = null;
                data.radarChart = null;
                data.selectedPeriode = activePeriode;
                data.periodeList = periodeList;
                data.isLoading = false;
                data.error = null;
                return data;
            },
            computed: {
                ...computedAwal,
                statistikKlasifikasi() {
                    const stats = { 'Sangat Baik': 0, 'Baik': 0, 'Cukup': 0, 'Kurang': 0 };
                    this.hasilKlasifikasi.forEach(h => {
                        if (!h || !h.hasil_klasifikasi) {
                            return;
                        }
                        if (stats[h.hasil_klasifikasi] !== undefined) {
                            stats[h.hasil_klasifikasi]++;
                        }
                    });
                    return stats;
                },
                totalBobot() {
                    return this.kriteria.reduce((sum, k) => sum + parseFloat(k.bobot || 0), 0);
                },
                totalKaryawan() {
                    return this.hasilKlasifikasi.length;
                }
            },
            mounted() {
                this.loadPreviewKlasifikasi();
            },
            methods: {
                ...methodAwal,
                async loadPreviewKlasifikasi() {
                    this.isLoading = true;
                    this.error = null;
                    try {
                        const response = await axiosInstance.post('KlasifikasiKinerja-Api?action=preview', {
                            nilai_k: this.k,
                            metode_jarak: 'euclidean',
                            periode_id: this.selectedPeriode || ''
                        });

                        if (!response.data.status) {
                            this.error = response.data.message || 'Gagal memuat klasifikasi';
                            if (response.data.redirect) {
                                window.location.href = response.data.redirect;
                            }
                            return;
                        }

                        const payload = response.data.data || {};
                        this.kriteria = payload.kriteria || this.kriteria;
                        this.dataTraining = (payload.data_training || []).map(item => ({
                            ...item,
                            nilai_kriteria: item.nilai_kriteria || item.nilai || []
                        }));
                        this.hasilKlasifikasi = (payload.hasil || []).filter(item => item && item.karyawan);

                        this.$nextTick(() => {
                            this.renderCharts();
                        });
                    } catch (err) {
                        this.error = err.response?.data?.message || err.message;
                    } finally {
                        this.isLoading = false;
                    }
                },
                renderCharts() {
                    this.renderDistributionChart();
                    this.renderBarChart();
                },
                renderDistributionChart() {
                    if (this.distributionChart) this.distributionChart.destroy();

                    const ctx = document.getElementById('pieChart');
                    if (ctx) {
                        this.distributionChart = new Chart(ctx, {
                            type: 'doughnut',
                            data: {
                                labels: ['Sangat Baik', 'Baik', 'Cukup', 'Kurang'],
                                datasets: [{
                                    data: [
                                        this.statistikKlasifikasi['Sangat Baik'],
                                        this.statistikKlasifikasi['Baik'],
                                        this.statistikKlasifikasi['Cukup'],
                                        this.statistikKlasifikasi['Kurang']
                                    ],
                                    backgroundColor: ['#1cc88a', '#4e73df', '#f6c23e', '#e74a3b']
                                }]
                            },
                            options: {
                                responsive: true,
                                plugins: { legend: { position: 'bottom' } }
                            }
                        });
                    }
                },
                renderBarChart() {
                    if (this.barChart) this.barChart.destroy();

                    const ctx = document.getElementById('barChart');
                    if (ctx) {
                        const avgPerKriteria = this.kriteria.map((k, idx) => {
                            if (this.hasilKlasifikasi.length === 0) return 0;
                            const total = this.hasilKlasifikasi.reduce((sum, item) => sum + (parseFloat(item.karyawan.nilai[idx]) || 0), 0);
                            return (total / this.hasilKlasifikasi.length).toFixed(2);
                        });

                        this.barChart = new Chart(ctx, {
                            type: 'bar',
                            data: {
                                labels: this.kriteria.map(k => k.kode),
                                datasets: [{
                                    label: 'Rata-rata Nilai (Skala 1-4)',
                                    data: avgPerKriteria,
                                    backgroundColor: '#4e73df'
                                }]
                            },
                            options: {
                                responsive: true,
                                scales: {
                                    y: { beginAtZero: true, max: 4 }
                                }
                            }
                        });
                    }
                },
                renderRadarChart() {
                    if (this.radarChart) this.radarChart.destroy();

                    const ctx = document.getElementById('radarDetailChart');
                    if (ctx && this.selectedKaryawan) {
                        this.radarChart = new Chart(ctx, {
                            type: 'radar',
                            data: {
                                labels: this.kriteria.map(k => k.kode),
                                datasets: [{
                                    label: this.selectedKaryawan.karyawan.nama,
                                    data: this.selectedKaryawan.karyawan.nilai,
                                    backgroundColor: 'rgba(78, 115, 223, 0.2)',
                                    borderColor: '#4e73df',
                                    pointBackgroundColor: '#4e73df'
                                }]
                            },
                            options: {
                                responsive: true,
                                scales: {
                                    r: { beginAtZero: true, max: 4, ticks: { stepSize: 1 } }
                                }
                            }
                        });
                    }
                },
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
                formatDistance(d) {
                    return typeof d === 'number' ? d.toFixed(4) : d;
                },
                hitungRataRata(nilai) {
                    if (!nilai || nilai.length === 0) return '0.00';
                    return (nilai.reduce((a, b) => a + b, 0) / nilai.length).toFixed(2);
                },
                lihatDetail(hasil) {
                    this.selectedKaryawan = hasil;
                    $('#modalDetail').modal('show');
                    this.$nextTick(() => this.renderRadarChart());
                },
                ubahK() {
                    if (this.k >= 1 && this.k <= 10) {
                        this.loadPreviewKlasifikasi();
                    }
                }
            }
        }).mount('#app')
    </script>
@endsection
@section('css')
    <style>
        .chart-container { position: relative; height: 280px; }
        .k-nearest-highlight { background-color: #d4edda !important; }
        .stat-card { transition: transform 0.2s; }
        .stat-card:hover { transform: translateY(-3px); }
        .formula-box {
            background: #f8f9fc;
            border: 1px solid #e3e6f0;
            border-radius: 8px;
            padding: 15px;
            font-family: 'Courier New', monospace;
        }
        .badge-lg { font-size: 1rem; padding: 8px 12px; }
    </style>
@endsection
@section('modal')
    {{-- Modal Detail Perhitungan KNN --}}
  
@endsection
@section('isi')
    <div class="row" id="app">
        <div class="modal fade" v-if="selectedKaryawan" id="modalDetail" tabindex="-1">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title" v-if="selectedKaryawan">
                            <i class="fas fa-calculator mr-2"></i>Detail Perhitungan KNN - @{{ selectedKaryawan.karyawan.nama }}
                        </h5>
                        <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body" v-if="selectedKaryawan">
                        <div class="row">
                            {{-- Info Karyawan --}}
                            <div class="col-md-4 mb-3">
                                <div class="card bg-light h-100">
                                    <div class="card-body">
                                        <h6 class="font-weight-bold mb-3">
                                            <i class="fas fa-user mr-2"></i>Informasi Karyawan
                                        </h6>
                                        <table class="table table-sm table-borderless mb-0">
                                            <tr><td width="100">Nomor Pekerja</td><td>: <code>@{{ selectedKaryawan.karyawan.nomor_pekerja || selectedKaryawan.karyawan.nip || '-' }}</code></td></tr>
                                            <tr><td>Nama</td><td>: <strong>@{{ selectedKaryawan.karyawan.nama }}</strong></td></tr>
                                            <tr><td>Jabatan</td><td>: @{{ selectedKaryawan.karyawan.jabatan }}</td></tr>
                                            <tr><td>Pekerjaan</td><td>: @{{ selectedKaryawan.karyawan.pekerjaan || '-' }}</td></tr>
                                            <tr><td>Atasan</td><td>: @{{ selectedKaryawan.karyawan.atasan }}</td></tr>
                                        </table>
                                        <hr>
                                        <p class="mb-1 font-weight-bold">Hasil Klasifikasi KNN:</p>
                                        <h3>@include('Komponen.KlasifikasiBadge', ['vueExpr' => 'selectedKaryawan.hasil_klasifikasi', 'large' => true])</h3>
                                        <p class="mb-0 mt-2">
                                            <small>Confidence Level: <strong class="text-primary">@{{ selectedKaryawan.confidence }}%</strong></small>
                                        </p>
                                    </div>
                                </div>
                            </div>
                            
                            {{-- Radar Chart --}}
                            <div class="col-md-8 mb-3">
                                <div class="card h-100">
                                    <div class="card-header bg-light py-2">
                                        <strong><i class="fas fa-chart-radar mr-2"></i>Visualisasi Nilai per Kriteria</strong>
                                    </div>
                                    <div class="card-body">
                                        <canvas id="radarDetailChart" height="200"></canvas>
                                    </div>
                                </div>
                            </div>
                            
                            {{-- Tabel Nilai Kriteria --}}
                            <div class="col-12 mb-3">
                                <div class="card">
                                    <div class="card-header bg-light py-2">
                                        <strong><i class="fas fa-list-ol mr-2"></i>Nilai per Kriteria (Skala 1-4)</strong>
                                    </div>
                                    <div class="card-body p-0">
                                        <div class="table-responsive">
                                            <table class="table table-sm table-bordered mb-0">
                                                <thead class="bg-secondary text-white">
                                                    <tr>
                                                        <th class="text-center" width="60">Kode</th>
                                                        <th>Kriteria Penilaian</th>
                                                        <th class="text-center" width="80">Bobot</th>
                                                        <th class="text-center" width="80">Nilai</th>
                                                        <th class="text-center" width="120">Nilai x Bobot</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr v-for="(k, idx) in kriteria" :key="k.id">
                                                        <td class="text-center"><span class="badge badge-secondary">@{{ k.kode }}</span></td>
                                                        <td>
                                                            @{{ k.nama }}
                                                            <br><small class="text-muted">@{{ k.nama_en }}</small>
                                                        </td>
                                                        <td class="text-center">@{{ k.bobot }}%</td>
                                                        <td class="text-center">
                                                            <span class="badge" :class="'badge-' + getNilaiClass(selectedKaryawan.karyawan.nilai[idx])">
                                                                @{{ selectedKaryawan.karyawan.nilai[idx] }}
                                                            </span>
                                                        </td>
                                                        <td class="text-center">
                                                            @{{ (selectedKaryawan.karyawan.nilai[idx] * k.bobot / 100).toFixed(3) }}
                                                        </td>
                                                    </tr>
                                                </tbody>
                                                <tfoot class="bg-light font-weight-bold">
                                                    <tr>
                                                        <td colspan="2" class="text-right">Total / Rata-rata:</td>
                                                        <td class="text-center">@{{ totalBobot }}%</td>
                                                        <td class="text-center">@{{ hitungRataRata(selectedKaryawan.karyawan.nilai) }}</td>
                                                        <td class="text-center">-</td>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            {{-- Rumus KNN --}}
                            <div class="col-12 mb-3">
                                <div class="alert alert-info">
                                    <strong><i class="fas fa-info-circle mr-2"></i>Rumus Euclidean Distance (Weighted):</strong><br>
                                    <code class="text-dark">d(x, y) = sqrt( SUM[ (x<sub>i</sub> - y<sub>i</sub>)<sup>2</sup> x (w<sub>i</sub>/100)<sup>2</sup> ] )</code><br>
                                    <small>Dimana: x<sub>i</sub> = nilai karyawan, y<sub>i</sub> = nilai data training, w<sub>i</sub> = bobot kriteria ke-i</small>
                                </div>
                            </div>
                            
                            {{-- Tabel Jarak --}}
                            <div class="col-12 mb-3">
                                <div class="card">
                                    <div class="card-header bg-light py-2">
                                        <strong><i class="fas fa-ruler mr-2"></i>Perhitungan Jarak ke Semua Data Training</strong>
                                    </div>
                                    <div class="card-body p-0">
                                        <div class="table-responsive">
                                            <table class="table table-sm table-bordered mb-0">
                                                <thead class="bg-dark text-white">
                                                    <tr>
                                                        <th class="text-center" width="60">ID</th>
                                                        <th>Nama (Data Training)</th>
                                                        <th class="text-center">Klasifikasi</th>
                                                        <th class="text-center" width="120">Jarak Euclidean</th>
                                                        <th class="text-center" width="80">Ranking</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr v-for="(d, idx) in selectedKaryawan.all_distances" :key="d.id"
                                                        :class="idx < k ? 'k-nearest-highlight' : ''">
                                                        <td class="text-center">@{{ d.id }}</td>
                                                        <td>
                                                            @{{ d.nama }}
                                                            <span v-if="idx < k" class="badge badge-success ml-2">K-Nearest</span>
                                                        </td>
                                                        <td class="text-center">
                                                            <span class="badge" :class="'badge-' + getKlasifikasiClass(d.klasifikasi)">
                                                                @{{ d.klasifikasi }}
                                                            </span>
                                                        </td>
                                                        <td class="text-center font-weight-bold">@{{ formatDistance(d.distance) }}</td>
                                                        <td class="text-center">@{{ idx + 1 }}</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            {{-- K-Nearest & Voting --}}
                            <div class="col-md-6 mb-3">
                                <div class="card border-success h-100">
                                    <div class="card-header bg-success text-white py-2">
                                        <strong><i class="fas fa-users mr-2"></i>@{{ k }} Tetangga Terdekat (K-Nearest)</strong>
                                    </div>
                                    <div class="card-body">
                                        <table class="table table-sm mb-0">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Nama</th>
                                                    <th>Jarak</th>
                                                    <th>Klasifikasi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr v-for="(n, idx) in selectedKaryawan.k_nearest" :key="n.id">
                                                    <td>@{{ idx + 1 }}</td>
                                                    <td>@{{ n.nama }}</td>
                                                    <td>@{{ formatDistance(n.distance) }}</td>
                                                    <td>
                                                        <span class="badge" :class="'badge-' + getKlasifikasiClass(n.klasifikasi)">
                                                            @{{ n.klasifikasi }}
                                                        </span>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <div class="card border-primary h-100">
                                    <div class="card-header bg-primary text-white py-2">
                                        <strong><i class="fas fa-vote-yea mr-2"></i>Hasil Voting Mayoritas</strong>
                                    </div>
                                    <div class="card-body">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Klasifikasi</th>
                                                    <th class="text-center">Jumlah Vote</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr v-for="(count, kelas) in selectedKaryawan.voting" :key="kelas"
                                                    :class="kelas === selectedKaryawan.hasil_klasifikasi ? 'table-success font-weight-bold' : ''">
                                                    <td>
                                                        <span class="badge" :class="'badge-' + getKlasifikasiClass(kelas)">@{{ kelas }}</span>
                                                        <i v-if="kelas === selectedKaryawan.hasil_klasifikasi" class="fas fa-check-circle text-success ml-2"></i>
                                                        <small v-if="kelas === selectedKaryawan.hasil_klasifikasi" class="text-success">(Terpilih)</small>
                                                    </td>
                                                    <td class="text-center">@{{ count }} vote</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                        <hr>
                                        <div class="alert alert-success mb-0 py-2">
                                            <small>
                                                <strong>Kesimpulan:</strong> Berdasarkan voting dari @{{ k }} tetangga terdekat, 
                                                <strong>@{{ selectedKaryawan.karyawan.nama }}</strong> diklasifikasikan sebagai 
                                                <span class="badge" :class="'badge-' + getKlasifikasiClass(selectedKaryawan.hasil_klasifikasi)">
                                                    @{{ selectedKaryawan.hasil_klasifikasi }}
                                                </span>
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            <i class="fas fa-times mr-1"></i>Tutup
                        </button>
                    </div>
                </div>
            </div>
        </div>
        {{-- Header --}}
        <div class="col-12 mb-3">
            <div class="card bg-gradient-primary text-white shadow">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h4 class="mb-1">
                                <i class="fas fa-sitemap mr-2"></i>Klasifikasi Kinerja Karyawan - Metode KNN
                            </h4>
                            <p class="mb-0 opacity-75">
                                Hasil analisa klasifikasi menggunakan algoritma K-Nearest Neighbor dengan perhitungan transparan
                            </p>
                        </div>
                        <div class="col-md-4 text-md-right mt-3 mt-md-0">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Parameter KNN --}}
        <div class="col-12 mb-3">
            <div class="card shadow">
                <div class="card-header py-2 bg-light">
                    <strong><i class="fas fa-cogs mr-2"></i>Parameter Algoritma KNN</strong>
                </div>
                <div class="card-body">
                    <div class="row align-items-end">
                        <div class="col-md-3 mb-2">
                            <label class="font-weight-bold small">Nilai K (Tetangga Terdekat):</label>
                            <div class="input-group">
                                <input type="number" v-model.number="k" class="form-control" min="1" max="10">
                                <div class="input-group-append">
                                    <button v-if="canManage" class="btn btn-primary" @click="ubahK">
                                        <i class="fas fa-sync-alt"></i> Update
                                    </button>
                                </div>
                            </div>
                            <small class="text-muted">Rekomendasi: 3, 5, atau 7 (ganjil)</small>
                        </div>
                        <div class="col-md-3 mb-2">
                            <label class="font-weight-bold small">Metode Jarak:</label>
                            <input type="text" class="form-control bg-light" value="Euclidean Distance (Weighted)" disabled>
                        </div>
                        <div class="col-md-3 mb-2">
                            <label class="font-weight-bold small">Periode:</label>
                            <select v-model="selectedPeriode" class="form-control" @change="loadPreviewKlasifikasi">
                                <option value="">Periode Aktif</option>
                                <option v-for="periode in periodeList" :key="periode.id" :value="periode.id">
                                    @{{ periode.nama_periode }} (@{{ periode.status }})
                                </option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-2">
                            <label class="font-weight-bold small">Data Training:</label>
                            <input type="text" class="form-control bg-light" :value="dataTraining.length + ' data karyawan'" disabled>
                        </div>
                        <div class="col-md-3 mb-2">
                            <label class="font-weight-bold small">Jumlah Kriteria:</label>
                            <input type="text" class="form-control bg-light" :value="kriteria.length + ' kriteria (Skala 1-4)'" disabled>
                        </div>
                    </div>
                    <div class="alert alert-info mt-3 mb-0">
                        <small>
                            Training KNN dibaca dari seluruh riwayat penilaian yang berstatus training, tetapi
                            <strong>periode aktif tidak pernah dipakai sebagai training</strong>. Semua data pada
                            <strong>periode aktif / periode terpilih</strong> akan diklasifikasi sebagai data testing.
                        </small>
                    </div>
                </div>
            </div>
        </div>

        {{-- Statistik Cards --}}
        @include('Pages.KlasifikasiKinerja.Partials.StatCard', ['variant' => 'success', 'title' => 'Sangat Baik', 'valueExpr' => "statistikKlasifikasi['Sangat Baik']", 'suffix' => ' Karyawan', 'hint' => 'Rata-rata >= 3.5', 'icon' => 'fas fa-star'])
        @include('Pages.KlasifikasiKinerja.Partials.StatCard', ['variant' => 'primary', 'title' => 'Baik', 'valueExpr' => "statistikKlasifikasi['Baik']", 'suffix' => ' Karyawan', 'hint' => 'Rata-rata >= 2.75', 'icon' => 'fas fa-thumbs-up'])
        @include('Pages.KlasifikasiKinerja.Partials.StatCard', ['variant' => 'warning', 'title' => 'Cukup', 'valueExpr' => "statistikKlasifikasi['Cukup']", 'suffix' => ' Karyawan', 'hint' => 'Rata-rata >= 2.0', 'icon' => 'fas fa-minus-circle'])
        @include('Pages.KlasifikasiKinerja.Partials.StatCard', ['variant' => 'danger', 'title' => 'Kurang', 'valueExpr' => "statistikKlasifikasi['Kurang']", 'suffix' => ' Karyawan', 'hint' => 'Rata-rata < 2.0', 'icon' => 'fas fa-exclamation-triangle'])

        {{-- Charts --}}
        <div class="col-lg-5 mb-4">
            <div class="card shadow h-100">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-chart-pie mr-2"></i>Distribusi Klasifikasi Kinerja
                    </h6>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="pieChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-7 mb-4">
            <div class="card shadow h-100">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-chart-bar mr-2"></i>Rata-rata Nilai per Kriteria (Skala 1-4)
                    </h6>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="barChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tabel Hasil Klasifikasi --}}
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-table mr-2"></i>Hasil Klasifikasi KNN (K = @{{ k }})
                    </h6>
                    <span class="badge badge-info">Total: @{{ totalKaryawan }} Karyawan</span>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="bg-primary text-white">
                                <tr>
                                    <th class="text-center" width="50">#</th>
                                    <th width="160">Nomor Pekerja</th>
                                    <th>Nama Karyawan</th>
                                    <th>Jabatan</th>
                                    <th>Pekerjaan</th>
                                    <th class="text-center" width="100">Rata-rata</th>
                                    <th class="text-center" width="140">Klasifikasi Penilaian</th>
                                    <th class="text-center" width="120">Klasifikasi KNN</th>
                                    <th class="text-center" width="100">Confidence</th>
                                    <th class="text-center" width="120">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(h, idx) in hasilKlasifikasi" :key="(h.karyawan && h.karyawan.id) ? h.karyawan.id : idx">
                                    <td class="text-center">@{{ idx + 1 }}</td>
                                    <td><code>@{{ h.karyawan.nomor_pekerja || h.karyawan.nip || '-' }}</code></td>
                                    <td><strong>@{{ h.karyawan.nama }}</strong></td>
                                    <td>@{{ h.karyawan.jabatan }}</td>
                                    <td>@{{ h.karyawan.pekerjaan || '-' }}</td>
                                    <td class="text-center">
                                        <span class="font-weight-bold">@{{ hitungRataRata(h.karyawan.nilai) }}</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge badge-lg" :class="'badge-' + getKlasifikasiClass((h.karyawan && h.karyawan.klasifikasi_penilaian) ? h.karyawan.klasifikasi_penilaian : '-')">
                                            @{{ (h.karyawan && h.karyawan.klasifikasi_penilaian) ? h.karyawan.klasifikasi_penilaian : '-' }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge badge-lg" :class="'badge-' + getKlasifikasiClass(h.hasil_klasifikasi || '-')">
                                            @{{ h.hasil_klasifikasi || '-' }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <strong class="text-primary">@{{ h.confidence }}%</strong>
                                    </td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-info" @click="lihatDetail(h)" title="Lihat Detail Perhitungan KNN">
                                            <i class="fas fa-calculator mr-1"></i> Detail KNN
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    {{-- Legend --}}
                    @include('Pages.KlasifikasiKinerja.Partials.Legend')
                </div>
            </div>
        </div>
    </div>
@endsection
