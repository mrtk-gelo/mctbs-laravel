<?php if (isset($component)) { $__componentOriginalcffbe109f495178c1251f7f6dc1fe742 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalcffbe109f495178c1251f7f6dc1fe742 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.shell','data' => ['title' => 'Welcome | MCTBS']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('shell'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Welcome | MCTBS']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

     <?php $__env->slot('nav', null, []); ?> <?php echo $__env->make('partials.nav', ['activePage' => 'home'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?> <?php $__env->endSlot(); ?>

    <section class="hero hero-home" id="home" data-scroll-section="home">
        <h1>Your Home In The <br>City of Pines</h1>
        <p>Escape to the cool breeze of Baguio City with a cozy, comfortable stay made for peaceful and memorable moments.</p>
        <div class="hero-actions">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->guard()->check()): ?>
                <a href="<?php echo e(auth()->user()->role?->name === 'Guest' ? '#transients' : auth()->user()->dashboardPath()); ?>" class="btn btn-primary">Book your escape</a>
            <?php else: ?>
                <a href="<?php echo e(route('login')); ?>" class="btn btn-primary">Book your escape</a>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <a href="#transients" class="btn btn-explore">Explore transient homes</a>
        </div>
    </section>

    <section class="container transients-section" id="transients" data-scroll-section="transients">
        <div class="transients-hero">
            <h2>Browse transient homes</h2>
            <p>Find comfortable stays across Baguio with a refined search experience and beautifully presented listings.</p>
        </div>
        <div class="transients-search-card">
            <form method="GET" action="<?php echo e(route('home')); ?>" class="google-search-bar" data-ajax-search data-results="#transient-results">
                <div class="search-input-wrapper">
                    <input type="text" name="q" value="<?php echo e($search); ?>" maxlength="100" placeholder="Search houses, locations, or descriptions..." aria-label="Search transient houses, locations, or descriptions">
                    <i class="fa fa-search search-icon"></i>
                </div>
            </form>
            <p class="sr-only" data-search-status aria-live="polite"></p>
            <p class="alert alert-danger mt-2" data-search-error hidden></p>
        </div>
        <div class="transient-home-results">
            <?php echo $__env->make('public.partials.transient-results', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        </div>
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
<?php /**PATH C:\Users\Karl\Herd\mctbs-laravel\resources\views/public/home.blade.php ENDPATH**/ ?>