<!DOCTYPE html>
<html lang="en">

<head>

    @extends('layouts.head')

</head>

<body id="page-top">

    <!-- Page Wrapper -->
    <div id="wrapper">

        <!-- Sidebar -->
        <x-sidebar></x-sidebar>
        <!-- End of Sidebar -->

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">

            <!-- Main Content -->
            <div id="content">

                <!-- Topbar -->
                <x-topbar></x-topbar>
                <!-- End of Topbar -->

                <!-- Begin Page Content -->
                <div class="container-fluid">

                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        </div>
                    @endif

                    <!-- Page Heading -->
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Customers</h1>
                        <button class="btn btn-primary btn-sm shadow-sm" type="button" data-toggle="modal"
                            data-target="#newcustomerModal"><i class="fas fa-plus fa-sm text-white-50 mr-1"></i>Add New Customer</button>
                    </div>

                    <!-- Search & Filter Bar -->
                    <div class="card shadow mb-4">
                        <div class="card-body py-3">
                            <form method="GET" action="{{ route('customers.index') }}" class="row align-items-end g-2">
                                <div class="col-md-5 mb-2 mb-md-0">
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-white"><i class="fas fa-search fa-sm text-gray-400"></i></span>
                                        </div>
                                        <input type="text" class="form-control border-left-0" name="search" placeholder="Search name, email, company, phone..."
                                            value="{{ request('search') }}">
                                    </div>
                                </div>
                                <div class="col-md-3 mb-2 mb-md-0">
                                    <select class="form-control" name="tag">
                                        <option value="">All Tags</option>
                                        @foreach ($allTags as $tag)
                                            <option value="{{ $tag->id }}" {{ request('tag') == $tag->id ? 'selected' : '' }}>
                                                {{ $tag->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2 mb-2 mb-md-0">
                                    <button type="submit" class="btn btn-primary btn-block">
                                        <i class="fas fa-filter mr-1"></i>Filter
                                    </button>
                                </div>
                                @if(request('search') || request('tag'))
                                <div class="col-md-2 mb-2 mb-md-0">
                                    <a href="{{ route('customers.index') }}" class="btn btn-outline-secondary btn-block">Clear</a>
                                </div>
                                @endif
                            </form>
                        </div>
                    </div>

                    <!-- Customers Table -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 d-flex justify-content-between align-items-center">
                            <h6 class="m-0 font-weight-bold text-primary">
                                {{ $customers->total() }} customer{{ $customers->total() !== 1 ? 's' : '' }}
                                @if(request('search')) matching "{{ request('search') }}" @endif
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Company</th>
                                            <th>Phone</th>
                                            <th>Email</th>
                                            <th>Tags</th>
                                            <th style="width:100px">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    @forelse ($customers as $b)
                                        <tr>
                                            <td>
                                                <a href="/customer_edit/{{ $b->id }}" class="font-weight-bold">{{ $b->fname }} {{ $b->lname }}</a>
                                            </td>
                                            <td>{{ $b->company ?? '-' }}</td>
                                            <td>{{ $b->phone ?? '-' }}</td>
                                            <td>{{ $b->email }}</td>
                                            <td>
                                                @foreach ($b->tags as $tag)
                                                    <span class="badge" style="background-color: {{ $tag->color }}; color: #fff; font-size: 0.7rem;">{{ $tag->name }}</span>
                                                @endforeach
                                            </td>
                                            <td>
                                                <a href="/customer_edit/{{ $b->id }}" class="btn btn-info btn-sm" title="Edit"><i class="fas fa-edit"></i></a>
                                                <form action="{{ route('customer.delete', $b->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this customer?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-sm" title="Delete"><i class="fas fa-trash"></i></button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center text-gray-500 py-4">
                                                <i class="fas fa-user-slash fa-2x mb-2 text-gray-300 d-block"></i>
                                                No customers found
                                            </td>
                                        </tr>
                                    @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination -->
                            <div class="d-flex justify-content-center mt-3">
                                {{ $customers->links() }}
                            </div>
                        </div>
                    </div>

                </div>
                <!-- /.container-fluid -->

            </div>
            <!-- End of Main Content -->

            <!-- Footer -->
            <x-footer></x-footer>
            <!-- End of Footer -->

        </div>
        <!-- End of Content Wrapper -->

    </div>
    <!-- End of Page Wrapper -->

    <!-- Scroll to Top Button-->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <!-- Add New Customer Modal -->
    <div class="modal" id="newcustomerModal">
        <div class="modal-dialog">
            <div class="modal-content">

                <!-- Modal Header -->
                <div class="modal-header">
                    <h5 class="modal-title">Add New Customer</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>

                <!-- Modal body -->
                <div class="modal-body">
                    <form action="/customer_add" method="post">
                        {{ csrf_field() }}
                        <!-- Form Row-->
                        <div class="row gx-3 mb-3">
                            <!-- Form Group (first name)-->
                            <div class="col-md-6">
                                <label class="small mb-1" for="inputFirstName">First name</label>
                                <input class="form-control" id="fname" name="fname" type="text"
                                    placeholder="Enter first name" required>
                            </div>
                            <!-- Form Group (last name)-->
                            <div class="col-md-6">
                                <label class="small mb-1" for="inputLastName">Last name</label>
                                <input class="form-control" id="lname" name="lname" type="text"
                                    placeholder="Enter last name" required>
                            </div>
                        </div>
                        <div class="row gx-3 mb-3">
                            <div class="col-md-12">
                                <label class="small mb-1" for="company">Company</label>
                                <input class="form-control" id="company" name="company" type="text"
                                    placeholder="Company name">
                            </div>
                        </div>
                        <div class="row gx-3 mb-3">
                            <div class="col-md-12">
                                <label class="small mb-1" for="address">Address</label>
                                <input class="form-control" id="address" name="address" type="text"
                                    placeholder="Address">
                            </div>
                        </div>
                        <div class="row gx-3 mb-3">
                            <div class="col-md-6">
                                <label class="small mb-1" for="phone">Phone</label>
                                <input class="form-control" id="phone" name="phone" type="tel"
                                    placeholder="Phone number">
                            </div>
                            <div class="col-md-6">
                                <label class="small mb-1" for="email">Email</label>
                                <input class="form-control" id="email" name="email" type="email"
                                    placeholder="Email address" required>
                            </div>
                        </div>
                        <button class="btn btn-primary" type="submit">Save Customer</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <x-main_scripts></x-main_scripts>
</body>

</html>
