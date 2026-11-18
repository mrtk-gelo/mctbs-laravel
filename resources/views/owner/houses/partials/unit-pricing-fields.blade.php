<div class="form-row">
    <div class="form-group" style="flex:1">
        <label class="required" for="pricing_mode">Pricing mode</label>
        <select id="pricing_mode" name="pricing_mode" required>
            @foreach (['Per Unit', 'Per Head'] as $mode)
                <option value="{{ $mode }}" @selected($pricingMode === $mode)>{{ $mode }}</option>
            @endforeach
        </select>
        @error('pricing_mode')<p class="form-hint text-danger">{{ $message }}</p>@enderror
    </div>
    <div class="form-group" style="flex:1">
        <label class="required" for="price" data-pricing-label="Per Unit">Price per unit per night (₱)</label>
        <label class="required" for="price" data-pricing-label="Per Head" hidden>Price per paying head per night (₱)</label>
        <input id="price" type="number" name="price" value="{{ $price }}" min="0" step="0.01" required>
        @error('price')<p class="form-hint text-danger">{{ $message }}</p>@enderror
    </div>
</div>
<div class="form-group" id="extra-guest-fee-group" data-pricing-mode="Per Unit">
    <label class="required" for="extra_guest_fee">Extra guest fee per paying guest per night (₱)</label>
    <input id="extra_guest_fee" type="number" name="extra_guest_fee" value="{{ $extraGuestFee }}" min="0" step="0.01" required>
    @error('extra_guest_fee')<p class="form-hint text-danger">{{ $message }}</p>@enderror
</div>
<script>
    const pricingModeSelect = document.getElementById('pricing_mode');
    const extraGuestFeeGroup = document.getElementById('extra-guest-fee-group');
    const extraGuestFeeInput = document.getElementById('extra_guest_fee');
    const pricingLabels = document.querySelectorAll('[data-pricing-label]');

    function updatePricingFields() {
        const isPerUnit = pricingModeSelect.value === 'Per Unit';
        extraGuestFeeGroup.hidden = !isPerUnit;
        extraGuestFeeGroup.style.display = isPerUnit ? '' : 'none';
        extraGuestFeeInput.disabled = !isPerUnit;
        pricingLabels.forEach((label) => {
            const isVisible = label.dataset.pricingLabel === pricingModeSelect.value;
            label.hidden = !isVisible;
            label.style.display = isVisible ? '' : 'none';
        });
    }

    pricingModeSelect.addEventListener('change', updatePricingFields);
    updatePricingFields();
</script>
