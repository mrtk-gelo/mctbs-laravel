<?php $__env->startSection('title', 'Login | MCTBS'); ?>

<?php $__env->startSection('content'); ?>
<div class="auth-wrapper">
    <div class="auth-left">
        <div class="auth-left-noise"></div>
        <div class="deco-ring deco-ring-1"></div>
        <div class="deco-ring deco-ring-2"></div>
        <div class="deco-ring deco-ring-3"></div>

        <div class="left-content">
            <div class="brand-logo">
                <img src="<?php echo e(asset('img/mctbs.png')); ?>" alt="MCTBS logo">
            </div>

            <h1 class="left-heading">Motal and Conteros<br>Transient Booking System</h1>
            <span class="heading-accent"></span>
            <p class="left-subheader">A modern booking system designed for Baguio transient house services.</p>
        </div>
    </div>

    <div class="auth-right">
        <div class="auth-card">
            <div class="card-eyebrow">
                <span class="card-eyebrow-dot"></span>
                Welcome back
            </div>
            <h2 class="card-title">Sign in to your account</h2>
            <p class="card-subtitle">Enter your credentials to access your dashboard.</p>

            <form method="POST" action="<?php echo e(route('login')); ?>" autocomplete="on">
                <?php echo csrf_field(); ?>

                <div class="form-field">
                    <label for="email">Email Address <span class="field-requirement">Required</span></label>
                    <div class="input-wrap">
                        <i class="fa fa-envelope input-icon"></i>
                        <input id="email" type="email" name="email" value="<?php echo e(old('email')); ?>" placeholder="you@email.com" required autofocus>
                    </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <div class="field-error"><?php echo e($message); ?></div>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                <div class="form-field">
                    <label for="password">Password <span class="field-requirement">Required</span></label>
                    <div class="input-wrap">
                        <i class="fa fa-lock input-icon"></i>
                        <input id="password" type="password" name="password" placeholder="••••••••" required>
                        <button type="button" class="password-toggle" aria-label="Toggle password visibility" onclick="const input = document.getElementById('password'); const value = input.type === 'password'; input.type = value ? 'text' : 'password'; this.innerHTML = value ? '<i class=\'fa fa-eye-slash\'></i>' : '<i class=\'fa fa-eye\'></i>';">
                            <i class="fa fa-eye"></i>
                        </button>
                    </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <div class="field-error"><?php echo e($message); ?></div>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                <button type="submit" class="btn-signin">
                    <i class="fa fa-arrow-right-to-bracket"></i>
                    Sign In
                </button>
            </form>

            <div class="divider">or</div>

            <p class="auth-link">
                Don't have an account?&nbsp;
                <a href="<?php echo e(route('register')); ?>">Register as Guest</a>
            </p>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.auth', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Karl\Herd\mctbs-laravel\resources\views/auth/login.blade.php ENDPATH**/ ?>