<div class="card mb-3">
    <div class="card-body py-2">
        <ul class="nav nav-tabs nav-tabs-bottom border-bottom-0">
            <li class="nav-item">
                <a href="{{ route('dailyMileages.index') }}"
                    class="nav-link {{ request()->routeIs('dailyMileages.*') ? 'active' : '' }}">
                    Daily Mileage
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('daily-mileage.index') }}"
                    class="nav-link {{ request()->routeIs('trackingData.*') || request()->routeIs('daily-mileage.*') ? 'active' : '' }}">
                    Tracking Data
                </a>
            </li>
        </ul>
    </div>
</div>
