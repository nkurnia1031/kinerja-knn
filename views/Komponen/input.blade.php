@php
    use app\Fungsi;
    use app\Model\AnggotaNew;
@endphp
@foreach ($e2 as $v => $e)
    @switch($v)
        @case('foto')
            <div class="form-group row">
                <label class="col-sm-3 text-right control-label col-form-label">{{ $e->label }}</label>
                <div class="col-sm-9">
                    <div class="input-group mb-3">
                        <input type="file" class="form-control" onchange="previewImage()" id="image-source" type="file"
                            accept="image/*" name="input[]" id="inputGroupFile04" aria-describedby="inputGroupFileAddon04"
                            aria-label="Upload">
                    </div>
                    <div class=" text-center">
                        @empty($e->val)
                            <?php $e->val = 'no-img-placeholder.png'; ?>
                        @endempty
                        <img src="upload/{{ $e->val }}" class="w-100 rounded " id="image-preview">
                    </div>
                    <small id="{{ $e->name }}" class="form-text text-muted"></small>
                </div>
            </div>
        @break

        @case('alamat')
        @case('cabang')
            <div class="form-group col-12 col-lg-12">
                <label class="col-form-label">{{ $e->label }}</label>
                <textarea type="{{ $e->type }}" {!! $e->red !!} maxlength="{{ $e->max }}"
                    name="input[{{ $v }}]" class="form-control" id="{{ $v }}1" placeholder="{{ $e->label }}"
                    aria-describedby="{{ $v }}12">{!! $e->val !!}</textarea>
            </div>
        @break

        @case('unit')
            <div class="form-group col-12 col-lg-6">
                <label class="col-form-label">{{ $e->label }}</label>
                <select id="{{ $v }}1" aria-describedby="{{ $v }}12" {!! $e->red !!}
                    value="{{ $e->val }}" maxlength="{{ $e->max }}" name="input[{{ $v }}]"
                    class=" form-control ">
                    @foreach (AnggotaNew::$unit as $r => $et)
                        <option value="{{ $et }}" {{ $et == $e->val ? 'selected' : '' }}>{{ $et }}
                        </option>
                    @endforeach
                </select>
                {{-- <small class="text-danger">Pilih Ya jika nanti bisa milih lebih dari 1 sub-kriteria</small> --}}
            </div>
        @break

        @case('jk')
            <div class="form-group col-12 col-lg-6">
                <label class="col-form-label">{{ $e->label }}</label>
                <select id="{{ $v }}1" aria-describedby="{{ $v }}12" {!! $e->red !!}
                    value="{{ $e->val }}" maxlength="{{ $e->max }}" name="input[{{ $v }}]"
                    class=" form-control ">
                    @foreach (['Laki-Laki', 'Perempuan'] as $r => $et)
                        <option value="{{ $et }}" {{ $et == $e->val ? 'selected' : '' }}>{{ $et }}
                        </option>
                    @endforeach
                </select>
                {{-- <small class="text-danger">Pilih Ya jika nanti bisa milih lebih dari 1 sub-kriteria</small> --}}
            </div>
        @break

        @case('level')
            <div class="form-group col-12 col-lg-6">
                <label class="col-form-label">{{ $e->label }}</label>
                <select id="{{ $v }}1" aria-describedby="{{ $v }}12" {!! $e->red !!}
                    value="{{ $e->val }}" maxlength="{{ $e->max }}" name="input[{{ $v }}]"
                    class=" form-control ">
                    @foreach (['Admin', 'Anggota', 'Ketua', 'Wakil Ketua', 'Sekretaris', 'Bendahara I', 'Bendahara II', 'Pengawas', 'Manajemen'] as $r => $et)
                        <option value="{{ $et }}" {{ $et == $e->val ? 'selected' : '' }}>{{ $et }}
                        </option>
                    @endforeach
                </select>
                {{-- <small class="text-danger">Pilih Ya jika nanti bisa milih lebih dari 1 sub-kriteria</small> --}}
            </div>
        @break

        @case('bank')
            <div class="form-group col-12 col-lg-12">
                <label class="col-form-label">{{ $e->label }}</label>
                <select id="{{ $v }}1" aria-describedby="{{ $v }}12" {!! $e->red !!}
                    value="{{ $e->val }}" maxlength="{{ $e->max }}" name="input[{{ $v }}]"
                    class=" form-control w-100 ">
                </select>
                {{-- <small class="text-danger">Pilih Ya jika nanti bisa milih lebih dari 1 sub-kriteria</small> --}}
            </div>
        @break

        @case('agama')
            <div class="form-group col-12 col-lg-6">
                <label class="col-form-label">{{ $e->label }}</label>
                <select id="{{ $v }}1" aria-describedby="{{ $v }}12" {!! $e->red !!}
                    value="{{ $e->val }}" maxlength="{{ $e->max }}" name="input[{{ $v }}]"
                    class=" form-control ">
                    @foreach (Fungsi::$agama as $r => $et)
                        <option value="{{ $et }}" {{ $et == $e->val ? 'selected' : '' }}>{{ $et }}
                        </option>
                    @endforeach
                </select>
                {{-- <small class="text-danger">Pilih Ya jika nanti bisa milih lebih dari 1 sub-kriteria</small> --}}
            </div>
        @break

        @case('statusTempatTinggal')
            <div class="form-group col-12 col-lg-6">
                <label class="col-form-label">{{ $e->label }}</label>
                <select id="{{ $v }}1" aria-describedby="{{ $v }}12" {!! $e->red !!}
                    value="{{ $e->val }}" maxlength="{{ $e->max }}" name="input[{{ $v }}]"
                    class=" form-control ">
                    @foreach (Fungsi::$statusTinggal as $r => $et)
                        <option value="{{ $et }}" {{ $et == $e->val ? 'selected' : '' }}>{{ $et }}
                        </option>
                    @endforeach
                </select>
                {{-- <small class="text-danger">Pilih Ya jika nanti bisa milih lebih dari 1 sub-kriteria</small> --}}
            </div>
        @break

        @case('pendidikan')
            <div class="form-group col-12 col-lg-6">
                <label class="col-form-label">{{ $e->label }}</label>
                <select id="{{ $v }}1" aria-describedby="{{ $v }}12" {!! $e->red !!}
                    value="{{ $e->val }}" maxlength="{{ $e->max }}" name="input[{{ $v }}]"
                    class=" form-control ">
                    @foreach (Fungsi::$pendidikan as $r => $et)
                        <option value="{{ $et }}" {{ $et == $e->val ? 'selected' : '' }}>{{ $et }}
                        </option>
                    @endforeach
                </select>
                {{-- <small class="text-danger">Pilih Ya jika nanti bisa milih lebih dari 1 sub-kriteria</small> --}}
            </div>
        @break

        @case('statusPerkawinan')
            <div class="form-group col-12 col-lg-6">
                <label class="col-form-label">{{ $e->label }}</label>
                <select id="{{ $v }}1" aria-describedby="{{ $v }}12" {!! $e->red !!}
                    value="{{ $e->val }}" maxlength="{{ $e->max }}" name="input[{{ $v }}]"
                    class=" form-control ">
                    @foreach (Fungsi::$statusPerkawinan as $r => $et)
                        <option value="{{ $et }}" {{ $et == $e->val ? 'selected' : '' }}>{{ $et }}
                        </option>
                    @endforeach
                </select>
                {{-- <small class="text-danger">Pilih Ya jika nanti bisa milih lebih dari 1 sub-kriteria</small> --}}
            </div>
        @break

        @case('hubunganAhliWaris')
            <div class="form-group col-12 col-lg-6">
                <label class="col-form-label">{{ $e->label }}</label>
                <select id="{{ $v }}1" aria-describedby="{{ $v }}12" {!! $e->red !!}
                    value="{{ $e->val }}" maxlength="{{ $e->max }}" name="input[{{ $v }}]"
                    class=" form-control ">
                    @foreach (Fungsi::$hubunganAhliWaris as $r => $et)
                        <option value="{{ $et }}" {{ $et == $e->val ? 'selected' : '' }}>{{ $et }}
                        </option>
                    @endforeach
                </select>
                {{-- <small class="text-danger">Pilih Ya jika nanti bisa milih lebih dari 1 sub-kriteria</small> --}}
            </div>
        @break

        @case('pns')
            <div class="form-group row">
                <label class="col-sm-3 text-right control-label col-form-label">{{ $e->label }}</label>
                <div class="col-sm-4 col-form-label">
                    <label for="ID_HERE" data-style="rounded" data-size="sm" class="toggle-switchy">
                        <input name="input[pns]" {{ $e->val ? 'checked' : '' }} type="checkbox" id="ID_HERE">
                        <span class="toggle">
                            <span class="switch"></span>
                        </span>
                    </label>

                    {{-- <small class="text-danger">Pilih Ya jika nanti bisa milih lebih dari 1 sub-kriteria</small> --}}
                </div>
                <div class="col-sm-5">
                    <div class="w-100 d-flex justify-content-end">
                        <label class="col-form-label mr-3">Akses</label>
                        <div class="col-form-label ">

                            <label for="ID_HERE2" data-style="rounded" data-size="sm" class="toggle-switchy">
                                <input name="input[statusAkses]" {{ !empty($key) && $key->statusAkses ? 'checked' : '' }}
                                    type="checkbox" id="ID_HERE2">
                                <span class="toggle">
                                    <span class="switch"></span>
                                </span>
                            </label>

                            {{-- <small class="text-danger">Pilih Ya jika nanti bisa milih lebih dari 1 sub-kriteria</small> --}}
                        </div>

                    </div>

                </div>

            </div>
        @break

        @case('tempatLahir')
            <div class="form-group col-12 col-lg-6">
                {{-- col-form-label-sm --}}
                {{-- form-control-sm --}}
                <label class=" col-form-label ">{{ $e->label }}</label>
                <input type="{{ $e->type }}" {!! $e->red !!} value="{{ $e->val }}"
                    maxlength="{{ $e->max }}" name="input[{{ $v }}]" class="form-control "
                    id="{{ $v }}1" placeholder="{{ $e->label }}" aria-describedby="{{ $v }}12">
                <small class="text-danger">{{ isset($e->small) ? $e->small : '' }}</small>
            </div>
        @break

        @case('tanggalLahir')
            <div class="form-group col-12 col-lg-6">
                {{-- col-form-label-sm --}}
                {{-- form-control-sm --}}
                <label class=" col-form-label ">{{ $e->label }}</label>
                <input type="{{ $e->type }}" {!! $e->red !!} value="{{ $e->val }}"
                    maxlength="{{ $e->max }}" name="input[{{ $v }}]" class="form-control "
                    id="{{ $v }}1" placeholder="{{ $e->label }}"
                    aria-describedby="{{ $v }}12">
                <small class="text-danger">{{ isset($e->small) ? $e->small : '' }}</small>
            </div>
        @break

        @case('kelurahan')
            <div class="form-group col-12 col-lg-6">
                {{-- col-form-label-sm --}}
                {{-- form-control-sm --}}
                <label class=" col-form-label ">{{ $e->label }}</label>
                <input type="{{ $e->type }}" {!! $e->red !!} value="{{ $e->val }}"
                    maxlength="{{ $e->max }}" name="input[{{ $v }}]" class="form-control "
                    id="{{ $v }}1" placeholder="{{ $e->label }}"
                    aria-describedby="{{ $v }}12">
                <small class="text-danger">{{ isset($e->small) ? $e->small : '' }}</small>
            </div>
        @break

        @case('kecamatan')
            <div class="form-group col-12 col-lg-6">
                {{-- col-form-label-sm --}}
                {{-- form-control-sm --}}
                <label class=" col-form-label ">{{ $e->label }}</label>
                <input type="{{ $e->type }}" {!! $e->red !!} value="{{ $e->val }}"
                    maxlength="{{ $e->max }}" name="input[{{ $v }}]" class="form-control "
                    id="{{ $v }}1" placeholder="{{ $e->label }}"
                    aria-describedby="{{ $v }}12">
                <small class="text-danger">{{ isset($e->small) ? $e->small : '' }}</small>
            </div>
        @break

        @case('kota')
            <div class="form-group col-12 col-lg-6">
                {{-- col-form-label-sm --}}
                {{-- form-control-sm --}}
                <label class=" col-form-label ">{{ $e->label }}</label>
                <input type="{{ $e->type }}" {!! $e->red !!} value="{{ $e->val }}"
                    maxlength="{{ $e->max }}" name="input[{{ $v }}]" class="form-control "
                    id="{{ $v }}1" placeholder="{{ $e->label }}"
                    aria-describedby="{{ $v }}12">
                <small class="text-danger">{{ isset($e->small) ? $e->small : '' }}</small>
            </div>
        @break

        @case('kodePos')
            <div class="form-group col-12 col-lg-6">
                {{-- col-form-label-sm --}}
                {{-- form-control-sm --}}
                <label class=" col-form-label ">{{ $e->label }}</label>
                <input type="{{ $e->type }}" {!! $e->red !!} value="{{ $e->val }}"
                    maxlength="{{ $e->max }}" name="input[{{ $v }}]" class="form-control "
                    id="{{ $v }}1" placeholder="{{ $e->label }}"
                    aria-describedby="{{ $v }}12">
                <small class="text-danger">{{ isset($e->small) ? $e->small : '' }}</small>
            </div>
        @break

        @case('nomorPekerja')
            <div class="form-group col-8">
                <div class="row">
                    <div class="form-group col-12 col-lg-6">
                        {{-- col-form-label-sm --}}
                        {{-- form-control-sm --}}
                        <label class=" col-form-label ">Direct Hire</label>
                        <select name="input[directHire]" id="direct-hire" class="form-control">
                            @foreach (AnggotaNew::$directHire as $item)
                                <option>{{ $item }}</option>
                            @endforeach

                        </select>
                        <small class="text-danger">{{ isset($e->small) ? $e->small : '' }}</small>
                    </div>
                    <div class="form-group col-12 col-lg-6">
                        {{-- col-form-label-sm --}}
                        {{-- form-control-sm --}}
                        <label class=" col-form-label ">{{ $e->label }}</label>
                        <input type="{{ $e->type }}" {!! $e->red !!} value="{{ $e->val }}"
                            maxlength="{{ $e->max }}" name="input[{{ $v }}]" class="form-control "
                            id="{{ $v }}1" placeholder="{{ $e->label }}"
                            aria-describedby="{{ $v }}12">
                        <small class="text-danger">{{ isset($e->small) ? $e->small : '' }}</small>
                    </div>
                </div>
            </div>
        @break

        @case('pengangkatanKaryawanTetap')
            <div class="form-group col-12 col-lg-4">
                {{-- col-form-label-sm --}}
                {{-- form-control-sm --}}
                <label class=" col-form-label ">{{ $e->label }}</label>
                <input type="{{ $e->type }}" {!! $e->red !!} value="{{ $e->val }}"
                    maxlength="{{ $e->max }}" name="input[{{ $v }}]" class="form-control "
                    id="{{ $v }}1" placeholder="{{ $e->label }}"
                    aria-describedby="{{ $v }}12">
                <small class="text-danger">{{ isset($e->small) ? $e->small : '' }}</small>
            </div>
        @break

        @case('password')
            <div class="form-group col-12 col-lg-12">
                {{-- col-form-label-sm --}}
                {{-- form-control-sm --}}
                <label class=" col-form-label ">{{ $e->label }}</label>
                <input type="{{ $e->type }}" autocomplete="new-password" {!! $e->red !!} value=""
                    maxlength="{{ $e->max }}" name="input[{{ $v }}]" class="form-control "
                    id="{{ $v }}1" placeholder="{{ $e->label }}"
                    aria-describedby="{{ $v }}12">
                <small class="text-danger">{{ isset($e->small) ? $e->small : '' }}</small>
            </div>
            <div class="form-group col-12 col-lg-12">
                <label for="password2">Konfirmasi {{ $e->label }}:</label>
                <input type="password" autocomplete="new-password" class="form-control" id="password2"
                    name="input[password2]">
                <small id="password-error" class="form-text text-danger d-none">Password tidak sama!</small>
            </div>
        @break

        @default
            <div class="form-group col-12 col-lg-12">
                {{-- col-form-label-sm --}}
                {{-- form-control-sm --}}
                <label class=" col-form-label ">{{ $e->label }}</label>
                <input type="{{ $e->type }}" {!! $e->red !!} value="{{ $e->val }}"
                    maxlength="{{ $e->max }}" name="input[{{ $v }}]" class="form-control "
                    id="{{ $v }}1" placeholder="{{ $e->label }}"
                    aria-describedby="{{ $v }}12">
                <small class="text-danger">{{ isset($e->small) ? $e->small : '' }}</small>
            </div>
    @endswitch
@endforeach
