<div class="app-menu navbar-menu">
    <div class="navbar-brand-box">
        <a href="{{ route('dashboard') }}" class="logo logo-dark">
            <span class="logo-sm"><span class="fw-bold fs-16 brand-text-sm">SF2</span></span>
            <span class="logo-lg"><span class="fw-bold fs-18 text-dark brand-text">Street Fighter 2</span></span>
        </a>
        <a href="{{ route('dashboard') }}" class="logo logo-light">
            <span class="logo-sm"><span class="fw-bold fs-16 brand-text-sm">SF2</span></span>
            <span class="logo-lg"><span class="fw-bold fs-18 text-white brand-text">Street Fighter 2</span></span>
        </a>
        <button type="button" class="btn btn-sm p-0 fs-20 header-item float-end btn-vertical-sm-hover" id="vertical-hover">
            <i class="ri-record-circle-line"></i>
        </button>
    </div>

    <div id="scrollbar">
        <div class="container-fluid">
            <div id="two-column-menu"></div>
            <ul class="navbar-nav" id="navbar-nav">
                <li class="menu-title"><span>Menu</span></li>
                <li class="nav-item">
                    <a class="nav-link menu-link @if (request()->routeIs('dashboard')) active @endif" href="{{ route('dashboard') }}">
                        <i class="ri-dashboard-2-line"></i> <span>Tablero</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link menu-link" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#modalTemporada">
                        <i class="ri-calendar-event-line"></i> <span>Temporada {{ $season ?? now()->year }}</span>
                    </a>
                </li>

                <li class="menu-title"><i class="ri-more-fill"></i> <span>Registro</span></li>
                <li class="nav-item">
                    <a class="nav-link menu-link" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#modalPleitoMovil">
                        <i class="ri-sword-line"></i> <span>Registrar Pleito</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link menu-link" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#modalPlayer">
                        <i class="ri-user-add-line"></i> <span>Registrar Player</span>
                    </a>
                </li>

                <li class="menu-title"><i class="ri-more-fill"></i> <span>Consultas</span></li>
                <li class="nav-item">
                    <a class="nav-link menu-link" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#modalReporteDia">
                        <i class="ri-file-list-3-line"></i> <span>Reporte del Dia</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link menu-link" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#modalVSDia">
                        <i class="ri-team-line"></i> <span>VS</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link menu-link" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#modalEstadisticas">
                        <i class="ri-bar-chart-2-line"></i> <span>Estadisticas</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link menu-link" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#modalRankingPuntos">
                        <i class="ri-trophy-line"></i> <span>Ranking por puntos</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <div class="sidebar-background"></div>
</div>
