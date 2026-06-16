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
                    profil: {
                        id: '',
                        nama: '',
                        jabatan: '',
                        pekerjaan: '',
                        tanggal_bergabung: '',
                        status: '',
                        role: '',
                        username: ''
                    },
                    atasanList: [],
                    loading: true,
                    editMode: false,
                    passwordForm: {
                        password_lama: '',
                        password_baru: '',
                        konfirmasi_password: ''
                    }
                };
            },
            mounted() {
                this.loadProfil();
            },
            methods: {
                loadProfil() {
                    this.loading = true;
                    axiosInstance.get('ProfilSaya-Api').then(res => {
                        this.profil = res.data.data?.profil || this.profil;
                        this.atasanList = res.data.data?.atasan || [];
                        this.loading = false;
                    }).catch(err => {
                        this.loading = false;
                        alert('Gagal memuat profil: ' + err.message);
                    });
                },
                toggleEdit() {
                    this.editMode = !this.editMode;
                },
                simpanProfil() {
                    axiosInstance.post('ProfilSaya-Update', this.profil).then(res => {
                        if (res.data.status) {
                            alert('Profil berhasil diperbarui!');
                            this.editMode = false;
                        } else {
                            alert('Gagal memperbarui profil: ' + res.data.message);
                        }
                    }).catch(err => {
                        alert('Terjadi kesalahan: ' + err.message);
                    });
                },
                ubahPassword() {
                    if (this.passwordForm.password_baru !== this.passwordForm.konfirmasi_password) {
                        alert('Konfirmasi password tidak sesuai!');
                        return;
                    }
                    
                    if (this.passwordForm.password_baru.length < 6) {
                        alert('Password baru minimal 6 karakter!');
                        return;
                    }
                    
                    axiosInstance.post('ProfilSaya-UbahPassword', this.passwordForm).then(res => {
                        if (res.data.status) {
                            alert('Password berhasil diubah!');
                            this.passwordForm = {
                                password_lama: '',
                                password_baru: '',
                                konfirmasi_password: ''
                            };
                            $('#modalPassword').modal('hide');
                        } else {
                            alert('Gagal mengubah password: ' + res.data.message);
                        }
                    }).catch(err => {
                        alert('Terjadi kesalahan: ' + err.message);
                    });
                },
                getStatusClass(status) {
                    const mapping = {
                        'aktif': 'success',
                        'nonaktif': 'secondary',
                        'cuti': 'warning',
                        'resign': 'danger'
                    };
                    return mapping[status] || 'secondary';
                },
                getStatusLabel(status) {
                    const mapping = {
                        'aktif': 'Aktif',
                        'nonaktif': 'Non-Aktif',
                        'cuti': 'Cuti',
                        'resign': 'Resign'
                    };
                    return mapping[status] || status;
                },
                getRoleLabel(role) {
                    const mapping = {
                        'admin': 'Admin',
                        'pimpinan': 'Pimpinan',
                        'atasan': 'Atasan',
                        'karyawan': 'Karyawan'
                    };
                    return mapping[role] || role;
                },
                getPekerjaanLabel(pekerjaan) {
                    const mapping = {
                        'tetap': 'Karyawan Tetap',
                        'kontrak': 'Karyawan Kontrak (PKWT)',
                        'magang': 'Magang (Internship)',
                        'outsource': 'Outsource'
                    };
                    return mapping[pekerjaan] || pekerjaan;
                },
                formatTanggal(tanggal) {
                    if (!tanggal) return '-';
                    const date = new Date(tanggal);
                    return date.toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric' });
                },
                hitungMasaKerja(tanggal) {
                    if (!tanggal) return '-';
                    const start = new Date(tanggal);
                    const now = new Date();
                    const diff = now - start;
                    const years = Math.floor(diff / (365.25 * 24 * 60 * 60 * 1000));
                    const months = Math.floor((diff % (365.25 * 24 * 60 * 60 * 1000)) / (30.44 * 24 * 60 * 60 * 1000));
                    
                    if (years > 0) {
                        return years + ' tahun ' + months + ' bulan';
                    }
                    return months + ' bulan';
                }
            }
        }).mount('#app')
    </script>
@endsection
@section('css')
    <style>
        .profil-foto {
            width: 150px;
            height: 150px;
            object-fit: cover;
            border: 5px solid #fff;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }

        .profil-header {
            background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
            padding: 30px;
            color: white;
        }

        .info-item {
            padding: 10px 0;
            border-bottom: 1px solid #e3e6f0;
        }

        .info-item:last-child {
            border-bottom: none;
        }

        .role-badge {
            font-size: 0.9rem;
            padding: 0.5rem 1rem;
        }
    </style>
@endsection
@section('modal')
    {{-- Modal Ubah Password --}}
    <div class="modal fade" id="modalPassword" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-warning text-white">
                    <h5 class="modal-title"><i class="fas fa-key"></i> Ubah Password</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info small">
                        <i class="fas fa-info-circle"></i> Password minimal 6 karakter
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold">Password Lama</label>
                        <input type="password" v-model="passwordForm.password_lama" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold">Password Baru</label>
                        <input type="password" v-model="passwordForm.password_baru" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold">Konfirmasi Password Baru</label>
                        <input type="password" v-model="passwordForm.konfirmasi_password" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="button" @click="ubahPassword" class="btn btn-warning">
                        <i class="fas fa-save"></i> Simpan
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('isi')
    <div class="row" id="app">
        <div class="col-12">
            <div class="card shadow mb-4">
                {{-- Header Profil --}}
                <div class="profil-header text-center">
                    <div class="rounded-circle profil-foto bg-white d-flex align-items-center justify-content-center mx-auto mb-3" 
                        style="font-size: 60px; color: #4e73df;">
                        @{{ profil.nama ? profil.nama.charAt(0).toUpperCase() : 'U' }}
                    </div>
                    <h3 class="mb-1">@{{ profil.nama }}</h3>
                    <p class="mb-2 opacity-75">@{{ profil.jabatan || 'Jabatan belum diatur' }}</p>
                    <span :class="'badge role-badge badge-' + (profil.role === 'admin' ? 'primary' : profil.role === 'pimpinan' ? 'dark' : profil.role === 'atasan' ? 'info' : 'secondary')">
                        <i class="fas fa-user-shield"></i> @{{ getRoleLabel(profil.role) }}
                    </span>
                </div>

                {{-- Detail Profil --}}
                <div class="card-body">
                    <div class="row">
                        {{-- Informasi Akun --}}
                        <div class="col-md-6">
                            <h5 class="font-weight-bold text-primary mb-3">
                                <i class="fas fa-user-circle"></i> Informasi Akun
                            </h5>
                            
                            <div class="info-item">
                                <small class="text-muted">Nama Lengkap</small>
                                <p class="mb-0 font-weight-bold" v-if="!editMode">@{{ profil.nama }}</p>
                                <input v-else type="text" v-model="profil.nama" class="form-control">
                            </div>
                            
                            <div class="info-item">
                                <small class="text-muted">Username</small>
                                <p class="mb-0 font-weight-bold">@{{ profil.username || '-' }}</p>
                            </div>
                            
                            <div class="info-item">
                                <small class="text-muted">Role Akses</small>
                                <p class="mb-0">
                                    <span :class="'badge badge-' + (profil.role === 'admin' ? 'primary' : profil.role === 'pimpinan' ? 'dark' : profil.role === 'atasan' ? 'info' : 'secondary')">
                                        @{{ getRoleLabel(profil.role) }}
                                    </span>
                                </p>
                            </div>
                            
                            <div class="info-item">
                                <small class="text-muted">Status Akun</small>
                                <p class="mb-0">
                                    <span :class="'badge badge-' + getStatusClass(profil.status)">
                                        @{{ getStatusLabel(profil.status) }}
                                    </span>
                                </p>
                            </div>
                        </div>

                        {{-- Informasi Pekerjaan --}}
                        <div class="col-md-6">
                            <h5 class="font-weight-bold text-primary mb-3">
                                <i class="fas fa-briefcase"></i> Informasi Pekerjaan
                            </h5>
                            
                            <div class="info-item">
                                <small class="text-muted">Jabatan</small>
                                <p class="mb-0 font-weight-bold" v-if="!editMode">@{{ profil.jabatan || '-' }}</p>
                                <input v-else type="text" v-model="profil.jabatan" class="form-control">
                            </div>
                            
                            <div class="info-item">
                                <small class="text-muted">Jenis Pekerjaan</small>
                                <p class="mb-0">@{{ getPekerjaanLabel(profil.pekerjaan) }}</p>
                            </div>
                            
                            <div class="info-item">
                                <small class="text-muted">Tanggal Bergabung</small>
                                <p class="mb-0">@{{ formatTanggal(profil.tanggal_bergabung) }}</p>
                            </div>
                            
                            <div class="info-item">
                                <small class="text-muted">Masa Kerja</small>
                                <p class="mb-0 font-weight-bold text-primary">
                                    @{{ hitungMasaKerja(profil.tanggal_bergabung) }}
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- Daftar Atasan --}}
                    <div class="mt-4" v-if="atasanList.length > 0">
                        <h5 class="font-weight-bold text-primary mb-3">
                            <i class="fas fa-sitemap"></i> Atasan Langsung
                        </h5>
                        <div class="row">
                            <div v-for="atasan in atasanList" :key="atasan.id" class="col-md-4 mb-2">
                                <div class="card bg-light">
                                    <div class="card-body py-2">
                                        <div class="d-flex align-items-center">
                                            <div class="rounded-circle bg-info text-white d-flex align-items-center justify-content-center mr-2" 
                                                style="width: 40px; height: 40px;">
                                                @{{ atasan.nama.charAt(0).toUpperCase() }}
                                            </div>
                                            <div>
                                                <strong>@{{ atasan.nama }}</strong><br>
                                                <small class="text-muted">@{{ atasan.jabatan }}</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr>

                    {{-- Action Buttons --}}
                    <div class="d-flex justify-content-between">
                        <div>
                            <button v-if="!editMode" @click="toggleEdit" class="btn btn-primary">
                                <i class="fas fa-edit"></i> Edit Profil
                            </button>
                            <template v-else>
                                <button @click="simpanProfil" class="btn btn-success mr-2">
                                    <i class="fas fa-save"></i> Simpan
                                </button>
                                <button @click="toggleEdit" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Batal
                                </button>
                            </template>
                        </div>
                        <button class="btn btn-warning" data-toggle="modal" data-target="#modalPassword">
                            <i class="fas fa-key"></i> Ubah Password
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
