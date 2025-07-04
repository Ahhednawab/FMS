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
              {{ ucfirst(Auth::user()->role) }}
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

        <!-- Main -->
        <li class="nav-item-header"><div class="text-uppercase font-size-xs line-height-xs">Main</div> <i class="icon-menu" title="Main"></i></li>
        <li class="nav-item">
          <a href="{{ route('admin.index')}}" class="nav-link">
            <i class="icon-home4"></i>
            <span>
              Dashboard
            </span>
          </a>
        </li>
        <li class="nav-item nav-item-submenu">
          <a href="#" class="nav-link"><i class="icon-copy"></i> <span>General</span></a>

          <ul class="nav nav-group-sub" data-submenu-title="Layouts">
            <li class="nav-item"><a href="{{ route('admin.users.index')}}" class="nav-link">User Managment</a></li>
            <li class="nav-item"><a href="{{ route('admin.cities.index')}}" class="nav-link">City Management</a></li>
            <li class="nav-item"><a href="{{ route('admin.stations.index')}}" class="nav-link">Station Management</a></li>
            <li class="nav-item"><a href="{{ route('admin.ibcCenters.index')}}" class="nav-link">IBC Center Management</a></li>
            <li class="nav-item"><a href="{{ route('admin.warehouses.index')}}" class="nav-link">Warehouse Management</a></li>
          </ul>
        </li>

        <li class="nav-item nav-item-submenu">
          <a href="" class="nav-link"><i class="icon-copy"></i> <span>Master Data</span></a>

          <ul class="nav nav-group-sub" data-submenu-title="Layouts">
            <li class="nav-item"><a href="{{ route('admin.vehicles.index')}}" class="nav-link">Vehicle Master Data</a></li>
            <li class="nav-item"><a href="{{ route('admin.drivers.index')}}" class="nav-link">Driver Master Data</a></li>
            <li class="nav-item"><a href="{{ route('admin.vendors.index')}}" class="nav-link">Vendor Management</a></li>
          </ul>
        </li>

        <li class="nav-item nav-item-submenu">
          <a href="#" class="nav-link"><i class="icon-copy"></i> <span>Inventory</span></a>

          <ul class="nav nav-group-sub" data-submenu-title="Layouts">
            <li class="nav-item"><a href="{{ route('admin.products.index')}}" class="nav-link">Product master data</a></li>
            <li class="nav-item"><a href="{{ route('admin.inventoryWarehouses.index')}}" class="nav-link">Warehouse Inventory</a></li>
          </ul>
        </li>


        <li class="nav-item nav-item-submenu">
          <a href="#" class="nav-link"><i class="icon-copy"></i> <span>Fleet transaction</span></a>

          <ul class="nav nav-group-sub" data-submenu-title="Layouts">
            <li class="nav-item"><a href="{{ route('admin.trackerMileages.index')}}" class="nav-link">Daily Mileage (a)</a></li>
            <li class="nav-item"><a href="{{ route('admin.dailyMileages.index')}}" class="nav-link">Daily Mileage (b)</a></li>
            <li class="nav-item"><a href="{{ route('admin.dailyFuelMileages.index')}}" class="nav-link">Daily fuel Mileage</a></li>
          </ul>
        </li>

        <li class="nav-item nav-item-submenu">
          <a href="#" class="nav-link"><i class="icon-copy"></i> <span>Inventory issuance</span></a>

          <ul class="nav nav-group-sub" data-submenu-title="Layouts">
            <li class="nav-item"><a href="{{ route('admin.inventoryDemands.index')}}" class="nav-link">Inventory Demand</a></li>
            <li class="nav-item"><a href="{{ route('admin.inventoryDispatchs.index')}}" class="nav-link">Inventory Dispatch</a></li>
            <li class="nav-item"><a href="{{ route('admin.inventoryLargerReports.index')}}" class="nav-link">Inventory Larger Report</a></li>
          </ul>
        </li>


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

        <li class="nav-item nav-item-submenu">
          <a href="#" class="nav-link"><i class="icon-copy"></i> <span>Attendece</span></a>

          <ul class="nav nav-group-sub" data-submenu-title="Layouts">
            <li class="nav-item"><a href="{{ route('admin.driverAttendances.index')}}" class="nav-link">Driver Attendance</a></li>
            <li class="nav-item"><a href="{{ route('admin.vehicleAttendances.index')}}" class="nav-link">Vehicle Attendance</a></li>

          </ul>
        </li>
      </ul>
    </div>
    <!-- /main navigation -->
  </div>
  <!-- /sidebar content -->
</div>
<!-- /main sidebar -->
