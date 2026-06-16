@php
    use app\Fungsi;
@endphp
@extends('Layout.html')
@section('js')
    <script>
        const {
            createApp
        } = Vue

        app = createApp({
            data() {
                return {
                    penilaianId: '{{ $data["penilaian_id"] ?? "" }}',
                    penilaian: @json($data['penilaian'] ?? null),
                    kriteria: @json($data['kriteria'] ?? []),
                    detailNilai: @json($data['detail_nilai'] ?? []),
                    knnHasil: @json($data['knn_hasil'] ?? null),
                    knnDetail: @json($data['knn_detail'] ?? []),
                    loading: false
                };
            },
            computed: {
                totalNilaiBobot() {
                    if (!this.detailNilai || this.detailNilai.length === 0) return 0;
                    return this.detailNilai.reduce((sum, item) => sum + parseFloat(item.nilai_bobot || 0), 0).toFixed(2);
                }
            },
            mounted() {
                // Data already loaded from server
            },
            methods: {
                getKlasifikasiClass(klasifikasi) {
                    const mapping = {
                        'Sangat Baik': 'success',
                        'Baik': 'primary',
                        'Cukup': 'warning',
                        'Kurang': 'danger',
                        'Sangat Kurang': 'dark'
                    };
                    return mapping[klasifikasi] || 'secondary';
                },
                getProgressColor(nilai) {
                    if (nilai >= 90) return 'bg-success';
                    if (nilai >= 80) return 'bg-primary';
                    if (nilai >= 70) return 'bg-warning';
                    return 'bg-danger';
                },
                cetakPenilaian() {
                    window.open('RekapPenilaian-Cetak?id=' + this.penilaianId, '_blank');
                },
                kembali() {
                    window.location.href = 'RekapPenilaian';
                },
                formatDate(dateStr) {
                    if (!dateStr) return '-';
                    const date = new Date(dateStr);
                    return date.toLocaleDateString('id-ID', { 
                        day: '2-digit', 
                        month: 'long', 
                        year: 'numeric' 
                    });
                },
                formatDateTime(dateStr) {
                    if (!dateStr) return '-';
                    const date = new Date(dateStr);
                    return date.toLocaleDateString('id-ID', { 
                        day: '2-digit', 
                        month: 'long', 
                        year: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit'
                    });
                }
            }
        }).mount('#app')
    </script>
@endsection
@section('css')
    <style>
        .info-card {
            transition: all 0.3s ease;
            border-left: 4px solid transparent;
        }
        .info-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
        }
        .info-card.primary { border-left-color: #4e73df; }
        .info-card.success { border-left-color: #1cc88a; }
        .info-card.warning { border-left-color: #f6c23e; }
        .info-card.info { border-left-color: #36b9cc; }
        .info-card.danger { border-left-color: #e74a3b; }
        
        .kriteria-row {
            transition: background-color 0.2s ease;
        }
        .kriteria-row:hover {
            background-color: rgba(78, 115, 223, 0.05);
        }
        
        .progress-slim {
            height: 8px;
            border-radius: 4px;
        }
        
        .nilai-badge {
            font-size: 2rem;
            font-weight: bold;
        }
        
        .knn-neighbor-card {
            border-left: 3px solid #36b9cc;
            transition: all 0.2s ease;
        }
        .knn-neighbor-card:hover {
            border-left-color: #4e73df;
            background-color: #f8f9fc;
        }
        
        .table-detail th {
            background-color: #f8f9fc;
            font-weight: 600;
            color: #5a5c69;
        }
        .table-detail td, .table-detail th {
            vertical-align: middle;
            padding: 12px 15px;
        }
    </style>
@endsection
@section('modal')
@endsection
@section('isi')
    <div class="row" id="app">
        {{-- Header --}}
        <div class="col-12 mb-3">
            <div class="card bg-gradient-primary text-white shadow">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h4 class="mb-1">
                                <i class="fas fa-file-alt mr-2"></i>
                                Detail Penilaian Kinerja
                            </h4>
                            <p class="mb-0 opacity-75" v-if="penilaian">
                                @{{ penilaian.nama_karyawan }} - @{{ penilaian.nama_periode }}
                            </p>
                        </div>
                        <div class="col-md-4 text-md-right mt-3 mt-md-0">
                            <button @click="kembali" class="btn btn-light btn-sm mr-2">
                                <i class="fas fa-arrow-left"></i> Kembali
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <template v-if="penilaian">
            {{-- Info Karyawan --}}
            <div class="col-lg-6 mb-3">
                <div class="card shadow h-100">
                    <div class="card-header py-3 bg-light">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-user"></i> Informasi Karyawan
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="text-muted small mb-1">Nama Karyawan</label>
                                <p class="font-weight-bold mb-0">@{{ penilaian.nama_karyawan }}</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="text-muted small mb-1">NIP / ID</label>
                                <p class="font-weight-bold mb-0">@{{ penilaian.karyawan_id || '-' }}</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="text-muted small mb-1">Jabatan</label>
                                <p class="font-weight-bold mb-0">@{{ penilaian.jabatan_karyawan || '-' }}</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="text-muted small mb-1">Jenis Pekerjaan</label>
                                <p class="mb-0">
                                    <span class="badge badge-secondary text-capitalize">
                                        @{{ penilaian.pekerjaan || '-' }}
                                    </span>
                                </p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="text-muted small mb-1">Status</label>
                                <p class="mb-0">
                                    <span :class="'badge badge-' + (penilaian.status_karyawan === 'aktif' ? 'success' : 'secondary')">
                                        @{{ penilaian.status_karyawan || '-' }}
                                    </span>
                                </p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="text-muted small mb-1">Tanggal Bergabung</label>
                                <p class="font-weight-bold mb-0">@{{ formatDate(penilaian.tanggal_bergabung) }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Info Penilaian --}}
            <div class="col-lg-6 mb-3">
                <div class="card shadow h-100">
                    <div class="card-header py-3 bg-light">
                        <h6 class="m-0 font-weight-bold text-info">
                            <i class="fas fa-clipboard-check"></i> Informasi Penilaian
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="text-muted small mb-1">Periode Penilaian</label>
                                <p class="font-weight-bold mb-0 text-primary">@{{ penilaian.nama_periode }}</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="text-muted small mb-1">Tanggal Penilaian</label>
                                <p class="font-weight-bold mb-0">@{{ formatDateTime(penilaian.updated_at) }}</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="text-muted small mb-1">Penilai (Atasan)</label>
                                <p class="font-weight-bold mb-0">@{{ penilaian.nama_penilai || '-' }}</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="text-muted small mb-1">Status Penilaian</label>
                                <p class="mb-0">
                                    <span :class="'badge badge-' + (penilaian.status === 'selesai' ? 'success' : penilaian.status === 'proses' ? 'warning' : 'secondary')">
                                        @{{ penilaian.status }}
                                    </span>
                                </p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="text-muted small mb-1">Data Training</label>
                                <p class="mb-0">
                                    <span :class="'badge badge-' + (penilaian.is_training == 1 ? 'info' : 'light')">
                                        @{{ penilaian.is_training == 1 ? 'Ya' : 'Tidak' }}
                                    </span>
                                </p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="text-muted small mb-1">ID Penilaian</label>
                                <p class="font-weight-bold mb-0">#@{{ penilaian.id }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Ringkasan Nilai --}}
            <div class="col-12 mb-3">
                <div class="card shadow">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-4 text-center border-right">
                                <h6 class="text-muted mb-2">Total Nilai</h6>
                                <span class="nilai-badge" :class="'text-' + getKlasifikasiClass(penilaian.klasifikasi)">
                                    @{{ penilaian.total_nilai || totalNilaiBobot }}
                                </span>
                            </div>
                            <div class="col-md-4 text-center border-right">
                                <h6 class="text-muted mb-2">Klasifikasi Penilaian</h6>
                                <span :class="'badge badge-lg badge-' + getKlasifikasiClass(penilaian.klasifikasi)" 
                                    style="font-size: 1.2rem; padding: 10px 20px;">
                                    @{{ penilaian.klasifikasi || '-' }}
                                </span>
                            </div>
                            <div class="col-md-4 text-center">
                                <h6 class="text-muted mb-2">Jumlah Kriteria</h6>
                                <span class="nilai-badge text-info">
                                    @{{ detailNilai.length }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 mb-3">
                <div class="card shadow">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-6 text-center border-right">
                                <h6 class="text-muted mb-2">Klasifikasi Penilaian</h6>
                                <span :class="'badge badge-lg badge-' + getKlasifikasiClass(penilaian.klasifikasi)" style="font-size: 1.1rem; padding: 8px 16px;">
                                    @{{ penilaian.klasifikasi || '-' }}
                                </span>
                            </div>
                            <div class="col-md-6 text-center">
                                <h6 class="text-muted mb-2">Klasifikasi KNN Tersimpan</h6>
                                <span :class="'badge badge-lg badge-' + getKlasifikasiClass(penilaian.klasifikasi_knn)" style="font-size: 1.1rem; padding: 8px 16px;">
                                    @{{ penilaian.klasifikasi_knn || '-' }}
                                </span>
                                <div v-if="penilaian.knn_confidence" class="mt-2 text-muted small">
                                    Confidence: @{{ penilaian.knn_confidence }}%
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Detail Nilai Per Kriteria --}}
            <div class="col-12 mb-3">
                <div class="card shadow">
                    <div class="card-header py-3 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-list-ol"></i> Detail Nilai Per Kriteria
                        </h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-detail table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th class="text-center" style="width: 60px;">No</th>
                                        <th style="width: 80px;">Kode</th>
                                        <th>Nama Kriteria</th>
                                        <th class="text-center" style="width: 100px;">Bobot (%)</th>
                                        <th class="text-center" style="width: 100px;">Nilai</th>
                                        <th class="text-center" style="width: 120px;">Nilai x Bobot</th>
                                        <th style="width: 200px;">Progress</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(item, index) in detailNilai" :key="index" class="kriteria-row">
                                        <td class="text-center">@{{ index + 1 }}</td>
                                        <td>
                                            <span class="badge badge-light">@{{ item.kode }}</span>
                                        </td>
                                        <td>
                                            <strong>@{{ item.nama }}</strong>
                                            <br v-if="item.nama_en">
                                            <small class="text-muted">@{{ item.nama_en }}</small>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge badge-info">@{{ item.bobot }}%</span>
                                        </td>
                                        <td class="text-center">
                                            <strong :class="'text-' + getKlasifikasiClass(item.nilai >= 90 ? 'Sangat Baik' : item.nilai >= 80 ? 'Baik' : item.nilai >= 70 ? 'Cukup' : 'Kurang')">
                                                @{{ item.nilai }}
                                            </strong>
                                        </td>
                                        <td class="text-center font-weight-bold">
                                            @{{ item.nilai_bobot }}
                                        </td>
                                        <td>
                                            <div class="progress progress-slim">
                                                <div class="progress-bar" 
                                                    :class="getProgressColor(item.nilai)"
                                                    :style="'width: ' + item.nilai + '%'">
                                                </div>
                                            </div>
                                            <small class="text-muted">@{{ item.nilai }}%</small>
                                        </td>
                                    </tr>
                                </tbody>
                                <tfoot>
                                    <tr class="bg-light font-weight-bold">
                                        <td colspan="3" class="text-right">Total</td>
                                        <td class="text-center">100%</td>
                                        <td class="text-center">-</td>
                                        <td class="text-center text-primary">@{{ penilaian.total_nilai || totalNilaiBobot }}</td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            {{-- KNN Classification Result (if available) --}}
            <div v-if="knnHasil" class="col-12 mb-3">
                <div class="card shadow border-left-info">
                    <div class="card-header py-3 bg-info text-white">
                        <h6 class="m-0 font-weight-bold">
                            <i class="fas fa-brain"></i> Hasil Klasifikasi KNN
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 text-center mb-3">
                                <h6 class="text-muted">Hasil Klasifikasi</h6>
                                <span :class="'badge badge-lg badge-' + getKlasifikasiClass(knnHasil.hasil_klasifikasi)" 
                                    style="font-size: 1.1rem; padding: 8px 16px;">
                                    @{{ knnHasil.hasil_klasifikasi }}
                                </span>
                            </div>
                            <div class="col-md-4 text-center mb-3">
                                <h6 class="text-muted">Confidence</h6>
                                <div class="progress mb-1" style="height: 25px;">
                                    <div class="progress-bar bg-success" 
                                        :style="'width: ' + knnHasil.confidence + '%'">
                                        @{{ knnHasil.confidence }}%
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 text-center mb-3">
                                <h6 class="text-muted">Rata-rata Nilai</h6>
                                <span class="font-weight-bold h4">@{{ knnHasil.rata_rata_nilai }}</span>
                            </div>
                        </div>

                        {{-- K-Nearest Neighbors --}}
                        <div v-if="knnDetail && knnDetail.length > 0" class="mt-4">
                            <h6 class="font-weight-bold mb-3">
                                <i class="fas fa-users"></i> K-Tetangga Terdekat
                            </h6>
                            <div class="row">
                                <div v-for="(neighbor, idx) in knnDetail" :key="idx" class="col-md-4 mb-2">
                                    <div class="card knn-neighbor-card">
                                        <div class="card-body py-2 px-3">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <strong class="text-dark">@{{ neighbor.nama_training }}</strong>
                                                    <br>
                                                    <small :class="'badge badge-' + getKlasifikasiClass(neighbor.klasifikasi_training)">
                                                        @{{ neighbor.klasifikasi_training }}
                                                    </small>
                                                </div>
                                                <div class="text-right">
                                                    <span class="badge badge-light">Rank #@{{ neighbor.ranking }}</span>
                                                    <br>
                                                    <small class="text-muted">Jarak: @{{ parseFloat(neighbor.nilai_jarak).toFixed(4) }}</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Catatan --}}
            <div v-if="penilaian.catatan" class="col-12 mb-3">
                <div class="card shadow">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-warning">
                            <i class="fas fa-sticky-note"></i> Catatan dari Penilai
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-light border mb-0">
                            <i class="fas fa-quote-left text-muted mr-2"></i>
                            @{{ penilaian.catatan }}
                            <i class="fas fa-quote-right text-muted ml-2"></i>
                        </div>
                    </div>
                </div>
            </div>
        </template>

        {{-- Empty State --}}
        <div v-else class="col-12">
            <div class="card shadow">
                <div class="card-body text-center py-5">
                    <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
                    <h5 class="text-muted">Data Penilaian Tidak Ditemukan</h5>
                    <p class="text-muted">Penilaian dengan ID yang diminta tidak tersedia.</p>
                    <button @click="kembali" class="btn btn-primary">
                        <i class="fas fa-arrow-left"></i> Kembali ke Rekap
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection
