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
                data = dataAwal();
                data.baseURL = 'RelasiAtasan';
                data.karyawanList = [];
                data.atasanList = [];
                data.selectedKaryawan = null;
                data.selectedAtasan = null;
                data.filterKaryawan = '';
                data.requestedKaryawanId = @json($data['selected_karyawan_id'] ?? null);
                return data;
            },
            computed: {
                ...computedAwal,
                filteredKaryawan() {
                    if (!this.filterKaryawan) return this.karyawanList;
                    const filter = this.filterKaryawan.toLowerCase();
                    return this.karyawanList.filter(k => 
                        k.nama.toLowerCase().includes(filter) || 
                        (k.jabatan && k.jabatan.toLowerCase().includes(filter))
                    );
                },
                availableAtasan() {
                    if (!this.selectedKaryawan) return this.atasanList;
                    // Exclude self and existing relations
                    const existingAtasanIds = this.data2
                        .filter(r => r.id_karyawan === this.selectedKaryawan.id)
                        .map(r => r.id_atasan);
                    return this.atasanList.filter(a => 
                        a.id !== this.selectedKaryawan.id && 
                        !existingAtasanIds.includes(a.id)
                    );
                }
            },
            mounted() {
                this.GetData();
                this.loadKaryawanList();
                this.loadAtasanList();
            },
            methods: {
                ...methodAwal,
                canManage() {
                    return '{{ $Session["admin"]->role ?? "" }}' === 'admin';
                },
                applyRequestedKaryawan() {
                    if (!this.requestedKaryawanId || !Array.isArray(this.karyawanList) || this.karyawanList.length === 0) {
                        return;
                    }

                    const karyawan = this.karyawanList.find(k => k.id == this.requestedKaryawanId);
                    if (karyawan) {
                        this.pilihKaryawan(karyawan);
                    }
                },
                loadKaryawanList() {
                    this.apiGet('Karyawan', {
                        status: 'aktif'
                    }).then(res => {
                        this.karyawanList = res.data.data.data || [];
                        this.applyRequestedKaryawan();
                    });
                },
                loadAtasanList() {
                    this.apiGet('Karyawan', {
                        'role[]': ['atasan', 'admin', 'pimpinan'],
                        status: 'aktif'
                    }).then(res => {
                        this.atasanList = res.data.data.data || [];
                    });
                },
                syncSelectedKaryawanToUrl(karyawanId = null) {
                    const url = new URL(window.location.href);

                    if (karyawanId) {
                        url.searchParams.set('karyawan_id', karyawanId);
                    } else {
                        url.searchParams.delete('karyawan_id');
                    }

                    window.history.replaceState({}, '', url.toString());
                },
                fokusDetailKaryawan() {
                    this.$nextTick(() => {
                        const detailPanel = this.$refs.detailPanel;
                        if (detailPanel && window.innerWidth < 992) {
                            detailPanel.scrollIntoView({
                                behavior: 'smooth',
                                block: 'start'
                            });
                        }
                    });
                },
                pilihKaryawan(karyawan) {
                    this.selectedKaryawan = karyawan;
                    this.selectedAtasan = null;
                    this.requestedKaryawanId = karyawan.id;
                    this.syncSelectedKaryawanToUrl(karyawan.id);
                    this.fokusDetailKaryawan();
                },
                batalPilih() {
                    this.selectedKaryawan = null;
                    this.selectedAtasan = null;
                    this.requestedKaryawanId = null;
                    this.syncSelectedKaryawanToUrl();
                },
                simpanRelasi() {
                    if (!this.selectedKaryawan || !this.selectedAtasan) {
                        new Toast({
                            message: 'Pilih karyawan dan atasan terlebih dahulu',
                            type: 'danger'
                        });
                        return;
                    }

                    this.proses = true;

                    const formData = new FormData();
                    formData.append('input[id_karyawan]', this.selectedKaryawan.id);
                    formData.append('input[id_atasan]', this.selectedAtasan);

                    axiosInstance({
                        method: 'post',
                        url: this.buildCrudUrl(),
                        data: formData
                    }).then((response) => {
                        const data = response.data;
                        this.proses = false;

                        if (data.status) {
                            this.selectedAtasan = null;
                            this.GetData();
                            new Toast({
                                message: data.data.msg,
                                type: 'success'
                            });
                        } else {
                            new Toast({
                                message: data.data.msg,
                                type: 'danger'
                            });
                        }
                    }).catch((error) => {
                        this.proses = false;
                        new Toast({
                            message: error,
                            type: 'danger'
                        });
                    });
                },
                getRelasiKaryawan(karyawanId) {
                    return this.data2.filter(r => r.id_karyawan === karyawanId);
                },
                getRelasiIndex(relasiId) {
                    return this.data2.findIndex(r => r.id === relasiId);
                },
                getRoleClass(role) {
                    const mapping = {
                        'admin': 'primary',
                        'pimpinan': 'dark',
                        'atasan': 'info',
                        'karyawan': 'secondary'
                    };
                    return mapping[role] || 'secondary';
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
                afterGetdata() {
                    this.fields = collect([
                        { name: 'nama_karyawan', label: 'Bawahan' },
                        { name: 'jabatan_karyawan', label: 'Jabatan Bawahan' },
                        { name: 'nama_atasan', label: 'Atasan' },
                        { name: 'jabatan_atasan', label: 'Jabatan Atasan' }
                    ]);
                }
            }
        }).mount('#app')
    </script>
@endsection
@section('css')
    <style>
        .karyawan-item {
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .karyawan-item:hover {
            background-color: #f8f9fc;
            transform: translateX(4px);
        }

        .karyawan-item.active {
            background-color: #4e73df;
            color: white;
            transform: translateX(8px) scale(1.01);
            box-shadow: inset 4px 0 0 #224abe, 0 10px 20px rgba(78, 115, 223, 0.2);
        }

        .karyawan-item.active small {
            color: rgba(255,255,255,0.7) !important;
        }

        .relasi-card {
            border-left: 4px solid #4e73df;
        }

        .atasan-badge {
            font-size: 0.85rem;
        }

        .detail-panel-active {
            animation: panelPulse 0.35s ease;
        }

        @keyframes panelPulse {
            0% {
                transform: translateY(10px);
                opacity: 0.7;
            }
            100% {
                transform: translateY(0);
                opacity: 1;
            }
        }
    </style>
@endsection
@section('modal')
@endsection
@section('isi')
    <div class="row" id="app">
        {{-- Info Card --}}
        <div class="col-12 mb-3">
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> 
                <strong>Relasi Atasan-Bawahan</strong> - Kelola hubungan hierarki antara atasan dan bawahan.
                Atasan dapat menilai kinerja karyawan yang menjadi bawahannya.
            </div>
        </div>

        {{-- Statistik --}}
        <div class="col-lg-4 col-md-6 mb-3">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Karyawan</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">@{{ karyawanList.length }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-md-6 mb-3">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Total Atasan</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">@{{ atasanList.length }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-tie fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-md-6 mb-3">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Total Relasi</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">@{{ data2.length }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-sitemap fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Daftar Karyawan --}}
        <div class="col-lg-5 mb-4">
            <div class="card shadow h-100">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-users"></i> Daftar Karyawan
                    </h6>
                </div>
                <div class="card-body p-0">
                    <div class="p-3">
                        <input type="text" v-model="filterKaryawan" class="form-control" 
                            placeholder="Cari karyawan...">
                    </div>
                    <div style="max-height: 500px; overflow-y: auto;">
                        <div v-for="karyawan in filteredKaryawan" :key="karyawan.id" 
                            class="karyawan-item p-3 border-bottom" 
                            :class="{'active': selectedKaryawan && selectedKaryawan.id === karyawan.id}"
                            @click="pilihKaryawan(karyawan)">
                            <div class="d-flex align-items-center">
                                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center mr-3" 
                                    style="width: 40px; height: 40px; min-width: 40px;"
                                    :class="{'bg-white text-primary': selectedKaryawan && selectedKaryawan.id === karyawan.id}">
                                    @{{ karyawan.nama.charAt(0).toUpperCase() }}
                                </div>
                                <div class="flex-grow-1">
                                    <strong>@{{ karyawan.nama }}</strong><br>
                                    <small class="text-muted">@{{ karyawan.jabatan || 'Jabatan belum diatur' }}</small>
                                </div>
                                <div>
                                    <span :class="'badge badge-' + getRoleClass(karyawan.role)" class="atasan-badge">
                                        @{{ getRoleLabel(karyawan.role) }}
                                    </span>
                                    <br>
                                    <small class="text-muted">
                                        @{{ getRelasiKaryawan(karyawan.id).length }} atasan
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Detail Relasi --}}
        <div class="col-lg-7 mb-4" ref="detailPanel">
            <div class="card shadow h-100">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-sitemap"></i> 
                        <span v-if="selectedKaryawan">Relasi Atasan: @{{ selectedKaryawan.nama }}</span>
                        <span v-else>Pilih Karyawan</span>
                    </h6>
                    <button v-if="selectedKaryawan" @click="batalPilih" class="btn btn-sm btn-secondary">
                        <i class="fas fa-times"></i> Batal
                    </button>
                </div>
                <div class="card-body">
                    {{-- Jika karyawan dipilih --}}
                    <template v-if="selectedKaryawan">
                        {{-- Info Karyawan --}}
                        <div class="card relasi-card mb-4 detail-panel-active">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center mr-3" 
                                        style="width: 60px; height: 60px; font-size: 24px;">
                                        @{{ selectedKaryawan.nama.charAt(0).toUpperCase() }}
                                    </div>
                                    <div>
                                        <h5 class="mb-0">@{{ selectedKaryawan.nama }}</h5>
                                        <p class="text-muted mb-0">@{{ selectedKaryawan.jabatan || 'Jabatan belum diatur' }}</p>
                                        <span :class="'badge badge-' + getRoleClass(selectedKaryawan.role)">
                                            @{{ getRoleLabel(selectedKaryawan.role) }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Form Tambah Atasan --}}
                        <div v-if="canManage()" class="card bg-light mb-4">
                            <div class="card-body">
                                <h6 class="font-weight-bold mb-3">
                                    <i class="fas fa-plus-circle"></i> Tambah Atasan
                                </h6>
                                <div class="row">
                                    <div class="col-md-8 mb-2 mb-md-0">
                                        <select v-model="selectedAtasan" class="form-control">
                                            <option value="">-- Pilih Atasan --</option>
                                            <option v-for="atasan in availableAtasan" :key="atasan.id" :value="atasan.id">
                                                @{{ atasan.nama }} - @{{ atasan.jabatan || 'N/A' }} (@{{ getRoleLabel(atasan.role) }})
                                            </option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <button @click="simpanRelasi" class="btn btn-primary btn-block" :disabled="!selectedAtasan || proses">
                                            <i class="fas fa-plus"></i> Tambah
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Daftar Atasan --}}
                        <h6 class="font-weight-bold mb-3">
                            <i class="fas fa-user-tie"></i> Daftar Atasan (@{{ getRelasiKaryawan(selectedKaryawan.id).length }})
                        </h6>
                        
                        <div v-if="getRelasiKaryawan(selectedKaryawan.id).length > 0">
                            <div v-for="relasi in getRelasiKaryawan(selectedKaryawan.id)" :key="relasi.id" 
                                class="card mb-2">
                                <div class="card-body py-2">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div class="d-flex align-items-center">
                                            <div class="rounded-circle bg-info text-white d-flex align-items-center justify-content-center mr-3" 
                                                style="width: 40px; height: 40px;">
                                                @{{ relasi.nama_atasan.charAt(0).toUpperCase() }}
                                            </div>
                                            <div>
                                                <strong>@{{ relasi.nama_atasan }}</strong><br>
                                                <small class="text-muted">@{{ relasi.jabatan_atasan || 'N/A' }}</small>
                                            </div>
                                        </div>
                                        <button v-if="canManage()" @click="HapusData(getRelasiIndex(relasi.id))" class="btn btn-sm btn-danger">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div v-else class="text-center py-4 text-muted">
                            <i class="fas fa-user-slash fa-2x mb-2"></i>
                            <p>Belum ada atasan yang ditugaskan</p>
                        </div>
                    </template>

                    {{-- Jika belum pilih karyawan --}}
                    <template v-else>
                        <div class="text-center py-5">
                            <i class="fas fa-hand-pointer fa-4x text-muted mb-3"></i>
                            <h5 class="text-muted">Pilih karyawan dari daftar di sebelah kiri</h5>
                            <p class="text-muted">untuk melihat dan mengatur relasi atasannya</p>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        @component('Pages.Table', [
            'title' => 'Semua Relasi Atasan-Bawahan',
            'rowSource' => 'data2',
            'showAdd' => false,
            'showPrint' => false,
            'showExcel' => false,
            'showRefresh' => false,
            'showAksiExpr' => 'canManage()',
            'customHeaderAksi' => '
                <button @click="GetData()" class="btn btn-sm btn-secondary">
                    <i class="fas fa-sync"></i> Refresh
                </button>
            ',
            'customAksi' => '
                <button @click="HapusData(index)" class="btn btn-sm btn-danger">
                    <i class="fas fa-trash"></i>
                </button>
            '
        ])
            @slot('td')
                <template v-else-if="inArray(a.name,['nama_karyawan'])">
                    <strong>@{{ i.nama_karyawan }}</strong>
                </template>
                <template v-else-if="inArray(a.name,['jabatan_karyawan','jabatan_atasan'])">
                    <span>@{{ i[a.name] || '-' }}</span>
                </template>
                <template v-else-if="inArray(a.name,['nama_atasan'])">
                    <span>@{{ i.nama_atasan }}</span>
                </template>
            @endslot
        @endcomponent
    </div>
@endsection
