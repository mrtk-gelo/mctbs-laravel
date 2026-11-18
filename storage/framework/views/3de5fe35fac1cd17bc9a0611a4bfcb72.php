<section class="card personnel-table-card" id="owner-results">
    <div class="card-header"><span>Registered owners</span><span class="text-muted fs-sm"><?php echo e($owners->count()); ?> matching</span></div>
    <div class="table-wrap">
        <table>
            <thead><tr><th scope="col">Select</th><th>Name</th><th>Email</th><th>Phone</th><th>Properties</th><th>Date registered</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $owners; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $person): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <tr>
                        <td><input type="checkbox" name="owner_ids[]" value="<?php echo e($person->user_id); ?>" form="bulk-owner-delete-form" aria-label="Select <?php echo e($person->first_name); ?> <?php echo e($person->last_name); ?> for deletion"></td>
                        <td><div class="person-cell">
                            <span class="avatar-initials"><?php echo e($person->initials()); ?></span>
                            <strong><?php echo e($person->first_name); ?> <?php echo e($person->last_name); ?></strong>
                        </div></td>
                        <td><?php echo e($person->email); ?></td>
                        <td><?php echo e($person->phone ?: '—'); ?></td>
                        <td><?php echo e($person->transient_houses_count); ?></td>
                        <td><?php echo e($person->registered_at?->format('M j, Y') ?? '—'); ?></td>
                        <td>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! $person->is_active): ?>
                                <span class="badge badge-cancelled">Inactive</span>
                            <?php elseif(! $person->email_verified_at): ?>
                                <span class="badge badge-pending">Unverified</span>
                            <?php else: ?>
                                <span class="badge badge-accepted">Active</span>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </td>
                        <td>
                            <form method="POST" action="<?php echo e(route('sysadmin.owners.destroy', $person->user_id)); ?>" onsubmit="return confirm('Delete this owner account? Accounts linked to protected records cannot be deleted.')">
                                <?php echo csrf_field(); ?>
                                <?php echo method_field('DELETE'); ?>
                                <button type="submit" class="btn btn-danger btn-sm" aria-label="Delete <?php echo e($person->first_name); ?> <?php echo e($person->last_name); ?>">
                                    <i class="fa fa-trash" aria-hidden="true"></i> Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    <tr><td colspan="8" class="empty-table-cell">No matching owners found.</td></tr>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </tbody>
        </table>
    </div>
</section>
<?php /**PATH C:\Users\Karl\Herd\mctbs-laravel\resources\views/sysadmin/partials/owner-results.blade.php ENDPATH**/ ?>