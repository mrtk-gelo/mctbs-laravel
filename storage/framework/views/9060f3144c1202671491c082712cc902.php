<div id="transient-results">
    <div class="results-bar">
        <div class="results-count">
            Showing <strong><?php echo e($houses->count()); ?></strong> propert<?php echo e($houses->count() === 1 ? 'y' : 'ies'); ?>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($search): ?> for "<strong><?php echo e($search); ?></strong>"<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($houses->isNotEmpty()): ?>
        <div class="unit-grid transient-house-grid">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $houses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $h): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <div class="unit-card">
                    <a href="<?php echo e(route('public.house', $h)); ?>" class="house-card">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($h->cover_photo): ?>
                            <img src="<?php echo e(asset('storage/' . $h->cover_photo)); ?>" class="house-card-img" alt="<?php echo e($h->name); ?>">
                        <?php else: ?>
                            <div class="house-card-img-placeholder"><i class="fa fa-building"></i></div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <div class="house-card-body">
                            <h3><?php echo e($h->name); ?></h3>
                            <div class="house-card-location">
                                <i class="fa fa-map-marker-alt"></i>
                                <?php echo e($h->city); ?><?php echo e($h->barangay ? ', '.$h->barangay : ''); ?>

                            </div>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($h->description): ?>
                                <p class="house-card-description">
                                    <?php echo e(\Illuminate\Support\Str::limit($h->description, 100, '...')); ?>

                                </p>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <div class="house-card-meta">
                                <div class="house-card-units">
                                    <i class="fa fa-door-open"></i> <?php echo e($h->units_count); ?> unit<?php echo e($h->units_count != 1 ? 's' : ''); ?>

                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
        </div>
    <?php else: ?>
        <div class="empty-state transient-empty-state">
            <i class="fa fa-building"></i>
            <h3>No properties found</h3>
            <p><?php echo e($search ? 'Try a different search term or location.' : 'No transient houses are available yet.'); ?></p>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php /**PATH C:\xampp\htdocs\mctbs-laravel\resources\views/public/partials/transient-results.blade.php ENDPATH**/ ?>