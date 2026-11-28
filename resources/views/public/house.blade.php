<x-shell title="{{ $house->name }} | MCTBS" :chat-house="$house">
    <x-slot:nav>@include('partials.nav', ['activePage' => 'transients'])</x-slot:nav>

    <style>
    .house-hero { position: relative; height: 380px; background: var(--primary); overflow: hidden; }
    .house-hero img { width: 100%; height: 100%; object-fit: cover; display: block; filter: brightness(.7); }
    .house-hero-placeholder { width: 100%; height: 100%; background: linear-gradient(135deg, var(--primary), #1a252f); display: flex; align-items: center; justify-content: center; font-size: 80px; color: rgba(255,255,255,.2); }
    .house-hero-overlay { position: absolute; bottom: 0; left: 0; right: 0; padding: 32px; background: linear-gradient(to top, rgba(17,24,39,.92), rgba(17,24,39,.54) 58%, rgba(17,24,39,.08)); color: #fff; text-shadow: 0 1px 3px rgba(0,0,0,.65); }
    .house-hero-overlay h1 { color: #fff; font-size: 30px; font-weight: 800; margin-bottom: 6px; }
    .house-hero-overlay .loc { color: rgba(255,255,255,.96); font-size: 14px; display: flex; align-items: center; gap: 6px; }
    .stars { color: #f39c12; font-size: 14px; display: inline-flex; gap: 2px; }
    .rating-pill { display: inline-flex; align-items: center; gap: 6px; background: #fff8e1; border: 1px solid #ffe082; color: #7d5c00; padding: 4px 12px; border-radius: 999px; font-size: 13px; font-weight: 700; }
    .section-title { font-size: 18px; font-weight: 700; margin-bottom: 16px; padding-bottom: 10px; border-bottom: 2px solid var(--border); display: flex; align-items: center; gap: 10px; }
    .section-title i { color: var(--primary); }
    .unit-row-card { background: var(--card); border: 1px solid var(--border); border-radius: 12px; overflow: hidden; display: flex; transition: box-shadow .2s, transform .2s; text-decoration: none; color: inherit; }
    .unit-row-card:hover { box-shadow: 0 8px 24px rgba(0,0,0,.1); transform: translateY(-2px); text-decoration: none; }
    .unit-row-card-img { width: 200px; flex-shrink: 0; object-fit: cover; display: block; }
    .unit-row-card-img-placeholder { width: 200px; flex-shrink: 0; background: linear-gradient(135deg, #2c3e5015, #e67e2215); display: flex; align-items: center; justify-content: center; font-size: 40px; color: var(--border); }
    .unit-row-card-body { padding: 18px; flex: 1; display: flex; flex-direction: column; justify-content: space-between; }
    .unit-row-card-body h3 { font-size: 16px; font-weight: 700; margin-bottom: 6px; }
    .unit-amenity-tag { display: inline-flex; align-items: center; gap: 4px; background: var(--bg); border: 1px solid var(--border); padding: 3px 10px; border-radius: 999px; font-size: 11px; color: var(--text-muted); }
    .review-card { background: var(--card); border: 1px solid var(--border); border-radius: 12px; padding: 20px; }
    .review-avatar { width: 42px; height: 42px; border-radius: 50%; background: var(--accent); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 14px; font-weight: 700; flex-shrink: 0; }
    .contact-link { display: flex; align-items: center; gap: 12px; padding: 12px 16px; border: 1px solid var(--border); border-radius: 10px; background: var(--card); text-decoration: none; color: var(--text); transition: all .15s; font-size: 14px; }
    .contact-link:hover { border-color: var(--accent); background: #fef5e7; text-decoration: none; }
    .contact-icon { width: 38px; height: 38px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 18px; flex-shrink: 0; }
    .contact-email { min-width: 0; overflow-wrap: anywhere; }
    .house-policy-grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 14px; }
    .house-policy-card { min-width: 0; padding: 16px; border: 1px solid var(--border); border-radius: 10px; background: var(--card); color: var(--text); line-height: 1.6; overflow-wrap: anywhere; }
    @media (max-width: 900px) { .house-policy-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
    @media (max-width: 560px) {
        .house-hero { height: 320px; }
        .house-hero-overlay { padding: 20px; }
        .house-hero-overlay h1 { font-size: 25px; }
        .house-policy-grid { grid-template-columns: minmax(0, 1fr); }
    }
    </style>

    <div class="house-hero">
        @if ($house->cover_photo)
            <img src="{{ asset('storage/' . $house->cover_photo) }}" alt="{{ $house->name }}">
        @else
            <div class="house-hero-placeholder"><i class="fa fa-building"></i></div>
        @endif
        <div class="house-hero-overlay">
            <a href="{{ route('public.transients') }}" style="color:rgba(255,255,255,.75);font-size:13px;text-decoration:none;display:inline-flex;align-items:center;gap:6px;margin-bottom:10px">
                <i class="fa fa-arrow-left"></i> Back to all transients
            </a>
            <h1>{{ $house->name }}</h1>
            <div class="loc">
                <i class="fa fa-map-marker-alt"></i>
                {{ $house->address }}, {{ $house->city }}{{ $house->barangay ? ', '.$house->barangay : '' }}
            </div>
        </div>
    </div>

    <div class="container" style="margin-top:32px">
        <div class="row" style="align-items:flex-start">

            <div class="col-2" style="display:flex;flex-direction:column;gap:32px">

                <div>
                    <div class="section-title"><i class="fa fa-info-circle"></i> About This Property</div>
                    <div style="display:flex;align-items:center;gap:12px;margin-bottom:14px;flex-wrap:wrap">
                        @if ($avgRating)
                            <div class="rating-pill"><i class="fa fa-star"></i> {{ $avgRating }} / 5.0</div>
                        @endif
                        <span class="text-muted fs-sm">{{ $reviews->count() }} guest review{{ $reviews->count() === 1 ? '' : 's' }}</span>
                        <span style="background:#eafaf1;color:var(--success);padding:4px 12px;border-radius:999px;font-size:13px;font-weight:600">
                            <i class="fa fa-check-circle"></i> Active
                        </span>
                    </div>
                    @if ($house->description)
                        <p style="color:var(--text-muted);line-height:1.8;margin-bottom:16px">{!! nl2br(e($house->description)) !!}</p>
                    @endif
                </div>

                @php
                    $housePolicies = preg_split('/\r\n|\r|\n/', (string) $house->policies, flags: PREG_SPLIT_NO_EMPTY) ?: [];
                @endphp
                @if ($housePolicies !== [])
                    <div>
                        <div class="section-title"><i class="fa fa-clipboard-list"></i> House Policies</div>
                        <div class="house-policy-grid">
                            @foreach ($housePolicies as $policy)
                                <div class="house-policy-card">{{ trim($policy) }}</div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div>
                    <div class="section-title"><i class="fa fa-door-open"></i> Available Units</div>
                    @if ($units->isNotEmpty())
                        <div style="display:flex;flex-direction:column;gap:16px">
                            @foreach ($units as $u)
                                <a href="{{ route('public.unit', $u) }}" class="unit-row-card">
                                    @if ($u->photos->isNotEmpty())
                                        <img src="{{ asset('storage/' . $u->photos->first()->photo_path) }}" class="unit-row-card-img" alt="{{ $u->unit_type }} photo">
                                    @elseif ($house->cover_photo)
                                        <img src="{{ asset('storage/' . $house->cover_photo) }}" class="unit-row-card-img" alt="{{ $house->name }} cover photo">
                                    @else
                                        <div class="unit-row-card-img-placeholder"><i class="fa fa-door-open"></i></div>
                                    @endif
                                    <div class="unit-row-card-body">
                                        <div>
                                            <h3>{{ $u->unit_type }}</h3>
                                            <div style="display:flex;align-items:center;gap:12px;margin-bottom:10px;flex-wrap:wrap">
                                                <span class="fs-sm text-muted"><i class="fa fa-users"></i> Capacity {{ $u->capacity }} standard; up to {{ $u->maximum_capacity ?? $u->capacity }} counted guests</span>
                                                @if ($u->child_free_through_age !== null)
                                                    <span class="fs-sm text-muted"><i class="fa fa-child"></i> Ages {{ $u->child_free_through_age }} and under are free and excluded</span>
                                                @endif
                                                <span class="fs-sm text-muted"><i class="fa fa-moon"></i> ₱{{ number_format($u->price, 2) }}/night</span>
                                            </div>
                                            @if ($u->description)
                                                <p class="fs-sm text-muted" style="margin-bottom:10px;line-height:1.6">{{ \Illuminate\Support\Str::limit($u->description, 100, '...') }}</p>
                                            @endif
                                        </div>
                                        <div style="display:flex;align-items:center;justify-content:space-between;margin-top:14px;padding-top:12px;border-top:1px solid var(--border)">
                                            <div style="font-size:20px;font-weight:800;color:var(--primary)">₱{{ number_format($u->price, 2) }}<span style="font-size:12px;font-weight:400;color:var(--text-muted)">/night</span></div>
                                            <span class="btn btn-primary btn-sm">View Unit <i class="fa fa-arrow-right"></i></span>
                                        </div>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    @else
                        <div class="empty-state" style="padding:40px 20px">
                            <i class="fa fa-door-open"></i>
                            <h3>No units available</h3>
                            <p>This property has no active units at the moment.</p>
                        </div>
                    @endif
                </div>

                <div>
                    <div class="section-title"><i class="fa fa-star"></i> Guest Reviews</div>

                    @if ($reviews->isNotEmpty())
                        <div style="background:var(--card);border:1px solid var(--border);border-radius:12px;padding:20px;display:flex;align-items:center;gap:24px;margin-bottom:20px;flex-wrap:wrap">
                            <div style="text-align:center">
                                <div style="font-size:52px;font-weight:800;color:var(--accent);line-height:1">{{ $avgRating }}</div>
                                <div class="stars">
                                    @for ($i = 1; $i <= 5; $i++)
                                        <i class="fa fa-star{{ $i <= floor($avgRating) ? '' : ($i - $avgRating < 1 ? '-half-alt' : '-o') }}"></i>
                                    @endfor
                                </div>
                                <div style="font-size:12px;color:var(--text-muted);margin-top:4px">{{ $reviews->count() }} review{{ $reviews->count() === 1 ? '' : 's' }}</div>
                            </div>
                        </div>

                        <div style="display:flex;flex-direction:column;gap:14px">
                            @foreach ($reviews as $r)
                                @php
                                    $reviewGuestName = $r->displayGuestName();
                                @endphp
                                <div class="review-card">
                                    <div style="display:flex;align-items:center;gap:12px;margin-bottom:12px">
                                        <div class="review-avatar">{{ strtoupper(substr($reviewGuestName, 0, 1)) }}</div>
                                        <div>
                                            <div style="font-weight:600;font-size:14px">{{ $reviewGuestName }}</div>
                                            <div style="font-size:12px;color:var(--text-muted)">{{ $r->created_at->format('F Y') }}</div>
                                        </div>
                                        <div class="stars" style="margin-left:auto">
                                            @for ($i = 1; $i <= 5; $i++)
                                                <i class="fa fa-star{{ $i <= $r->rating ? '' : '-o' }}"></i>
                                            @endfor
                                        </div>
                                    </div>
                                    <p style="font-size:14px;color:var(--text-muted);line-height:1.7;margin:0">{{ $r->comment }}</p>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted fs-sm">No reviews yet — this property hasn't had any completed stays reviewed.</p>
                    @endif
                </div>

            </div>

            <div style="width:300px;flex-shrink:0;display:flex;flex-direction:column;gap:20px;position:sticky;top:80px">
                <div class="card">
                    <div class="card-header"><i class="fa fa-address-card" style="color:var(--accent)"></i> Contact the Host</div>
                    <div class="card-body" style="display:flex;flex-direction:column;gap:10px">
                        @if ($house->contact_number)
                            <a href="tel:{{ preg_replace('/[^0-9+]/', '', $house->contact_number) }}" class="contact-link">
                                <div class="contact-icon" style="background:#d4edda;color:var(--success)"><i class="fa fa-phone"></i></div>
                                <div>
                                    <div style="font-size:11px;color:var(--text-muted)">Phone</div>
                                    <div style="font-weight:600">{{ $house->contact_number }}</div>
                                </div>
                            </a>
                        @endif
                        @if ($house->owner->email)
                            <a href="mailto:{{ $house->owner->email }}" class="contact-link">
                                <div class="contact-icon" style="background:#d1ecf1;color:var(--info)"><i class="fa fa-envelope"></i></div>
                                <div>
                                    <div style="font-size:11px;color:var(--text-muted)">Email</div>
                                    <div class="contact-email" style="font-weight:600">{{ $house->owner->email }}</div>
                                </div>
                            </a>
                        @endif
                        @foreach ($house->social_links ?? [] as $socialLink)
                            @php
                                $socialName = trim($socialLink['name'] ?? '');
                                $socialIcon = match (strtolower($socialName)) {
                                    'facebook' => 'fa-brands fa-facebook',
                                    'instagram' => 'fa-brands fa-instagram',
                                    'messenger' => 'fa-brands fa-facebook-messenger',
                                    'whatsapp' => 'fa-brands fa-whatsapp',
                                    'tiktok' => 'fa-brands fa-tiktok',
                                    'telegram' => 'fa-brands fa-telegram',
                                    'youtube' => 'fa-brands fa-youtube',
                                    default => 'fa-solid fa-link',
                                };
                            @endphp
                            <a href="{{ $socialLink['url'] }}" class="contact-link" target="_blank" rel="noopener noreferrer">
                                <div class="contact-icon" style="background:#e8f4fc;color:#1877f2"><i class="{{ $socialIcon }}"></i></div>
                                <div>
                                    <div style="font-size:11px;color:var(--text-muted)">Social media</div>
                                    <div style="font-weight:600">{{ $socialName }}</div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>

            </div>
        </div>
    </div>

</x-shell>
