<?php $__env->startSection('title', 'Register | MCTBS'); ?>

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
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($invite): ?>
                <div class="card-eyebrow">
                    <span class="card-eyebrow-dot"></span>
                    You're invited as <?php echo e($invite->role->displayName()); ?>

                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($token && ! $invite): ?>
                <div class="auth-alert error">
                    <i class="fa fa-clock"></i>
                    <span>This invitation link is invalid or has expired.</span>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <h2 class="card-title">
                <?php echo e($invite ? 'Complete your account setup' : 'Create your account'); ?>

            </h2>
            <p class="card-subtitle">Fill in your details to get started.</p>

            <form method="POST" action="<?php echo e(route('register')); ?>" autocomplete="on">
                <?php echo csrf_field(); ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($token): ?>
                    <input type="hidden" name="token" value="<?php echo e($token); ?>">
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <div class="form-row">
                    <div class="form-field">
                        <label for="first_name">First Name <span class="field-requirement">Required</span></label>
                        <div class="input-wrap">
                            <i class="fa fa-user input-icon"></i>
                            <input id="first_name" type="text" name="first_name" value="<?php echo e(old('first_name')); ?>" placeholder="Juan" required>
                        </div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['first_name'];
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
                        <label for="last_name">Last Name <span class="field-requirement">Required</span></label>
                        <div class="input-wrap">
                            <i class="fa fa-user input-icon"></i>
                            <input id="last_name" type="text" name="last_name" value="<?php echo e(old('last_name')); ?>" placeholder="Dela Cruz" required>
                        </div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['last_name'];
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
                </div>

                <div class="form-field">
                    <label for="email">Email Address <span class="field-requirement">Required</span></label>
                    <div class="input-wrap">
                        <i class="fa fa-envelope input-icon"></i>
                        <input id="email" type="email" name="email" value="<?php echo e($invite ? $invite->email : old('email')); ?>" placeholder="you@email.com" <?php echo e($invite ? 'readonly' : ''); ?> required>
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
                    <label for="phone">Phone Number <span class="field-requirement">Optional</span></label>
                    <div class="input-wrap">
                        <i class="fa fa-phone input-icon"></i>
                        <input id="phone" type="text" name="phone" value="<?php echo e(old('phone')); ?>" placeholder="+63 912 345 6789">
                    </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['phone'];
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
                    <p class="field-hint">At least 8 characters, with uppercase and lowercase letters, a number, and a symbol.</p>
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

                <div class="form-field">
                    <label for="password_confirmation">Confirm Password <span class="field-requirement">Required</span></label>
                    <div class="input-wrap">
                        <i class="fa fa-lock input-icon"></i>
                        <input id="password_confirmation" type="password" name="password_confirmation" placeholder="••••••••" required>
                    </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['password_confirmation'];
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

                <div class="terms-consent">
                    <input id="terms_accepted" type="checkbox" name="terms_accepted" value="1" <?php if(old('terms_accepted')): echo 'checked'; endif; ?> required>
                    <label for="terms_accepted">I have read and accept the <span class="field-requirement">Required</span></label>
                    <a href="#registration-terms" data-terms-open>Terms and Conditions</a>
                </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['terms_accepted'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <div class="field-error terms-error"><?php echo e($message); ?></div>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <button type="submit" class="btn-signin">
                    <i class="fa fa-user-plus"></i>
                    Create Account
                </button>
            </form>

            <div class="divider">or</div>

            <p class="auth-link">
                Already have an account?&nbsp;
                <a href="<?php echo e(route('login')); ?>">Sign In</a>
            </p>
        </div>
    </div>
</div>

<dialog class="terms-dialog" id="registration-terms" aria-labelledby="terms-dialog-title">
    <div class="terms-dialog-header">
        <h2 id="terms-dialog-title">Terms and Conditions</h2>
        <button type="button" class="terms-dialog-close" data-terms-close aria-label="Close Terms and Conditions">
            <i class="fa fa-xmark"></i>
        </button>
    </div>
    <div class="terms-dialog-content">
        <p class="terms-draft-notice">Draft terms for MCTBS. These are a starting point and will be replaced with the final terms.</p>
        <section>
            <h3>Using your account</h3>
            <p>Provide accurate registration details, keep your password private, and use your account only for its intended purpose. You are responsible for activity carried out through your account and should contact the Account Manager if you believe it has been accessed without permission.</p>
        </section>
        <section>
            <h3>Transient listings and booking requests</h3>
            <p>MCTBS helps guests browse transient properties and submit booking requests. A submitted request is not a confirmed reservation; it remains subject to review by the property owner or authorized staff. Availability, rates, and property details should be confirmed through the information shown for the listing and booking.</p>
        </section>
        <section>
            <h3>Payments and booking policies</h3>
            <p>When a booking requires payment, follow the instructions shown in the system and submit genuine payment details or proof. Any applicable payment deadlines, cancellation terms, and other booking-specific policies are presented during the booking process and should be reviewed before proceeding.</p>
        </section>
        <section>
            <h3>Responsible use and account information</h3>
            <p>Do not submit false information, misuse another person’s account, interfere with the system, or use MCTBS for unlawful activity. Information you provide may be used to operate bookings and may be visible to the owner or authorized staff responsible for the relevant property.</p>
        </section>
        <section>
            <h3>Changes and support</h3>
            <p>These draft terms may be updated as the service changes. Contact the Account Manager or the property owner through the available channels if you have a question about your account or booking.</p>
        </section>
    </div>
    <div class="terms-dialog-actions">
        <button type="button" class="btn-terms-close" data-terms-close>Close</button>
    </div>
</dialog>

<script>
    const termsDialog = document.getElementById('registration-terms');

    document.querySelector('[data-terms-open]').addEventListener('click', (event) => {
        event.preventDefault();
        termsDialog.showModal();
    });

    document.querySelectorAll('[data-terms-close]').forEach((button) => {
        button.addEventListener('click', () => termsDialog.close());
    });

    termsDialog.addEventListener('click', (event) => {
        if (event.target === termsDialog) {
            termsDialog.close();
        }
    });
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.auth', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\mctbs-laravel\resources\views/auth/register.blade.php ENDPATH**/ ?>