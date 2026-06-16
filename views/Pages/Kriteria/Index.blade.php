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
                data.baseURL = 'Kriteria';
                return data;
            },
            computed: computedAwal,
            mounted() {
                this.GetData();
            },
            beforeUpdated() {},
            updated() {},
            methods: methodAwal
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
@endsection
@section('isi')
    <div class="row" id="app">
        {{-- Info Card --}}
        <div class="col-12 mb-3">
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> 
                <strong>Kriteria Penilaian Kinerja</strong> - Kelola kriteria yang digunakan untuk menilai kinerja karyawan.
                Setiap kriteria memiliki bobot untuk perhitungan nilai akhir.
            </div>
        </div>

        {{-- Statistik Kriteria --}}
        <div class="col-lg-4 col-md-6 mb-3">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Kriteria</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">@{{ data2.length }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clipboard-list fa-2x text-gray-300"></i>
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
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Total Bobot</div>
                            <div v-if="data2" class="h5 mb-0 font-weight-bold text-gray-800">
                                @{{ data2.reduce((sum, item) => sum + parseFloat(item.bobot || 0), 0) }}%
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-percentage fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-md-6 mb-3">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Status Bobot</div>
                            <div v-if="data2" class="h5 mb-0 font-weight-bold text-gray-800">
                                <span v-if="data2.reduce((sum, item) => sum + parseFloat(item.bobot || 0), 0) == 100" class="text-success">
                                    <i class="fas fa-check-circle"></i> Valid
                                </span>
                                <span v-else class="text-danger">
                                    <i class="fas fa-exclamation-circle"></i> Belum 100%
                                </span>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-double fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @component('Pages.Form')
            @slot('tambahan')
                <template v-else-if="inArray(data.name,['tipe'])">
                    <select class="form-control" :name="'input[' + data.name + ']'" :id="data.name + '1'">
                        <option value="">-- Pilih Tipe --</option>
                        <option value="kuantitatif" :selected="data.val == 'kuantitatif'">Kuantitatif (Angka)</option>
                        <option value="kualitatif" :selected="data.val == 'kualitatif'">Kualitatif (Pilihan)</option>
                    </select>
                </template>
                <template v-else-if="inArray(data.name,['status'])">
                    <select class="form-control" :name="'input[' + data.name + ']'" :id="data.name + '1'">
                        <option value="">-- Pilih Status --</option>
                        <option value="aktif" :selected="data.val == 'aktif'">Aktif</option>
                        <option value="nonaktif" :selected="data.val == 'nonaktif'">Non-Aktif</option>
                    </select>
                </template>
            @endslot
        @endcomponent
        @component('Pages.Filter')
        @endcomponent

        @component('Pages.Table')
            @slot('tambahanth')
             
            @endslot
            @slot('td')
            <template v-else-if="inArray(a.name,['bobot'])">
                <span class="badge badge-info">@{{ i.bobot }}%</span>
            </template>
            <template v-else-if="inArray(a.name,['status'])">
                <span v-if="i.status == 'aktif'" class="badge badge-success">Aktif</span>
                    <span v-else class="badge badge-danger">Non-Aktif</span>
            </template>

            @endslot
            @slot('tambahantd')
              
            @endslot
        @endcomponent
    </div>
@endsection
