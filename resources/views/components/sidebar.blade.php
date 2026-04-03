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
    <li class="nav-item active">
        <a class="nav-link" href="/">
            <i class="fas fa-fw fa-tachometer-alt"></i>
            <span>Dashboard</span></a>
    </li>

    <hr class="sidebar-divider">

    <div class="sidebar-heading">
        Store Operations
    </div>

    <li class="nav-item">
        <a class="nav-link" href="{{ route('transactions.index') }}">
            <i class="fas fa-fw fa-receipt"></i>
            <span>All Transactions</span></a>
    </li>

    <li class="nav-item">
        <a class="nav-link" href="{{ route('transactions.import') }}">
            <i class="fas fa-fw fa-file-excel"></i>
            <span>Re-Import Dataset</span></a>
    </li>

    <hr class="sidebar-divider">

    <!-- Outdated Components below this are commented out, but retained for reference -->
    <!--
    <div class="sidebar-heading">
        Interface
    </div>

    <li class="nav-item active">
        <a class="nav-link" href="/payments">
            <i class="fas fa-fw fa-credit-card"></i>
            <span>Show Payments</span></a>
    </li>
    <li class="nav-item active">
        <a class="nav-link" href="/tools">
            <i class="fas fa-fw fa-table"></i>
            <span>Tools</span></a>
    </li>

    -->

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