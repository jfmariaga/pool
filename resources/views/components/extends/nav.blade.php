<nav class="header-navbar navbar-expand-md navbar navbar-with-menu navbar-without-dd-arrow fixed-top navbar-semi-dark navbar-shadow nav__jota">
    <div class="navbar-wrapper">
        <div class="navbar-header bg_white">
            <ul class="nav navbar-nav flex-row h_100">
                <li class="nav-item mobile-menu d-md-none mr-auto">
                    <a class="nav-link nav-menu-main menu-toggle hidden-xs" href="#">
                        <i class="ft-menu font-large-1"></i>
                    </a>
                </li>
                <li class="nav-item h_100 logo_menu align_content">
                    <a class="" href="{{ route('dashboard') }}">
                        <center>
                            <h1 class="brand-logo">
                                <b>Black Pool</b>
                            </h1>
                        </center>
                        {{-- <img style="border-radius:8px;height:100%;" class="brand-logo" alt="modern admin logo h_100" src="{{ asset('img/logo_pool.png') }}"> --}}
                    </a>
                </li>
                <li class="nav-item d-md-none">
                    <a class="nav-link open-navbar-container" data-toggle="collapse" data-target="#navbar-mobile"><i
                            class="la la-ellipsis-v"></i></a>
                </li>
            </ul>
        </div>
        <div class="navbar-container content">
            <div class="collapse navbar-collapse" id="navbar-mobile">
                <ul class="nav navbar-nav mr-auto float-left">
                    <li class="nav-item d-none d-md-block">
                        <div class="pointer d-flex" id="change-dark-mode">
                            <i class="icon_dark_mode fa-solid fa-moon mr-0" style="font-size:20px; margin-top:3px;"></i>
                            <span class="mr-1 text_dark_mode" style="margin-top:5px; margin-left:3px;">Dark</span>
                        </div>
                    </li>
                </ul>
                <ul class="nav navbar-nav float-right">
                    <li class="dropdown dropdown-user nav-item">
                        <a class="dropdown-toggle nav-link dropdown-user-link" href="javascript:">
                            <span class="mr-1">Bienvenido, @yield('prueba')
                                <span class="user-name text-bold-700">{{ auth()->user()->name }}</span>
                            </span>
                            <span class="avatar avatar-online contenedor-img-main">
                                <img class="img-main" src="{{ asset('storage/avatars/'.( auth()->user()->picture ? auth()->user()->picture : 'default.png' )) }}"
                                    alt="avatar">
                            </span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</nav>
