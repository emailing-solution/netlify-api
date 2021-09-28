<nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
    <div class="sidebar-sticky pt-3">
        <ul class="nav flex-column">
            @can('is_admin')
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('users.index') }}">
                        <span data-feather="users"></span>
                        USERS
                    </a>
                </li>
            @endcan
            <li class="nav-item">
                <a class="nav-link" href="{{ route('accounts.index') }}">
                    <span data-feather="plus"></span>
                    NETLIFY
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('sites.index') }}">
                    <span data-feather="layers"></span>
                    SITES
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="{{ route('process.index') }}">
                    <span data-feather="code"></span>
                    MY PROCESS
                </a>
            </li>
        </ul>
    </div>
</nav>
