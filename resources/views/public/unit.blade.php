<x-shell title="{{ $unit->unit_type }} | MCTBS">
    <x-slot:nav>@include('partials.nav', ['activePage' => 'transients'])</x-slot:nav>

    <style>
    .unit-photo-placeholder {
        width: 100%; height: 360px; border-radius: 14px;
        background: linear-gradient(135deg, #2c3e5015, #e67e2215);
        display: flex; align-items: center; justify-content: center;
        font-size: 72px; color: var(--border);
    }
    .unit-gallery-main { position: relative; }
    .unit-gallery-main img { display: block; width: 100%; height: 360px; border-radius: 14px; object-fit: cover; }
    .unit-gallery-thumbnails { display: flex; gap: 10px; margin-top: 10px; overflow-x: auto; padding: 2px 2px 8px; }
    .unit-gallery-thumbnail { flex: 0 0 82px; width: 82px; height: 62px; padding: 0; overflow: hidden; border: 2px solid transparent; border-radius: 8px; background: none; cursor: pointer; }
    .unit-gallery-thumbnail[aria-current="true"] { border-color: var(--primary); }
    .unit-gallery-thumbnail img { display: block; width: 100%; height: 100%; object-fit: cover; }
    .unit-gallery-count { position: absolute; right: 12px; bottom: 12px; padding: 5px 10px; border-radius: 999px; background: rgba(0,0,0,.65); color: #fff; font-size: 12px; }
    .section-title {
        font-size: 17px; font-weight: 700; margin-bottom: 14px;
        padding-bottom: 10px; border-bottom: 2px solid var(--border);
        display: flex; align-items: center; gap: 8px;
    }
    .section-title i { color: var(--primary); }
    .detail-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 12px; }
    .detail-item { background: var(--bg); border: 1px solid var(--border); border-radius: 10px; padding: 14px; display: flex; align-items: center; gap: 12px; }
    .detail-icon { width: 38px; height: 38px; border-radius: 8px; background: #fef5e7; color: var(--primary); display: flex; align-items: center; justify-content: center; font-size: 16px; flex-shrink: 0; }
    .policy-item { display: flex; align-items: flex-start; gap: 12px; padding: 12px 0; border-bottom: 1px solid var(--border); }
    .policy-item:last-child { border-bottom: none; }
    .policy-icon { width: 34px; height: 34px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 14px; flex-shrink: 0; }
    .cal-wrap { background: var(--card); border: 1px solid var(--border); border-radius: 12px; overflow: hidden; }
    .cal-top { background: #fef3c7; color: var(--primary-dk); padding: 14px 20px; display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid var(--border); }
    .cal-top h3 { color: var(--primary-dk); font-size: 15px; font-weight: 600; margin: 0; }
    .cal-top button { background: rgba(146,64,14,.1); border: none; color: var(--primary-dk); border-radius: 6px; padding: 4px 12px; cursor: pointer; font-size: 13px; }
    .cal-top button:hover { background: rgba(146,64,14,.18); }
    .cal-body { padding: 16px; }
    .cal-days-header { display: grid; grid-template-columns: repeat(7,1fr); margin-bottom: 6px; }
    .cal-days-header span { text-align: center; font-size: 11px; font-weight: 700; color: var(--text-muted); padding: 4px 0; }
    .cal-grid { display: grid; grid-template-columns: repeat(7,1fr); gap: 3px; }
    .cal-cell { aspect-ratio: 1; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 500; cursor: default; position: relative; }
    .cal-cell.other { opacity: .25; }
    .cal-cell.today { font-weight: 800; color: var(--primary); }
    .cal-cell.status-available   { background: #d4edda; color: #155724;  border: 1px solid #c3e6cb; }
    .cal-cell.status-booked      { background: #f8d7da; color: #721c24;  border: 1px solid #f5c6cb; text-decoration: line-through; }
    .cal-cell.status-unavailable { background: #fff3cd; color: #856404;  border: 1px solid #ffc107; }
    .cal-cell.checkout-turnover { background: linear-gradient(to bottom, #f8d7da 0 50%, #d4edda 50% 100%); color: #374151; border: 1px solid #c3e6cb; text-decoration: none; }
    .cal-cell.past { background: #e5e7eb; border: 1px solid #d1d5db; color: #6b7280; opacity: 1; }
    .cal-legend { display: flex; gap: 14px; flex-wrap: wrap; margin-top: 12px; padding-top: 12px; border-top: 1px solid var(--border); }
    .cal-legend-item { display: flex; align-items: center; gap: 7px; font-size: 14px; color: var(--text-muted); }
    .cal-legend-dot { width: 15px; height: 15px; border-radius: 3px; flex-shrink: 0; }
    .unit-booking-column { width: 300px; flex-shrink: 0; }
    .book-sidebar { background: var(--card); border: 1px solid var(--border); border-radius: 14px; overflow: hidden; position: sticky; top: 80px; box-shadow: 0 8px 24px rgba(17,24,39,.08); }
    .book-sidebar-header { padding: 16px 20px; background: #fef3c7; color: var(--primary-dk); border-bottom: 1px solid var(--border); }
    .book-sidebar-header strong { display: block; color: var(--primary-dk); font-size: 15px; }
    .book-sidebar-header span { display: block; margin-top: 3px; color: var(--text-muted); font-size: 12px; }
    .book-sidebar-body { padding: 20px; }
    .booking-price { padding-bottom: 16px; border-bottom: 1px solid var(--border); }
    .booking-price strong { color: var(--text); font-size: 28px; line-height: 1.1; }
    .booking-price span { color: var(--text-muted); font-size: 13px; }
    .booking-price p { margin: 5px 0 0; color: var(--text-muted); font-size: 12px; }
    .booking-facts { display: grid; gap: 14px; margin: 16px 0; }
    .booking-fact { display: flex; align-items: flex-start; gap: 10px; min-width: 0; }
    .booking-fact i { width: 18px; margin-top: 2px; color: var(--primary); text-align: center; }
    .booking-fact div { min-width: 0; }
    .booking-fact span { display: block; color: var(--text-muted); font-size: 11px; }
    .booking-fact strong { display: block; color: var(--text); font-size: 13px; line-height: 1.45; overflow-wrap: anywhere; }
    .booking-note { margin: 14px 0 0; padding: 10px 11px; border-radius: 8px; background: #fffbeb; color: #713f12; font-size: 12px; line-height: 1.5; }
    .booking-availability-link { display: block; margin-top: 14px; color: var(--primary); font-size: 13px; font-weight: 700; text-align: center; text-decoration: none; }
    .booking-availability-link:hover { text-decoration: underline; }
    @media (max-width: 900px) {
        .unit-detail-column { order: 2; flex: 1 1 100%; }
        .unit-booking-column { order: 1; width: 100%; flex-shrink: 1; }
        .book-sidebar { position: static; }
    }
    </style>

    <div class="container" style="margin-top:24px">

        <div style="display:flex;align-items:center;gap:8px;font-size:13px;color:var(--text-muted);margin-bottom:20px;flex-wrap:wrap">
            <a href="{{ route('public.transients') }}" style="color:var(--text-muted)">Transients</a>
            <i class="fa fa-chevron-right" style="font-size:10px"></i>
            <a href="{{ route('public.house', $unit->house) }}" style="color:var(--text-muted)">{{ $unit->house->name }}</a>
            <i class="fa fa-chevron-right" style="font-size:10px"></i>
            <span style="color:var(--text)">{{ $unit->unit_type }}</span>
        </div>

        <div class="row" style="align-items:flex-start">

            <div class="col-2 unit-detail-column" style="display:flex;flex-direction:column;gap:28px">

                <div>
                    @if ($unit->photos->isNotEmpty())
                        <div class="unit-gallery" data-unit-gallery>
                            <div class="unit-gallery-main">
                                <img
                                    data-gallery-main
                                    src="{{ asset('storage/' . $unit->photos->first()->photo_path) }}"
                                    alt="{{ $unit->unit_type }} photo 1"
                                >
                                @if ($unit->photos->count() > 1)
                                    <span class="unit-gallery-count" data-gallery-count>1 / {{ $unit->photos->count() }}</span>
                                @endif
                            </div>
                            @if ($unit->photos->count() > 1)
                                <div class="unit-gallery-thumbnails" aria-label="Unit photo gallery">
                                    @foreach ($unit->photos as $photo)
                                        <button
                                            class="unit-gallery-thumbnail"
                                            type="button"
                                            data-gallery-photo="{{ asset('storage/' . $photo->photo_path) }}"
                                            data-gallery-alt="{{ $unit->unit_type }} photo {{ $loop->iteration }}"
                                            data-gallery-index="{{ $loop->iteration }}"
                                            aria-label="View photo {{ $loop->iteration }}"
                                            aria-current="{{ $loop->first ? 'true' : 'false' }}"
                                        >
                                            <img src="{{ asset('storage/' . $photo->photo_path) }}" alt="">
                                        </button>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @elseif ($unit->house->cover_photo)
                        <img src="{{ asset('storage/' . $unit->house->cover_photo) }}" alt="{{ $unit->house->name }}" style="width:100%;height:360px;border-radius:14px;object-fit:cover">
                    @else
                        <div class="unit-photo-placeholder"><i class="fa fa-door-open"></i></div>
                    @endif
                </div>

                <div>
                    <h1 style="font-size:26px;font-weight:800;margin-bottom:6px">{{ $unit->unit_type }}</h1>
                    <div style="font-size:14px;color:var(--text-muted);display:flex;align-items:center;gap:8px;flex-wrap:wrap">
                        <span><i class="fa fa-building" style="color:var(--accent)"></i> {{ $unit->house->name }}</span>
                        <span>&bull;</span>
                        <span><i class="fa fa-map-marker-alt" style="color:var(--accent)"></i> {{ $unit->house->city }}{{ $unit->house->barangay ? ', '.$unit->house->barangay : '' }}</span>
                    </div>
                    @if ($unit->description)
                        <p style="margin-top:14px;color:var(--text-muted);line-height:1.8">{!! nl2br(e($unit->description)) !!}</p>
                    @endif
                </div>

                <div>
                    <div class="section-title"><i class="fa fa-list-check"></i> Unit Details</div>
                    <div class="detail-grid">
                        @foreach ($unitDetails as $d)
                            <div class="detail-item">
                                <div class="detail-icon"><i class="fa {{ $d['icon'] }}"></i></div>
                                <div>
                                    <div style="font-size:11px;color:var(--text-muted)">{{ $d['label'] }}</div>
                                    <div style="font-size:13px;font-weight:600">{{ $d['value'] }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                @if ($unit->house->policies)
                    <div>
                        <div class="section-title"><i class="fa fa-clipboard-list"></i> House Policies</div>
                        <div style="background:var(--bg);border:1px solid var(--border);border-radius:var(--radius);padding:16px;font-size:14px;line-height:1.8;color:var(--text)">
                            {!! nl2br(e($unit->house->policies)) !!}
                        </div>
                    </div>
                @else
                    <div>
                        <div class="section-title"><i class="fa fa-clipboard-list"></i> House Policies</div>
                        <div style="background:#fff8e1;border:1px solid #ffe082;border-radius:var(--radius);padding:16px;font-size:13px;line-height:1.8;color:#7d5c00">
                            No house policies have been provided for this property.
                        </div>
                    </div>
                @endif

                <div>
                    <div class="section-title"><i class="fa fa-star"></i> Reviews</div>
                    <div class="card" style="padding:16px">
                        @if ($reviewCount > 0)
                            <p class="mb-3">
                                <strong>{{ number_format((float) $averageRating, 1) }} out of 5</strong>
                                <span class="text-muted">· {{ $reviewCount }} {{ \Illuminate\Support\Str::plural('review', $reviewCount) }}</span>
                            </p>
                            @foreach ($reviews as $review)
                                <article style="padding:14px 0;border-top:1px solid var(--border)">
                                    <div class="flex-between" style="gap:12px">
                                        <strong>{{ $review->displayGuestName() }}</strong>
                                        <span class="text-muted fs-sm">{{ $review->created_at->format('M j, Y') }}</span>
                                    </div>
                                    <div aria-label="{{ $review->rating }} out of 5 stars" style="color:var(--accent);margin-top:4px">
                                        @for ($star = 1; $star <= $review->rating; $star++)
                                            <i class="fa fa-star" aria-hidden="true"></i>
                                        @endfor
                                    </div>
                                    @if ($review->comment)
                                        <p style="margin:8px 0 0">{{ $review->comment }}</p>
                                    @endif
                                </article>
                            @endforeach
                        @else
                            <p class="text-muted mb-0">No reviews yet</p>
                        @endif
                    </div>
                </div>

                <div>
                    <div class="section-title" id="unit-availability"><i class="fa fa-calendar-alt"></i> Availability Calendar</div>
                    <div class="cal-wrap">
                        <div class="cal-top">
                            <button onclick="prevMonth()"><i class="fa fa-chevron-left"></i></button>
                            <h3 id="calTitle"></h3>
                            <button onclick="nextMonth()"><i class="fa fa-chevron-right"></i></button>
                        </div>
                        <div class="cal-body">
                            <div class="cal-days-header">
                                @foreach (['Sun','Mon','Tue','Wed','Thu','Fri','Sat'] as $d)
                                    <span>{{ $d }}</span>
                                @endforeach
                            </div>
                            <div class="cal-grid" id="calGrid"></div>
                            <div class="cal-legend">
                                <div class="cal-legend-item"><div class="cal-legend-dot" style="background:#d4edda;border:1px solid #28a745"></div> Available</div>
                                <div class="cal-legend-item"><div class="cal-legend-dot" style="background:#f8d7da;border:1px solid #dc3545"></div> Booked</div>
                                <div class="cal-legend-item"><div class="cal-legend-dot" style="background:#fff3cd;border:1px solid #ffc107"></div> Unavailable</div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <aside class="unit-booking-column" aria-label="Delux Unit Summary">
                <div class="book-sidebar">
                    <div class="book-sidebar-header">
                        <strong>Delux Unit Summary</strong>
                        <span>Unit details at a glance</span>
                    </div>
                    <div class="book-sidebar-body">
                        <div class="booking-price">
                            <strong>₱{{ number_format($unit->price, 2) }}</strong>
                            <span>/ night</span>
                            <p>Listed nightly rate</p>
                        </div>
                        <div class="booking-facts">
                            <div class="booking-fact">
                                <i class="fa fa-building" aria-hidden="true"></i>
                                <div>
                                    <span>Property</span>
                                    <strong>{{ $unit->house->name }} · {{ $unit->house->city }}</strong>
                                </div>
                            </div>
                            <div class="booking-fact">
                                <i class="fa fa-users" aria-hidden="true"></i>
                                <div>
                                    <span>Guest capacity</span>
                                    <strong>{{ $unit->capacity }} standard · up to {{ $unit->maximum_capacity ?? $unit->capacity }} counted guests</strong>
                                </div>
                            </div>
                            <div class="booking-fact">
                                <i class="fa fa-sign-in-alt" aria-hidden="true"></i>
                                <div>
                                    <span>Check-in</span>
                                    <strong>{{ \App\Support\UnitTimeFormatter::format((string) $unit->check_in_time) }}</strong>
                                </div>
                            </div>
                            <div class="booking-fact">
                                <i class="fa fa-sign-out-alt" aria-hidden="true"></i>
                                <div>
                                    <span>Check-out</span>
                                    <strong>{{ \App\Support\UnitTimeFormatter::format((string) $unit->check_out_time) }}</strong>
                                </div>
                            </div>
                            @if ($unit->child_free_through_age !== null)
                                <div class="booking-fact">
                                    <i class="fa fa-child" aria-hidden="true"></i>
                                    <div>
                                        <span>Free child age</span>
                                        <strong>{{ $unit->child_free_through_age }} and under; not counted toward capacity</strong>
                                    </div>
                                </div>
                            @endif
                            @if ((int) ($unit->maximum_capacity ?? $unit->capacity) > (int) $unit->capacity)
                                <div class="booking-fact">
                                    <i class="fa fa-user-plus" aria-hidden="true"></i>
                                    <div>
                                        <span>Extra guest fee</span>
                                        <strong>₱{{ number_format((float) $unit->extra_guest_fee, 2) }} per guest/night above standard capacity</strong>
                                    </div>
                                </div>
                            @endif
                        </div>
                        @auth
                            @if (auth()->user()->role?->name === 'Guest')
                                <a href="{{ route('guest.book', $unit) }}" id="bookBtn" class="btn btn-primary btn-block btn-lg">
                                    <i class="fa fa-calendar-check"></i> Book Now
                                </a>
                            @else
                                <div class="alert alert-info" style="font-size:13px;margin:0">
                                    <i class="fa fa-info-circle"></i> Only guest accounts can make bookings.
                                </div>
                            @endif
                        @else
                            <a href="{{ route('guest.book', $unit) }}" class="btn btn-primary btn-block btn-lg">
                                <i class="fa fa-sign-in-alt"></i> Login to Book
                            </a>
                            <div class="text-center mt-1 fs-sm text-muted">
                                No account? <a href="{{ route('register') }}">Register free</a>
                            </div>
                        @endauth

                        <p class="booking-note">
                            <i class="fa fa-info-circle" aria-hidden="true"></i>
                            Choose your dates and guest count to review the booking total.
                        </p>
                        <a class="booking-availability-link" href="#unit-availability">
                            <i class="fa fa-calendar-alt" aria-hidden="true"></i> Check the availability calendar
                        </a>
                    </div>
                </div>
            </aside>

        </div>
    </div>

    <script>
    // Availability Calendar
    const blockedDates = @json($blocked);
    const blockedNotes = @json($blockedNotes);
    const checkoutDates = @json($checkoutDates);
    const checkInDates = @json($checkInDates);
    const calendarToday = @json($calendarToday->toDateString());
    const checkInTime = @json(\App\Support\UnitTimeFormatter::format((string) $unit->check_in_time));
    const checkOutTime = @json(\App\Support\UnitTimeFormatter::format((string) $unit->check_out_time));
    const [todayYear, todayMonth, todayDay] = calendarToday.split('-').map(Number);
    let calYear  = todayYear;
    let calMonth = todayMonth - 1;
    const months = ['January','February','March','April','May','June','July','August','September','October','November','December'];

    function renderCal() {
        const today     = new Date(todayYear, todayMonth - 1, todayDay);
        const firstDay  = new Date(calYear, calMonth, 1).getDay();
        const daysInMo  = new Date(calYear, calMonth+1, 0).getDate();
        document.getElementById('calTitle').textContent = months[calMonth]+' '+calYear;
        let html = '';
        for (let i=0; i<firstDay; i++) html += '<div class="cal-cell other"></div>';
        for (let d=1; d<=daysInMo; d++) {
            const dateStr = calYear+'-'+String(calMonth+1).padStart(2,'0')+'-'+String(d).padStart(2,'0');
            const cellDate = new Date(calYear, calMonth, d);
            const isPast   = cellDate < new Date(today.getFullYear(), today.getMonth(), today.getDate());
            const isToday  = (d === today.getDate() && calMonth === today.getMonth() && calYear === today.getFullYear());

            let cls = 'status-available';
            if (blockedDates[dateStr] === 'BOOKED') {
                cls = 'status-booked';
            } else if (blockedDates[dateStr] === 'UNAVAILABLE') {
                cls = 'status-unavailable';
            } else if (checkoutDates[dateStr] && !checkInDates[dateStr]) {
                cls = 'checkout-turnover';
            } else if (isPast) {
                cls = 'past';
            }
            if (isToday) cls += ' today';
            let dataInfo = '';
            if (!isPast) {
                if (blockedDates[dateStr] === 'BOOKED') {
                    dataInfo = `data-info='${encodeURIComponent(JSON.stringify({type:'BOOKED', note: blockedNotes[dateStr] ?? 'This date is already booked'}))}'`;
                } else if (blockedDates[dateStr] === 'UNAVAILABLE') {
                    dataInfo = `data-info='${encodeURIComponent(JSON.stringify({type:'UNAVAILABLE', note: blockedNotes[dateStr] ?? ''}))}'`;
                } else if (checkoutDates[dateStr] && !checkInDates[dateStr]) {
                    dataInfo = `data-info='${encodeURIComponent(JSON.stringify({type:'TURNOVER', note: 'Check-out at ' + checkOutTime + '; new check-in available at ' + checkInTime + '.'}))}'`;
                }
            }
            html += `<div class="cal-cell ${cls}" ${dataInfo}>${d}</div>`;
        }
        document.getElementById('calGrid').innerHTML = html;
    }
    function prevMonth() { if (calMonth===0){calMonth=11;calYear--;}else{calMonth--;} renderCal(); }
    function nextMonth() { if (calMonth===11){calMonth=0;calYear++;}else{calMonth++;} renderCal(); }
    renderCal();

    // Guest tooltip card
    const guestTooltip = document.createElement('div');
    guestTooltip.style.cssText = `
        display:none;position:fixed;z-index:99999;pointer-events:none;
        background:#fff;border:1px solid #dde1e7;border-radius:10px;
        box-shadow:0 4px 20px rgba(0,0,0,.15);padding:14px 16px;
        min-width:180px;font-size:13px;line-height:1.8;
    `;
    document.body.appendChild(guestTooltip);

    document.addEventListener('mouseover', function(e) {
        const cell = e.target.closest('.cal-cell[data-info]');
        if (!cell) { guestTooltip.style.display='none'; return; }
        const info = JSON.parse(decodeURIComponent(cell.getAttribute('data-info')));
        const icon = info.type === 'TURNOVER' ? '↔' : (info.type.includes('maintenance') ? '🔧' : '🚫');
        guestTooltip.replaceChildren();
        const heading = document.createElement('div');
        heading.style.cssText = 'font-weight:700;color:#2c3e50;margin-bottom:4px';
        heading.textContent = info.type === 'TURNOVER' ? `${icon} Check-out / new check-in` : `${icon} Not Available`;
        const reason = document.createElement('div');
        reason.style.cssText = 'color:#666;font-size:12px';
        reason.textContent = info.note || 'This date is not available for booking.';
        guestTooltip.append(heading, reason);
        guestTooltip.style.display = 'block';
    });
    document.addEventListener('mousemove', function(e) {
        if (guestTooltip.style.display === 'none') return;
        guestTooltip.style.left = Math.min(e.clientX+15, window.innerWidth-200) + 'px';
        guestTooltip.style.top  = Math.max(10, e.clientY - guestTooltip.offsetHeight - 10) + 'px';
    });
    document.addEventListener('mouseout', function(e) {
        const cell = e.target.closest('.cal-cell[data-info]');
        if (cell) guestTooltip.style.display = 'none';
    });

    </script>
    <script>
        document.querySelectorAll('[data-unit-gallery]').forEach((gallery) => {
            const mainImage = gallery.querySelector('[data-gallery-main]');
            const count = gallery.querySelector('[data-gallery-count]');

            gallery.querySelectorAll('[data-gallery-photo]').forEach((thumbnail) => {
                thumbnail.addEventListener('click', () => {
                    mainImage.src = thumbnail.dataset.galleryPhoto;
                    mainImage.alt = thumbnail.dataset.galleryAlt;
                    gallery.querySelectorAll('[data-gallery-photo]').forEach((photo) => {
                        photo.setAttribute('aria-current', photo === thumbnail ? 'true' : 'false');
                    });

                    if (count) {
                        count.textContent = `${thumbnail.dataset.galleryIndex} / ${gallery.querySelectorAll('[data-gallery-photo]').length}`;
                    }
                });
            });
        });
    </script>
</x-shell>
