@php
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
@endphp

@foreach ($sections as $section)
    @php
        $field = $section['field'];
        $items = $section['items'];
    @endphp
    <div class="form-group" data-unit-item-group>
        <h3>{{ $section['title'] }}</h3>
        <p class="form-hint">Optional. Add as many rows as needed.</p>
        <div data-unit-item-list>
            @foreach ($items as $index => $item)
                <div class="form-row" data-unit-item-row data-index="{{ $index }}">
                    <div class="form-group" style="flex:2">
                        <label class="required" for="{{ $field }}_{{ $index }}_name">{{ $section['name_label'] }}</label>
                        <input id="{{ $field }}_{{ $index }}_name" type="text" name="{{ $field }}[{{ $index }}][name]" value="{{ $item['name'] }}" maxlength="100" required>
                        @error($field.'.'.$index.'.name')
                            <p class="form-hint text-danger">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="form-group" style="flex:1">
                        <label class="required" for="{{ $field }}_{{ $index }}_price">Price (₱)</label>
                        <input id="{{ $field }}_{{ $index }}_price" type="number" name="{{ $field }}[{{ $index }}][price]" value="{{ $item['price'] }}" min="0" step="0.01" required>
                        @error($field.'.'.$index.'.price')
                            <p class="form-hint text-danger">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="form-group" style="align-self:flex-end">
                        <button type="button" class="btn btn-outline" data-remove-unit-item>Remove</button>
                    </div>
                </div>
            @endforeach
        </div>
        <button type="button" class="btn btn-outline" data-add-unit-item>Add row</button>
        @error($field)
            <p class="form-hint text-danger">{{ $message }}</p>
        @enderror
        <template>
            <div class="form-row" data-unit-item-row data-index="__INDEX__">
                <div class="form-group" style="flex:2">
                    <label class="required" for="{{ $field }}___INDEX___name">{{ $section['name_label'] }}</label>
                    <input id="{{ $field }}___INDEX___name" type="text" name="{{ $field }}[__INDEX__][name]" maxlength="100" required>
                </div>
                <div class="form-group" style="flex:1">
                    <label class="required" for="{{ $field }}___INDEX___price">Price (₱)</label>
                    <input id="{{ $field }}___INDEX___price" type="number" name="{{ $field }}[__INDEX__][price]" min="0" step="0.01" required>
                </div>
                <div class="form-group" style="align-self:flex-end">
                    <button type="button" class="btn btn-outline" data-remove-unit-item>Remove</button>
                </div>
            </div>
        </template>
    </div>
@endforeach

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
