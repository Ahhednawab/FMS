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
                    <a href="{{ route('admin.index') }}"
                        class="nav-link {{ request()->routeIs('admin.index') ? 'active' : '' }}">
                        <i class="icon-home4"></i>
                        <span>Dashboard</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="{{ route('admin.roles.index') }}"
                        class="nav-link {{ request()->routeIs('admin.roles.*') ? 'active' : '' }}">
                        <i class="icon-home4"></i>
                        <span>Roles and Permissions</span></a>
                </li>
                @if (auth()->user()->hasPermission('drafts'))
                    <li class="nav-item">
                        <a href="{{ route('drafts.index') }}"
                            class="nav-link {{ request()->routeIs('drafts.*') ? 'active' : '' }}">
                            <i class="icon-copy"></i>
                            <span>Drafts</span>
                        </a>
                    </li>
                @endif

                <li
                    class="nav-item nav-item-submenu
          {{ request()->routeIs('salaries.*') || request()->routeIs('vehicleAttendances.*') ? 'nav-item-open' : '' }}">

                    <a href="#" class="nav-link"><i class="icon-copy"></i> <span>Accounts</span></a>

                    <ul class="nav nav-group-sub" data-submenu-title="Layouts"
                        style="{{ request()->routeIs('salaries.*') || request()->routeIs('vehicleAttendances.*') ? 'display:block;' : '' }}">
                        @if (auth()->user()->hasPermission('driver_attendances'))
                            <li class="nav-item"><a href="{{ route('salaries.index') }}"
                                    class="nav-link {{ request()->routeIs('salaries .*') ? 'active' : '' }}">Salaries</a>
                            </li>
                        @endif
                        @if (auth()->user()->hasPermission('driver_attendances'))
                            <li class="nav-item">
                                <a href="{{ route('advance.index') }}"
                                    class="nav-link {{ request()->routeIs('advance.*') ? 'active' : '' }}">
                                    Issued Advance
                                </a>
                            </li>
                        @endif

                        @if (auth()->user()->hasPermission('vehicle_attendances'))
                            <li class="nav-item"><a href="{{ route('vehicleAttendances.index') }}"
                                    class="nav-link {{ request()->routeIs('vehicleAttendances.*') ? 'active' : '' }}">Invoice</a>
                            </li>
                        @endif
                    </ul>
                </li>


                @if (auth()->user()->hasPermission('users') ||
                        auth()->user()->hasPermission('cities') ||
                        auth()->user()->hasPermission('stations') ||
                        auth()->user()->hasPermission('ibc_centers'))

                    <li
                        class="nav-item nav-item-submenu 
          {{ request()->routeIs('users.*') ||
          request()->routeIs('cities.*') ||
          request()->routeIs('stations.*') ||
          request()->routeIs('ibcCenters.*') ||
          request()->routeIs('warehouses.*')
              ? 'nav-item-open'
              : '' }}">

                        <a href="#" class="nav-link"><i class="icon-copy"></i><span>General</span></a>

                        <ul class="nav nav-group-sub" data-submenu-title="Layouts"
                            style="{{ request()->routeIs('users.*') ||
                            request()->routeIs('cities.*') ||
                            request()->routeIs('stations.*') ||
                            request()->routeIs('ibcCenters.*')
                                ? 'display:block;'
                                : '' }}">

                            @if (auth()->user()->hasPermission('users'))
                                <li class="nav-item">
                                    <a href="{{ route('users.index') }}"
                                        class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}">User
                                        Management</a>
                                </li>
                            @endif
                            @if (auth()->user()->hasPermission('cities'))
                                <li class="nav-item">
                                    <a href="{{ route('cities.index') }}"
                                        class="nav-link {{ request()->routeIs('cities.*') ? 'active' : '' }}">City
                                        Management</a>
                                </li>
                            @endif
                            @if (auth()->user()->hasPermission('stations'))
                                <li class="nav-item">
                                    <a href="{{ route('stations.index') }}"
                                        class="nav-link {{ request()->routeIs('stations.*') ? 'active' : '' }}">Station
                                        Management</a>
                                </li>
                            @endif
                            @if (auth()->user()->hasPermission('ibc_centers'))
                                <li class="nav-item">
                                    <a href="{{ route('ibcCenters.index') }}"
                                        class="nav-link {{ request()->routeIs('ibcCenters.*') ? 'active' : '' }}">IBC
                                        Center Management</a>
                                </li>
                            @endif
                            {{-- <li class="nav-item">
              <a href="{{ route('admin.warehouses.index')}}" class="nav-link {{ request()->routeIs('admin.warehouses.*') ? 'active' : '' }}">Warehouse Management</a>
            </li> --}}
                        </ul>
                    </li>
                @endif
                @if (auth()->user()->hasPermission('vehicles') ||
                        auth()->user()->hasPermission('drivers') ||
                        auth()->user()->hasPermission('vendors') ||
                        auth()->user()->hasPermission('insurance_companies'))
                    <li
                        class="nav-item nav-item-submenu 
          {{ request()->routeIs('vehicles.*') || request()->routeIs('drivers.*') || request()->routeIs('vendors.*')
              ? 'nav-item-open'
              : '' }}">

                        <a href="" class="nav-link"><i class="icon-copy"></i> <span>Master Data</span></a>

                        <ul class="nav nav-group-sub" data-submenu-title="Layouts"
                            style="{{ request()->routeIs('vehicles.*') ||
                            request()->routeIs('drivers.*') ||
                            request()->routeIs('vendors.*') ||
                            request()->routeIs('insurance-companies.*')
                                ? 'display:block;'
                                : '' }}">

                            @if (auth()->user()->hasPermission('vehicles'))
                                <li class="nav-item">
                                    <a href="{{ route('vehicles.index') }}"
                                        class="nav-link {{ request()->routeIs('vehicles.*') ? 'active' : '' }}">Vehicle
                                        Master Data</a>
                                </li>
                            @endif
                            @if (auth()->user()->hasPermission('drivers'))
                                <li class="nav-item">
                                    <a href="{{ route('drivers.index') }}"
                                        class="nav-link {{ request()->routeIs('drivers.*') ? 'active' : '' }}">Driver
                                        Master Data</a>
                                </li>
                            @endif
                            @if (auth()->user()->hasPermission('vendors'))
                                <li class="nav-item">
                                    <a href="{{ route('vendors.index') }}"
                                        class="nav-link {{ request()->routeIs('vendors.*') ? 'active' : '' }}">Vendor
                                        Management</a>
                                </li>
                            @endif
                            @if (auth()->user()->hasPermission('insurance_companies'))
                                <li class="nav-item">
                                    <a href="{{ route('insurance-companies.index') }}"
                                        class="nav-link {{ request()->routeIs('insurance-companies.*') ? 'active' : '' }}">Insurance
                                        Company Management</a>
                                </li>
                            @endif
                        </ul>
                    </li>
                @endif
                @if (auth()->user()->hasPermission('brands') || auth()->user()->hasPermission('categories'))
                    <li
                        class="nav-item nav-item-submenu 
          {{ request()->routeIs('brands.*') || request()->routeIs('categories.*') || request()->routeIs('productList.*')
              ? 'nav-item-open'
              : '' }}">
                        <a href="#" class="nav-link"><i class="icon-copy"></i> <span>Product Management</span></a>

                        <ul class="nav nav-group-sub" data-submenu-title="Layouts"
                            style="{{ request()->routeIs('brands.*') || request()->routeIs('categories.*') ? 'display:block;' : '' }}">
                            @if (auth()->user()->hasPermission('brands'))
                                <li class="nav-item"><a href="{{ route('brands.index') }}"
                                        class="nav-link {{ request()->routeIs('brands.*') ? 'active' : '' }}">Brand
                                        Management</a></li>
                            @endif
                            @if (auth()->user()->hasPermission('categories'))
                                <li class="nav-item"><a href="{{ route('categories.index') }}"
                                        class="nav-link {{ request()->routeIs('categories.*') ? 'active' : '' }}">Category
                                        Management</a></li>
                            @endif
                            {{-- <li class="nav-item"><a href="{{ route('admin.productList.index')}}" class="nav-link {{ request()->routeIs('admin.productList.*') ? 'active' : '' }}">Product Management</a></li> --}}
                        </ul>
                    </li>
                @endif
                @if (auth()->user()->hasPermission('daily_mileages') ||
                        auth()->user()->hasPermission('daily_mileage_reports') ||
                        auth()->user()->hasPermission('daily_fuels') ||
                        auth()->user()->hasPermission('daily_fuel_reports'))
                    <li
                        class="nav-item nav-item-submenu 
          {{ request()->routeIs('dailyMileages.*') ||
          request()->routeIs('dailyMileageReports.*') ||
          request()->routeIs('dailyFuels.*') ||
          request()->routeIs('dailyFuelReports.*')
              ? 'nav-item-open'
              : '' }}">
                        <a href="#" class="nav-link"><i class="icon-copy"></i> <span>Fleet
                                Transaction</span></a>

                        <ul class="nav nav-group-sub" data-submenu-title="Layouts"
                            style="{{ request()->routeIs('dailyMileages.*') ||
                            request()->routeIs('dailyMileageReports.*') ||
                            request()->routeIs('dailyFuels.*') ||
                            request()->routeIs('dailyFuelReports.*')
                                ? 'display:block;'
                                : '' }}">

                            @if (auth()->user()->hasPermission('daily_mileages'))
                                <li class="nav-item"><a href="{{ route('dailyMileages.index') }}"
                                        class="nav-link {{ request()->routeIs('dailyMileages.*') ? 'active' : '' }}">Daily
                                        Mileage</a></li>
                            @endif
                            @if (auth()->user()->hasPermission('daily_mileage_reports'))
                                <li class="nav-item"><a href="{{ route('dailyMileageReports.index') }}"
                                        class="nav-link {{ request()->routeIs('dailyMileageReports.*') ? 'active' : '' }}">Mileage
                                        Report</a></li>
                            @endif
                            @if (auth()->user()->hasPermission('daily_fuels'))
                                <li class="nav-item"><a href="{{ route('dailyFuels.index') }}"
                                        class="nav-link {{ request()->routeIs('dailyFuels.*') ? 'active' : '' }}">Daily
                                        Fueling</a></li>
                            @endif
                            @if (auth()->user()->hasPermission('daily_fuel_reports'))
                                <li class="nav-item"><a href="{{ route('dailyFuelReports.index') }}"
                                        class="nav-link {{ request()->routeIs('dailyFuelReports.*') ? 'active' : '' }}">Fuel
                                        Report</a></li>
                            @endif
                        </ul>
                    </li>
                @endif
                @if (auth()->user()->hasPermission('warehouses') ||
                        auth()->user()->hasPermission('assigned_inventory') ||
                        auth()->user()->hasPermission('products') ||
                        auth()->user()->hasPermission('master_warehouse_inventory') ||
                        auth()->user()->hasPermission('purchases') ||
                        auth()->user()->hasPermission('suppliers') ||
                        auth()->user()->hasPermission('issues'))
                    <li
                        class="nav-item nav-item-submenu 
    {{ request()->routeIs('products.*') ||
    request()->routeIs('inventoryWarehouses.*') ||
    request()->routeIs('master_warehouse_inventory.*') ||
    request()->routeIs('purchases.*') ||
    request()->routeIs('warehouses.*') ||
    request()->routeIs('suppliers.*')
        ? 'nav-item-open'
        : '' }}">

                        <a href="#" class="nav-link"><i class="icon-copy"></i> <span>Inventory
                                Management</span></a>

                        <ul class="nav nav-group-sub" data-submenu-title="Inventory"
                            style="{{ request()->routeIs('master_warehouse_inventory.*') ||
                            request()->routeIs('purchases.*') ||
                            request()->routeIs('warehouses.*') ||
                            request()->routeIs('assigned_inventory.*') ||
                            request()->routeIs('productList.*') ||
                            request()->routeIs('suppliers.*') ||
                            request()->routeIs('issues.*')
                                ? 'display:block;'
                                : '' }}">

                            @if (auth()->user()->hasPermission('warehouses'))
                                <li class="nav-item">
                                    <a href="{{ route('warehouses.index') }}"
                                        class="nav-link {{ request()->routeIs('warehouses.*') ? 'active' : '' }}">
                                        Warehouse Management
                                    </a>
                                </li>
                            @endif
                            @if (auth()->user()->hasPermission('assigned_inventory'))
                                <li class="nav-item">
                                    <a href="{{ route('assigned_inventory.index') }}"
                                        class="nav-link {{ request()->routeIs('assigned_inventory.*') ? 'active' : '' }}">
                                        Assigned Inventory
                                    </a>
                                </li>
                            @endif
                            @if (auth()->user()->hasPermission('products'))
                                <li class="nav-item">
                                    <a href="{{ route('productList.index') }}"
                                        class="nav-link {{ request()->routeIs('productList.*') ? 'active' : '' }}">
                                        Products List
                                    </a>
                                </li>
                            @endif
                            @if (auth()->user()->hasPermission('master_warehouse_inventory'))
                                <li class="nav-item">
                                    <a href="{{ route('master_warehouse_inventory.index') }}"
                                        class="nav-link {{ request()->routeIs('master_warehouse_inventory.*') ? 'active' : '' }}">
                                        Master Warehouse Inventory
                                    </a>
                                </li>
                            @endif
                            @if (auth()->user()->hasPermission('purchases'))
                                <li class="nav-item">
                                    <a href="{{ route('purchases.index') }}"
                                        class="nav-link {{ request()->routeIs('purchases.*') ? 'active' : '' }}">
                                        Purchases
                                    </a>
                                </li>
                            @endif
                            @if (auth()->user()->hasPermission('suppliers'))
                                <li class="nav-item">
                                    <a href="{{ route('suppliers.index') }}"
                                        class="nav-link {{ request()->routeIs('suppliers.*') ? 'active' : '' }}">
                                        Suppliers
                                    </a>
                                </li>
                            @endif
                            @if (auth()->user()->hasPermission('issues'))
                                <li class="nav-item">
                                    <a href="{{ route('issues.index') }}"
                                        class="nav-link {{ request()->routeIs('issues.*') ? 'active' : '' }}">
                                        Issues
                                    </a>
                                </li>
                            @endif
                        </ul>
                    </li>

                @endif

                {{-- <li class="nav-item nav-item-submenu 
          {{ request()->routeIs('admin.products.*') 
          || request()->routeIs('admin.inventoryWarehouses.*')
            ? 'nav-item-open' : '' }}">

          <a href="#" class="nav-link"><i class="icon-copy"></i> <span>Inventory Management</span></a>

          <ul class="nav nav-group-sub" data-submenu-title="Layouts" 
            style="{{ request()->routeIs('admin.products.*') 
            || request()->routeIs('admin.inventoryWarehouses.*') 
              ? 'display:block;' : '' }}">

            <li class="nav-item"><a href="{{ route('admin.products.index')}}" class="nav-link {{ request()->routeIs('admin.products.*') ? 'active' : '' }}">Product Inventory</a></li>
            <li class="nav-item"><a href="{{ route('admin.inventoryWarehouses.index')}}" class="nav-link {{ request()->routeIs('admin.inventoryWarehouses.*') ? 'active' : '' }}">Warehouse Inventory</a></li>
          </ul>
        </li> --}}




                {{-- <li class="nav-item nav-item-submenu">
          <a href="#" class="nav-link"><i class="icon-copy"></i> <span>Inventory issuance</span></a>

          <ul class="nav nav-group-sub" data-submenu-title="Layouts">
            <li class="nav-item"><a href="{{ route('admin.inventoryDemands.index')}}" class="nav-link">Inventory Demand</a></li>
            <li class="nav-item"><a href="{{ route('admin.inventoryDispatchs.index')}}" class="nav-link">Inventory Dispatch</a></li>
            <li class="nav-item"><a href="{{ route('admin.inventoryLargerReports.index')}}" class="nav-link">Inventory Larger Report</a></li>
          </ul>
        </li> --}}

                @if (auth()->user()->hasPermission('accident_details') || auth()->user()->hasPermission('accident_reports'))
                    <li
                        class="nav-item nav-item-submenu 
    {{ request()->routeIs('accidentDetails.*') || request()->routeIs('accidentReports.*') ? 'nav-item-open' : '' }}">
                        <a href="#" class="nav-link"><i class="icon-copy"></i> <span>Accidents</span></a>

                        <ul class="nav nav-group-sub" data-submenu-title="Layouts"
                            style="{{ request()->routeIs('master_warehouse_inventory.*') ||
                            request()->routeIs('accidentDetails.*') ||
                            request()->routeIs('accidentReports.*')
                                ? 'display:block;'
                                : '' }}">
                            @if (auth()->user()->hasPermission('accident_details'))
                                <li class="nav-item"><a href="{{ route('accidentDetails.index') }}"
                                        class="nav-link {{ request()->routeIs('accidentDetails.*') ? 'active' : '' }}">Accident
                                        Details</a></li>
                            @endif
                            @if (auth()->user()->hasPermission('accident_reports'))
                                <li class="nav-item"><a href="{{ route('accidentReports.index') }}"
                                        class="nav-link {{ request()->routeIs('accidentReports.*') ? 'active' : '' }}">Accident
                                        Report</a></li>
                            @endif
                        </ul>
                    </li>
                @endif
                @if (auth()->user()->hasPermission('client_invoices') ||
                        auth()->user()->hasPermission('cash_payments') ||
                        auth()->user()->hasPermission('bank_payments'))
                    <li
                        class="nav-item nav-item-submenu {{ request()->routeIs('clientInvoices.*') ||
                        request()->routeIs('cashPayments.*') ||
                        request()->routeIs('bankPayments.*')
                            ? 'nav-item-open'
                            : '' }}">
                        <a href="#" class="nav-link"><i class="icon-copy"></i> <span>Accounts</span></a>

                        <ul class="nav nav-group-sub" data-submenu-title="Layouts"
                            style="{{ request()->routeIs('clientInvoices.*') ||
                            request()->routeIs('cashPayments.*') ||
                            request()->routeIs('bankPayments.*')
                                ? 'display:block;'
                                : '' }}">
                            @if (auth()->user()->hasPermission('client_invoices'))
                                <li class="nav-item"><a href="{{ route('clientInvoices.index') }}"
                                        class="nav-link {{ request()->routeIs('clientInvoices.*') ? 'active' : '' }}">Client
                                        Invoice</a></li>
                            @endif
                            @if (auth()->user()->hasPermission('cash_payments'))
                                <li class="nav-item"><a href="{{ route('cashPayments.index') }}"
                                        class="nav-link {{ request()->routeIs('cashPayments.*') ? 'active' : '' }}">Cash
                                        Payment Voucher</a></li>
                            @endif

                            @if (auth()->user()->hasPermission('bank_payments'))
                                <li class="nav-item"><a href="{{ route('bankPayments.index') }}"
                                        class="nav-link {{ request()->routeIs('bankPayments.*') ? 'active' : '' }}">Bank
                                        Payment Voucher</a></li>
                            @endif
                        </ul>
                    </li>
                @endif
                @if (auth()->user()->hasPermission('vehicle_maintenances') ||
                        auth()->user()->hasPermission('vehicle_maintenance_reports'))

                    <li
                        class="nav-item nav-item-submenu {{ request()->routeIs('vehicleMaintenances.*') || request()->routeIs('vehicleMaintenanceReports.*')
                            ? 'nav-item-open'
                            : '' }}">
                        <a href="#" class="nav-link"><i class="icon-copy"></i> <span>Vehicle
                                Maintenance</span></a>

                        <ul class="nav nav-group-sub" data-submenu-title="Layouts"
                            style="{{ request()->routeIs('vehicleMaintenances.*') || request()->routeIs('vehicleMaintenanceReports.*')
                                ? 'display:block;'
                                : '' }}">
                            @if (auth()->user()->hasPermission('vehicle_maintenances'))
                                <li class="nav-item"><a href="{{ route('vehicleMaintenances.index') }}"
                                        class="nav-link {{ request()->routeIs('vehicleMaintenances.*') ? 'active' : '' }}">Vehicle
                                        Maintenance</a></li>
                            @endif
                            @if (auth()->user()->hasPermission('vehicle_maintenance_reports'))
                                <li class="nav-item"><a href="{{ route('vehicleMaintenanceReports.index') }}"
                                        class="nav-link {{ request()->routeIs('vehicleMaintenanceReports.*') ? 'active' : '' }}">Vehicle
                                        Maintenance Report</a></li>
                            @endif
                        </ul>
                    </li>
                @endif
                @if (auth()->user()->hasPermission('driver_attendances') || auth()->user()->hasPermission('vehicle_attendances'))
                    <li
                        class="nav-item nav-item-submenu
          {{ request()->routeIs('driverAttendances.*') || request()->routeIs('vehicleAttendances.*')
              ? 'nav-item-open'
              : '' }}">

                        <a href="#" class="nav-link"><i class="icon-copy"></i> <span>Attendece</span></a>

                        <ul class="nav nav-group-sub" data-submenu-title="Layouts"
                            style="{{ request()->routeIs('driverAttendances.*') || request()->routeIs('vehicleAttendances.*')
                                ? 'display:block;'
                                : '' }}">
                            @if (auth()->user()->hasPermission('driver_attendances'))
                                <li class="nav-item"><a href="{{ route('driverAttendances.index') }}"
                                        class="nav-link {{ request()->routeIs('driverAttendances.*') ? 'active' : '' }}">Driver
                                        Attendance</a></li>
                            @endif
                            @if (auth()->user()->hasPermission('vehicle_attendances'))
                                <li class="nav-item"><a href="{{ route('vehicleAttendances.index') }}"
                                        class="nav-link {{ request()->routeIs('vehicleAttendances.*') ? 'active' : '' }}">Vehicle
                                        Attendance</a></li>
                            @endif
                        </ul>
                    </li>

                @endif

            </ul>
        </div>
        <!-- /main navigation -->
    </div>
    <!-- /sidebar content -->
</div>
<!-- /main sidebar -->
