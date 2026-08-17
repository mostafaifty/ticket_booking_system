<!-- Main Sidebar Container -->
<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="{{ route('home') }}" class="brand-link">
        <i class="fas fa-train brand-image ml-3 mt-1 text-info"></i>
        <span class="brand-text font-weight-light font-weight-bold ml-2">Railway Booking</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Sidebar user panel -->
        @auth
            <div class="user-panel mt-3 pb-3 mb-3 d-flex align-items-center">
                <div class="image text-white ml-3">
                    <i class="fas fa-user-circle fa-2x"></i>
                </div>
                <div class="info">
                    <a href="#" class="d-block text-truncate" style="max-width: 160px;">{{ auth()->user()->name }}</a>
                    <small class="badge badge-info">{{ ucfirst(auth()->user()->role) }}</small>
                </div>
            </div>
        @else
            <div class="user-panel mt-3 pb-3 mb-3 d-flex align-items-center">
                <div class="image text-white ml-3">
                    <i class="fas fa-user-circle fa-2x"></i>
                </div>
                <div class="info">
                    <span class="d-block text-white-50">Guest User</span>
                    <a href="{{ route('login') }}" class="text-sm text-info">Login here</a>
                </div>
            </div>
        @endauth

        <!-- Sidebar Menu -->
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                
                <!-- General Navigation -->
                <li class="nav-item">
                    <a href="{{ route('home') }}" class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-home"></i>
                        <p>Home</p>
                    </a>
                </li>

                @auth
                    @if(auth()->user()->isAdmin())
                        <!-- ADMIN MENU -->
                        <li class="nav-header">ADMINISTRATION</li>
                        
                        <li class="nav-item">
                            <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-tachometer-alt"></i>
                                <p>Admin Dashboard</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="#" class="nav-link disabled text-muted">
                                <i class="nav-icon fas fa-subway"></i>
                                <p>Trains (Inc. 3)</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="#" class="nav-link disabled text-muted">
                                <i class="nav-icon fas fa-map-marker-alt"></i>
                                <p>Stations (Inc. 3)</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="#" class="nav-link disabled text-muted">
                                <i class="nav-icon fas fa-calendar-alt"></i>
                                <p>Schedules (Inc. 4)</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="#" class="nav-link disabled text-muted">
                                <i class="nav-icon fas fa-ticket-alt"></i>
                                <p>All Bookings (Inc. 8)</p>
                            </a>
                        </li>
                    @else
                        <!-- PASSENGER MENU -->
                        <li class="nav-header">PASSENGER SERVICES</li>

                        <li class="nav-item">
                            <a href="{{ route('passenger.dashboard') }}" class="nav-link {{ request()->routeIs('passenger.dashboard') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-tachometer-alt"></i>
                                <p>My Dashboard</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="#" class="nav-link disabled text-muted">
                                <i class="nav-icon fas fa-search"></i>
                                <p>Search Trains (Inc. 5)</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="#" class="nav-link disabled text-muted">
                                <i class="nav-icon fas fa-clipboard-list"></i>
                                <p>My Bookings (Inc. 8)</p>
                            </a>
                        </li>
                    @endif

                    <li class="nav-header">ACCOUNT</li>
                    <li class="nav-item">
                        <form method="POST" action="{{ route('logout') }}" id="sidebar-logout-form">
                            @csrf
                            <a href="#" onclick="event.preventDefault(); document.getElementById('sidebar-logout-form').submit();" class="nav-link text-danger">
                                <i class="nav-icon fas fa-sign-out-alt"></i>
                                <p>Logout</p>
                            </a>
                        </form>
                    </li>
                @else
                    <li class="nav-header">AUTHENTICATION</li>
                    <li class="nav-item">
                        <a href="{{ route('login') }}" class="nav-link {{ request()->routeIs('login') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-sign-in-alt"></i>
                            <p>Login</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('register') }}" class="nav-link {{ request()->routeIs('register') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-user-plus"></i>
                            <p>Register</p>
                        </a>
                    </li>
                @endauth
            </ul>
        </nav>
        <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
</aside>
