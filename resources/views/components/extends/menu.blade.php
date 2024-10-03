<div class="menu__jota">
    <!-- menú de navegación -->
    <div class="main-menu menu-fixed menu-dark menu-accordion menu-shadow" data-scroll-to-active="true">
        <div class="main-menu-content">
            <ul class="navigation navigation-main" id="main-menu-navigation" data-menu="menu-navigation">
                <li class="nav-item {{ Route::is('dashboard') ? 'active' : '' }}">
                    <a href="{{ route('dashboard') }}" onclick="blockPage()"><i class="fa-solid fa-gauge"></i>
                        <span class="menu-title" data-i18n="nav.support_documentation.main">Dashboard</span>
                    </a>
                </li>
                <li class="nav-item {{ Route::is('categorias') ? 'active' : '' }}">
                    <a href="{{ route('categorias') }}" onclick="blockPage()"><i class="fa-solid fa-list"></i>
                        <span class="menu-title" data-i18n="nav.support_documentation.main">Categorías</span>
                    </a>
                </li>
                <li class="nav-item {{ Route::is('compras') ? 'active' : '' }}">
                    <a href="{{ route('compras') }}" onclick="blockPage()"><i class="fa-solid fa-cart-shopping"></i>
                        <span class="menu-title" data-i18n="nav.support_documentation.main">Compras</span>
                    </a>
                </li>
                <li class="nav-item {{ Route::is('productos') ? 'active' : '' }}">
                    <a href="{{ route('productos') }}" onclick="blockPage()"><i
                            class="fa-duotone fa-solid fa-layer-group"></i>
                        <span class="menu-title" data-i18n="nav.support_documentation.main">Productos</span>
                    </a>
                </li>
                <li class="nav-item {{ Route::is('proveedores') ? 'active' : '' }}">
                    <a href="{{ route('proveedores') }}" onclick="blockPage()"><i
                            class="fa-sharp fa-solid fa-handshake"></i>
                        <span class="menu-title" data-i18n="nav.support_documentation.main">Proveedores</span>
                    </a>
                </li>
                <!-- Contabilidad con submenú -->
                <li class="nav-item has-sub">
                    <a href="#"><i class="fa-solid fa-calculator"></i>
                        <span class="menu-title" data-i18n="nav.support_documentation.main">Contabilidad</span>
                    </a>
                    <ul class="menu-content">
                        <li class="{{ Route::is('cuentas') ? 'active' : '' }}">
                            <a href="{{ route('cuentas') }}" onclick="blockPage()">
                                <i class="fa-solid fa-wallet"></i>
                                <span class="menu-item" data-i18n="nav.support_documentation.main">Cuentas</span>
                            </a>
                        </li>
                        <li class="{{ Route::is('movimientos') ? 'active' : '' }}">
                            <a href="{{ route('movimientos') }}" onclick="blockPage()">
                                <i class="fa-solid fa-exchange-alt"></i>
                                <span class="menu-item" data-i18n="nav.support_documentation.main">Movimientos</span>
                            </a>
                        </li>
                    </ul>
                </li>
                <li class="nav-item {{ Route::is('usuarios') ? 'active' : '' }}">
                    <a href="{{ route('usuarios') }}" onclick="blockPage()"><i class="fa-solid fa-users"></i>
                        <span class="menu-title" data-i18n="nav.support_documentation.main">Usuarios</span>
                    </a>
                </li>
                <li class="nav-item ">
                    <a href="javascript:" onclick="$('#submitLogout').submit()"><i
                            class="la la-power-off c_red"></i>
                        <span class="menu-title c_red" data-i18n="nav.support_documentation.main">Cerrar sesión</span>
                    </a>
                    <form id="submitLogout" action="{{ url('logout') }}" method="POST">
                        @csrf
                    </form>
                </li>
            </ul>
        </div>
    </div>
</div>
