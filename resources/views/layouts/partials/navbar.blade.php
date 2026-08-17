<!-- Navbar -->
<nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <!-- Left navbar links -->
    <ul class="navbar-nav">
        <li class="nav-item">
            <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
        </li>
        <li class="nav-item d-none d-sm-inline-block">
            <a href="{{ route('home') }}" class="nav-link">Home</a>
        </li>
        @auth
            @if(auth()->user()->isAdmin())
                <li class="nav-item d-none d-sm-inline-block">
                    <a href="{{ route('admin.dashboard') }}" class="nav-link">Admin Dashboard</a>
                </li>
            @else
                <li class="nav-item d-none d-sm-inline-block">
                    <a href="{{ route('passenger.dashboard') }}" class="nav-link">My Dashboard</a>
                </li>
            @endif
        @endauth
    </ul>

    <!-- Right navbar links -->
    <ul class="navbar-nav ml-auto">
        @auth
            <li class="nav-item dropdown user-menu">
                <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown">
                    <i class="fas fa-user-circle mr-1"></i>
                    <span class="d-none d-md-inline">{{ auth()->user()->name }} ({{ ucfirst(auth()->user()->role) }})</span>
                </a>
                <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                    <!-- User image -->
                    <li class="user-header bg-primary">
                        <i class="fas fa-user-circle fa-4x text-white mb-2"></i>
                        <p>
                            {{ auth()->user()->name }}
                            <small>{{ auth()->user()->email }}</small>
                            <span class="badge badge-light mt-1">{{ ucfirst(auth()->user()->role) }}</span>
                        </p>
                    </li>
                    <!-- Menu Footer-->
                    <li class="user-footer">
                        <form method="POST" action="{{ route('logout') }}" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-default btn-flat float-right text-danger">
                                <i class="fas fa-sign-out-alt mr-1"></i> Log Out
                            </button>
                        </form>
                    </li>
                </ul>
            </li>
        @else
            <li class="nav-item">
                <a href="{{ route('login') }}" class="nav-link">
                    <i class="fas fa-sign-in-alt mr-1"></i> Login
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('register') }}" class="nav-link">
                    <i class="fas fa-user-plus mr-1"></i> Register
                </a>
            </li>
        @endauth
        <li class="nav-item">
            <a class="nav-link" data-widget="fullscreen" href="#" role="button">
                <i class="fas fa-expand-arrows-alt"></i>
            </a>
        </li>
    </ul>
</nav>
<!-- /.navbar -->
