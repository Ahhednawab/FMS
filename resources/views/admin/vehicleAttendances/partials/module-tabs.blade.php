<div class="card mb-3">
    <div class="card-body py-2">
        <ul class="nav nav-tabs card-header-tabs border-bottom-0">
            <li class="nav-item">
                <a href="{{ route('vehicleAttendances.index') }}"
                    class="nav-link {{ request()->routeIs('vehicleAttendances.index') || request()->routeIs('vehicleAttendances.show') || request()->routeIs('vehicleAttendances.create') || request()->routeIs('vehicleAttendances.edit') ? 'active' : '' }}">
                    Vehicle Attendance
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('vehicleAttendances.monthly.index') }}"
                    class="nav-link {{ request()->routeIs('vehicleAttendances.monthly.*') ? 'active' : '' }}">
                    Monthly Vehicle Attendance
                </a>
            </li>
        </ul>
    </div>
</div>
