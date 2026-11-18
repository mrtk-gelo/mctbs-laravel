<x-shell title="Reports | MCTBS">
    <x-slot:nav>
        @if ($dashboardPrefix === 'owner')
            @include('partials.owner-nav', ['activePage' => 'reports'])
        @else
            @include('partials.staff-nav', ['activePage' => 'reports'])
        @endif
    </x-slot:nav>

    <div class="container workspace-page">
        <div class="page-header-row page-header report-page-header">
            <div>
                <h1>Reports</h1>
                <p>{{ $periodLabel }}</p>
            </div>
            <div class="report-header-actions">
                <a class="btn btn-outline" href="{{ route($dashboardPrefix.'.reports.excel', $reportQuery) }}">
                    <i class="fa fa-file-excel"></i> Download Excel
                </a>
                <a class="btn btn-outline" href="{{ route($dashboardPrefix.'.reports.pdf', $reportQuery) }}">
                    <i class="fa fa-file-pdf"></i> Download PDF
                </a>
            </div>
        </div>

        <div class="report-toolbar">
            <div class="report-period-tabs" role="tablist" aria-label="Report period">
                @foreach (['weekly' => 'Weekly', 'monthly' => 'Monthly', 'annually' => 'Annually'] as $periodKey => $periodTitle)
                    <a
                        class="report-period-tab {{ $period === $periodKey ? 'active' : '' }}"
                        href="{{ route($dashboardPrefix.'.bookings.index', array_merge($reportQuery, ['period' => $periodKey])) }}"
                        @if ($period === $periodKey) aria-current="page" @endif
                    >{{ $periodTitle }}</a>
                @endforeach
            </div>
        </div>

        <form class="report-range-filter" method="GET" action="{{ route($dashboardPrefix.'.bookings.index') }}" id="report-filter-form">
            <input type="hidden" name="section" value="reports">
            <input type="hidden" name="period" value="{{ $period }}">
            <div class="report-range-field">
                <label for="report-year">Year</label>
                <select id="report-year" name="year">
                    @foreach (range(2000, now()->year) as $yearOption)
                        <option value="{{ $yearOption }}" @selected($selectedYear === $yearOption)>{{ $yearOption }}</option>
                    @endforeach
                </select>
            </div>
            <div class="report-range-field">
                <label for="report-quarter">Quarter</label>
                <select id="report-quarter" name="quarter">
                    @foreach (['All', 'Q1', 'Q2', 'Q3', 'Q4', 'Custom'] as $quarterOption)
                        <option value="{{ $quarterOption }}" @selected($selectedQuarter === $quarterOption)>{{ $quarterOption }}</option>
                    @endforeach
                </select>
            </div>
            <div class="report-range-field">
                <label for="report-house">House</label>
                <select id="report-house" name="house_id">
                    <option value="">All my houses</option>
                    @foreach ($houses as $house)
                        <option value="{{ $house->house_id }}" @selected($selectedHouseId === $house->house_id)>{{ $house->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="report-range-field">
                <label for="report-from-date">From date</label>
                <input id="report-from-date" type="date" name="from_date" value="{{ old('from_date', $fromDate) }}" required>
                @error('from_date')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>
            <div class="report-range-field">
                <label for="report-to-date">To date</label>
                <input id="report-to-date" type="date" name="to_date" value="{{ old('to_date', $toDate) }}" required>
                @error('to_date')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>
            <div class="report-range-actions">
                <button class="btn btn-primary" type="submit">Apply filters</button>
                <a class="btn btn-outline" href="{{ route($dashboardPrefix.'.bookings.index', ['section' => 'reports']) }}">Reset</a>
            </div>
        </form>

        <div class="stats-grid report-summary">
            <div class="stat-card">
                <span class="stat-icon green"><i class="fa fa-peso-sign"></i></span>
                <div>
                    <div class="stat-value">₱{{ number_format($totalRevenue, 2) }}</div>
                    <div class="stat-label">Net Revenue (after refunds)</div>
                    @if ($totalRefunded > 0)
                        <small class="text-muted">Refunds: <span style="color:#c0392b">-₱{{ number_format($totalRefunded, 2) }}</span></small>
                    @endif
                </div>
            </div>
            <div class="stat-card">
                <span class="stat-icon blue"><i class="fa fa-calendar-check"></i></span>
                <div><div class="stat-value">{{ $totalBookings }}</div><div class="stat-label">Bookings in range</div></div>
            </div>
            <div class="stat-card">
                <span class="stat-icon orange"><i class="fa fa-circle-check"></i></span>
                <div><div class="stat-value">{{ $completedBookings }}</div><div class="stat-label">Completed bookings</div></div>
            </div>
            <div class="stat-card">
                <span class="stat-icon red"><i class="fa fa-clock"></i></span>
                <div><div class="stat-value">{{ $pendingBookings }}</div><div class="stat-label">Pending requests</div></div>
            </div>
        </div>

        <div class="report-content-grid">
            <section class="card report-card">
                <div class="card-header">
                    <span><i class="fa fa-chart-column"></i> Net Revenue (after refunds)</span>
                    <span class="report-period-caption">{{ $periodLabel }}</span>
                </div>
                <div class="report-bars">
                    @php
                        $maximumRevenue = max((float) $chartRows->max('revenue'), 1);
                    @endphp
                    @forelse ($chartRows as $row)
                        <div class="report-bar-row">
                            <span class="report-bar-label">{{ $row['label'] }}</span>
                            <div class="report-bar-track" role="img" aria-label="{{ $row['label'] }}: ₱{{ number_format($row['revenue'], 2) }}">
                                <span class="report-bar-fill" style="width: {{ min(100, max(0, (float) $row['revenue'] / $maximumRevenue * 100)) }}%"></span>
                            </div>
                            <strong>₱{{ number_format($row['revenue'], 2) }}</strong>
                        </div>
                    @empty
                        <div class="empty-state compact-empty"><i class="fa fa-chart-line"></i><h3>No revenue data</h3><p>Verified payments will appear here.</p></div>
                    @endforelse
                </div>
            </section>

            <section class="card report-card">
                <div class="card-header"><span><i class="fa fa-wallet"></i> Verified payment methods</span></div>
                <div class="payment-breakdown">
                    @forelse ($paymentMethods as $method => $amount)
                        <div class="payment-breakdown-row">
                            <span><i class="fa fa-credit-card"></i> {{ ucwords(str_replace('_', ' ', $method)) }}</span>
                            <strong>₱{{ number_format($amount, 2) }}</strong>
                        </div>
                    @empty
                        <div class="empty-state compact-empty"><i class="fa fa-receipt"></i><h3>No payments yet</h3><p>There are no verified payments in this range.</p></div>
                    @endforelse
                </div>
            </section>
        </div>

        <section class="card report-top-units">
            <div class="card-header"><span>Revenue by period — {{ ucfirst($period) }}</span></div>
            <div class="table-wrap">
                <table>
                    <thead><tr><th>{{ ucfirst($period) }}</th><th>Payments received</th><th>Refunds</th><th>Net Revenue (after refunds)</th></tr></thead>
                    <tbody>
                        @forelse ($revenueByPeriodRows as $row)
                            <tr>
                                <td>{{ $row['period'] }}</td>
                                <td>₱{{ number_format($row['payments'], 2) }}</td>
                                <td style="color:#c0392b">{{ $row['refunds'] > 0 ? '-₱'.number_format($row['refunds'], 2) : '₱0.00' }}</td>
                                <td><strong>₱{{ number_format($row['revenue'], 2) }}</strong></td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="empty-table-cell">No revenue for these filters.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="card report-top-units">
            <div class="card-header"><span>Revenue per House</span></div>
            <div class="table-wrap">
                <table>
                    <thead><tr><th>House</th><th>Payments received</th><th>Refunds</th><th>Net Revenue (after refunds)</th></tr></thead>
                    <tbody>
                        @forelse ($revenueByHouse as $row)
                            <tr><td>{{ $row['house'] }}</td><td>₱{{ number_format($row['payments'], 2) }}</td><td style="color:#c0392b">{{ $row['refunds'] > 0 ? '-₱'.number_format($row['refunds'], 2) : '₱0.00' }}</td><td><strong>₱{{ number_format($row['revenue'], 2) }}</strong></td></tr>
                        @empty
                            <tr><td colspan="4" class="empty-table-cell">No houses found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="card report-top-units">
            <div class="card-header"><span>Revenue per Unit</span></div>
            <div class="table-wrap">
                <table>
                    <thead><tr><th>Unit</th><th>House</th><th>Payments received</th><th>Refunds</th><th>Net Revenue (after refunds)</th></tr></thead>
                    <tbody>
                        @forelse ($revenueByUnit as $row)
                            <tr><td>{{ $row['unit'] }}</td><td>{{ $row['house'] }}</td><td>₱{{ number_format($row['payments'], 2) }}</td><td style="color:#c0392b">{{ $row['refunds'] > 0 ? '-₱'.number_format($row['refunds'], 2) : '₱0.00' }}</td><td><strong>₱{{ number_format($row['revenue'], 2) }}</strong></td></tr>
                        @empty
                            <tr><td colspan="5" class="empty-table-cell">No units found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        @if ($period === 'annually')
            <section class="card report-top-units">
                <div class="card-header"><span>Revenue per Quarter — {{ $selectedYear }}</span></div>
                <div class="table-wrap">
                    <table>
                        <thead><tr><th>Quarter</th><th>Payments received</th><th>Refunds</th><th>Net Revenue (after refunds)</th></tr></thead>
                        <tbody>
                            @foreach ($quarterRevenueRows as $row)
                                <tr><td>{{ $row['quarter'] }}</td><td>₱{{ number_format($row['payments'], 2) }}</td><td style="color:#c0392b">{{ $row['refunds'] > 0 ? '-₱'.number_format($row['refunds'], 2) : '₱0.00' }}</td><td><strong>₱{{ number_format($row['revenue'], 2) }}</strong></td></tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>
        @endif

        <section class="card report-top-units">
            <div class="card-header"><span>Booking Activity per House</span></div>
            <div class="table-wrap">
                <table>
                    <thead><tr><th>House</th><th>Month with most check-ins</th><th>Check-ins that month</th><th>Top 5 booked dates</th></tr></thead>
                    <tbody>
                        @forelse ($activityRows as $row)
                            <tr>
                                <td>{{ $row['house'] }}</td>
                                <td>{{ $row['most_booked_month'] }}</td>
                                <td>{{ $row['month_bookings'] }}</td>
                                <td>
                                    @forelse ($row['top_dates'] as $date)
                                        <span>{{ \Illuminate\Support\Carbon::parse($date['date'])->format('M j, Y') }} ({{ $date['bookings'] }})</span>@if (! $loop->last), @endif
                                    @empty
                                        No booked dates
                                    @endforelse
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="empty-table-cell">No houses found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    <script>
        const reportYear = document.getElementById('report-year');
        const reportQuarter = document.getElementById('report-quarter');
        const reportFrom = document.getElementById('report-from-date');
        const reportTo = document.getElementById('report-to-date');

        function fillQuarterRange() {
            const year = Number(reportYear.value);
            const quarter = reportQuarter.value;
            const startMonth = quarter === 'All' ? 1 : (Number(quarter.slice(1)) - 1) * 3 + 1;
            const endMonth = quarter === 'All' ? 12 : startMonth + 2;
            const start = new Date(year, startMonth - 1, 1);
            const end = new Date(year, endMonth, 0);
            reportFrom.value = `${year}-${String(start.getMonth() + 1).padStart(2, '0')}-${String(start.getDate()).padStart(2, '0')}`;
            reportTo.value = `${year}-${String(end.getMonth() + 1).padStart(2, '0')}-${String(end.getDate()).padStart(2, '0')}`;
        }

        reportQuarter.addEventListener('change', () => {
            if (reportQuarter.value !== 'Custom') {
                fillQuarterRange();
            }
        });
        reportYear.addEventListener('change', () => {
            if (reportQuarter.value !== 'Custom') {
                fillQuarterRange();
            }
        });
        [reportFrom, reportTo].forEach((field) => {
            field.addEventListener('input', () => {
                reportQuarter.value = 'Custom';
            });
        });
    </script>
</x-shell>
