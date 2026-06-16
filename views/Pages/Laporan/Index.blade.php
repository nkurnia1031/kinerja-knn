@extends('Layout.html')
@section('js')
    <script>
        const { createApp } = Vue;

        app = createApp({
            data() {
                return {
                    jenisLaporan: 'rekap_karyawan',
                    periodeList: @json($data['periodeList'] ?? []),
                    filterOptions: @json($data['filterOptions'] ?? []),
                    filters: {
                        jenis_laporan: 'rekap_karyawan',
                        q: '',
                        status: '',
                        role: '',
                        pekerjaan: '',
                        periode_id: '',
                        klasifikasi: ''
                    },
                    rows: [],
                    summary: {},
                    judulLaporan: '',
                    filterLabel: 'Semua data',
                    loading: false,
                    modalWidgetType: '',
                    modalWidgetTitle: '',
                    modalWidgetItems: []
                };
            },
            mounted() {
                this.loadData();
            },
            methods: {
                loadData() {
                    this.loading = true;
                    this.filters.jenis_laporan = this.jenisLaporan;

                    axiosInstance.get('Laporan-Api', {
                        params: this.filters
                    }).then(res => {
                        const data = res.data.data || {};
                        this.rows = data.rows || [];
                        this.summary = data.summary || {};
                        this.judulLaporan = data.judul_laporan || '';
                        this.filterLabel = data.filter_label || 'Semua data';
                    }).finally(() => {
                        this.loading = false;
                    });
                },
                resetFilter() {
                    this.filters.q = '';
                    this.filters.status = '';
                    this.filters.role = '';
                    this.filters.pekerjaan = '';
                    this.filters.periode_id = '';
                    this.filters.klasifikasi = '';
                    this.loadData();
                },
                onJenisLaporanChange() {
                    this.resetFilter();
                },
                buildCetakUrl() {
                    const params = new URLSearchParams();
                    Object.entries({
                        ...this.filters,
                        jenis_laporan: this.jenisLaporan
                    }).forEach(([key, value]) => {
                        if (value !== null && value !== '') {
                            params.append(key, value);
                        }
                    });
                    return 'Laporan-Cetak?' + params.toString();
                },
                getBadgeClass(value) {
                    const map = {
                        'Sangat Baik': 'success',
                        'Baik': 'primary',
                        'Cukup': 'warning',
                        'Kurang': 'danger',
                        'Sangat Kurang': 'dark',
                        'aktif': 'success',
                        'nonaktif': 'secondary',
                        'cuti': 'warning',
                        'resign': 'danger',
                        'admin': 'primary',
                        'pimpinan': 'dark',
                        'atasan': 'info',
                        'karyawan': 'secondary'
                    };
                    return map[value] || 'secondary';
                },
                getDecisionCategory(item) {
                    const label = item.klasifikasi_knn || '';
                    if (['Sangat Baik', 'Baik'].includes(label)) return 'Prioritas Apresiasi';
                    if (label === 'Cukup') return 'Perlu Penguatan Kinerja';
                    if (['Kurang', 'Sangat Kurang'].includes(label)) return 'Prioritas Pembinaan';
                    return '-';
                },
                getDecisionBadgeClass(item) {
                    const category = this.getDecisionCategory(item);
                    const map = {
                        'Prioritas Apresiasi': 'success',
                        'Perlu Penguatan Kinerja': 'warning',
                        'Prioritas Pembinaan': 'danger'
                    };
                    return map[category] || 'secondary';
                },
                openWidgetModal(type) {
                    if (this.jenisLaporan !== 'rekap_penilaian_klasifikasi') return;

                    let items = [];
                    let title = '';

                    if (type === 'reward') {
                        title = 'Daftar Prioritas Apresiasi';
                        items = this.rows
                            .filter(item => ['Sangat Baik', 'Baik'].includes(item.klasifikasi_knn))
                            .sort((a, b) => parseFloat(b.total_nilai || 0) - parseFloat(a.total_nilai || 0));
                    } else if (type === 'pemantauan') {
                        title = 'Daftar Perlu Penguatan Kinerja';
                        items = this.rows
                            .filter(item => item.klasifikasi_knn === 'Cukup')
                            .sort((a, b) => parseFloat(b.total_nilai || 0) - parseFloat(a.total_nilai || 0));
                    } else if (type === 'bimbingan') {
                        title = 'Daftar Prioritas Pembinaan';
                        items = this.rows
                            .filter(item => ['Kurang', 'Sangat Kurang'].includes(item.klasifikasi_knn))
                            .sort((a, b) => parseFloat(a.total_nilai || 0) - parseFloat(b.total_nilai || 0));
                    } else if (type === 'total') {
                        title = 'Daftar Seluruh Hasil Penilaian';
                        items = [...this.rows].sort((a, b) => parseFloat(b.total_nilai || 0) - parseFloat(a.total_nilai || 0));
                    }

                    this.modalWidgetType = type;
                    this.modalWidgetTitle = title;
                    this.modalWidgetItems = items;
                    $('#modalWidgetLaporan').modal('show');
                }
            }
        }).mount('#app');
    </script>
@endsection
@section('css')
    <style>
        .report-card {
            transition: all 0.2s ease;
            cursor: pointer;
            border: 2px solid transparent;
        }

        .report-card:hover {
            transform: translateY(-4px);
        }

        .report-card.active {
            border-color: #4e73df;
            box-shadow: 0 0.5rem 1rem rgba(78, 115, 223, 0.18);
        }
    </style>
@endsection
@section('modal')
    
@endsection
@section('isi')
    <div class="row" id="app">
        <div class="modal fade" id="modalWidgetLaporan" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">@{{ modalWidgetTitle }}</h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="table-responsive" v-if="modalWidgetItems.length > 0">
                            <table class="table table-bordered table-hover mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th>No</th>
                                        <th>Nama</th>
                                        <th>Jabatan</th>
                                        <th>Total Nilai</th>
                                        <th>Klasifikasi KNN</th>
                                        <th>Kategori Keputusan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(item, index) in modalWidgetItems" :key="item.id">
                                        <td class="text-center">@{{ index + 1 }}</td>
                                        <td>@{{ item.nama || '-' }}</td>
                                        <td>@{{ item.jabatan || '-' }}</td>
                                        <td class="text-center font-weight-bold">@{{ item.total_nilai || 0 }}</td>
                                        <td class="text-center">
                                            <span :class="'badge badge-' + getBadgeClass(item.klasifikasi_knn)">@{{ item.klasifikasi_knn || '-' }}</span>
                                        </td>
                                        <td class="text-center">
                                            <span :class="'badge badge-' + getDecisionBadgeClass(item)">@{{ getDecisionCategory(item) }}</span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div v-else class="text-center text-muted py-4">
                            Tidak ada data untuk kategori ini.
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 mb-3">
            <div class="card shadow border-left-primary">
                <div class="card-body">
                    <h4 class="mb-1">Laporan</h4>
                    <p class="mb-0 text-muted">Halaman ini menyatukan pilihan <strong>Rekap Karyawan</strong> dan <strong>Rekap Penilaian &amp; Klasifikasi</strong> dalam satu laporan.</p>
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-3">
            <div class="card shadow report-card" :class="{ active: jenisLaporan === 'rekap_karyawan' }" @click="jenisLaporan = 'rekap_karyawan'; onJenisLaporanChange()">
                <div class="card-body">
                    <h5 class="mb-1"><i class="fas fa-users text-primary"></i> Rekap Karyawan</h5>
                    <small class="text-muted">Menampilkan ringkasan data seluruh karyawan sesuai filter.</small>
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-3">
            <div class="card shadow report-card" :class="{ active: jenisLaporan === 'rekap_penilaian_klasifikasi' }" @click="jenisLaporan = 'rekap_penilaian_klasifikasi'; onJenisLaporanChange()">
                <div class="card-body">
                    <h5 class="mb-1"><i class="fas fa-chart-bar text-success"></i> Rekap Penilaian &amp; Klasifikasi</h5>
                    <small class="text-muted">Menampilkan hasil nilai akhir, klasifikasi penilaian, dan klasifikasi KNN.</small>
                </div>
            </div>
        </div>

        <div class="col-12 mb-3">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Filter Laporan</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label>Cari</label>
                            <input type="text" v-model="filters.q" class="form-control" placeholder="Nama, nomor pekerja, jabatan">
                        </div>

                        <template v-if="jenisLaporan === 'rekap_karyawan'">
                            <div class="col-md-4 mb-3">
                                <label>Status</label>
                                <select v-model="filters.status" class="form-control">
                                    <option value="">Semua Status</option>
                                    <option v-for="item in filterOptions.status" :value="item">@{{ item }}</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label>Role</label>
                                <select v-model="filters.role" class="form-control">
                                    <option value="">Semua Role</option>
                                    <option v-for="item in filterOptions.role" :value="item">@{{ item }}</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label>Pekerjaan</label>
                                <select v-model="filters.pekerjaan" class="form-control">
                                    <option value="">Semua Pekerjaan</option>
                                    <option v-for="item in filterOptions.pekerjaan" :value="item">@{{ item }}</option>
                                </select>
                            </div>
                        </template>

                        <template v-else>
                            <div class="col-md-4 mb-3">
                                <label>Periode Penilaian</label>
                                <select v-model="filters.periode_id" class="form-control">
                                    <option value="">Semua Periode</option>
                                    <option v-for="item in periodeList" :value="item.id">@{{ item.nama_periode }}</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label>Klasifikasi</label>
                                <select v-model="filters.klasifikasi" class="form-control">
                                    <option value="">Semua Klasifikasi</option>
                                    <option v-for="item in filterOptions.klasifikasi" :value="item">@{{ item }}</option>
                                </select>
                            </div>
                        </template>
                    </div>

                    <div class="d-flex justify-content-end">
                        <button type="button" class="btn btn-secondary mr-2" @click="resetFilter()">
                            <i class="fas fa-undo"></i> Reset
                        </button>
                        <button type="button" class="btn btn-primary mr-2" @click="loadData()" :disabled="loading">
                            <i class="fas fa-search"></i> @{{ loading ? 'Memuat...' : 'Tampilkan' }}
                        </button>
                        <a :href="buildCetakUrl()" target="_blank" class="btn btn-success">
                            <i class="fas fa-print"></i> Cetak Laporan
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <template v-if="jenisLaporan === 'rekap_karyawan'">
            <div class="col-md-3 mb-3">
                <div class="card shadow border-left-primary">
                    <div class="card-body">
                        <div class="text-xs text-primary font-weight-bold text-uppercase mb-1">Total Karyawan</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">@{{ summary.total || 0 }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card shadow border-left-success">
                    <div class="card-body">
                        <div class="text-xs text-success font-weight-bold text-uppercase mb-1">Status Aktif</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">@{{ summary.aktif || 0 }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card shadow border-left-info">
                    <div class="card-body">
                        <div class="text-xs text-info font-weight-bold text-uppercase mb-1">Total Atasan</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">@{{ summary.atasan || 0 }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card shadow border-left-dark">
                    <div class="card-body">
                        <div class="text-xs text-dark font-weight-bold text-uppercase mb-1">Total Pimpinan</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">@{{ summary.pimpinan || 0 }}</div>
                    </div>
                </div>
            </div>
        </template>

        <template v-else>
            <div class="col-md-3 mb-3">
                <div class="card shadow border-left-primary report-card" @click="openWidgetModal('total')">
                    <div class="card-body">
                        <div class="text-xs text-primary font-weight-bold text-uppercase mb-1">Total Penilaian</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">@{{ summary.total || 0 }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card shadow border-left-success report-card" @click="openWidgetModal('reward')">
                    <div class="card-body">
                        <div class="text-xs text-success font-weight-bold text-uppercase mb-1">Prioritas Apresiasi</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">@{{ summary.kandidat_reward || 0 }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card shadow border-left-info report-card" @click="openWidgetModal('pemantauan')">
                    <div class="card-body">
                        <div class="text-xs text-info font-weight-bold text-uppercase mb-1">Perlu Penguatan</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">@{{ summary.perlu_pemantauan || 0 }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card shadow border-left-danger report-card" @click="openWidgetModal('bimbingan')">
                    <div class="card-body">
                        <div class="text-xs text-danger font-weight-bold text-uppercase mb-1">Prioritas Pembinaan</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">@{{ summary.perlu_bimbingan || 0 }}</div>
                    </div>
                </div>
            </div>
            <div class="col-12 mb-3">
                <div class="alert alert-light border-left-warning shadow-sm mb-0">
                    Widget rekomendasi pada laporan ini menggunakan <strong>klasifikasi KNN</strong> sebagai dasar pendukung keputusan untuk pemberian reward pada karyawan overperform dan bimbingan pada karyawan underperform.
                </div>
            </div>
        </template>

        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">@{{ judulLaporan || 'Preview Laporan' }}</h6>
                    <small class="text-muted">@{{ filterLabel }}</small>
                </div>
                <div class="card-body">
                    <div class="table-responsive" v-if="rows.length > 0">
                        <table class="table table-bordered table-hover">
                            <thead class="bg-light" v-if="jenisLaporan === 'rekap_karyawan'">
                                <tr>
                                    <th>No</th>
                                    <th>Nomor Pekerja</th>
                                    <th>Nama</th>
                                    <th>Jabatan</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th>Pekerjaan</th>
                                    <th>Tanggal Bergabung</th>
                                </tr>
                            </thead>
                            <thead class="bg-light" v-else>
                                <tr>
                                    <th>No</th>
                                    <th>Nomor Pekerja</th>
                                    <th>Nama</th>
                                    <th>Jabatan</th>
                                    <th>Periode</th>
                                    <th>Penilai</th>
                                    <th>Total Nilai</th>
                                    <th>Klasifikasi</th>
                                    <th>Klasifikasi KNN</th>
                                    <th>Kategori Keputusan</th>
                                    <th>Tanggal Penilaian</th>
                                </tr>
                            </thead>
                            <tbody v-if="jenisLaporan === 'rekap_karyawan'">
                                <tr v-for="(item, index) in rows" :key="item.id">
                                    <td class="text-center">@{{ index + 1 }}</td>
                                    <td>@{{ item.username || '-' }}</td>
                                    <td>@{{ item.nama || '-' }}</td>
                                    <td>@{{ item.jabatan || '-' }}</td>
                                    <td class="text-center">
                                        <span :class="'badge badge-' + getBadgeClass(item.role)">@{{ item.role || '-' }}</span>
                                    </td>
                                    <td class="text-center">
                                        <span :class="'badge badge-' + getBadgeClass(item.status)">@{{ item.status || '-' }}</span>
                                    </td>
                                    <td>@{{ item.pekerjaan || '-' }}</td>
                                    <td class="text-center">@{{ item.tanggal_bergabung || '-' }}</td>
                                </tr>
                            </tbody>
                            <tbody v-else>
                                <tr v-for="(item, index) in rows" :key="item.id">
                                    <td class="text-center">@{{ index + 1 }}</td>
                                    <td>@{{ item.nomor_pekerja || '-' }}</td>
                                    <td>@{{ item.nama || '-' }}</td>
                                    <td>@{{ item.jabatan || '-' }}</td>
                                    <td>@{{ item.nama_periode || '-' }}</td>
                                    <td>@{{ item.nama_penilai || '-' }}</td>
                                    <td class="text-center font-weight-bold">@{{ item.total_nilai || 0 }}</td>
                                    <td class="text-center">
                                        <span :class="'badge badge-' + getBadgeClass(item.klasifikasi)">@{{ item.klasifikasi || '-' }}</span>
                                    </td>
                                    <td class="text-center">
                                        <span :class="'badge badge-' + getBadgeClass(item.klasifikasi_knn)">@{{ item.klasifikasi_knn || '-' }}</span>
                                    </td>
                                    <td class="text-center">
                                        <span :class="'badge badge-' + getDecisionBadgeClass(item)">@{{ getDecisionCategory(item) }}</span>
                                    </td>
                                    <td>@{{ item.tanggal_penilaian || '-' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div v-else class="text-center py-5 text-muted">
                        Data laporan belum tersedia untuk filter yang dipilih.
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
