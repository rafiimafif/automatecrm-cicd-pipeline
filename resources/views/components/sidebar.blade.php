<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

    <!-- Sidebar - Brand -->
    <a class="sidebar-brand d-flex align-items-center justify-content-center" href="/"
        style="height: 5rem; padding: 0 1rem; background: transparent;">
        <img src="/img/logo.svg?v={{ time() }}" alt="automateCRM"
            style="max-width: 100%; max-height: 80%; width: auto; object-fit: contain;">
    </a>

    <!-- Divider -->
    <hr class="sidebar-divider my-0">

    <!-- Nav Item - Dashboard -->
    <li class="nav-item {{ request()->is('/') ? 'active' : '' }}">
        <a class="nav-link" href="/">
            <i class="fas fa-fw fa-tachometer-alt"></i>
            <span>Dashboard</span></a>
    </li>

    <hr class="sidebar-divider">

    <div class="sidebar-heading">
        CRM
    </div>

    <li class="nav-item {{ request()->is('customers*') || request()->is('customer_edit*') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('customers.index') }}">
            <i class="fas fa-fw fa-users"></i>
            <span>Customers</span></a>
    </li>

    <li class="nav-item {{ request()->is('deals*') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('deals.index') }}">
            <i class="fas fa-fw fa-handshake"></i>
            <span>Deals Pipeline</span></a>
    </li>

    <li class="nav-item {{ request()->is('tasks*') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('tasks.index') }}">
            <i class="fas fa-fw fa-tasks"></i>
            <span>Tasks</span></a>
    </li>

    <hr class="sidebar-divider">

    <div class="sidebar-heading">
        Store Operations
    </div>

    <li class="nav-item {{ request()->is('transactions') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('transactions.index') }}">
            <i class="fas fa-fw fa-receipt"></i>
            <span>All Transactions</span></a>
    </li>

    <li class="nav-item {{ request()->is('import-transactions') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('transactions.import') }}">
            <i class="fas fa-fw fa-file-excel"></i>
            <span>Re-Import Dataset</span></a>
    </li>

    <hr class="sidebar-divider">

    <div class="sidebar-heading">
        Management
    </div>

    <li class="nav-item {{ request()->is('services') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('services.index') }}">
            <i class="fas fa-fw fa-cogs"></i>
            <span>Services</span></a>
    </li>

    <li class="nav-item {{ request()->is('payments') ? 'active' : '' }}">
        <a class="nav-link" href="/payments">
            <i class="fas fa-fw fa-credit-card"></i>
            <span>Payments</span></a>
    </li>

    <li class="nav-item {{ request()->is('tags') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('tags.index') }}">
            <i class="fas fa-fw fa-tags"></i>
            <span>Tags</span></a>
    </li>

    <li class="nav-item {{ request()->is('tools') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('tools.show') }}">
            <i class="fas fa-fw fa-wrench"></i>
            <span>Tools</span></a>
    </li>

    <li class="nav-item {{ request()->is('activity-log') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('activity.log') }}">
            <i class="fas fa-fw fa-history"></i>
            <span>Activity Log</span></a>
    </li>

    <!-- Divider -->
    <hr class="sidebar-divider d-none d-md-block">

    <!-- Sidebar Toggler (Sidebar) -->
    <div class="text-center d-none d-md-inline">
        <button class="rounded-circle border-0" id="sidebarToggle"></button>
    </div>

    <!-- Sidebar Message -->
    <div class="sidebar-card d-none d-lg-flex">
        <img class="sidebar-card-illustration mb-2" src="img/undraw_rocket.svg" alt="...">
        <p class="text-center mb-2"><strong>automateCRM</strong> is a CRM system for managing customers, services, and
            payments. Built by Rafii Muhammad Afif.</p>
        <a class="btn btn-success btn-sm" href="https://github.com/rafiimafif/automatecrm-cicd-pipeline">View on GitHub</a>
    </div>

</ul>