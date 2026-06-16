@php
    $nama = $v === 'penerima_id' ? $data->penerima_nama : $data->teknisi_nama;
    $nohp = $v === 'penerima_id' ? $data->penerima_nohp : $data->teknisi_nohp;
@endphp

<strong>{{ $nama }}</strong>
<div class="text-muted">
    No. HP : <strong>{{ $nohp }}</strong>
</div>
