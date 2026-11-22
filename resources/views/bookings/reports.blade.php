<x-shell title="Reports & Analytics | MCTBS">
    <x-slot:nav>
        @if ($dashboardPrefix === 'owner')
            @include('partials.owner-nav', ['activePage' => 'reports'])
        @else
            @include('partials.staff-nav', ['activePage' => 'reports'])
        @endif
    </x-slot:nav>

    @php
        $metricDatasets = [
            'revenue' => $chartRows->map(fn (array $row): array => ['label' => $row['label'], 'value' => (float) $row['revenue']])->values(),
            'bookings' => collect($bookingChartRows),
            'payments' => collect($paymentChartRows),
        ];
    @endphp

    <div class="container workspace-page analytics-page">
        <div class="page-header-row page-header analytics-header">
            <div>
                <p class="eyebrow">Owner analytics</p>
                <h1>Reports &amp; Analytics</h1>
                <p>Monitor revenue, bookings, payments, and property performance.</p>
            </div>
            <div class="analytics-export-actions">
                <a class="btn btn-outline" href="{{ route($dashboardPrefix.'.reports.excel', $reportQuery) }}">
                    <i class="fa fa-file-excel"></i> Excel
                </a>
                <a class="btn btn-outline" href="{{ route($dashboardPrefix.'.reports.pdf', $reportQuery) }}">
                    <i class="fa fa-file-pdf"></i> PDF
                </a>
            </div>
        </div>

        <div class="analytics-period-bar">
            <div class="analytics-period-tabs" role="tablist" aria-label="Report period">
                @foreach (['weekly' => 'Weekly', 'monthly' => 'Monthly', 'annually' => 'Annually'] as $periodKey => $periodTitle)
                    <a
                        class="analytics-period-tab {{ $period === $periodKey ? 'active' : '' }}"
                        href="{{ route($dashboardPrefix.'.bookings.index', array_merge($reportQuery, ['period' => $periodKey])) }}"
                        @if ($period === $periodKey) aria-current="page" @endif
                    >{{ $periodTitle }}</a>
                @endforeach
            </div>

            <div class="analytics-active-period">
                <i class="fa fa-calendar"></i>
                <span>{{ $periodLabel }}</span>
            </div>

            <button class="btn btn-outline analytics-filter-toggle" type="button" aria-expanded="false" aria-controls="analytics-filter-panel">
                <i class="fa fa-sliders"></i> Filters
            </button>
        </div>

        <form class="analytics-filter-panel" id="analytics-filter-panel" method="GET" action="{{ route($dashboardPrefix.'.bookings.index') }}" hidden>
            <input type="hidden" name="section" value="reports">
            <input type="hidden" name="period" value="{{ $period }}">
            <input type="hidden" name="report_tab" value="{{ $activeReportTab }}">
            <div class="analytics-filter-grid">
                <div class="analytics-filter-field">
                    <label for="report-year">Year</label>
                    <select id="report-year" name="year">
                        @foreach (range(2000, now()->year) as $yearOption)
                            <option value="{{ $yearOption }}" @selected($selectedYear === $yearOption)>{{ $yearOption }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="analytics-filter-field">
                    <label for="report-quarter">Quarter</label>
                    <select id="report-quarter" name="quarter">
                        @foreach (['All', 'Q1', 'Q2', 'Q3', 'Q4', 'Custom'] as $quarterOption)
                            <option value="{{ $quarterOption }}" @selected($selectedQuarter === $quarterOption)>{{ $quarterOption }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="analytics-filter-field">
                    <label for="report-house">Property</label>
                    <select id="report-house" name="house_id">
                        <option value="">All my houses</option>
                        @foreach ($houses as $house)
                            <option value="{{ $house->house_id }}" @selected($selectedHouseId === $house->house_id)>{{ $house->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="analytics-filter-field">
                    <label for="report-from-date">From date</label>
                    <input id="report-from-date" type="date" name="from_date" value="{{ old('from_date', $fromDate) }}" required>
                </div>
                <div class="analytics-filter-field">
                    <label for="report-to-date">To date</label>
                    <input id="report-to-date" type="date" name="to_date" value="{{ old('to_date', $toDate) }}" required>
                </div>
            </div>
            @error('from_date') <p class="form-error">{{ $message }}</p> @enderror
            @error('to_date') <p class="form-error">{{ $message }}</p> @enderror
            <div class="analytics-filter-actions">
                <button class="btn btn-primary" type="submit">Apply filters</button>
                <a class="btn btn-outline" href="{{ route($dashboardPrefix.'.bookings.index', ['section' => 'reports']) }}">Reset</a>
            </div>
        </form>

        <nav class="analytics-main-tabs" aria-label="Reports sections">
            @foreach (['overview' => 'Overview', 'transactions' => 'Transactions', 'breakdown' => 'Breakdown'] as $tabKey => $tabTitle)
                <button type="button"
                    class="analytics-main-tab {{ $activeReportTab === $tabKey ? 'active' : '' }}"
                    data-report-tab="{{ $tabKey }}"
                    aria-selected="{{ $activeReportTab === $tabKey ? 'true' : 'false' }}">
                    {{ $tabTitle }}
                </button>
            @endforeach
        </nav>

        <section class="analytics-tab-panel {{ $activeReportTab === 'overview' ? 'active' : '' }}" data-report-panel="overview">
            <div class="analytics-chart-card card">
                <div class="analytics-card-header">
                    <div>
                        <span class="analytics-card-kicker">Business performance</span>
                        <h2 id="analytics-chart-title">Net Revenue</h2>
                    </div>
                    <div class="analytics-metric-tabs" role="tablist" aria-label="Chart metric">
                        <button type="button" class="active" data-chart-metric="revenue" aria-selected="true">Revenue</button>
                        <button type="button" data-chart-metric="bookings" aria-selected="false">Bookings</button>
                        <button type="button" data-chart-metric="payments" aria-selected="false">Payments</button>
                    </div>
                </div>
                <div class="analytics-chart-value">
                    <strong id="analytics-chart-headline">₱{{ number_format($totalRevenue, 2) }}</strong>
                    <span>{{ $periodLabel }}</span>
                </div>
                <div class="analytics-line-chart" id="analytics-line-chart" role="img" aria-label="Net revenue line chart"></div>
            </div>

            <div class="analytics-kpis">
                <article class="analytics-kpi card">
                    <span class="analytics-kpi-icon green"><i class="fa fa-wallet"></i></span>
                    <div><span>Payments received</span><strong>₱{{ number_format($totalReceived, 2) }}</strong><small>Verified payments</small></div>
                </article>
                <article class="analytics-kpi card">
                    <span class="analytics-kpi-icon red"><i class="fa fa-arrow-rotate-left"></i></span>
                    <div><span>Refunds</span><strong>₱{{ number_format($totalRefunded, 2) }}</strong><small>Recorded refunds</small></div>
                </article>
                <article class="analytics-kpi card">
                    <span class="analytics-kpi-icon blue"><i class="fa fa-calendar-plus"></i></span>
                    <div><span>Total bookings</span><strong>{{ $totalBookings }}</strong><small>Selected reporting period</small></div>
                </article>
                <article class="analytics-kpi card">
                    <span class="analytics-kpi-icon amber"><i class="fa fa-circle-check"></i></span>
                    <div><span>Completed stays</span><strong>{{ $completedBookings }}</strong><small>Completed bookings</small></div>
                </article>
            </div>

            <div class="analytics-secondary-grid">
                <section class="card analytics-secondary-card">
                    <div class="analytics-card-header">
                        <div><span class="analytics-card-kicker">Check-in activity</span><h2>Booking Activity</h2></div>
                    </div>
                    <div class="analytics-mini-bars">
                        @forelse ($bookingChartRows as $row)
                            @php $bookingMaximum = max(collect($bookingChartRows)->max('value'), 1); @endphp
                            <div class="analytics-mini-row">
                                <span>{{ $row['label'] }}</span>
                                <div class="analytics-mini-track"><i style="width: {{ min(100, (float) $row['value'] / $bookingMaximum * 100) }}%"></i></div>
                                <strong>{{ $row['value'] }}</strong>
                            </div>
                        @empty
                            <div class="analytics-empty">No booking activity in this range.</div>
                        @endforelse
                    </div>
                </section>

                <section class="card analytics-secondary-card">
                    <div class="analytics-card-header">
                        <div><span class="analytics-card-kicker">Property comparison</span><h2>Revenue by Property</h2></div>
                    </div>
                    <div class="analytics-mini-bars">
                        @php $houseMaximum = max(collect($revenueByHouse)->max('revenue'), 1); @endphp
                        @forelse ($revenueByHouse as $row)
                            <div class="analytics-mini-row analytics-property-row">
                                <span title="{{ $row['house'] }}">{{ $row['house'] }}</span>
                                <div class="analytics-mini-track"><i style="width: {{ min(100, max(0, (float) $row['revenue'] / $houseMaximum * 100)) }}%"></i></div>
                                <strong>₱{{ number_format($row['revenue'], 2) }}</strong>
                            </div>
                        @empty
                            <div class="analytics-empty">No property revenue in this range.</div>
                        @endforelse
                    </div>
                </section>
            </div>

            <div class="analytics-overview-bottom">
                <section class="card analytics-payment-methods">
                    <div class="analytics-card-header"><div><span class="analytics-card-kicker">Collected funds</span><h2>Verified Payment Methods</h2></div></div>
                    <div class="analytics-method-list">
                        @forelse ($paymentMethods as $method => $amount)
                            @php $methodShare = $totalReceived > 0 ? (float) $amount / $totalReceived * 100 : 0; @endphp
                            <div class="analytics-method-row">
                                <span>{{ ucwords(str_replace('_', ' ', $method)) }}</span>
                                <div><i style="width: {{ $methodShare }}%"></i></div>
                                <strong>₱{{ number_format($amount, 2) }}</strong>
                            </div>
                        @empty
                            <div class="analytics-empty">No verified payments in this range.</div>
                        @endforelse
                    </div>
                </section>

                <section class="card analytics-recent">
                    <div class="analytics-card-header">
                        <div><span class="analytics-card-kicker">Latest activity</span><h2>Recent Transactions</h2></div>
                        <button type="button" class="analytics-text-button" data-switch-report-tab="transactions">View all <i class="fa fa-arrow-right"></i></button>
                    </div>
                    <div class="analytics-recent-list">
                        @forelse ($recentTransactions as $transaction)
                            <div class="analytics-recent-row">
                                <span class="analytics-transaction-icon {{ strtolower($transaction['type']) }}"><i class="fa {{ $transaction['type'] === 'Payment' ? 'fa-arrow-down' : 'fa-arrow-up' }}"></i></span>
                                <div>
                                    <strong>{{ $transaction['booking_reference'] ?: $transaction['reference'] }}</strong>
                                    <small>{{ $transaction['type'] }} · {{ $transaction['payment_method'] }} · {{ $transaction['date']->format('M j, Y') }}</small>
                                </div>
                                <strong class="{{ $transaction['type'] === 'Refund' ? 'refund' : '' }}">{{ $transaction['type'] === 'Refund' ? '-' : '+' }}₱{{ number_format($transaction['amount'], 2) }}</strong>
                            </div>
                        @empty
                            <div class="analytics-empty">No recent transactions.</div>
                        @endforelse
                    </div>
                </section>
            </div>
        </section>

        <section class="analytics-tab-panel {{ $activeReportTab === 'transactions' ? 'active' : '' }}" data-report-panel="transactions">
            <div class="card analytics-transactions-card">
                <div class="analytics-card-header">
                    <div><span class="analytics-card-kicker">Financial history</span><h2>Transaction Report</h2></div>
                    <details class="analytics-inline-filter">
                        <summary class="btn btn-outline"><i class="fa fa-sliders"></i> Filters</summary>
                        <form method="GET" action="{{ route($dashboardPrefix.'.bookings.index') }}" class="analytics-transaction-form">
                            <input type="hidden" name="section" value="reports">
                            @foreach ($reportQuery as $key => $value)
                                @if ($value !== null)<input type="hidden" name="{{ $key }}" value="{{ $value }}">@endif
                            @endforeach
                            <input type="hidden" name="report_tab" value="transactions">
                            <label>Search<input type="search" name="transaction_search" value="{{ $transactionFilters['search'] }}" placeholder="Reference, guest, property"></label>
                            <label>Type
                                <select name="transaction_type">
                                    @foreach (['All', 'Payments', 'Refunds'] as $option)
                                        <option value="{{ $option }}" @selected($transactionFilters['type'] === $option)>{{ $option }}</option>
                                    @endforeach
                                </select>
                            </label>
                            <label>Payment method
                                <input type="text" name="payment_method" value="{{ $transactionFilters['payment_method'] }}" placeholder="Cash, Card...">
                            </label>
                            <label>From date<input type="date" name="transaction_from" value="{{ $transactionFilters['from'] }}"></label>
                            <label>To date<input type="date" name="transaction_to" value="{{ $transactionFilters['to'] }}"></label>
                            <button class="btn btn-primary" type="submit">Apply</button>
                        </form>
                    </details>
                </div>
                <form class="analytics-transaction-search" method="GET" action="{{ route($dashboardPrefix.'.bookings.index') }}">
                    <input type="hidden" name="section" value="reports">
                    @foreach ($reportQuery as $key => $value)
                        @if ($value !== null)<input type="hidden" name="{{ $key }}" value="{{ $value }}">@endif
                    @endforeach
                    <input type="hidden" name="report_tab" value="transactions">
                    <i class="fa fa-magnifying-glass"></i>
                    <input type="search" name="transaction_search" value="{{ $transactionFilters['search'] }}" placeholder="Search transactions">
                    <button class="btn btn-outline" type="submit">Search</button>
                </form>
                <div class="table-wrap analytics-table-wrap">
                    <table>
                        <thead><tr><th>Reference</th><th>Booking / Guest</th><th>Property / Unit</th><th>Type</th><th>Method</th><th>Date</th><th>Amount</th><th>Status</th></tr></thead>
                        <tbody>
                            @forelse ($transactions as $transaction)
                                <tr>
                                    <td><strong>{{ $transaction['reference'] }}</strong></td>
                                    <td>{{ $transaction['booking_reference'] ?: '—' }}<small>{{ $transaction['guest'] ?: 'Guest' }}</small></td>
                                    <td>{{ $transaction['property'] ?: '—' }}<small>{{ $transaction['unit'] ?: '—' }}</small></td>
                                    <td><span class="analytics-type {{ strtolower($transaction['type']) }}">{{ $transaction['type'] }}</span></td>
                                    <td>{{ $transaction['payment_method'] }}</td>
                                    <td>{{ $transaction['date']->format('M j, Y') }}</td>
                                    <td><strong class="{{ $transaction['type'] === 'Refund' ? 'refund' : '' }}">{{ $transaction['type'] === 'Refund' ? '-' : '' }}₱{{ number_format($transaction['amount'], 2) }}</strong></td>
                                    <td>{{ $transaction['status'] }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="8" class="empty-table-cell">No transactions match these filters.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($transactions->hasPages())
                    <div class="analytics-pagination">{{ $transactions->withQueryString()->links() }}</div>
                @endif
            </div>
        </section>

        <section class="analytics-tab-panel {{ $activeReportTab === 'breakdown' ? 'active' : '' }}" data-report-panel="breakdown">
            <div class="analytics-breakdown">
                <nav class="analytics-breakdown-tabs" aria-label="Breakdown reports">
                    @foreach (['period' => 'By Period', 'property' => 'By Property', 'unit' => 'By Unit', 'quarter' => 'Quarterly', 'activity' => 'Booking Activity'] as $sectionKey => $sectionTitle)
                        <button type="button" class="{{ $activeBreakdownSection === $sectionKey ? 'active' : '' }}" data-breakdown-section="{{ $sectionKey }}">{{ $sectionTitle }}</button>
                    @endforeach
                </nav>
                <div class="card analytics-breakdown-card">
                    <div class="analytics-breakdown-panel {{ $activeBreakdownSection === 'period' ? 'active' : '' }}" data-breakdown-panel="period">
                        <div class="analytics-card-header"><div><span class="analytics-card-kicker">Revenue schedule</span><h2>Revenue by Period</h2></div></div>
                        <div class="table-wrap"><table><thead><tr><th>{{ ucfirst($period) }}</th><th>Payments received</th><th>Refunds</th><th>Net revenue</th></tr></thead><tbody>
                            @forelse ($revenueByPeriodRows as $row)<tr><td>{{ $row['period'] }}</td><td>₱{{ number_format($row['payments'], 2) }}</td><td class="refund">-₱{{ number_format($row['refunds'], 2) }}</td><td><strong>₱{{ number_format($row['revenue'], 2) }}</strong></td></tr>@empty<tr><td colspan="4" class="empty-table-cell">No revenue for these filters.</td></tr>@endforelse
                        </tbody></table></div>
                    </div>
                    <div class="analytics-breakdown-panel {{ $activeBreakdownSection === 'property' ? 'active' : '' }}" data-breakdown-panel="property">
                        <div class="analytics-card-header"><div><span class="analytics-card-kicker">Property performance</span><h2>Revenue by Property</h2></div></div>
                        <div class="table-wrap"><table><thead><tr><th>Property</th><th>Payments received</th><th>Refunds</th><th>Net revenue</th></tr></thead><tbody>
                            @forelse ($revenueByHouse as $row)<tr><td>{{ $row['house'] }}</td><td>₱{{ number_format($row['payments'], 2) }}</td><td class="refund">-₱{{ number_format($row['refunds'], 2) }}</td><td><strong>₱{{ number_format($row['revenue'], 2) }}</strong></td></tr>@empty<tr><td colspan="4" class="empty-table-cell">No properties found.</td></tr>@endforelse
                        </tbody></table></div>
                    </div>
                    <div class="analytics-breakdown-panel {{ $activeBreakdownSection === 'unit' ? 'active' : '' }}" data-breakdown-panel="unit">
                        <div class="analytics-card-header"><div><span class="analytics-card-kicker">Unit performance</span><h2>Revenue by Unit</h2></div></div>
                        <div class="table-wrap"><table><thead><tr><th>Unit</th><th>Property</th><th>Payments received</th><th>Refunds</th><th>Net revenue</th></tr></thead><tbody>
                            @forelse ($revenueByUnit as $row)<tr><td>{{ $row['unit'] }}</td><td>{{ $row['house'] }}</td><td>₱{{ number_format($row['payments'], 2) }}</td><td class="refund">-₱{{ number_format($row['refunds'], 2) }}</td><td><strong>₱{{ number_format($row['revenue'], 2) }}</strong></td></tr>@empty<tr><td colspan="5" class="empty-table-cell">No units found.</td></tr>@endforelse
                        </tbody></table></div>
                    </div>
                    <div class="analytics-breakdown-panel {{ $activeBreakdownSection === 'quarter' ? 'active' : '' }}" data-breakdown-panel="quarter">
                        <div class="analytics-card-header"><div><span class="analytics-card-kicker">{{ $selectedYear }}</span><h2>Quarterly Revenue</h2></div></div>
                        <div class="table-wrap"><table><thead><tr><th>Quarter</th><th>Payments received</th><th>Refunds</th><th>Net revenue</th></tr></thead><tbody>
                            @foreach ($quarterRevenueRows as $row)<tr><td>{{ $row['quarter'] }}</td><td>₱{{ number_format($row['payments'], 2) }}</td><td class="refund">-₱{{ number_format($row['refunds'], 2) }}</td><td><strong>₱{{ number_format($row['revenue'], 2) }}</strong></td></tr>@endforeach
                        </tbody></table></div>
                    </div>
                    <div class="analytics-breakdown-panel {{ $activeBreakdownSection === 'activity' ? 'active' : '' }}" data-breakdown-panel="activity">
                        <div class="analytics-card-header"><div><span class="analytics-card-kicker">Check-in summary</span><h2>Booking Activity by Property</h2></div></div>
                        <div class="table-wrap"><table><thead><tr><th>Property</th><th>Peak Check-in Month</th><th>Check-ins during Peak Month</th><th>Most Booked Dates</th></tr></thead><tbody>
                            @forelse ($activityRows as $row)<tr><td>{{ $row['house'] }}</td><td>{{ $row['most_booked_month'] }}</td><td>{{ $row['month_bookings'] }}</td><td>@forelse ($row['top_dates'] as $date)<span>{{ \Illuminate\Support\Carbon::parse($date['date'])->format('M j, Y') }} ({{ $date['bookings'] }})</span>@if (! $loop->last), @endif @empty No booked dates @endforelse</td></tr>@empty<tr><td colspan="4" class="empty-table-cell">No properties found.</td></tr>@endforelse
                        </tbody></table></div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <script>
        window.analyticsDatasets = @json($metricDatasets);
        window.analyticsTotalRevenue = {{ (float) $totalRevenue }};
        window.analyticsTotalBookings = {{ (int) $totalBookings }};
        window.analyticsTotalPayments = {{ (float) $totalReceived }};
    </script>
</x-shell>
