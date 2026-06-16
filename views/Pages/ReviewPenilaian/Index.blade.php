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
                data.baseURL = 'ReviewPenilaian';
                data.detailPenilaian = null;
                data.showDetail = false;
                data.requestedKaryawanId = null;
                return data;
            },
            computed: computedAwal,
            mounted() {
                const karyawanId = this.getQueryParam('karyawan_id');
                if (karyawanId) {
                    this.requestedKaryawanId = karyawanId;
                    this.request.karyawan_id = karyawanId;
                    this.lastQuery = this.normalizeQueryParams({
                        q: this.request.q,
                        SortBy: this.request.SortBy,
                        SortWith: this.request.SortWith,
                        limit: this.request.limit,
                        'karyawan_id[]': [karyawanId]
                    }).replace(/^\?/, '');
                    this.GetData('?' + this.lastQuery);
                    return;
                }

                this.GetData();
            },
            methods: {
                ...methodAwal,
                lihatDetail(penilaian) {
                    axiosInstance.get(`ReviewPenilaian-Api?id=${penilaian.id}`).then(res => {
                        this.detailPenilaian = res.data.data.data[0];
                        this.showDetail = true;
                        $('#modalDetailReview').modal('show');
                    });
                },
                tutupDetail() {
                    this.showDetail = false;
                    this.detailPenilaian = null;
                },
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
                cetakPenilaian(id) {
                    window.open('ReviewPenilaian-Cetak?id=' + id, '_blank');
                },
                afterGetdata() {
                    if (this.requestedKaryawanId) {
                        this.request.karyawan_id = this.requestedKaryawanId;
                    }
                    this.data = this.data.transform((item) => {
                        item.karyawan = item.karyawan || [];
                        item.periode = item.periode || [];
                        item.jabatan_display = item.karyawan[0]?.jabatan || '-';
                        item.tanggal_display = item.created_at || '-';
                        return item;
                    });
                    this.fields = collect([
                        { name: 'tanggal_display', label: 'Tanggal' },
                        { name: 'karyawan', label: 'Nama Karyawan' },
                        { name: 'jabatan_display', label: 'Jabatan' },
                        { name: 'periode', label: 'Periode' },
                        { name: 'total_nilai', label: 'Total Nilai' },
                        { name: 'klasifikasi', label: 'Klasifikasi' }
                    ]);
                    $('#modalDetailReview').off('hidden.bs.modal').on('hidden.bs.modal', () => {
                        this.tutupDetail();
                    });
                    this.syncFilterFormValues({
                        karyawan_id: this.requestedKaryawanId ? [this.requestedKaryawanId] : null
                    });
                }
            }
        }).mount('#app')
    </script>
@endsection
@section('css')
    <style>
        .review-card {
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .review-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
        }
    </style>
@endsection
@section('modal')
   
@endsection
@section('isi')
    <div class="row" id="app">
        {{-- Header --}}
        <div class="modal fade" id="modalDetailReview" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-xl" role="document">
                <div class="modal-content" v-if="showDetail && detailPenilaian">
                    <div class="modal-header bg-info text-white d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold">
                            <i class="fas fa-file-alt"></i> Detail Penilaian - @{{ detailPenilaian.karyawan[0]?.nama  }}
                        </h6>
                        <div>
                            <button class="btn btn-sm btn-light" data-dismiss="modal">
                                <i class="fas fa-times"></i> Tutup
                            </button>
                        </div>
                    </div>
                    <div class="modal-body">
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h5 class="font-weight-bold">@{{ detailPenilaian.karyawan[0]?.nama }}</h5>
                                <p class="mb-1"><strong>Jabatan:</strong> @{{ detailPenilaian.karyawan[0]?.jabatan }}</p>
                                <p class="mb-1"><strong>Pekerjaan:</strong> @{{ detailPenilaian.karyawan[0]?.pekerjaan }}</p>
                                <p class="mb-1"><strong>Periode:</strong> @{{ detailPenilaian.periode[0].nama_periode }}</p>
                            </div>
                            <div class="col-md-6">
                                <div class="card bg-light">
                                    <div class="card-body text-center">
                                        <h6 class="text-muted">Total Nilai</h6>
                                        <h2 :class="'text-' + getKlasifikasiClass(detailPenilaian.klasifikasi)">
                                            @{{ detailPenilaian.total_nilai }}
                                        </h2>
                                        <span :class="'badge badge-' + getKlasifikasiClass(detailPenilaian.klasifikasi)">
                                            @{{ detailPenilaian.klasifikasi }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
    
                        <h6 class="font-weight-bold mb-3">Detail Nilai Per Kriteria</h6>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="bg-light">
                                    <tr>
                                        <th>No</th>
                                        <th>Kriteria</th>
                                        <th>Bobot</th>
                                        <th>Nilai</th>
                                        <th>Nilai x Bobot</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(detail, index) in detailPenilaian.detail_kriteria">
                                        <td class="text-center">@{{ index + 1 }}</td>
                                        <td>@{{ detail.nama_kriteria }}</td>
                                        <td class="text-center">@{{ detail.bobot }}%</td>
                                        <td class="text-center">@{{ detail.nilai }}</td>
                                        <td class="text-center">@{{ detail.nilai_bobot }}</td>
                                    </tr>
                                </tbody>
                                <tfoot class="bg-light font-weight-bold">
                                    <tr>
                                        <td colspan="4" class="text-right">Total:</td>
                                        <td class="text-center">@{{ detailPenilaian.total_nilai }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
    
                        <div v-if="detailPenilaian.catatan" class="mt-4">
                            <h6 class="font-weight-bold">Catatan Penilaian:</h6>
                            <div class="alert alert-secondary">
                                @{{ detailPenilaian.catatan }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
       
        <div class="col-12 mb-3">
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> 
                <strong>Review Penilaian</strong> - Lihat kembali penilaian yang telah Anda berikan kepada bawahan.
            </div>
        </div>

        @component('Pages.Filter')
        @endcomponent

        @component('Pages.Table', [
            'title' => 'Daftar Penilaian yang Telah Diberikan',
            'showAdd' => false,
            'showPrint' => false,
            'showExcel' => false,
            'showDefaultAksi' => false,
            'customAksi' => '
                <div class="d-none d-sm-block text-center">
                    <button @click="lihatDetail(i)" class="shadow btn btn-sm btn-info mx-1 my-1">
                        Detail
                    </button>
                </div>
            '
        ])
            @slot('td')
                <template v-else-if="inArray(a.name,['karyawan'])">
                    <strong>@{{ i.karyawan[0]?.nama }}</strong><br>
                    <small class="text-muted">@{{ i.karyawan[0]?.pekerjaan }}</small>
                </template>
                <template v-else-if="inArray(a.name,['periode'])">
                    <span>@{{ i.periode[0]?.nama_periode }}</span>
                </template>
                <template v-else-if="inArray(a.name,['jabatan_display','tanggal_display'])">
                    <span>@{{ i[a.name] }}</span>
                </template>
                <template v-else-if="inArray(a.name,['total_nilai'])">
                    <span class="font-weight-bold">@{{ i.total_nilai }}</span>
                </template>
                <template v-else-if="inArray(a.name,['klasifikasi'])">
                    <span :class="'badge badge-' + getKlasifikasiClass(i.klasifikasi)">
                        @{{ i.klasifikasi }}
                    </span>
                </template>
            @endslot
        @endcomponent
    </div>
@endsection
