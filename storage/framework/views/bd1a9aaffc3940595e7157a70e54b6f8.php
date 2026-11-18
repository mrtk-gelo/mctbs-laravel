<?php if (isset($component)) { $__componentOriginalcffbe109f495178c1251f7f6dc1fe742 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalcffbe109f495178c1251f7f6dc1fe742 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.shell','data' => ['title' => ''.e($house->name).' | MCTBS']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('shell'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => ''.e($house->name).' | MCTBS']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

     <?php $__env->slot('nav', null, []); ?> <?php echo $__env->make('partials.nav', ['activePage' => 'transients'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?> <?php $__env->endSlot(); ?>

    <style>
    .house-hero { position: relative; height: 380px; background: var(--primary); overflow: hidden; }
    .house-hero img { width: 100%; height: 100%; object-fit: cover; display: block; filter: brightness(.75); }
    .house-hero-placeholder { width: 100%; height: 100%; background: linear-gradient(135deg, var(--primary), #1a252f); display: flex; align-items: center; justify-content: center; font-size: 80px; color: rgba(255,255,255,.2); }
    .house-hero-overlay { position: absolute; bottom: 0; left: 0; right: 0; padding: 24px 32px; background: linear-gradient(to top, rgba(0,0,0,.75), transparent); color: #fff; }
    .house-hero-overlay h1 { font-size: 30px; font-weight: 800; margin-bottom: 6px; }
    .house-hero-overlay .loc { font-size: 14px; opacity: .9; display: flex; align-items: center; gap: 6px; }
    .stars { color: #f39c12; font-size: 14px; display: inline-flex; gap: 2px; }
    .rating-pill { display: inline-flex; align-items: center; gap: 6px; background: #fff8e1; border: 1px solid #ffe082; color: #7d5c00; padding: 4px 12px; border-radius: 999px; font-size: 13px; font-weight: 700; }
    .section-title { font-size: 18px; font-weight: 700; margin-bottom: 16px; padding-bottom: 10px; border-bottom: 2px solid var(--border); display: flex; align-items: center; gap: 10px; }
    .section-title i { color: var(--accent); }
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
    .inquiry-box { background: var(--card); border: 1px solid var(--border); border-radius: 14px; overflow: hidden; }
    .inquiry-box-header { background: var(--primary-lt); color: #fff; padding: 20px 24px; }
    .inquiry-box-header h3 { font-size: 17px; font-weight: 700; margin-bottom: 4px; }
    .inquiry-box-header p  { font-size: 13px; opacity: .8; margin: 0; }
    .inquiry-box-body { padding: 24px; }
    .house-policy-grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 14px; }
    .house-policy-card { min-width: 0; padding: 16px; border: 1px solid var(--border); border-radius: 10px; background: var(--card); color: var(--text); line-height: 1.6; overflow-wrap: anywhere; }
    @media (max-width: 900px) { .house-policy-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
    @media (max-width: 560px) { .house-policy-grid { grid-template-columns: minmax(0, 1fr); } }
    </style>

    <div class="house-hero">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($house->cover_photo): ?>
            <img src="<?php echo e(asset('storage/' . $house->cover_photo)); ?>" alt="<?php echo e($house->name); ?>">
        <?php else: ?>
            <div class="house-hero-placeholder"><i class="fa fa-building"></i></div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <div class="house-hero-overlay">
            <a href="<?php echo e(route('public.transients')); ?>" style="color:rgba(255,255,255,.75);font-size:13px;text-decoration:none;display:inline-flex;align-items:center;gap:6px;margin-bottom:10px">
                <i class="fa fa-arrow-left"></i> Back to all transients
            </a>
            <h1><?php echo e($house->name); ?></h1>
            <div class="loc">
                <i class="fa fa-map-marker-alt"></i>
                <?php echo e($house->address); ?>, <?php echo e($house->city); ?><?php echo e($house->barangay ? ', '.$house->barangay : ''); ?>

            </div>
        </div>
    </div>

    <div class="container" style="margin-top:32px">
        <div class="row" style="align-items:flex-start">

            <div class="col-2" style="display:flex;flex-direction:column;gap:32px">

                <div>
                    <div class="section-title"><i class="fa fa-info-circle"></i> About This Property</div>
                    <div style="display:flex;align-items:center;gap:12px;margin-bottom:14px;flex-wrap:wrap">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($avgRating): ?>
                            <div class="rating-pill"><i class="fa fa-star"></i> <?php echo e($avgRating); ?> / 5.0</div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <span class="text-muted fs-sm"><?php echo e($reviews->count()); ?> guest review<?php echo e($reviews->count() === 1 ? '' : 's'); ?></span>
                        <span style="background:#eafaf1;color:var(--success);padding:4px 12px;border-radius:999px;font-size:13px;font-weight:600">
                            <i class="fa fa-check-circle"></i> Active
                        </span>
                    </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($house->description): ?>
                        <p style="color:var(--text-muted);line-height:1.8;margin-bottom:16px"><?php echo nl2br(e($house->description)); ?></p>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                <?php
                    $housePolicies = preg_split('/\r\n|\r|\n/', (string) $house->policies, flags: PREG_SPLIT_NO_EMPTY) ?: [];
                ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($housePolicies !== []): ?>
                    <div>
                        <div class="section-title"><i class="fa fa-clipboard-list"></i> House Policies</div>
                        <div class="house-policy-grid">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $housePolicies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $policy): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <div class="house-policy-card"><?php echo e(trim($policy)); ?></div>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <div>
                    <div class="section-title"><i class="fa fa-door-open"></i> Available Units</div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($units->isNotEmpty()): ?>
                        <div style="display:flex;flex-direction:column;gap:16px">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $units; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $u): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <a href="<?php echo e(route('public.unit', $u)); ?>" class="unit-row-card">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($u->photos->isNotEmpty()): ?>
                                        <img src="<?php echo e(asset('storage/' . $u->photos->first()->photo_path)); ?>" class="unit-row-card-img" alt="<?php echo e($u->unit_type); ?> photo">
                                    <?php elseif($house->cover_photo): ?>
                                        <img src="<?php echo e(asset('storage/' . $house->cover_photo)); ?>" class="unit-row-card-img" alt="<?php echo e($house->name); ?> cover photo">
                                    <?php else: ?>
                                        <div class="unit-row-card-img-placeholder"><i class="fa fa-door-open"></i></div>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    <div class="unit-row-card-body">
                                        <div>
                                            <h3><?php echo e($u->unit_type); ?></h3>
                                            <div style="display:flex;align-items:center;gap:12px;margin-bottom:10px;flex-wrap:wrap">
                                                <span class="fs-sm text-muted"><i class="fa fa-users"></i> Capacity <?php echo e($u->capacity); ?> standard; up to <?php echo e($u->maximum_capacity ?? $u->capacity); ?> counted guests</span>
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($u->child_free_through_age !== null): ?>
                                                    <span class="fs-sm text-muted"><i class="fa fa-child"></i> Ages <?php echo e($u->child_free_through_age); ?> and under are free and excluded</span>
                                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                <span class="fs-sm text-muted"><i class="fa fa-moon"></i> ₱<?php echo e(number_format($u->price, 2)); ?>/night</span>
                                            </div>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($u->description): ?>
                                                <p class="fs-sm text-muted" style="margin-bottom:10px;line-height:1.6"><?php echo e(\Illuminate\Support\Str::limit($u->description, 100, '...')); ?></p>
                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </div>
                                        <div style="display:flex;align-items:center;justify-content:space-between;margin-top:14px;padding-top:12px;border-top:1px solid var(--border)">
                                            <div style="font-size:20px;font-weight:800;color:var(--accent)">₱<?php echo e(number_format($u->price, 2)); ?><span style="font-size:12px;font-weight:400;color:var(--text-muted)">/night</span></div>
                                            <span class="btn btn-primary btn-sm">View Unit <i class="fa fa-arrow-right"></i></span>
                                        </div>
                                    </div>
                                </a>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-state" style="padding:40px 20px">
                            <i class="fa fa-door-open"></i>
                            <h3>No units available</h3>
                            <p>This property has no active units at the moment.</p>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                <div>
                    <div class="section-title"><i class="fa fa-star"></i> Guest Reviews</div>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($reviews->isNotEmpty()): ?>
                        <div style="background:var(--card);border:1px solid var(--border);border-radius:12px;padding:20px;display:flex;align-items:center;gap:24px;margin-bottom:20px;flex-wrap:wrap">
                            <div style="text-align:center">
                                <div style="font-size:52px;font-weight:800;color:var(--accent);line-height:1"><?php echo e($avgRating); ?></div>
                                <div class="stars">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php for($i = 1; $i <= 5; $i++): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                        <i class="fa fa-star<?php echo e($i <= floor($avgRating) ? '' : ($i - $avgRating < 1 ? '-half-alt' : '-o')); ?>"></i>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endfor; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                </div>
                                <div style="font-size:12px;color:var(--text-muted);margin-top:4px"><?php echo e($reviews->count()); ?> review<?php echo e($reviews->count() === 1 ? '' : 's'); ?></div>
                            </div>
                        </div>

                        <div style="display:flex;flex-direction:column;gap:14px">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $reviews; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <?php
                                    $reviewGuestName = $r->displayGuestName();
                                ?>
                                <div class="review-card">
                                    <div style="display:flex;align-items:center;gap:12px;margin-bottom:12px">
                                        <div class="review-avatar"><?php echo e(strtoupper(substr($reviewGuestName, 0, 1))); ?></div>
                                        <div>
                                            <div style="font-weight:600;font-size:14px"><?php echo e($reviewGuestName); ?></div>
                                            <div style="font-size:12px;color:var(--text-muted)"><?php echo e($r->created_at->format('F Y')); ?></div>
                                        </div>
                                        <div class="stars" style="margin-left:auto">
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php for($i = 1; $i <= 5; $i++): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                                <i class="fa fa-star<?php echo e($i <= $r->rating ? '' : '-o'); ?>"></i>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endfor; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                        </div>
                                    </div>
                                    <p style="font-size:14px;color:var(--text-muted);line-height:1.7;margin:0"><?php echo e($r->comment); ?></p>
                                </div>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted fs-sm">No reviews yet — this property hasn't had any completed stays reviewed.</p>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

            </div>

            <div style="width:300px;flex-shrink:0;display:flex;flex-direction:column;gap:20px;position:sticky;top:80px">
                <div class="card">
                    <div class="card-header"><i class="fa fa-address-card" style="color:var(--accent)"></i> Contact the Host</div>
                    <div class="card-body" style="display:flex;flex-direction:column;gap:10px">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($house->contact_number): ?>
                            <a href="tel:<?php echo e(preg_replace('/[^0-9+]/', '', $house->contact_number)); ?>" class="contact-link">
                                <div class="contact-icon" style="background:#d4edda;color:var(--success)"><i class="fa fa-phone"></i></div>
                                <div>
                                    <div style="font-size:11px;color:var(--text-muted)">Phone</div>
                                    <div style="font-weight:600"><?php echo e($house->contact_number); ?></div>
                                </div>
                            </a>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($house->owner->email): ?>
                            <a href="mailto:<?php echo e($house->owner->email); ?>" class="contact-link">
                                <div class="contact-icon" style="background:#d1ecf1;color:var(--info)"><i class="fa fa-envelope"></i></div>
                                <div>
                                    <div style="font-size:11px;color:var(--text-muted)">Email</div>
                                    <div class="contact-email" style="font-weight:600"><?php echo e($house->owner->email); ?></div>
                                </div>
                            </a>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $house->social_links ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $socialLink): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <?php
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
                            ?>
                            <a href="<?php echo e($socialLink['url']); ?>" class="contact-link" target="_blank" rel="noopener noreferrer">
                                <div class="contact-icon" style="background:#e8f4fc;color:#1877f2"><i class="<?php echo e($socialIcon); ?>"></i></div>
                                <div>
                                    <div style="font-size:11px;color:var(--text-muted)">Social media</div>
                                    <div style="font-weight:600"><?php echo e($socialName); ?></div>
                                </div>
                            </a>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </div>
                </div>

                <div class="inquiry-box">
                    <div class="inquiry-box-header">
                        <h3><i class="fa fa-paper-plane"></i> Send an Inquiry</h3>
                        <p>Your inquiry will be sent directly to the property owner.</p>
                    </div>
                    <div class="inquiry-box-body">
                        <form method="POST" action="<?php echo e(route('public.houses.inquiries.store', $house)); ?>">
                            <?php echo csrf_field(); ?>
                            <div class="form-group">
                                <label for="inqName">Your name</label>
                                <input id="inqName" type="text" name="name" required maxlength="150" value="<?php echo e(old('name', auth()->check() ? auth()->user()->first_name.' '.auth()->user()->last_name : '')); ?>">
                            </div>
                            <div class="form-group">
                                <label for="inqEmail">Your email</label>
                                <input id="inqEmail" type="email" name="email" required maxlength="191" value="<?php echo e(old('email', auth()->user()->email ?? '')); ?>">
                            </div>
                            <div class="form-group">
                                <label for="inqMessage">Message to the property owner</label>
                                <textarea id="inqMessage" name="message" rows="4" required maxlength="5000" placeholder="Ask the owner about availability, pricing, or other details."><?php echo e(old('message')); ?></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="fa fa-paper-plane"></i> Email the owner
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

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
<?php /**PATH C:\xampp\htdocs\mctbs-laravel\resources\views/public/house.blade.php ENDPATH**/ ?>