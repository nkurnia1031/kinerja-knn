@php
    if (empty($tfoot)) {
        $tfoot = '';
    }
    $title = $title ?? 'Data';
    $showAdd = $showAdd ?? true;
    $showPrint = $showPrint ?? false;
    $showExcel = $showExcel ?? false;
    $showRefresh = $showRefresh ?? true;
    $showAksi = $showAksi ?? true;
    $showDefaultAksi = $showDefaultAksi ?? true;
    $customAksi = $customAksi ?? '';
    $customHeaderAksi = $customHeaderAksi ?? '';
    $tableClass = $tableClass ?? 'table text-nowrap table-striped w-100';
    $tableId = $tableId ?? 'idTable';
    $rowSource = $rowSource ?? 'data2';
    $rowClassExpr = $rowClassExpr ?? '';
    $showAksiExpr = $showAksiExpr ?? ($showAksi ? 'true' : 'false');
    $showUsernameMenu = $showUsernameMenu ?? true;
    $tambahanth = $tambahanth ?? '';
    $tambahantd = $tambahantd ?? '';

@endphp
<div class="col-lg-12 col-md-12">
    <div class="card o-hidden  border-bottom-dark shadow  ">
        <div class="card-header align-items-center justify-content-between d-flex py-3">
            <span>
                <h6 class="m-0 font-weight-bold text-dark">{{ $title }}</h6>
            </span>
            <span class="d-flex flex-wrap justify-content-end">
                @if (!empty($customHeaderAksi))
                    {!! $customHeaderAksi !!}
                @else
                    @if ($showAdd)
                        <a @click="setAwal" class="btn mr-1 mb-1 btn-sm btn-primary"><i class="fa fa-plus"></i>
                            Tambah Data</a>
                    @endif
                    @if ($showPrint)
                        <a :href="baseURL + '-Cetak?' + lastQuery" target="_blank" class="btn mr-1 mb-1 btn-sm btn-dark"><i
                                class="fa fa-print"></i> Cetak</a>
                    @endif
                    @if ($showExcel)
                        <a :href="baseURL + '-XLSX?' + lastQuery" target="_blank" class="btn mr-1 mb-1 btn-sm btn-success"><i
                                class="fa fa-file-excel"></i> Export XLSX</a>
                    @endif
                    @if ($showRefresh)
                        <a href="javascript:;" @click="GetData()" class="btn mr-1 mb-1 btn-sm btn-secondary"><i
                                class="fa fa-sync"></i> Refresh</a>
                    @endif
                @endif
            </span>
        </div>
        <div class="table-responsive">
            <table class="{{ $tableClass }}" id="{{ $tableId }}">
                <thead class="text-center">
                    <tr>
                        <th>No.</th>
                        <th v-for="a in fields" class="border text-wrap">
                            @{{ a.label }}</th>
                        {!! $tambahanth !!}
                        @if ($showAksi)
                            <th data-priority="1" class="border border-right" v-if="{!! $showAksiExpr !!}">Aksi</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(i,index) in {{ $rowSource }}" @if (!empty($rowClassExpr)) :class="{!! $rowClassExpr !!}" @endif>
                        <td>@{{ index + 1 }}</td>
                        <td class=" text-wrap" v-for="a in fields">
                            <template v-if="inArray(a.name,['deskripsi','anggotaHadir','catatan'])">
                                <pre class="text-left text-nowrap" v-html="i[a.name]"></pre>
                            </template>
                            {!! $td !!}
                            <template v-else-if="inArray(a.name,['foto'])">
                                <a href="javascript:;" @click="LihatGambar(i[a.name])">

                                    <img v-if="i[a.name]" :src="'upload/' + i[a.name]" width="100" class="img-fluid"
                                        alt="Foto Produk">
                                    <img v-else src="upload/no-img-placeholder.png" width="100" class="img-fluid"
                                        alt="Foto Produk" srcset="">
                                </a>
                            </template>
                            <template v-else-if="inArray(a.name,['file','lampiran'])">
                                <a href="javascript:;" class="btn btn-sm btn-info" @click="LihatGambar(i[a.name])">

                                    View
                                </a>
                            </template>
                            @if ($showUsernameMenu)
                                <template v-else-if="inArray(a.name,['username'])">
                                    <div class="d-flex justify-content-between">

                                        <span class="text-nowrap"> @{{ i[a.name] }}</span>

                                        <div class="btn-group d-xs-block d-sm-none">
                                            <button type="button" class="btn btn-sm btn-danger dropdown-toggle"
                                                data-toggle="dropdown" aria-expanded="false">
                                                ...
                                            </button>
                                            <div class="dropdown-menu">
                                                <a class="dropdown-item" @click="HapusData(index)" data-placement="top"
                                                    title="Data Akan dihapus selamanya" href="javascript:;">Hapus</a>

                                                <a class="dropdown-item" @click="EditData(index)" href="javascript:;"
                                                    data-toggle="tooltip" data-placement="top"
                                                    title="update sebagian atau seluruh data">Edit</a>

                                            </div>
                                        </div>
                                    </div>

                                </template>
                            @endif
                            <template v-else-if="inArray(a.name,['modal','jual','diskon','harga','nominal'])">
                                <span class="text-nowrap"> Rp @{{ formatRupiah(i[a.name]) }}</span>


                            </template>
                            <template v-else>
                                <span class="text-nowrap"> @{{ i[a.name] }}</span>
                            </template>

                        </td>
                        {!! $tambahantd !!}

                        @if ($showAksi)
                            <td v-if="{!! $showAksiExpr !!}">
                                @if (!empty($customAksi))
                                    {!! $customAksi !!}
                                @elseif ($showDefaultAksi)
                                    <div class="d-none d-sm-block">
                                        <a v-bind:onclick="'app.HapusData('+index+')'" data-placement="top"
                                            title="Data Akan dihapus selamanya" href="javascript:;"
                                            class="shadow  btn btn-sm btn-danger mx-1 my-1">Hapus</a>

                                        <a v-bind:onclick="'app.EditData('+index+')'" href="javascript:;" data-toggle="tooltip"
                                            data-placement="top" title="update sebagian atau seluruh data"
                                            class="shadow btn text-white btn-sm btn-success mx-1 my-1">Edit</a>

                                    </div>
                                @endif
                            </td>
                        @endif



                    </tr>

                </tbody>
                <tfoot>
                    {!! $tfoot !!}
                </tfoot>

            </table>


        </div>
    </div>
</div>
