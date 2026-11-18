<?php if (isset($component)) { $__componentOriginalcffbe109f495178c1251f7f6dc1fe742 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalcffbe109f495178c1251f7f6dc1fe742 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.shell','data' => ['title' => 'Browse Transients | MCTBS']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('shell'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Browse Transients | MCTBS']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

     <?php $__env->slot('nav', null, []); ?> <?php echo $__env->make('partials.nav', ['activePage' => 'transients'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?> <?php $__env->endSlot(); ?>

    <section class="container transients-section transients-page" id="transients">
        <div class="transients-hero">
            <h1>Browse transient homes</h1>
            <p>Find comfortable stays across Baguio with a refined search experience and beautifully presented listings.</p>
        </div>
        <div class="transients-search-card">
            <form method="GET" action="<?php echo e(route('public.transients')); ?>" class="google-search-bar" data-ajax-search data-results="#transient-results">
                <div class="search-input-wrapper">
                    <input type="text" name="q" value="<?php echo e($search); ?>" maxlength="100" placeholder="Search transient houses, locations, or descriptions..." aria-label="Search transient houses, locations, or descriptions">
                    <i class="fa fa-search search-icon"></i>
                </div>
            </form>
            <p class="sr-only" data-search-status aria-live="polite"></p>
            <p class="alert alert-danger mt-2" data-search-error hidden></p>
        </div>
        <?php echo $__env->make('public.partials.transient-results', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    </section>
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
<?php /**PATH C:\xampp\htdocs\mctbs-laravel\resources\views/public/transients.blade.php ENDPATH**/ ?>