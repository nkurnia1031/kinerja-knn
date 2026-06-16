@php
    use app\Fungsi;
@endphp
@extends('Layout.html')
@section('js')
    <script>
        const { createApp } = Vue

        app = createApp({
            data() {
                data = dataAwal();
                data.baseURL = 'KlasifikasiKinerja';
                data.penilaian = @json($data['penilaian'] ?? null);
                data.kriteria = @json($data['kriteria'] ?? []);
                data.klasifikasiLabels = @json($data['klasifikasi_labels'] ?? []);
                
                // Calibration form
                data.calibrationForm = {
                    knn_klasifikasi: '',
                    manual_klasifikasi: '',
                    catatan_kalibrasi: '',
                    add_to_training: true
                };
                
                data.isSubmitting = false;
                data.showCalibrationModal = false;
                
                return data;
            },
            computed: {
                ...computedAwal,
                nilaiKriteria() {
                    if (!this.penilaian || !this.penilaian.nilai_kriteria) return [];
                    const nilai = typeof this.penilaian.nilai_kriteria === 'string' 
                        ? JSON.parse(this.penilaian.nilai_kriteria) 
                        : this.penilaian.nilai_kriteria;
                    return Object.values(nilai);
                },
                rataRata() {
                    if (this.nilaiKriteria.length === 0) return 0;
                    const sum = this.nilaiKriteria.reduce((a, b) => a + parseFloat(b || 0), 0);
                    return (sum / this.nilaiKriteria.length).toFixed(2);
                },
                suggestedKlasifikasi() {
                    const rata = parseFloat(this.rataRata);
                    if (rata >= 3.5) return 'Sangat Baik';
                    if (rata >= 2.75) return 'Baik';
                    if (rata >= 2.0) return 'Cukup';
                    return 'Kurang';
                },
                isTraining() {
                    return this.penilaian && this.penilaian.is_training == 1;
                }
            },
            mounted() {
                if (this.penilaian) {
                    this.calibrationForm.knn_klasifikasi = this.penilaian.klasifikasi || '';
                    this.calibrationForm.manual_klasifikasi = this.penilaian.klasifikasi || this.suggestedKlasifikasi;
                }
            },
            methods: {
                ...methodAwal,
                
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
                    nilai = parseFloat(nilai);
                    if (nilai >= 4) return 'success';
                    if (nilai >= 3) return 'primary';
                    if (nilai >= 2) return 'warning';
                    return 'danger';
                },
                
                getNilaiLabel(nilai) {
                    nilai = parseFloat(nilai);
                    if (nilai >= 4) return 'Baik Sekali';
                    if (nilai >= 3) return 'Baik';
                    if (nilai >= 2) return 'Cukup';
                    return 'Kurang Baik';
                },
                
                openCalibrationModal() {
                    this.showCalibrationModal = true;
                    this.$nextTick(() => {
                        $('#calibrationModal').modal('show');
                    });
                },
                
                closeCalibrationModal() {
                    $('#calibrationModal').modal('hide');
                    this.showCalibrationModal = false;
                },
                
                async submitCalibration() {
                    if (!this.calibrationForm.manual_klasifikasi) {
                        new Toast({ message: 'Pilih klasifikasi manual', type: 'danger' });
                        return;
                    }
                    
                    this.isSubmitting = true;
                    
                    try {
                        const response = await axiosInstance.post('KlasifikasiKinerja-Api?action=calibrate', {
                            penilaian_id: this.penilaian.id,
                            knn_klasifikasi: this.calibrationForm.knn_klasifikasi,
                            manual_klasifikasi: this.calibrationForm.manual_klasifikasi,
                            catatan_kalibrasi: this.calibrationForm.catatan_kalibrasi,
                            add_to_training: this.calibrationForm.add_to_training
                        });
                        
                        if (response.data.status) {
                            new Toast({ message: response.data.message, type: 'success' });
                            this.closeCalibrationModal();
                            
                            // Update local data
                            this.penilaian.klasifikasi = this.calibrationForm.manual_klasifikasi;
                            if (this.calibrationForm.add_to_training) {
                                this.penilaian.is_training = 1;
                            }
                        } else {
                            new Toast({ message: response.data.message, type: 'danger' });
                        }
                    } catch (error) {
                        new Toast({ message: 'Gagal menyimpan kalibrasi', type: 'danger' });
                    } finally {
                        this.isSubmitting = false;
                    }
                },
                
                async toggleTraining() {
                    const action = this.isTraining ? 'remove-from-training' : 'add-to-training';
                    const klasifikasi = this.penilaian.klasifikasi || this.suggestedKlasifikasi;
                    
                    try {
                        const response = await axiosInstance.post(`KlasifikasiKinerja-Api?action=${action}`, {
                            penilaian_id: this.penilaian.id,
                            klasifikasi: klasifikasi
                        });
                        
                        if (response.data.status) {
                            new Toast({ message: response.data.message, type: 'success' });
                            this.penilaian.is_training = this.isTraining ? 0 : 1;
                            if (!this.isTraining) {
                                this.penilaian.klasifikasi = klasifikasi;
                            }
                        } else {
                            new Toast({ message: response.data.message, type: 'danger' });
                        }
                    } catch (error) {
                        new Toast({ message: 'Gagal memperbarui status training', type: 'danger' });
                    }
                },
                
                goBack() {
                    window.history.back();
                }
            }
        }).mount('#app')
    </script>
@endsection
@section('css')
    <style>
        .nilai-badge {
            font-size: 1.2rem;
            font-weight: bold;
            min-width: 40px;
            display: inline-block;
            text-align: center;
        }
        .info-row {
            padding: 8px 0;
            border-bottom: 1px solid #e3e6f0;
        }
        .info-row:last-child {
            border-bottom: none;
        }
        .training-badge {
            position: absolute;
            top: 10px;
            right: 10px;
        }
        .calibration-highlight {
            background: linear-gradient(135deg, #f8f9fc 0%, #e9ecef 100%);
            border-left: 4px solid #4e73df;
        }
    </style>
@endsection
@section('modal')
    {{-- Calibration Modal --}}
    <div class="modal fade" id="calibrationModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title">
                        <i class="fas fa-balance-scale mr-2"></i>Kalibrasi Klasifikasi
                    </h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info mb-4">
                        <i class="fas fa-info-circle mr-2"></i>
                        <strong>Kalibrasi</strong> digunakan untuk mengoreksi hasil klasifikasi KNN yang tidak sesuai 
                        dan menambahkan data ke training untuk meningkatkan akurasi analisa selanjutnya.
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card mb-3">
                                <div class="card-header bg-light py-2">
                                    <strong>Klasifikasi Saat Ini (KNN)</strong>
                                </div>
                                <div class="card-body text-center">
                                    <span class="badge badge-lg" 
                                          :class="'badge-' + getKlasifikasiClass(calibrationForm.knn_klasifikasi)"
                                          style="font-size: 1.2rem; padding: 10px 20px;">
                                        @{{ calibrationForm.knn_klasifikasi || 'Belum Diklasifikasi' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card mb-3 calibration-highlight">
                                <div class="card-header bg-light py-2">
                                    <strong>Klasifikasi Manual (Koreksi)</strong>
                                </div>
                                <div class="card-body">
                                    <select class="form-control form-control-lg" v-model="calibrationForm.manual_klasifikasi">
                                        <option value="">-- Pilih Klasifikasi --</option>
                                        <option v-for="label in klasifikasiLabels" :value="label">@{{ label }}</option>
                                    </select>
                                    <small class="text-muted mt-2 d-block">
                                        <i class="fas fa-lightbulb mr-1"></i>
                                        Saran berdasarkan rata-rata: <strong class="text-primary">@{{ suggestedKlasifikasi }}</strong>
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label><strong>Catatan Kalibrasi</strong></label>
                        <textarea class="form-control" v-model="calibrationForm.catatan_kalibrasi" rows="3"
                                  placeholder="Jelaskan alasan perubahan klasifikasi (opsional)..."></textarea>
                    </div>
                    
                    <div class="custom-control custom-switch mt-3">
                        <input type="checkbox" class="custom-control-input" id="addToTraining" 
                               v-model="calibrationForm.add_to_training">
                        <label class="custom-control-label" for="addToTraining">
                            <strong>Tambahkan ke Data Training</strong>
                            <br><small class="text-muted">Data ini akan digunakan untuk melatih model KNN selanjutnya</small>
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-warning" @click="submitCalibration" :disabled="isSubmitting">
                        <i class="fas fa-check mr-1"></i>
                        <span v-if="isSubmitting">Menyimpan...</span>
                        <span v-else>Simpan Kalibrasi</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('isi')
    <div class="row" id="app">
        {{-- Header --}}
        <div class="col-12 mb-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-1">
                        <i class="fas fa-user-tag mr-2"></i>Detail Penilaian & Klasifikasi
                    </h4>
                    <p class="text-muted mb-0">Lihat detail nilai dan kelola klasifikasi kinerja</p>
                </div>
                <div>
                    <button class="btn btn-secondary" @click="goBack">
                        <i class="fas fa-arrow-left mr-1"></i> Kembali
                    </button>
                </div>
            </div>
        </div>
        
        <template v-if="penilaian">
            {{-- Info Karyawan & Klasifikasi --}}
            <div class="col-md-4 mb-4">
                <div class="card shadow h-100 position-relative">
                    <span class="badge training-badge" :class="isTraining ? 'badge-success' : 'badge-secondary'">
                        <i class="fas" :class="isTraining ? 'fa-check' : 'fa-times'"></i>
                        @{{ isTraining ? 'Data Training' : 'Bukan Training' }}
                    </span>
                    <div class="card-header bg-primary text-white py-3">
                        <h6 class="mb-0"><i class="fas fa-user mr-2"></i>Informasi Karyawan</h6>
                    </div>
                    <div class="card-body">
                        <div class="info-row">
                            <small class="text-muted">Nama Karyawan</small>
                            <div class="font-weight-bold">@{{ penilaian.nama }}</div>
                        </div>
                        <div class="info-row">
                            <small class="text-muted">Jabatan</small>
                            <div>@{{ penilaian.jabatan || '-' }}</div>
                        </div>
                        <div class="info-row">
                            <small class="text-muted">Jenis Pekerjaan</small>
                            <div class="text-capitalize">@{{ penilaian.pekerjaan || '-' }}</div>
                        </div>
                        <div class="info-row">
                            <small class="text-muted">Periode Penilaian</small>
                            <div>@{{ penilaian.nama_periode || '-' }}</div>
                        </div>
                        <div class="info-row">
                            <small class="text-muted">Penilai</small>
                            <div>@{{ penilaian.penilai_nama || '-' }}</div>
                        </div>
                        <div class="info-row">
                            <small class="text-muted">Status</small>
                            <div>
                                <span class="badge" :class="{
                                    'badge-success': penilaian.status === 'selesai',
                                    'badge-warning': penilaian.status === 'proses',
                                    'badge-secondary': penilaian.status === 'draft',
                                    'badge-danger': penilaian.status === 'batal'
                                }">@{{ penilaian.status }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            {{-- Hasil Klasifikasi --}}
            <div class="col-md-4 mb-4">
                <div class="card shadow h-100">
                    <div class="card-header bg-info text-white py-3">
                        <h6 class="mb-0"><i class="fas fa-chart-pie mr-2"></i>Hasil Klasifikasi</h6>
                    </div>
                    <div class="card-body text-center">
                        <div class="mb-3">
                            <small class="text-muted d-block mb-2">Rata-rata Nilai</small>
                            <h2 class="text-primary mb-0">@{{ rataRata }}</h2>
                            <small class="text-muted">dari skala 4.00</small>
                        </div>
                        
                        <hr>
                        
                        <div class="mb-3">
                            <small class="text-muted d-block mb-2">Klasifikasi</small>
                            <span class="badge badge-lg" 
                                  :class="'badge-' + getKlasifikasiClass(penilaian.klasifikasi || suggestedKlasifikasi)"
                                  style="font-size: 1.3rem; padding: 12px 24px;">
                                @{{ penilaian.klasifikasi || suggestedKlasifikasi }}
                            </span>
                            <div v-if="!penilaian.klasifikasi" class="mt-2">
                                <small class="text-warning"><i class="fas fa-exclamation-triangle mr-1"></i>Belum dikonfirmasi</small>
                            </div>
                        </div>
                        
                        <hr>
                        
                        <div class="mt-3">
                            <button class="btn btn-warning btn-block mb-2" @click="openCalibrationModal">
                                <i class="fas fa-balance-scale mr-1"></i> Kalibrasi Klasifikasi
                            </button>
                            <button class="btn btn-block" 
                                    :class="isTraining ? 'btn-outline-danger' : 'btn-outline-success'"
                                    @click="toggleTraining">
                                <i class="fas" :class="isTraining ? 'fa-minus-circle' : 'fa-plus-circle'"></i>
                                @{{ isTraining ? 'Hapus dari Training' : 'Tambah ke Training' }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            {{-- Ringkasan Nilai --}}
            <div class="col-md-4 mb-4">
                <div class="card shadow h-100">
                    <div class="card-header bg-success text-white py-3">
                        <h6 class="mb-0"><i class="fas fa-calculator mr-2"></i>Ringkasan Nilai</h6>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-6 mb-3">
                                <div class="border rounded p-3">
                                    <small class="text-muted d-block">Total Kriteria</small>
                                    <h4 class="mb-0 text-primary">@{{ kriteria.length }}</h4>
                                </div>
                            </div>
                            <div class="col-6 mb-3">
                                <div class="border rounded p-3">
                                    <small class="text-muted d-block">Nilai Tertinggi</small>
                                    <h4 class="mb-0 text-success">@{{ Math.max(...nilaiKriteria) || 0 }}</h4>
                                </div>
                            </div>
                            <div class="col-6 mb-3">
                                <div class="border rounded p-3">
                                    <small class="text-muted d-block">Nilai Terendah</small>
                                    <h4 class="mb-0 text-danger">@{{ Math.min(...nilaiKriteria) || 0 }}</h4>
                                </div>
                            </div>
                            <div class="col-6 mb-3">
                                <div class="border rounded p-3">
                                    <small class="text-muted d-block">Saran Sistem</small>
                                    <span class="badge" :class="'badge-' + getKlasifikasiClass(suggestedKlasifikasi)">
                                        @{{ suggestedKlasifikasi }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            {{-- Detail Nilai per Kriteria --}}
            <div class="col-12">
                <div class="card shadow">
                    <div class="card-header bg-white py-3">
                        <h6 class="mb-0"><i class="fas fa-list-ol mr-2"></i>Detail Nilai per Kriteria</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th width="60" class="text-center">No</th>
                                        <th width="80">Kode</th>
                                        <th>Nama Kriteria</th>
                                        <th width="80" class="text-center">Bobot</th>
                                        <th width="80" class="text-center">Nilai</th>
                                        <th width="120" class="text-center">Kategori</th>
                                        <th width="150">Progress</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(k, idx) in kriteria" :key="k.id">
                                        <td class="text-center">@{{ idx + 1 }}</td>
                                        <td><code>@{{ k.kode }}</code></td>
                                        <td>
                                            @{{ k.nama }}
                                            <br><small class="text-muted">@{{ k.nama_en }}</small>
                                        </td>
                                        <td class="text-center">@{{ k.bobot }}%</td>
                                        <td class="text-center">
                                            <span class="nilai-badge badge" :class="'badge-' + getNilaiClass(nilaiKriteria[idx])">
                                                @{{ nilaiKriteria[idx] || '-' }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge" :class="'badge-' + getNilaiClass(nilaiKriteria[idx])">
                                                @{{ getNilaiLabel(nilaiKriteria[idx]) }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="progress" style="height: 20px;">
                                                <div class="progress-bar" 
                                                     :class="'bg-' + getNilaiClass(nilaiKriteria[idx])"
                                                     :style="'width: ' + ((nilaiKriteria[idx] || 0) / 4 * 100) + '%'">
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                                <tfoot class="bg-light font-weight-bold">
                                    <tr>
                                        <td colspan="3" class="text-right">Rata-rata Nilai</td>
                                        <td class="text-center">100%</td>
                                        <td class="text-center">
                                            <span class="nilai-badge badge badge-primary">@{{ rataRata }}</span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge" :class="'badge-' + getKlasifikasiClass(suggestedKlasifikasi)">
                                                @{{ suggestedKlasifikasi }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="progress" style="height: 20px;">
                                                <div class="progress-bar bg-primary" 
                                                     :style="'width: ' + (rataRata / 4 * 100) + '%'">
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            {{-- Catatan --}}
            <div class="col-12 mt-4" v-if="penilaian.catatan">
                <div class="card shadow">
                    <div class="card-header bg-white py-3">
                        <h6 class="mb-0"><i class="fas fa-sticky-note mr-2"></i>Catatan</h6>
                    </div>
                    <div class="card-body">
                        <p class="mb-0" style="white-space: pre-line;">@{{ penilaian.catatan }}</p>
                    </div>
                </div>
            </div>
        </template>
        
        {{-- Empty State --}}
        <template v-else>
            <div class="col-12">
                <div class="card shadow">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-search fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">Data Penilaian Tidak Ditemukan</h5>
                        <p class="text-muted mb-3">Silakan pilih data penilaian dari halaman sebelumnya.</p>
                        <button class="btn btn-primary" @click="goBack">
                            <i class="fas fa-arrow-left mr-1"></i> Kembali
                        </button>
                    </div>
                </div>
            </div>
        </template>
    </div>
@endsection
