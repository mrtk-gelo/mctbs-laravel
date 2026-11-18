<?php
    $sections = [
        [
            'field' => 'addons',
            'title' => 'Add-on Amenities',
            'name_label' => 'Name',
            'items' => $addons,
        ],
        [
            'field' => 'damage_items',
            'title' => 'Damage Policies',
            'name_label' => 'Item name',
            'items' => $damageItems,
        ],
    ];
?>

<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $sections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $section): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
    <?php
        $field = $section['field'];
        $items = $section['items'];
    ?>
    <div class="form-group" data-unit-item-group>
        <h3><?php echo e($section['title']); ?></h3>
        <p class="form-hint">Optional. Add as many rows as needed.</p>
        <div data-unit-item-list>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <div class="form-row" data-unit-item-row data-index="<?php echo e($index); ?>">
                    <div class="form-group" style="flex:2">
                        <label class="required" for="<?php echo e($field); ?>_<?php echo e($index); ?>_name"><?php echo e($section['name_label']); ?></label>
                        <input id="<?php echo e($field); ?>_<?php echo e($index); ?>_name" type="text" name="<?php echo e($field); ?>[<?php echo e($index); ?>][name]" value="<?php echo e($item['name']); ?>" maxlength="100" required>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = [$field.'.'.$index.'.name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="form-hint text-danger"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                    <div class="form-group" style="flex:1">
                        <label class="required" for="<?php echo e($field); ?>_<?php echo e($index); ?>_price">Price (₱)</label>
                        <input id="<?php echo e($field); ?>_<?php echo e($index); ?>_price" type="number" name="<?php echo e($field); ?>[<?php echo e($index); ?>][price]" value="<?php echo e($item['price']); ?>" min="0" step="0.01" required>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = [$field.'.'.$index.'.price'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="form-hint text-danger"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                    <div class="form-group" style="align-self:flex-end">
                        <button type="button" class="btn btn-outline" data-remove-unit-item>Remove</button>
                    </div>
                </div>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
        </div>
        <button type="button" class="btn btn-outline" data-add-unit-item>Add row</button>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = [$field];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
            <p class="form-hint text-danger"><?php echo e($message); ?></p>
        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <template>
            <div class="form-row" data-unit-item-row data-index="__INDEX__">
                <div class="form-group" style="flex:2">
                    <label class="required" for="<?php echo e($field); ?>___INDEX___name"><?php echo e($section['name_label']); ?></label>
                    <input id="<?php echo e($field); ?>___INDEX___name" type="text" name="<?php echo e($field); ?>[__INDEX__][name]" maxlength="100" required>
                </div>
                <div class="form-group" style="flex:1">
                    <label class="required" for="<?php echo e($field); ?>___INDEX___price">Price (₱)</label>
                    <input id="<?php echo e($field); ?>___INDEX___price" type="number" name="<?php echo e($field); ?>[__INDEX__][price]" min="0" step="0.01" required>
                </div>
                <div class="form-group" style="align-self:flex-end">
                    <button type="button" class="btn btn-outline" data-remove-unit-item>Remove</button>
                </div>
            </div>
        </template>
    </div>
<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>

<script>
    document.querySelectorAll('[data-unit-item-group]').forEach((group) => {
        const list = group.querySelector('[data-unit-item-list]');
        const template = group.querySelector('template');
        let nextIndex = Array.from(list.querySelectorAll('[data-unit-item-row]'))
            .reduce((highestIndex, row) => Math.max(highestIndex, Number(row.dataset.index) + 1), 0);

        group.querySelector('[data-add-unit-item]').addEventListener('click', () => {
            const row = template.content.firstElementChild.cloneNode(true);
            row.innerHTML = row.innerHTML.replace(/__INDEX__/g, String(nextIndex));
            row.dataset.index = String(nextIndex);
            nextIndex++;
            list.append(row);
        });

        list.addEventListener('click', (event) => {
            const removeButton = event.target.closest('[data-remove-unit-item]');

            if (removeButton) {
                removeButton.closest('[data-unit-item-row]').remove();
            }
        });
    });
</script>
<?php /**PATH C:\xampp\htdocs\mctbs-laravel\resources\views/owner/houses/partials/unit-extra-items.blade.php ENDPATH**/ ?>