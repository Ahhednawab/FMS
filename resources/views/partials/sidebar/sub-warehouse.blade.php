<div class="sidebar sidebar-dark sidebar-main sidebar-expand-lg">
    <!-- Sidebar content -->
    <div class="sidebar-content">

        <!-- User menu -->
        <div class="sidebar-section sidebar-user my-1">
            <div class="sidebar-section-body">
                <div class="media">
                    <a href="#" class="mr-3">
                        <img src={{ asset('assets/images/placeholders/placeholder.jpg') }} class="rounded-circle"
                            alt="">
                    </a>

                    <div class="media-body">
                        <div class="font-weight-semibold">{{ Auth::user()->name }}</div>
                        <div class="font-size-sm line-height-sm opacity-50">
                            {{ ucfirst(Auth::user()->role->name) }}
                        </div>
                    </div>

                    <div class="ml-3 align-self-center">
                        <button type="button"
                            class="btn btn-outline-light-100 text-white border-transparent btn-icon rounded-pill btn-sm sidebar-control sidebar-main-resize d-none d-lg-inline-flex">
                            <i class="icon-transmission"></i>
                        </button>

                        <button type="button"
                            class="btn btn-outline-light-100 text-white border-transparent btn-icon rounded-pill btn-sm sidebar-mobile-main-toggle d-lg-none">
                            <i class="icon-cross2"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <!-- /user menu -->


        <!-- Main navigation -->
        <div class="sidebar-section">
            <ul class="nav nav-sidebar" data-nav-type="accordion">

                <li class="nav-item">
                    <a href="{{ route(auth()->user()->role->slug . '.index') }}"
                        class="nav-link {{ request()->routeIs(auth()->user()->role->slug . '.index') ? 'active' : '' }}">
                        <i class="icon-home4"></i>
                        <span>Dashboard</span>
                    </a>
                </li>

                <li
                    class="nav-item nav-item-submenu 
                    {{ request()->routeIs('master-warehouse.inventoryWarehouses.*') ||
                    request()->routeIs('master-warehouse.master_warehouse_inventory.*') ||
                    request()->routeIs('master-warehouse.purchases.*') ||
                    request()->routeIs('master-warehouse.suppliers.*') ||
                    request()->routeIs('master-warehouse.master_warehouse_inventory.index')
                        ? 'nav-item-open'
                        : '' }}">

                    <a href="#" class="nav-link"><i class="icon-copy"></i> <span>Inventory Management</span></a>

                    <ul class="nav nav-group-sub" data-submenu-title="Inventory"
                        style="{{ request()->routeIs('master-warehouse.purchases.*') ||
                        request()->routeIs('master-warehouse.assigned_inventory.*') ||
                        request()->routeIs('master-warehouse.suppliers.*') ||
                        request()->routeIs('master-warehouse.master_warehouse_inventory.index')
                            ? 'display:block;'
                            : '' }}">




                        <li class="nav-item">
                            <a href="{{ route('sub-warehouse.assigned_inventory.index') }}"
                                class="nav-link {{ request()->routeIs('sub-warehouse.assigned_inventory.*') ? 'active' : '' }}">
                                Assigned Inventory
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="{{ route('sub-warehouse.master_warehouse_inventory.request_inventory') }}"
                                class="nav-link {{ request()->routeIs('sub-warehouse.master_warehouse_inventory.*') ? 'active' : '' }}">
                                Request Inventory
                            </a>
                        </li>


                        <li class="nav-item">
                            <a href="{{ route('master-warehouse.purchases.index') }}"
                                class="nav-link {{ request()->routeIs('master-warehouse.purchases.*') ? 'active' : '' }}">
                                Maintenance (work in progress)
                                {{-- link change karna hai  --}}
                            </a>
                        </li>

                    </ul>
                </li>


            </ul>
        </div>
        <!-- /main navigation -->
    </div>
    <!-- /sidebar content -->
</div>
<!-- /main sidebar -->
