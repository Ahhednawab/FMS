<div class="sidebar sidebar-dark sidebar-main sidebar-expand-lg">
  <!-- Sidebar content -->
  <div class="sidebar-content">

    <!-- User menu -->
    <div class="sidebar-section sidebar-user my-1">
      <div class="sidebar-section-body">
        <div class="media">
          <a href="#" class="mr-3">
            <img src={{ asset("assets/images/placeholders/placeholder.jpg")}} class="rounded-circle" alt="">
          </a>

          <div class="media-body">
            <div class="font-weight-semibold">{{Auth::user()->name}}</div>
            <div class="font-size-sm line-height-sm opacity-50">
              {{ ucfirst(Auth::user()->role->name) }}
            </div>
          </div>

          <div class="ml-3 align-self-center">
            <button type="button" class="btn btn-outline-light-100 text-white border-transparent btn-icon rounded-pill btn-sm sidebar-control sidebar-main-resize d-none d-lg-inline-flex">
              <i class="icon-transmission"></i>
            </button>

            <button type="button" class="btn btn-outline-light-100 text-white border-transparent btn-icon rounded-pill btn-sm sidebar-mobile-main-toggle d-lg-none">
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
          <a href="{{ route('admin.index')}}" class="nav-link {{ request()->routeIs('admin.index') ? 'active' : '' }}">
            <i class="icon-home4"></i>
            <span>Dashboard</span>
          </a>
        </li>

        <li class="nav-item">
          <a href="{{ route('admin.drafts.index')}}" class="nav-link {{ request()->routeIs('admin.drafts.*') ? 'active' : '' }}">
            <i class="icon-copy"></i>
            <span>Drafts</span>
          </a>
        </li>

        <li class="nav-item nav-item-submenu 
          {{ request()->routeIs('admin.users.*') 
          || request()->routeIs('admin.cities.*') 
          || request()->routeIs('admin.stations.*') 
          || request()->routeIs('admin.ibcCenters.*') 
          || request()->routeIs('admin.warehouses.*') 
            ? 'nav-item-open' : '' }}">

          <a href="#" class="nav-link"><i class="icon-copy"></i><span>General</span></a>

          <ul class="nav nav-group-sub" data-submenu-title="Layouts" 
            style="{{ request()->routeIs('admin.users.*') 
            || request()->routeIs('admin.cities.*') 
            || request()->routeIs('admin.stations.*') 
            || request()->routeIs('admin.ibcCenters.*') 
            || request()->routeIs('admin.warehouses.*') 
              ? 'display:block;' : '' }}">
              
            <li class="nav-item">
              <a href="{{ route('admin.users.index')}}" class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">User Management</a>
            </li>
            <li class="nav-item">
              <a href="{{ route('admin.cities.index')}}" class="nav-link {{ request()->routeIs('admin.cities.*') ? 'active' : '' }}">City Management</a>
            </li>
            <li class="nav-item">
              <a href="{{ route('admin.stations.index')}}" class="nav-link {{ request()->routeIs('admin.stations.*') ? 'active' : '' }}">Station Management</a>
            </li>
            <li class="nav-item">
              <a href="{{ route('admin.ibcCenters.index')}}" class="nav-link {{ request()->routeIs('admin.ibcCenters.*') ? 'active' : '' }}">IBC Center Management</a>
            </li>
            <li class="nav-item">
              <a href="{{ route('admin.warehouses.index')}}" class="nav-link {{ request()->routeIs('admin.warehouses.*') ? 'active' : '' }}">Warehouse Management</a>
            </li>
          </ul>
        </li>


        <li class="nav-item nav-item-submenu 
          {{ request()->routeIs('admin.vehicles.*') 
          || request()->routeIs('admin.drivers.*') 
          || request()->routeIs('admin.vendors.*') 
            ? 'nav-item-open' : '' }}">
          
          <a href="" class="nav-link"><i class="icon-copy"></i> <span>Master Data</span></a>

          <ul class="nav nav-group-sub" data-submenu-title="Layouts" 
            style="{{ request()->routeIs('admin.vehicles.*') 
            || request()->routeIs('admin.drivers.*') 
            || request()->routeIs('admin.vendors.*') 
            || request()->routeIs('admin.insurance-companies.*')
              ? 'display:block;' : '' }}">

            <li class="nav-item">
              <a href="{{ route('admin.vehicles.index')}}" class="nav-link {{ request()->routeIs('admin.vehicles.*') ? 'active' : '' }}">Vehicle Master Data</a>
            </li>
            <li class="nav-item">
              <a href="{{ route('admin.drivers.index')}}" class="nav-link {{ request()->routeIs('admin.drivers.*') ? 'active' : '' }}">Driver Master Data</a>
            </li>
            <li class="nav-item">
              <a href="{{ route('admin.vendors.index')}}" class="nav-link {{ request()->routeIs('admin.vendors.*') ? 'active' : '' }}">Vendor Management</a>
            </li>
            <li class="nav-item">
              <a href="{{ route('admin.insurance-companies.index')}}" class="nav-link {{ request()->routeIs('admin.insurance-companies.*') ? 'active' : '' }}">Insurance Company Management</a>
            </li>
          </ul>
        </li>

        <li class="nav-item nav-item-submenu 
          {{ request()->routeIs('admin.brands.*') 
          || request()->routeIs('admin.categories.*') 
          || request()->routeIs('admin.productList.*') 
            ? 'nav-item-open' : '' }}">
          <a href="#" class="nav-link"><i class="icon-copy"></i> <span>Product Management</span></a>

          <ul class="nav nav-group-sub" data-submenu-title="Layouts" 
            style="{{ request()->routeIs('admin.brands.*') 
            || request()->routeIs('admin.categories.*') 
            || request()->routeIs('admin.productList.*') 
              ? 'display:block;' : '' }}">

            <li class="nav-item"><a href="{{ route('admin.brands.index')}}" class="nav-link {{ request()->routeIs('admin.brands.*') ? 'active' : '' }}">Brand Management</a></li>
            <li class="nav-item"><a href="{{ route('admin.categories.index')}}" class="nav-link {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">Category Management</a></li>
            <li class="nav-item"><a href="{{ route('admin.productList.index')}}" class="nav-link {{ request()->routeIs('admin.productList.*') ? 'active' : '' }}">Product Management</a></li>
          </ul>
        </li>

        <li class="nav-item nav-item-submenu 
          {{ request()->routeIs('admin.dailyMileages.*') 
          || request()->routeIs('admin.dailyMileageReports.*')           
          || request()->routeIs('admin.dailyFuels.*')           
          || request()->routeIs('admin.dailyFuelReports.*')           
            ? 'nav-item-open' : '' }}">
          <a href="#" class="nav-link"><i class="icon-copy"></i> <span>Fleet Transaction</span></a>

          <ul class="nav nav-group-sub" data-submenu-title="Layouts" 
            style="{{ request()->routeIs('admin.dailyMileages.*')
            || request()->routeIs('admin.dailyMileageReports.*') 
            || request()->routeIs('admin.dailyFuels.*')
            || request()->routeIs('admin.dailyFuelReports.*')

              ? 'display:block;' : '' }}">

            <li class="nav-item"><a href="{{ route('admin.dailyMileages.index')}}" class="nav-link {{ request()->routeIs('admin.dailyMileages.*') ? 'active' : '' }}">Daily Mileage</a></li>
            <li class="nav-item"><a href="{{ route('admin.dailyMileageReports.index')}}" class="nav-link {{ request()->routeIs('admin.dailyMileageReports.*') ? 'active' : '' }}">Mileage Report</a></li>
            <li class="nav-item"><a href="{{ route('admin.dailyFuels.index')}}" class="nav-link {{ request()->routeIs('admin.dailyFuels.*') ? 'active' : '' }}">Daily Fueling</a></li>
            <li class="nav-item"><a href="{{ route('admin.dailyFuelReports.index')}}" class="nav-link {{ request()->routeIs('admin.dailyFuelReports.*') ? 'active' : '' }}">Fuel Report</a></li>
          </ul>
        </li>


     <li class="nav-item nav-item-submenu 
    {{ request()->routeIs('admin.products.*') 
        || request()->routeIs('admin.inventoryWarehouses.*')
        || request()->routeIs('admin.master_warehouse_inventory.*')
        || request()->routeIs('admin.purchases.*')
        || request()->routeIs('admin.warehouses.*')
        ? 'nav-item-open' : '' }}">

    <a href="#" class="nav-link"><i class="icon-copy"></i> <span>Inventory Management</span></a>

    <ul class="nav nav-group-sub" data-submenu-title="Inventory" 
        style="{{  request()->routeIs('admin.master_warehouse_inventory.*')
            || request()->routeIs('admin.purchases.*')
            || request()->routeIs('admin.warehouses.*')
            ? 'display:block;' : '' }}">

      
        <li class="nav-item">
            <a href="{{ route('admin.master_warehouse_inventory.index') }}" 
               class="nav-link {{ request()->routeIs('admin.master_warehouse_inventory.*') ? 'active' : '' }}">
               Master Warehouse Inventory
            </a>
        </li>

        <li class="nav-item">
            <a href="{{ route('admin.purchases.index') }}" 
               class="nav-link {{ request()->routeIs('admin.purchases.*') ? 'active' : '' }}">
               Purchases
            </a>
        </li>

        <li class="nav-item">
            <a href="{{ route('admin.warehouses.create') }}" 
               class="nav-link {{ request()->routeIs('admin.warehouses.create') ? 'active' : '' }}">
               Create Warehouse
            </a>
        </li>


    </ul>
</li>


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


        <li class="nav-item nav-item-submenu">
          <a href="#" class="nav-link"><i class="icon-copy"></i> <span>Accidents</span></a>

          <ul class="nav nav-group-sub" data-submenu-title="Layouts">
            <li class="nav-item"><a href="{{ route('admin.accidentDetails.index')}}" class="nav-link">Accident Details</a></li>
            <li class="nav-item"><a href="{{ route('admin.accidentReports.index')}}" class="nav-link">Accident Report</a></li>
          </ul>
        </li>


        <li class="nav-item nav-item-submenu">
          <a href="#" class="nav-link"><i class="icon-copy"></i> <span>Accounts</span></a>

          <ul class="nav nav-group-sub" data-submenu-title="Layouts">
            <li class="nav-item"><a href="{{ route('admin.clientInvoices.index')}}" class="nav-link">Client Invoice</a></li>
            <li class="nav-item"><a href="{{ route('admin.cashPayments.index')}}" class="nav-link">Cash Payment Voucher</a></li>
            <li class="nav-item"><a href="{{ route('admin.bankPayments.index')}}" class="nav-link">Bank Payment Voucher</a></li>
          </ul>
        </li>


        <li class="nav-item nav-item-submenu">
          <a href="#" class="nav-link"><i class="icon-copy"></i> <span>Vehicle Maintenance</span></a>

          <ul class="nav nav-group-sub" data-submenu-title="Layouts">
            <li class="nav-item"><a href="{{ route('admin.vehicleMaintenances.index')}}" class="nav-link">Vehicle Maintenance</a></li>
            <li class="nav-item"><a href="{{ route('admin.vehicleMaintenanceReports.index')}}" class="nav-link">Vehicle Maintenance Report</a></li>
          </ul> 
        </li>

        <li class="nav-item nav-item-submenu
          {{ request()->routeIs('admin.driverAttendances.*') 
          || request()->routeIs('admin.vehicleAttendances.*')
            ? 'nav-item-open' : '' }}">

          <a href="#" class="nav-link"><i class="icon-copy"></i> <span>Attendece</span></a>

          <ul class="nav nav-group-sub" data-submenu-title="Layouts"
            style="{{ request()->routeIs('admin.driverAttendances.*') 
            || request()->routeIs('admin.vehicleAttendances.*') 
              ? 'display:block;' : '' }}">
            <li class="nav-item"><a href="{{ route('admin.driverAttendances.index')}}" class="nav-link {{ request()->routeIs('admin.driverAttendances.*') ? 'active' : '' }}">Driver Attendance</a></li>
            <li class="nav-item"><a href="{{ route('admin.vehicleAttendances.index')}}" class="nav-link {{ request()->routeIs('admin.vehicleAttendances.*') ? 'active' : '' }}">Vehicle Attendance</a></li>
          </ul>
        </li>
      </ul>
    </div>
    <!-- /main navigation -->
  </div>
  <!-- /sidebar content -->
</div>
<!-- /main sidebar -->
