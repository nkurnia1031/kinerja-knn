<ul class="navbar-nav bg-1 shadow sidebar rounded-left sidebar-dark accordion"
    style="background-size: cover;background:no-repeat center center fixed" id="accordionSidebar">
    <!-- Sidebar - Brand -->
    <div class="py-2 px-0 rounded-left" style="background: white">
        <img src="assets/Picture1.png" alt="Logo Sistem Penilaian Kinerja" class=" img-fluid shadow-lg ">
    </div>
    <!-- Divider -->
    <hr class="sidebar-divider my-0">
    <!-- Nav Item - Dashboard -->
    <li class="nav-item <?php if ($data['link'] == 'Dashboard'): ?> active <?php endif;?> ">
        <a class="nav-link text-white" href="Dashboard">
            <i class="fas fa-fw fa-tachometer-alt"></i>
            <span>Dashboard</span></a>
    </li>

    @php
        $role = $Session['admin']->role ?? null;
    @endphp
    @if (!empty($Session['admin']) && in_array($role, [ 'karyawan']))
        <li class="nav-item <?php if ($data['link'] == 'RiwayatPenilaian'): ?> active <?php endif;?> ">
            <a class="nav-link text-white" href="RiwayatPenilaian">
                <i class="fas  fa-fw fa-chart-bar"></i>
                <span>Rekap Penilaian</span></a>
        </li>
    @endif

    {{-- Menu untuk Admin dan Atasan --}}
    @if (!empty($Session['admin']) && in_array($role, [ 'atasan']))
        <hr class="sidebar-divider">
        <div class="sidebar-heading text-white-50">
            Penilaian Kinerja
        </div>
        <li class="nav-item <?php if ($data['link'] == 'PenilaianBawahan'): ?> active <?php endif;?> ">
            <a class="nav-link text-white" href="PenilaianBawahan">
                <i class="fas fa-fw fa-edit"></i>
                <span>Penilaian Bawahan</span></a>
        </li>
        <li class="nav-item <?php if ($data['link'] == 'ReviewPenilaian'): ?> active <?php endif;?> ">
            <a class="nav-link text-white" href="ReviewPenilaian">
                <i class="fas fa-fw fa-eye"></i>
                <span>Review Penilaian</span></a>
        </li>
        <li class="nav-item <?php if ($data['link'] == 'DaftarBawahan'): ?> active <?php endif;?> ">
            <a class="nav-link text-white" href="DaftarBawahan">
                <i class="fas fa-fw fa-user-friends"></i>
                <span>Daftar Bawahan</span></a>
        </li>
    @endif

    {{-- Menu untuk Admin dan Pimpinan --}}
    @if (!empty($Session['admin']) && in_array($role, ['admin']))
        <hr class="sidebar-divider">
        <div class="sidebar-heading text-white-50">
            Data Master
        </div>
        <li class="nav-item <?php if ($data['link'] == 'Karyawan'): ?> active <?php endif;?> ">
            <a class="nav-link text-white" href="Karyawan">
                <i class="fas fa-fw fa-users"></i>
                <span>Data Karyawan</span></a>
        </li>
        <li class="nav-item <?php if ($data['link'] == 'RelasiAtasan'): ?> active <?php endif;?> ">
            <a class="nav-link text-white" href="RelasiAtasan">
                <i class="fas fa-fw fa-sitemap"></i>
                <span>Atur Relasi Atasan</span></a>
        </li>
        <li class="nav-item <?php if ($data['link'] == 'Kriteria'): ?> active <?php endif;?> ">
            <a class="nav-link text-white" href="Kriteria">
                <i class="fas fa-fw fa-clipboard-list"></i>
                <span>Kriteria Penilaian</span></a>
        </li>
        <li class="nav-item <?php if ($data['link'] == 'PeriodePenilaian'): ?> active <?php endif;?> ">

            <a class="nav-link text-white" href="PeriodePenilaian">
                <i class="fas fa-fw fa-calendar-alt"></i>
                <span>Periode Penilaian</span></a>
        </li>
    
        @endif
       
        @if (!empty($Session['admin']) && in_array($role, ['admin', 'pimpinan']))

        <hr class="sidebar-divider">
        <div class="sidebar-heading text-white-50">
            Laporan dan Analisis
        </div>
        @if (!empty($Session['admin']) && in_array($role, ['admin']))

        <li class="nav-item <?php if ($data['link'] == 'RekapPenilaian'): ?> active <?php endif;?> ">
            <a class="nav-link text-white" href="RekapPenilaian">
                <i class="fas fa-fw fa-chart-bar"></i>
                <span>Rekap Penilaian</span></a>
        </li>
        <li class="nav-item <?php if ($data['link'] == 'AnalisaKinerja'): ?> active <?php endif;?> ">
            <a class="nav-link text-white" href="AnalisaKinerja">
                <i class="fas fa-fw fa-brain"></i>
                <span>Analisa Kinerja</span></a>
        </li>
    @endif
        <li class="nav-item <?php if ($data['link'] == 'Laporan'): ?> active <?php endif;?> ">
            <a class="nav-link text-white" href="Laporan">
                <i class="fas fa-fw fa-file-alt"></i>
                <span>Laporan</span></a>
        </li>
    @endif
   

    {{-- Login jika belum login --}}
    @if (empty($Session['admin']))
        <li class="nav-item <?php if ($data['link'] == 'Login'): ?> active <?php endif;?> ">
            <a class="nav-link text-white" href="Login">
                <i class="fas fa-fw fa-sign-in-alt"></i>
                <span>Login</span></a>
        </li>
    @endif

    {{-- Logout --}}
    @if (!empty($Session['admin']))
        <hr class="sidebar-divider">
        <li class="nav-item <?php if ($data['link'] == 'Logout'): ?> active <?php endif;?> ">
            <a class="nav-link text-white" href="Logout">
                <i class="fas fa-fw fa-sign-out-alt"></i>
                <span>Logout</span></a>
        </li>
    @endif

    <!-- Divider -->
    <hr class="sidebar-divider d-none d-md-block">
    <!-- Sidebar Toggler (Sidebar) -->
    <div class="text-center d-none d-md-inline">
        <button style="display: none;" class="rounded-circle border-0" id="sidebarToggle"></button>
    </div>
</ul>
