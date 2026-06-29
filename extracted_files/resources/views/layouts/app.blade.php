<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="color-scheme" content="light only">
    <meta name="supported-color-schemes" content="light only">
    <title>@yield('page_title', 'Dashboard') - WR2School</title>
    <link href='https://unpkg.com/boxicons@2.1.1/css/boxicons.min.css' rel='stylesheet'>
    
    <!-- Select2 & jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <style>
        :root {
            --sidebar-color: #0C447C;
            --primary-color: #378ADD;
            --primary-color-light: #1a5a9a;
            --toggle-color: #EF9F27;
            --text-color: #E6F1FB;
            --tran-03: all 0.2s ease;
            --tran-05: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background: #E6F1FB;
            color: #1a2a3a;
            padding-left: 88px;
            transition: var(--tran-05);
        }

        body.sidebar-open {
            padding-left: 250px;
        }

        /* ── Sidebar ── */
        .modern-sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: 250px;
            padding: 10px 14px;
            background: var(--sidebar-color);
            transition: var(--tran-05);
            z-index: 9999;
            /* no overflow:hidden — toggle button sticks out to the right */
        }

        .modern-sidebar.close {
            width: 88px;
        }

        /* Header */
        .modern-sidebar header {
            position: relative;
        }

        .modern-sidebar header .image-text {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 0;
        }

        .modern-sidebar header .logo-icon {
            min-width: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            color: var(--text-color);
        }

        .modern-sidebar header .logo-text {
            display: flex;
            flex-direction: column;
        }

        .modern-sidebar header .logo-text .name {
            font-size: 18px;
            font-weight: 600;
            color: var(--text-color);
            white-space: nowrap;
        }

        .modern-sidebar header .logo-text .profession {
            font-size: 13px;
            color: #9fc8f0;
            white-space: nowrap;
            text-transform: capitalize;
        }

        .modern-sidebar.close header .logo-text {
            display: none;
        }

        /* Toggle button */
        .modern-sidebar .toggle {
            position: absolute;
            top: 50%;
            right: -25px;
            transform: translateY(-50%) rotate(180deg);
            height: 25px;
            width: 25px;
            background-color: var(--primary-color);
            color: var(--sidebar-color);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            cursor: pointer;
            transition: var(--tran-05);
        }

        .modern-sidebar.close .toggle {
            transform: translateY(-50%) rotate(0deg);
        }

        /* Menu bar */
        .modern-sidebar .menu-bar {
            height: calc(100% - 65px);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            overflow-y: auto;
            overflow-x: hidden;
            margin-top: 4px;
        }

        .menu-bar::-webkit-scrollbar {
            display: none;
        }

        .modern-sidebar .menu {
            margin-top: 20px;
        }

        .modern-sidebar .menu-links {
            list-style: none;
            padding: 0;
        }

        .modern-sidebar li {
            height: 50px;
            list-style: none;
            display: flex;
            align-items: center;
            margin-top: 6px;
        }

        .modern-sidebar li a {
            height: 100%;
            width: 100%;
            border-radius: 6px;
            text-decoration: none;
            display: flex;
            align-items: center;
            transition: var(--tran-03);
        }

        .modern-sidebar li a:hover,
        .modern-sidebar li a.active {
            background-color: var(--primary-color);
        }

        .modern-sidebar .nav-icon {
            min-width: 60px;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            color: var(--text-color);
        }

        .modern-sidebar .nav-text {
            font-size: 16px;
            font-weight: 500;
            color: var(--text-color);
            white-space: nowrap;
            opacity: 1;
            transition: var(--tran-03);
        }

        .modern-sidebar.close .nav-text {
            display: none;
        }

        /* Dropdown Menu */
        .dropdown-menu-item {
            flex-direction: column;
            align-items: flex-start;
            height: auto !important;
            margin-top: 6px;
        }

        .dropdown-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
            height: 50px;
            cursor: pointer;
            border-radius: 6px;
            transition: var(--tran-03);
            color: var(--text-color);
        }

        .dropdown-header:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }

        .dropdown-header .left-part {
            display: flex;
            align-items: center;
            height: 100%;
        }

        .dropdown-header .arrow {
            font-size: 20px;
            margin-right: 15px;
            transition: transform 0.3s ease;
        }

        .dropdown-menu-item.active .dropdown-header .arrow {
            transform: rotate(-180deg);
        }

        .sub-menu {
            list-style: none;
            padding: 0;
            display: none;
            width: 100%;
        }
        
        .sub-menu a {
            padding-left: 20px;
        }

        .dropdown-menu-item.active .sub-menu {
            display: block;
        }

        .modern-sidebar.close .dropdown-header .arrow {
            display: none;
        }

        .modern-sidebar.close .sub-menu {
            display: none !important;
        }

        /* Bottom (logout) */
        .modern-sidebar .bottom-content {
            padding-bottom: 10px;
        }

        .modern-sidebar .bottom-content li {
            margin-top: 8px;
        }

        .modern-sidebar .logout-btn {
            width: 100%;
            display: flex;
            align-items: center;
            background: none;
            border: none;
            cursor: pointer;
            border-radius: 6px;
            height: 50px;
            transition: var(--tran-03);
        }

        .modern-sidebar .logout-btn:hover {
            background-color: #dc3545;
        }

        /* ── Navbar ── */
        .navbar {
            background-color: #0C447C;
            color: #fff;
            padding: 1rem 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 100;
            border-bottom: 1px solid #dce8f5;
        }

        .navbar-user-name {
            color: #E6F1FB;
            font-size: .9rem;
        }

        /* ── Content ── */
        .content-container {
            width: 100%;
            margin: 0;
            padding: 1.5rem 2rem;
            box-sizing: border-box;
        }
    </style>
    @yield('styles')
</head>

<body>

    {{-- Sidebar --}}
    <nav class="modern-sidebar close" id="sidebar">
        <header>
            <div class="image-text">
                <img src="{{ asset('logo-we2.png') }}" alt="WE2 Logo" class="logo-icon"
                    style="width: 40px; height: 40px; object-fit: contain;">
                <div class="logo-text">
                    <span class="name">WR2School</span>
                    <span class="profession">{{ auth()->user()->role === 'coordinator' ? 'Koordinator' : str_replace('_', ' ', auth()->user()->role) }}</span>
                </div>
            </div>
            <i class='bx bx-chevron-right toggle' id="sidebarToggle" onclick="toggleMySidebar()"></i>
        </header>

        <div class="menu-bar">
            <div class="menu">
                <ul class="menu-links">
                    @if(auth()->user()->role === 'coordinator')
                        <li><a href="{{ route('coordinator.dashboard') }}"
                                class="{{ request()->routeIs('coordinator.dashboard') ? 'active' : '' }}"><span
                                    class="nav-icon"><i class='bx bx-home-alt'></i></span><span
                                    class="nav-text">Dashboard</span></a></li>
                        <li><a href="{{ route('coordinator.profile') }}"
                                class="{{ request()->routeIs('coordinator.profile') ? 'active' : '' }}"><span
                                    class="nav-icon"><i class='bx bx-user'></i></span><span
                                    class="nav-text">Profile</span></a></li>
                        <li><a href="{{ route('coordinator.manage-guru') }}"
                                class="{{ request()->routeIs('coordinator.manage-guru') ? 'active' : '' }}"><span
                                    class="nav-icon"><i class='bx bx-user-voice'></i></span><span class="nav-text">Manage
                                    Guru</span></a></li>
                        <li><a href="{{ route('coordinator.manage-siswa') }}"
                                class="{{ request()->routeIs('coordinator.manage-siswa') ? 'active' : '' }}"><span
                                    class="nav-icon"><i class='bx bx-group'></i></span><span class="nav-text">Manage
                                    Siswa</span></a></li>
                        @php
                            $isSetupActive = request()->routeIs('coordinator.manage-tahun') || request()->routeIs('coordinator.manage-bobot') || request()->routeIs('coordinator.manage-periode');
                        @endphp
                        <li class="dropdown-menu-item {{ $isSetupActive ? 'active' : '' }}">
                            <div class="dropdown-header" onclick="this.parentElement.classList.toggle('active')">
                                <div class="left-part">
                                    <span class="nav-icon"><i class='bx bx-cog'></i></span>
                                    <span class="nav-text" style="font-weight: 700;">Setup</span>
                                </div>
                                <i class='bx bx-chevron-down arrow nav-text'></i>
                            </div>
                            <ul class="sub-menu">
                                <li><a href="{{ route('coordinator.manage-tahun') }}"
                                        class="{{ request()->routeIs('coordinator.manage-tahun') ? 'active' : '' }}"><span
                                            class="nav-icon"><i class='bx bx-calendar-star'></i></span><span
                                            class="nav-text">Tahun Ajaran</span></a></li>
                                <li><a href="{{ route('coordinator.manage-bobot') }}"
                                        class="{{ request()->routeIs('coordinator.manage-bobot') ? 'active' : '' }}"><span
                                            class="nav-icon"><i class='bx bx-slider-alt'></i></span><span
                                            class="nav-text">Bobot Nilai</span></a></li>
                                <li><a href="{{ route('coordinator.manage-periode') }}"
                                        class="{{ request()->routeIs('coordinator.manage-periode') ? 'active' : '' }}"><span
                                            class="nav-icon"><i class='bx bx-time'></i></span><span
                                            class="nav-text">Periode</span></a></li>
                            </ul>
                        </li>
                        <li><a href="{{ route('coordinator.manage-kelas') }}"
                                class="{{ request()->routeIs('coordinator.manage-kelas') ? 'active' : '' }}"><span
                                    class="nav-icon"><i class='bx bx-building'></i></span><span class="nav-text">Manage
                                    Kelas</span></a></li>
                        <li><a href="{{ route('coordinator.manage-jadwal') }}"
                                class="{{ request()->routeIs('coordinator.manage-jadwal') ? 'active' : '' }}"><span
                                    class="nav-icon"><i class='bx bx-calendar'></i></span><span class="nav-text">Manage
                                    Jadwal</span></a></li>
                        <li><a href="{{ route('coordinator.manage-subjects') }}"
                                class="{{ request()->routeIs('coordinator.manage-subjects') ? 'active' : '' }}"><span
                                    class="nav-icon"><i class='bx bx-book'></i></span><span class="nav-text">Manage
                                    Subjects</span></a></li>
                        <li><a href="{{ route('coordinator.manage-nilai') }}"
                                class="{{ request()->routeIs('coordinator.manage-nilai') ? 'active' : '' }}"><span
                                    class="nav-icon"><i class='bx bx-bar-chart-alt-2'></i></span><span
                                    class="nav-text">Manage Nilai</span></a></li>
                        <li><a href="{{ route('coordinator.manage-absensi') }}"
                                class="{{ request()->routeIs('coordinator.manage-absensi') ? 'active' : '' }}"><span
                                    class="nav-icon"><i class='bx bx-check-square'></i></span><span
                                    class="nav-text">Manage Absensi</span></a></li>
                        <li><a href="{{ route('coordinator.cetak') }}"
                                class="{{ request()->routeIs('coordinator.cetak') ? 'active' : '' }}"><span
                                    class="nav-icon"><i class='bx bx-printer'></i></span><span class="nav-text">Cetak
                                    Rapor</span></a></li>
                        <li><a href="{{ route('coordinator.progress') }}"
                                class="{{ request()->routeIs('coordinator.progress') ? 'active' : '' }}"><span
                                    class="nav-icon"><i class='bx bx-line-chart'></i></span><span
                                    class="nav-text">Progress</span></a></li>
                        <li><a href="{{ route('coordinator.kenaikan-kelas') }}"
                                class="{{ request()->routeIs('coordinator.kenaikan-kelas') ? 'active' : '' }}"><span
                                    class="nav-icon"><i class='bx bx-transfer'></i></span><span
                                    class="nav-text">Kenaikan Kelas</span></a></li>
                    @elseif(auth()->user()->role === 'guru')
                        <li><a href="{{ route('guru.dashboard') }}"
                                class="{{ request()->routeIs('guru.dashboard') ? 'active' : '' }}"><span class="nav-icon"><i
                                        class='bx bx-home-alt'></i></span><span class="nav-text">Dashboard</span></a></li>
                        <li><a href="{{ route('guru.profile') }}"
                                class="{{ request()->routeIs('guru.profile') ? 'active' : '' }}"><span class="nav-icon"><i
                                        class='bx bx-user'></i></span><span class="nav-text">Profile</span></a></li>
                        <li><a href="{{ route('guru.jadwal') }}"
                                class="{{ request()->routeIs('guru.jadwal') ? 'active' : '' }}"><span class="nav-icon"><i
                                        class='bx bx-calendar'></i></span><span class="nav-text">Jadwal</span></a></li>
                        <li><a href="{{ route('guru.nilai') }}"
                                class="{{ request()->routeIs('guru.nilai') ? 'active' : '' }}"><span class="nav-icon"><i
                                        class='bx bx-edit'></i></span><span class="nav-text">Nilai Siswa</span></a></li>
                    @elseif(auth()->user()->role === 'wali_kelas')
                        @php
                            $isWkActive = request()->routeIs('walikelas.*');
                            $isGuruActive = request()->routeIs('guru.*');
                        @endphp
                        <li class="dropdown-menu-item {{ $isWkActive ? 'active' : '' }}">
                            <div class="dropdown-header" onclick="this.parentElement.classList.toggle('active')">
                                <div class="left-part">
                                    <span class="nav-icon"><i class='bx bx-user-pin'></i></span>
                                    <span class="nav-text" style="font-weight: 700;">Wali Kelas</span>
                                </div>
                                <i class='bx bx-chevron-down arrow nav-text'></i>
                            </div>
                            <ul class="sub-menu">
                                <li><a href="{{ route('walikelas.dashboard') }}"
                                        class="{{ request()->routeIs('walikelas.dashboard') ? 'active' : '' }}"><span
                                            class="nav-icon"><i class='bx bx-home-alt'></i></span><span
                                            class="nav-text">Dashboard</span></a></li>
                                <li><a href="{{ route('walikelas.profile') }}"
                                        class="{{ request()->routeIs('walikelas.profile') ? 'active' : '' }}"><span
                                            class="nav-icon"><i class='bx bx-user'></i></span><span
                                            class="nav-text">Profile</span></a></li>
                                <li><a href="{{ route('walikelas.kelas') }}"
                                        class="{{ request()->routeIs('walikelas.kelas') ? 'active' : '' }}"><span
                                            class="nav-icon"><i class='bx bx-building'></i></span><span class="nav-text">Data
                                            Kelas</span></a></li>
                                <li><a href="{{ route('walikelas.jadwal') }}"
                                        class="{{ request()->routeIs('walikelas.jadwal') ? 'active' : '' }}"><span
                                            class="nav-icon"><i class='bx bx-calendar'></i></span><span
                                            class="nav-text">Jadwal</span></a></li>
                                <li><a href="{{ route('walikelas.absensi') }}"
                                        class="{{ request()->routeIs('walikelas.absensi') ? 'active' : '' }}"><span
                                            class="nav-icon"><i class='bx bx-check-square'></i></span><span
                                            class="nav-text">Absensi</span></a></li>
                                <li><a href="{{ route('walikelas.nilai') }}"
                                        class="{{ request()->routeIs('walikelas.nilai') ? 'active' : '' }}"><span
                                            class="nav-icon"><i class='bx bx-bar-chart-alt-2'></i></span><span
                                            class="nav-text">Nilai</span></a></li>
                                <li><a href="{{ route('walikelas.cetak') }}"
                                        class="{{ request()->routeIs('walikelas.cetak') ? 'active' : '' }}"><span
                                            class="nav-icon"><i class='bx bx-printer'></i></span><span class="nav-text">Cetak
                                            Report</span></a></li>
                            </ul>
                        </li>
                        
                        <li class="dropdown-menu-item {{ $isGuruActive ? 'active' : '' }}">
                            <div class="dropdown-header" onclick="this.parentElement.classList.toggle('active')">
                                <div class="left-part">
                                    <span class="nav-icon"><i class='bx bx-chalkboard'></i></span>
                                    <span class="nav-text" style="font-weight: 700;">Guru</span>
                                </div>
                                <i class='bx bx-chevron-down arrow nav-text'></i>
                            </div>
                            <ul class="sub-menu">
                                <li><a href="{{ route('guru.dashboard') }}"
                                        class="{{ request()->routeIs('guru.dashboard') ? 'active' : '' }}"><span class="nav-icon"><i
                                                class='bx bx-home-alt'></i></span><span class="nav-text">Dashboard Guru</span></a></li>
                                <li><a href="{{ route('guru.profile') }}"
                                        class="{{ request()->routeIs('guru.profile') ? 'active' : '' }}"><span class="nav-icon"><i
                                                class='bx bx-user'></i></span><span class="nav-text">Profile Guru</span></a></li>
                                <li><a href="{{ route('guru.jadwal') }}"
                                        class="{{ request()->routeIs('guru.jadwal') ? 'active' : '' }}"><span class="nav-icon"><i
                                                class='bx bx-calendar'></i></span><span class="nav-text">Jadwal Mengajar</span></a></li>
                                <li><a href="{{ route('guru.nilai') }}"
                                        class="{{ request()->routeIs('guru.nilai') ? 'active' : '' }}"><span class="nav-icon"><i
                                                class='bx bx-edit'></i></span><span class="nav-text">Nilai Siswa</span></a></li>
                            </ul>
                        </li>
                    @endif
                </ul>
            </div>

            <div class="bottom-content">
                <li>
                    <form action="{{ route('logout') }}" method="POST" style="width:100%;">
                        @csrf
                        <button type="submit" class="logout-btn">
                            <span class="nav-icon"><i class='bx bx-log-out'></i></span>
                            <span class="nav-text">Logout</span>
                        </button>
                    </form>
                </li>
            </div>
        </div>
    </nav>

    {{-- Navbar --}}
    <div class="navbar">
        <div style="display:flex; align-items:center; gap:12px;">
            <h1 style="color:#fff; font-size:1.3rem;">@yield('page_title', 'Dashboard')</h1>
        </div>
        <span class="navbar-user-name">{{ auth()->user()->nama }}</span>
    </div>

    <div class="content-container">
        @if(session('success'))
            <div
                style="background:#d4edda; color:#155724; border:1px solid #c3e6cb; padding:.75rem 1rem; border-radius:6px; margin-bottom:1.5rem; display:flex; align-items:center; gap:8px;">
                <i class='bx bx-check-circle' style="font-size:1.2rem;"></i> {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div
                style="background:#f8d7da; color:#721c24; border:1px solid #f5c6cb; padding:.75rem 1rem; border-radius:6px; margin-bottom:1.5rem; display:flex; align-items:center; gap:8px;">
                <i class='bx bx-error-circle' style="font-size:1.2rem;"></i> {{ session('error') }}
            </div>
        @endif

        @yield('content')
    </div>

    <script>
        window.toggleMySidebar = function () {
            var sidebar = document.getElementById('sidebar');
            var body = document.body;
            if (!sidebar) return;
            sidebar.classList.toggle('close');
            body.classList.toggle('sidebar-open');
            try {
                if (sidebar.classList.contains('close')) {
                    localStorage.setItem('sidebarState', 'closed');
                } else {
                    localStorage.setItem('sidebarState', 'open');
                }
            } catch (e) { }
        };

        document.addEventListener('DOMContentLoaded', function () {
            var sidebar = document.getElementById('sidebar');
            var body = document.body;
            if (!sidebar) return;

            try {
                var state = localStorage.getItem('sidebarState');
                if (state === 'open') {
                    sidebar.classList.remove('close');
                    body.classList.add('sidebar-open');
                }
            } catch (e) { }
        });
    </script>
    @yield('scripts')
</body>

</html>