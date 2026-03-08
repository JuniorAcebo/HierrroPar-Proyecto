<div id="layoutSidenav_nav">
    <nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
        <div class="sb-sidenav-menu">
            <div class="nav">
                <!-- Logo Compacto -->
                <div class="sb-sidenav-header">
                    <div class="d-flex align-items-center">
                        <div class="sb-sidenav-logo-icon">
                            <i class="fa-solid fa-cubes-stacked"></i>
                        </div>
                        <div class="ms-2">
                            <div class="fw-bold" style="font-size: 0.9rem;">Sistema</div>
                            <small class="text-muted" style="font-size: 0.7rem;">Admin</small>
                        </div>
                    </div>
                </div>

                    @can('ver-panel')

                        <div class="sb-sidenav-menu-heading">Inicio</div>

                        <a class="nav-link" href="#">
                            <div class="sb-nav-link-icon">
                                <i class="fa-solid fa-gauge-high"></i>
                            </div>
                            PanelHome
                        </a>
                        
                    @endcan

                <div class="sb-sidenav-menu-heading">Módulos</div>
 
                    <!-- PRODUCTOS -->
                    @can('ver-producto')
                        <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseProductos"
                            aria-expanded="false" aria-controls="collapseLayouts"
                            style="padding: 0.6rem 0.8rem; margin: 0.1rem 0.3rem;">
                            <div class="sb-nav-link-icon" style="font-size: 0.9rem;"><i class="fa-brands fa-shopify"></i></div>
                            Productos
                            <div class="sb-sidenav-collapse-arrow"><i class="fas fa-chevron-down"></i></div>
                            
                        </a>
                        <div class="collapse" id="collapseProductos" aria-labelledby="headingOne"
                            data-bs-parent="#sidenavAccordion">
                            <nav class="sb-sidenav-menu-nested nav" style="padding-left: 1rem;">
                                @can('ver-producto')
                                    <a class="nav-link" href="{{ route('productos.index') }}"
                                        style="padding: 0.5rem 0.8rem; font-size: 0.85rem;">
                                        <i class="fas fa-list me-1" style="font-size: 0.8rem;"></i>Lista
                                    </a>
                                @endcan
                                @can('ajustar-stock-producto')
                                    <a class="nav-link" href="{{ route('productos.createAjuste') }}"
                                        style="padding: 0.5rem 0.8rem; font-size: 0.85rem;">
                                        <i class="fas fa-plus me-1" style="font-size: 0.8rem;"></i>Añadir Ajuste
                                    </a>
                                    <a class="nav-link" href="{{ route('productos.historialAjustes') }}"
                                        style="padding: 0.5rem 0.8rem; font-size: 0.85rem;">
                                        <i class="fas fa-history me-1" style="font-size: 0.8rem;"></i>Lista de Ajustes
                                    </a>
                                @endcan
                            </nav>
                        </div>
                    @endcan

                    <!-- VENTAS -->
                    @can('ver-venta')
                        <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseVentas" aria-expanded="false">
                            <div class="sb-nav-link-icon"><i class="fa-solid fa-cart-shopping"></i></div>
                            Ventas
                            <div class="sb-sidenav-collapse-arrow"><i class="fas fa-chevron-down"></i></div>
                        </a>
                        <div class="collapse" id="collapseVentas" data-bs-parent="#sidenavAccordion">
                            <nav class="sb-sidenav-menu-nested nav">
                                @can('ver-venta')
                                    <a class="nav-link" href="#"><i class="fa-solid fa-list-ul me-1"></i>Lista</a>
                                @endcan
                                @can('crear-venta')
                                    <a class="nav-link" href="#"><i class="fa-solid fa-plus me-1"></i>Crear</a>
                                @endcan
                            </nav>
                        </div>
                    @endcan

                    @can('ver-traslado')
                        <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseTraslados" aria-expanded="false">
                            <div class="sb-nav-link-icon"><i class="fa-solid fa-exchange-alt"></i></div>
                            Traslados
                            <div class="sb-sidenav-collapse-arrow"><i class="fas fa-chevron-down"></i></div>
                        </a>
                        <div class="collapse" id="collapseTraslados" data-bs-parent="#sidenavAccordion">
                            <nav class="sb-sidenav-menu-nested nav">
                                @can('ver-traslado')
                                    <a class="nav-link" href="#"><i class="fa-solid fa-list-ul me-1"></i>Lista</a>
                                @endcan
                                @can('crear-traslado')
                                    <a class="nav-link" href="#"><i class="fa-solid fa-plus me-1"></i>Crear</a>
                                @endcan
                            </nav>
                        </div>
                    @endcan
                    

                    @can('ver-cliente')
                        <a class="nav-link" href="#">
                            <div class="sb-nav-link-icon"><i class="fa-solid fa-user-group"></i></div>
                            Clientes
                        </a>
                    @endcan
                    

                    @can('ver-almacen')
                        <a class="nav-link" href="#">
                            <div class="sb-nav-link-icon"><i class="fa-solid fa-warehouse"></i></div>
                            Almacenes
                        </a>
                    @endcan


                @if(auth()->user()->role && auth()->user()->role->name === 'Administrador')
                    <div class="sb-sidenav-menu-heading">Administración</div>
                @endif

                @can('ver-user')
                    <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseUser" aria-expanded="false">
                        <div class="sb-nav-link-icon"><i class="fa-solid fa-user-gear"></i></div>
                        Usuarios
                        <div class="sb-sidenav-collapse-arrow"><i class="fas fa-chevron-down"></i></div>
                        </a>
                        <div class="collapse" id="collapseUser" data-bs-parent="#sidenavAccordion">
                            <nav class="sb-sidenav-menu-nested nav">
                                @can('ver-user')
                                    <a class="nav-link" href="{{ route('users.index') }}"><i class="fa-solid fa-list-ul me-1"></i>Lista</a>
                                @endcan
                                @can('ver-role')
                                    <a class="nav-link" href="{{ route('roles.index') }}">
                                    <div class="sb-nav-link-icon"><i class="fa-solid fa-shield-halved"></i></div>
                                        Roles
                                    </a>
                                @endcan
                            </nav>
                        </div>
                    @endcan
                
            </div>
        </div>

        <div class="sb-sidenav-footer">
            <div class="small">Conectado:</div>
            <div class="d-flex align-items-center">
                <div class="user-avatar"><i class="fas fa-user-circle"></i></div>
                <div class="ms-2">
                    <strong>{{ auth()->user()->name }}</strong>
                    <div class="small text-muted">{{ auth()->user()->role->name ?? 'Sin Rol' }}</div>
                </div>
            </div>
        </div>
    </nav>
</div>

<style>
    .sb-sidenav {
        background: #1f2a38;
        color: #ecf0f1;
        border-right: 1px solid rgba(255, 255, 255, 0.1);
    }

    .sb-sidenav-header { padding: 1rem; }
    .sb-sidenav-logo-icon {
        color: #4fc1ff;
        background: rgba(79, 193, 255, 0.1);
        padding: 0.5rem;
        border-radius: 8px;
        width: 36px;
        height: 36px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .nav-link {
        color: #ecf0f1 !important;
        border-radius: 6px;
        transition: all 0.2s ease;
    }

    .nav-link:hover { background: rgba(79,193,255,0.2) !important; }

    .nav-link.active {
        background: linear-gradient(135deg,#4fc1ff,#1abc9c);
        box-shadow: 0 2px 8px rgba(79,193,255,0.3);
    }

    .sb-nav-link-icon { color: #4fc1ff; width: 20px; text-align: center; }
    .sb-sidenav-collapse-arrow { color: #7f8c8d; }
    .collapse.show .sb-sidenav-collapse-arrow { transform: rotate(180deg); }
    .sb-sidenav-menu-nested .nav-link { font-size: 0.82rem; border-left: 2px solid rgba(79,193,255,0.3); }
    .sb-sidenav-menu-nested .nav-link:hover { border-left-color: #4fc1ff; background: rgba(79,193,255,0.15); }
    .sb-sidenav-footer { background: rgba(0,0,0,0.2); padding: 0.8rem; font-size: 0.8rem; }
    .user-avatar { color: #4fc1ff; font-size: 1.2rem; }
</style>

