@php
    $policyValues = old('policies', $policies ?? []);
    if (! is_array($policyValues)) {
        $policyValues = preg_split('/\r\n|\r|\n/', (string) $policyValues, flags: PREG_SPLIT_NO_EMPTY) ?: [];
    }
    $policyValues = $policyValues ?: [''];
@endphp

<div class="form-group house-policy-fields">
    <label>Policies</label>
    <p class="form-hint">Add one policy per box.</p>
    <div data-house-policies>
        @foreach ($policyValues as $index => $policy)
            <div class="house-policy-row" data-house-policy-row>
                <input type="text" name="policies[{{ $index }}]" value="{{ $policy }}" maxlength="1000" aria-label="Policy {{ $loop->iteration }}" placeholder="Example: Quiet hours begin at 10 PM">
                <button type="button" class="btn btn-outline btn-sm" data-remove-house-policy aria-label="Remove policy">Remove</button>
            </div>
        @endforeach
    </div>
    @error('policies')
        <p class="form-hint text-danger">{{ $message }}</p>
    @enderror
    @foreach ($errors->get('policies.*') as $policyErrors)
        @foreach ($policyErrors as $error)
            <p class="form-hint text-danger">{{ $error }}</p>
        @endforeach
    @endforeach
    <button type="button" class="btn btn-outline btn-sm" data-add-house-policy>Add Policy</button>
    <template data-house-policy-template>
        <div class="house-policy-row" data-house-policy-row>
            <input type="text" name="policies[__INDEX__]" maxlength="1000" aria-label="Policy" placeholder="Example: Quiet hours begin at 10 PM">
            <button type="button" class="btn btn-outline btn-sm" data-remove-house-policy aria-label="Remove policy">Remove</button>
        </div>
    </template>
</div>
