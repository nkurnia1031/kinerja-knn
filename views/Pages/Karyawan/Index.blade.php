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
                data.baseURL = 'Karyawan';
                data.atasanList = [];
                return data;
            },
            computed: computedAwal,
            mounted() {
                this.GetData();
                this.loadAtasanList();
            },
            methods: {
                ...methodAwal,
                canManage() {
                    return '{{ $Session["admin"]->role ?? "" }}' === 'admin';
                },
                loadAtasanList() {
                    this.apiGet('Karyawan', {
                        role: 'atasan,admin,pimpinan'
                    }).then(res => {
                        this.atasanList = res.data.data.data || [];
                    });
                },
                formatTanggal(tanggal) {
                    if (!tanggal) return '-';
                    const date = new Date(tanggal);
                    return date.toLocaleDateString('id-ID', {
                        day: '2-digit',
                        month: 'short',
                        year: 'numeric'
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
                getPekerjaanClass(pekerjaan) {
                    const mapping = {
                        'tetap': 'success',
                        'kontrak': 'warning',
                        'magang': 'info',
                        'outsource': 'secondary'
                    };
                    return mapping[pekerjaan] || 'secondary';
                },
                getPekerjaanLabel(pekerjaan) {
                    const mapping = {
                        'tetap': 'Tetap',
                        'kontrak': 'Kontrak',
                        'magang': 'Magang',
                        'outsource': 'Outsource'
                    };
                    return mapping[pekerjaan] || pekerjaan;
                },
                lihatRelasi(karyawanId) {
                    window.location.href = 'RelasiAtasan?karyawan_id=' + karyawanId;
                },
                afterGetdata() {
                    this.fields = collect([
                        { name: 'nama', label: 'Nama' },
                        { name: 'jabatan', label: 'Jabatan' },
                        { name: 'pekerjaan', label: 'Jenis Pekerjaan' },
                        { name: 'tanggal_bergabung', label: 'Tgl Bergabung' },
                        { name: 'status', label: 'Status' },
                        { name: 'role', label: 'Role' },
                        { name: 'username', label: 'Username' },
                        { name: 'password', label: 'Password' }
                    ]);
                }
            }
        }).mount('#app')
    </script>
@endsection
@section('css')
    <style>
        .slide-fade-enter-active {
            transition: all 0.3s ease-out;
        }

        .slide-fade-leave-active {
            transition: all 0.8s cubic-bezier(1, 0.5, 0.8, 1);
        }

        .slide-fade-enter-from,
        .slide-fade-leave-to {
            transform: translateX(20px);
            opacity: 0;
        }

        .table-bordered td,
        .table-bordered th {
            color: black !important;
            border: 1px solid #000000 !important;
            padding-top: 5px !important;
            padding-bottom: 5px !important;
            padding-left: 8px !important;
            padding-right: 8px !important;
        }
    </style>
@endsection
@section('modal')
    {{-- Modal Atur Atasan --}}
    <div class="modal fade" id="modalAtasan" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title"><i class="fas fa-sitemap"></i> Atur Relasi Atasan</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="font-weight-bold">Pilih Atasan</label>
                        <select class="form-control" id="selectAtasan">
                            <option value="">-- Pilih Atasan --</option>
                            <option v-for="atasan in atasanList" :key="atasan.id" :value="atasan.id">
                                @{{ atasan.nama }} - @{{ atasan.jabatan }}
                            </option>
                        </select>
                        <small class="text-muted">
                            Atasan akan dapat menilai kinerja karyawan ini
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-info">
                        <i class="fas fa-save"></i> Simpan
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('isi')
    <div class="row" id="app">
        <div class="col-12 mb-3">
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i>
                <strong>Data Karyawan</strong> - Kelola data karyawan yang akan dinilai kinerjanya.
                Pastikan data jabatan, pekerjaan, status, dan role sudah diisi dengan benar.
            </div>
        </div>

        @component('Pages.Form')
            @slot('tambahan')
                {{-- Field: Pekerjaan --}}
                {{-- <template v-else-if="inArray(data.name,['pekerjaan'])">
                    <select class="form-control" :name="'input[' + data.name + ']'" :id="data.name + '1'">
                        <option value="">-- Pilih Jenis Pekerjaan --</option>
                        <option value="tetap" :selected="data.val == 'tetap'">Karyawan Tetap</option>
                        <option value="kontrak" :selected="data.val == 'kontrak'">Karyawan Kontrak (PKWT)</option>
                        <option value="magang" :selected="data.val == 'magang'">Magang (Internship)</option>
                        <option value="outsource" :selected="data.val == 'outsource'">Outsource (Pihak Ketiga)</option>
                    </select>
                </template> --}}
                {{-- Field: Status --}}
                <template v-else-if="inArray(data.name,['status'])">
                    <select class="form-control" :name="'input[' + data.name + ']'" :id="data.name + '1'">
                        <option value="">-- Pilih Status --</option>
                        <option value="aktif" :selected="data.val == 'aktif'">Aktif</option>
                        <option value="nonaktif" :selected="data.val == 'nonaktif'">Non-Aktif</option>
                        <option value="cuti" :selected="data.val == 'cuti'">Cuti</option>
                        <option value="resign" :selected="data.val == 'resign'">Resign</option>
                    </select>
                </template>
                {{-- Field: Role --}}
                <template v-else-if="inArray(data.name,['role'])">
                    <select class="form-control" :name="'input[' + data.name + ']'" :id="data.name + '1'">
                        <option value="">-- Pilih Role --</option>
                        <option value="admin" :selected="data.val == 'admin'">Admin </option>
                        <option value="pimpinan" :selected="data.val == 'pimpinan'">Pimpinan</option>
                        <option value="atasan" :selected="data.val == 'atasan'">Atasan </option>
                        <option value="karyawan" :selected="data.val == 'karyawan'">Karyawan</option>
                    </select>
                </template>
                {{-- Field: Tanggal Bergabung --}}
                <template v-else-if="inArray(data.name,['tanggal_bergabung'])">
                    <input type="date" class="form-control" :name="'input[' + data.name + ']'"
                        :id="data.name + '1'" :value="data.val">
                </template>
                {{-- Field: Password --}}
                <template v-else-if="inArray(data.name,['password'])">
                    <input type="password" class="form-control" :name="'input[' + data.name + ']'"
                        :id="data.name + '1'" :value="data.val" placeholder="Kosongkan jika tidak ingin mengubah password">
                    <small class="text-muted">Password akan dienkripsi secara otomatis</small>
                </template>
            @endslot
        @endcomponent
        @component('Pages.Filter')
        @endcomponent
        @component('Pages.Table', [
            'title' => 'Data Karyawan',
            'rowSource' => 'data2',
            'showAdd' => false,
            'showPrint' => false,
            'showExcel' => false,
            'showRefresh' => false,
            'showAksiExpr' => 'canManage()',
            'showUsernameMenu' => false,
            'tambahanth' => '<th>Atasan</th>',
            'tambahantd' => '
                <td>
                    <span v-if="i.nama_atasan">@{{ i.nama_atasan }}</span>
                    <span v-else class="text-muted">-</span>
                </td>
            ',
            'customHeaderAksi' => '
                <a v-if="canManage()" @click="setAwal" class="btn mr-1 mb-1 btn-sm btn-primary">
                    <i class="fa fa-plus"></i> Tambah
                </a>
                <a href="javascript:;" @click="GetData()" class="btn mr-1 mb-1 btn-sm btn-secondary">
                    <i class="fa fa-sync"></i> Refresh
                </a>
            ',
            'customAksi' => '
                <div class="d-none d-sm-block text-center">
                    <button @click="EditData(index)" class="shadow btn btn-sm btn-warning mx-1 my-1" title="Edit">
                        <i class="fas fa-edit"></i>
                    </button>
                   
                    <button @click="HapusData(index)" class="shadow btn btn-sm btn-danger mx-1 my-1" title="Hapus">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            '
        ])
            @slot('td')
                <template v-else-if="inArray(a.name,['nama'])">
                    <strong>@{{ i.nama }}</strong>
                </template>
                <template v-else-if="inArray(a.name,['pekerjaan'])">
                    <span :class="'badge badge-' + getPekerjaanClass(i.pekerjaan)">
                        @{{ getPekerjaanLabel(i.pekerjaan) }}
                    </span>
                </template>
                <template v-else-if="inArray(a.name,['tanggal_bergabung'])">
                    <span class="text-nowrap">@{{ formatTanggal(i.tanggal_bergabung) }}</span>
                </template>
                <template v-else-if="inArray(a.name,['status'])">
                    <span :class="'badge badge-' + getStatusClass(i.status)">
                        @{{ getStatusLabel(i.status) }}
                    </span>
                </template>
                <template v-else-if="inArray(a.name,['role'])">
                    <span :class="'badge badge-' + getRoleClass(i.role)">
                        @{{ getRoleLabel(i.role) }}
                    </span>
                </template>
                <template v-else-if="inArray(a.name,['username'])">
                    <span class="text-nowrap">@{{ i.username || '-' }}</span>
                </template>
            @endslot
        @endcomponent
    </div>
@endsection
