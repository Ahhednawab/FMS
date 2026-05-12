<div class="card mb-3">
    <div class="card-body py-2">
        <ul class="nav nav-tabs card-header-tabs border-bottom-0">
            <li class="nav-item">
                <a href="{{ route('driverAttendances.index') }}"
                    class="nav-link {{ request()->routeIs('driverAttendances.index') || request()->routeIs('driverAttendances.show') || request()->routeIs('driverAttendances.create') || request()->routeIs('driverAttendances.edit') ? 'active' : '' }}">
                    Driver Attendance
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('driverAttendances.monthly.index') }}"
                    class="nav-link {{ request()->routeIs('driverAttendances.monthly.*') ? 'active' : '' }}">
                    Monthly Attendance
                </a>
            </li>
        </ul>
    </div>
</div>
