@php
    $socialLinks = old('social_links', $socialLinks ?? []);
    $socialLinks = $socialLinks ?: [['name' => '', 'url' => '']];
@endphp

<div class="form-group social-links-fields">
    <label>Social media contacts</label>
    <p class="form-hint">Add the social media name and its profile or contact link. Only links you provide will appear to guests.</p>
    <div data-social-links>
        @foreach ($socialLinks as $index => $socialLink)
            <div class="form-row social-link-row" data-social-link-row>
                <div class="form-group" style="flex:1">
                    <label for="social-name-{{ $index }}">Social media name</label>
                    <input id="social-name-{{ $index }}" type="text" name="social_links[{{ $index }}][name]" value="{{ $socialLink['name'] ?? '' }}" maxlength="50" placeholder="Facebook">
                </div>
                <div class="form-group" style="flex:2">
                    <label for="social-url-{{ $index }}">Social media link</label>
                    <input id="social-url-{{ $index }}" class="social-link-url" type="url" name="social_links[{{ $index }}][url]" value="{{ $socialLink['url'] ?? '' }}" maxlength="2048" placeholder="https://facebook.com/your-page">
                </div>
                <button type="button" class="btn btn-outline btn-sm" data-remove-social-link aria-label="Remove social media link">Remove</button>
            </div>
        @endforeach
    </div>
    @error('social_links')
        <p class="form-hint text-danger">{{ $message }}</p>
    @enderror
    <button type="button" class="btn btn-outline btn-sm" data-add-social-link>Add social media link</button>
    <template data-social-link-template>
        <div class="form-row social-link-row" data-social-link-row>
            <div class="form-group" style="flex:1">
                <label>Social media name</label>
                <input type="text" name="social_links[__INDEX__][name]" maxlength="50" placeholder="Instagram">
            </div>
            <div class="form-group" style="flex:2">
                <label>Social media link</label>
                <input class="social-link-url" type="url" name="social_links[__INDEX__][url]" maxlength="2048" placeholder="https://instagram.com/your-page">
            </div>
            <button type="button" class="btn btn-outline btn-sm" data-remove-social-link aria-label="Remove social media link">Remove</button>
        </div>
    </template>
</div>
