<?php if (isset($component)) { $__componentOriginalcffbe109f495178c1251f7f6dc1fe742 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalcffbe109f495178c1251f7f6dc1fe742 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.shell','data' => ['title' => ''.e($unit->unit_type).' | MCTBS']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('shell'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => ''.e($unit->unit_type).' | MCTBS']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

     <?php $__env->slot('nav', null, []); ?> <?php echo $__env->make('partials.nav', ['activePage' => 'transients'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?> <?php $__env->endSlot(); ?>

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
    .unit-gallery-thumbnail[aria-current="true"] { border-color: var(--accent); }
    .unit-gallery-thumbnail img { display: block; width: 100%; height: 100%; object-fit: cover; }
    .unit-gallery-count { position: absolute; right: 12px; bottom: 12px; padding: 5px 10px; border-radius: 999px; background: rgba(0,0,0,.65); color: #fff; font-size: 12px; }
    .section-title {
        font-size: 17px; font-weight: 700; margin-bottom: 14px;
        padding-bottom: 10px; border-bottom: 2px solid var(--border);
        display: flex; align-items: center; gap: 8px;
    }
    .section-title i { color: var(--accent); }
    .detail-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 12px; }
    .detail-item { background: var(--bg); border: 1px solid var(--border); border-radius: 10px; padding: 14px; display: flex; align-items: center; gap: 12px; }
    .detail-icon { width: 38px; height: 38px; border-radius: 8px; background: #fef5e7; color: var(--accent); display: flex; align-items: center; justify-content: center; font-size: 16px; flex-shrink: 0; }
    .policy-item { display: flex; align-items: flex-start; gap: 12px; padding: 12px 0; border-bottom: 1px solid var(--border); }
    .policy-item:last-child { border-bottom: none; }
    .policy-icon { width: 34px; height: 34px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 14px; flex-shrink: 0; }
    .cal-wrap { background: var(--card); border: 1px solid var(--border); border-radius: 12px; overflow: hidden; }
    .cal-top { background: var(--primary-lt); color: #fff; padding: 14px 20px; display: flex; align-items: center; justify-content: space-between; }
    .cal-top h3 { font-size: 15px; font-weight: 600; margin: 0; }
    .cal-top button { background: rgba(255,255,255,.2); border: none; color: #fff; border-radius: 6px; padding: 4px 12px; cursor: pointer; font-size: 13px; }
    .cal-top button:hover { background: rgba(255,255,255,.3); }
    .cal-body { padding: 16px; }
    .cal-days-header { display: grid; grid-template-columns: repeat(7,1fr); margin-bottom: 6px; }
    .cal-days-header span { text-align: center; font-size: 11px; font-weight: 700; color: var(--text-muted); padding: 4px 0; }
    .cal-grid { display: grid; grid-template-columns: repeat(7,1fr); gap: 3px; }
    .cal-cell { aspect-ratio: 1; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 500; cursor: default; position: relative; }
    .cal-cell.other { opacity: .25; }
    .cal-cell.today { font-weight: 800; color: var(--accent); }
    .cal-cell.status-available   { background: #d4edda; color: #155724;  border: 1px solid #c3e6cb; }
    .cal-cell.status-booked      { background: #f8d7da; color: #721c24;  border: 1px solid #f5c6cb; text-decoration: line-through; }
    .cal-cell.status-unavailable { background: #fff3cd; color: #856404;  border: 1px solid #ffc107; }
    .cal-cell.checkout-turnover { background: linear-gradient(to bottom, #f8d7da 0 50%, #d4edda 50% 100%); color: #374151; border: 1px solid #c3e6cb; text-decoration: none; }
    .cal-cell.past { background: #e5e7eb; border: 1px solid #d1d5db; color: #6b7280; opacity: 1; }
    .cal-legend { display: flex; gap: 14px; flex-wrap: wrap; margin-top: 12px; padding-top: 12px; border-top: 1px solid var(--border); }
    .cal-legend-item { display: flex; align-items: center; gap: 7px; font-size: 14px; color: var(--text-muted); }
    .cal-legend-dot { width: 15px; height: 15px; border-radius: 3px; flex-shrink: 0; }
    .inquiry-box { background: var(--card); border: 1px solid var(--border); border-radius: 14px; overflow: hidden; }
    .inquiry-box-header { background: var(--primary-lt); color: #fff; padding: 18px 22px; }
    .inquiry-box-header h3 { font-size: 16px; font-weight: 700; margin-bottom: 3px; }
    .inquiry-box-header p  { font-size: 13px; opacity: .8; margin: 0; }
    .inquiry-box-body { padding: 20px; }
    .book-sidebar { background: var(--card); border: 1px solid var(--border); border-radius: 14px; overflow: hidden; position: sticky; top: 80px; }
    .book-sidebar-body { padding: 20px; }
    </style>

    <div class="container" style="margin-top:24px">

        <div style="display:flex;align-items:center;gap:8px;font-size:13px;color:var(--text-muted);margin-bottom:20px;flex-wrap:wrap">
            <a href="<?php echo e(route('public.transients')); ?>" style="color:var(--text-muted)">Transients</a>
            <i class="fa fa-chevron-right" style="font-size:10px"></i>
            <a href="<?php echo e(route('public.house', $unit->house)); ?>" style="color:var(--text-muted)"><?php echo e($unit->house->name); ?></a>
            <i class="fa fa-chevron-right" style="font-size:10px"></i>
            <span style="color:var(--text)"><?php echo e($unit->unit_type); ?></span>
        </div>

        <div class="row" style="align-items:flex-start">

            <div class="col-2" style="display:flex;flex-direction:column;gap:28px">

                <div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($unit->photos->isNotEmpty()): ?>
                        <div class="unit-gallery" data-unit-gallery>
                            <div class="unit-gallery-main">
                                <img
                                    data-gallery-main
                                    src="<?php echo e(asset('storage/' . $unit->photos->first()->photo_path)); ?>"
                                    alt="<?php echo e($unit->unit_type); ?> photo 1"
                                >
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($unit->photos->count() > 1): ?>
                                    <span class="unit-gallery-count" data-gallery-count>1 / <?php echo e($unit->photos->count()); ?></span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($unit->photos->count() > 1): ?>
                                <div class="unit-gallery-thumbnails" aria-label="Unit photo gallery">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $unit->photos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $photo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                        <button
                                            class="unit-gallery-thumbnail"
                                            type="button"
                                            data-gallery-photo="<?php echo e(asset('storage/' . $photo->photo_path)); ?>"
                                            data-gallery-alt="<?php echo e($unit->unit_type); ?> photo <?php echo e($loop->iteration); ?>"
                                            data-gallery-index="<?php echo e($loop->iteration); ?>"
                                            aria-label="View photo <?php echo e($loop->iteration); ?>"
                                            aria-current="<?php echo e($loop->first ? 'true' : 'false'); ?>"
                                        >
                                            <img src="<?php echo e(asset('storage/' . $photo->photo_path)); ?>" alt="">
                                        </button>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                </div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    <?php elseif($unit->house->cover_photo): ?>
                        <img src="<?php echo e(asset('storage/' . $unit->house->cover_photo)); ?>" alt="<?php echo e($unit->house->name); ?>" style="width:100%;height:360px;border-radius:14px;object-fit:cover">
                    <?php else: ?>
                        <div class="unit-photo-placeholder"><i class="fa fa-door-open"></i></div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                <div>
                    <h1 style="font-size:26px;font-weight:800;margin-bottom:6px"><?php echo e($unit->unit_type); ?></h1>
                    <div style="font-size:14px;color:var(--text-muted);display:flex;align-items:center;gap:8px;flex-wrap:wrap">
                        <span><i class="fa fa-building" style="color:var(--accent)"></i> <?php echo e($unit->house->name); ?></span>
                        <span>&bull;</span>
                        <span><i class="fa fa-map-marker-alt" style="color:var(--accent)"></i> <?php echo e($unit->house->city); ?><?php echo e($unit->house->barangay ? ', '.$unit->house->barangay : ''); ?></span>
                    </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($unit->description): ?>
                        <p style="margin-top:14px;color:var(--text-muted);line-height:1.8"><?php echo nl2br(e($unit->description)); ?></p>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                <div>
                    <div class="section-title"><i class="fa fa-list-check"></i> Unit Details</div>
                    <div class="detail-grid">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $unitDetails; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <div class="detail-item">
                                <div class="detail-icon"><i class="fa <?php echo e($d['icon']); ?>"></i></div>
                                <div>
                                    <div style="font-size:11px;color:var(--text-muted)"><?php echo e($d['label']); ?></div>
                                    <div style="font-size:13px;font-weight:600"><?php echo e($d['value']); ?></div>
                                </div>
                            </div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </div>
                </div>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($unit->house->policies): ?>
                    <div>
                        <div class="section-title"><i class="fa fa-clipboard-list"></i> House Policies</div>
                        <div style="background:var(--bg);border:1px solid var(--border);border-radius:var(--radius);padding:16px;font-size:14px;line-height:1.8;color:var(--text)">
                            <?php echo nl2br(e($unit->house->policies)); ?>

                        </div>
                    </div>
                <?php else: ?>
                    <div>
                        <div class="section-title"><i class="fa fa-clipboard-list"></i> House Policies</div>
                        <div style="background:#fff8e1;border:1px solid #ffe082;border-radius:var(--radius);padding:16px;font-size:13px;line-height:1.8;color:#7d5c00">
                            No house policies have been provided for this property.
                        </div>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <div>
                    <div class="section-title"><i class="fa fa-star"></i> Reviews</div>
                    <div class="card" style="padding:16px">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($reviewCount > 0): ?>
                            <p class="mb-3">
                                <strong><?php echo e(number_format((float) $averageRating, 1)); ?> out of 5</strong>
                                <span class="text-muted">· <?php echo e($reviewCount); ?> <?php echo e(\Illuminate\Support\Str::plural('review', $reviewCount)); ?></span>
                            </p>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $reviews; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $review): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <article style="padding:14px 0;border-top:1px solid var(--border)">
                                    <div class="flex-between" style="gap:12px">
                                        <strong><?php echo e($review->displayGuestName()); ?></strong>
                                        <span class="text-muted fs-sm"><?php echo e($review->created_at->format('M j, Y')); ?></span>
                                    </div>
                                    <div aria-label="<?php echo e($review->rating); ?> out of 5 stars" style="color:var(--accent);margin-top:4px">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php for($star = 1; $star <= $review->rating; $star++): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                            <i class="fa fa-star" aria-hidden="true"></i>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endfor; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                    </div>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($review->comment): ?>
                                        <p style="margin:8px 0 0"><?php echo e($review->comment); ?></p>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </article>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <?php else: ?>
                            <p class="text-muted mb-0">No reviews yet</p>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>

                <div>
                    <div class="section-title"><i class="fa fa-calendar-alt"></i> Availability Calendar</div>
                    <div class="cal-wrap">
                        <div class="cal-top">
                            <button onclick="prevMonth()"><i class="fa fa-chevron-left"></i></button>
                            <h3 id="calTitle"></h3>
                            <button onclick="nextMonth()"><i class="fa fa-chevron-right"></i></button>
                        </div>
                        <div class="cal-body">
                            <div class="cal-days-header">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                    <span><?php echo e($d); ?></span>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
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

                <div>
                    <div class="section-title"><i class="fa fa-paper-plane"></i> Send an Inquiry</div>
                    <div class="inquiry-box">
                        <div class="inquiry-box-header">
                            <h3>Have questions about this unit?</h3>
                            <p>Your inquiry will be sent directly to the property owner.</p>
                        </div>
                        <div class="inquiry-box-body">
                            <form method="POST" action="<?php echo e(route('public.units.inquiries.store', $unit)); ?>">
                                <?php echo csrf_field(); ?>
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="inqName">Your name</label>
                                        <input id="inqName" type="text" name="name" maxlength="150" required value="<?php echo e(old('name', auth()->check() ? auth()->user()->first_name.' '.auth()->user()->last_name : '')); ?>">
                                    </div>
                                    <div class="form-group">
                                        <label for="inqEmail">Your email</label>
                                        <input id="inqEmail" type="email" name="email" maxlength="191" required value="<?php echo e(old('email', auth()->user()->email ?? '')); ?>">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="inqMessage">Message to the property owner</label>
                                    <textarea id="inqMessage" name="message" rows="4" maxlength="5000" required placeholder="Ask the owner about availability, pricing, or other details."><?php echo e(old('message')); ?></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary btn-block">
                                    <i class="fa fa-paper-plane"></i> Email the owner
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

            </div>

            <div style="width:300px;flex-shrink:0">
                <div class="book-sidebar">
                    <div class="book-sidebar-body">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->guard()->check()): ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->role?->name === 'Guest'): ?>
                                <a href="<?php echo e(route('guest.book', $unit)); ?>" id="bookBtn" class="btn btn-primary btn-block btn-lg">
                                    <i class="fa fa-calendar-check"></i> Book Now
                                </a>
                            <?php else: ?>
                                <div class="alert alert-info" style="font-size:13px;margin:0">
                                    <i class="fa fa-info-circle"></i> Only guest accounts can make bookings.
                                </div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php else: ?>
                            <a href="<?php echo e(route('guest.book', $unit)); ?>" class="btn btn-primary btn-block btn-lg">
                                <i class="fa fa-sign-in-alt"></i> Login to Book
                            </a>
                            <div class="text-center mt-1 fs-sm text-muted">
                                No account? <a href="<?php echo e(route('register')); ?>">Register free</a>
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                        <div class="divider"></div>
                        <div style="font-size:12px;color:var(--text-muted);text-align:center;line-height:1.6">
                            <i class="fa fa-shield-alt" style="color:var(--success)"></i>
                            Secure booking &bull; No hidden fees<br>Free cancellation before acceptance
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script>
    // Availability Calendar
    const blockedDates = <?php echo json_encode($blocked, 15, 512) ?>;
    const blockedNotes = <?php echo json_encode($blockedNotes, 15, 512) ?>;
    const checkoutDates = <?php echo json_encode($checkoutDates, 15, 512) ?>;
    const checkInDates = <?php echo json_encode($checkInDates, 15, 512) ?>;
    const calendarToday = <?php echo json_encode($calendarToday->toDateString(), 15, 512) ?>;
    const checkInTime = <?php echo json_encode(\App\Support\UnitTimeFormatter::format((string) $unit->check_in_time), 15, 512) ?>;
    const checkOutTime = <?php echo json_encode(\App\Support\UnitTimeFormatter::format((string) $unit->check_out_time), 15, 512) ?>;
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
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalcffbe109f495178c1251f7f6dc1fe742)): ?>
<?php $attributes = $__attributesOriginalcffbe109f495178c1251f7f6dc1fe742; ?>
<?php unset($__attributesOriginalcffbe109f495178c1251f7f6dc1fe742); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalcffbe109f495178c1251f7f6dc1fe742)): ?>
<?php $component = $__componentOriginalcffbe109f495178c1251f7f6dc1fe742; ?>
<?php unset($__componentOriginalcffbe109f495178c1251f7f6dc1fe742); ?>
<?php endif; ?>
<?php /**PATH C:\Users\Karl\Herd\mctbs-laravel\resources\views/public/unit.blade.php ENDPATH**/ ?>