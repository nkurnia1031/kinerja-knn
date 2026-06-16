<?php use app\Fungsi as Fungsi; $ttd = $data['ttd'] ?? (object) ['ket' => 'Mengetahui,', 'jabatan' => '', 'nama' => '', 'tambahan' => '']; ?>
<div class="row mt-2 justify-content-end" id="ttdNYa" style="font-size:11pt">
    <div class="col-12 d-flex justify-content-end">
        <table style="width: 280px; margin-right: 0; text-align: left;">
            <tr>
                <td class=" text-nowrap"> <b style="color:black;"> Dumai, {{date('d')}}  {{Fungsi::$bulan[date('n')]}}  {{date('Y')}}</b></td>
            </tr>
            <tr>
                <td class=" text-nowrap"> <b style="color:black;">  {{$ttd->ket}}</td>
            </tr>
            <tr>
                <td class="">
                    <p style="color:black" class="m-0 p-0 ">
                         <b style="color:black;">
                        {{$ttd->jabatan}}
                    </b>

                    </p>
                </td>
            </tr>
              <tr>
                <td class="">
                    <p style="color:black" class="m-0 p-0 ">

                    </p>
                </td>
            </tr>
            <tr>
                <td class="text-center">
                </td>
            </tr>
            <tr>
                <td>
                    <br>
                    <br>
                    <br>
                </td>
            </tr>
            <tr>
                <td style="color: black;">
                    <p class="m-0 p-0">
                        <b>
                        <u> {{$ttd->nama}} </u><br>
                         {{$ttd->tambahan}}
                         </b>
                    </p>
                </td>
            </tr>
        </table>
    </div>
</div>
