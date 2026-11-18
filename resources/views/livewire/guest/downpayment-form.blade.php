<div class="downpayment-livewire-form">
    <form id="downpayment-form" class="downpayment-form" wire:submit="submit">
        <section class="card">
            <div class="card-header">Choose payment method</div>
            <div class="card-body">
                <p class="form-hint">Select how you paid.</p>
                <div class="downpayment-method-tabs">
                    <input class="downpayment-method-input" id="downpayment-gcash" type="radio" wire:model="paymentMethod" value="GCash">
                    <label class="downpayment-method-tab" for="downpayment-gcash">
                        <i class="fa fa-mobile-alt" aria-hidden="true"></i> GCash
                    </label>
                    <input class="downpayment-method-input" id="downpayment-bank" type="radio" wire:model="paymentMethod" value="Bank Transfer">
                    <label class="downpayment-method-tab" for="downpayment-bank">
                        <i class="fa fa-university" aria-hidden="true"></i> Bank Transfer
                    </label>
                    <div class="policy-box downpayment-method-instructions downpayment-method-instructions-gcash">
                        Use the GCash details from the property. Enter the reference and attach proof.
                    </div>
                    <div class="policy-box downpayment-method-instructions downpayment-method-instructions-bank">
                        Use the bank details from the property. Enter the reference and attach proof.
                    </div>
                    <div class="policy-box downpayment-method-instructions downpayment-method-instructions-default">
                        Choose GCash or Bank Transfer for instructions.
                    </div>
                </div>
                @error('paymentMethod')<p class="form-hint text-danger">{{ $message }}</p>@enderror
            </div>
        </section>

        <section class="card">
            <div class="card-header">Submit payment details</div>
            <div class="card-body">
                <div class="flex-between fw-bold fs-lg mb-2">
                    <span>Downpayment due</span>
                    <strong>₱{{ number_format($downpaymentAmount, 2) }}</strong>
                </div>
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <div class="form-group">
                    <label for="downpayment-amount">Amount (₱)</label>
                    <input id="downpayment-amount" type="number" inputmode="decimal" wire:model="amount" min="{{ number_format($downpaymentAmount, 2, '.', '') }}" max="{{ number_format($remainingBalance, 2, '.', '') }}" step="0.01" required>
                    <small class="form-hint">Minimum downpayment: ₱{{ number_format($downpaymentAmount, 2) }}.</small>
                    @error('amount')<p class="form-hint text-danger">{{ $message }}</p>@enderror
                </div>
                <div class="form-group">
                    <label for="downpayment-reference">Reference number</label>
                    <input id="downpayment-reference" type="text" wire:model.blur="referenceNumber" maxlength="100">
                    @error('referenceNumber')<p class="form-hint text-danger">{{ $message }}</p>@enderror
                </div>
                <div class="form-group">
                    <label for="downpayment-proof">Payment proof (JPG, PNG, or PDF)</label>
                    <input id="downpayment-proof" class="downpayment-file-input" type="file" wire:model="paymentReceipt" accept="image/jpeg,image/png,application/pdf" required>
                    <small class="form-hint">Up to 5 MB.</small>
                    @error('paymentReceipt')<p class="form-hint text-danger">{{ $message }}</p>@enderror
                    <div wire:loading wire:target="paymentReceipt" class="form-hint">Uploading file…</div>
                    @if ($paymentReceipt)
                        @if (str_starts_with($paymentReceipt->getMimeType(), 'image/'))
                            <img class="downpayment-proof-preview" src="{{ $paymentReceipt->temporaryUrl() }}" alt="Selected payment proof preview">
                        @else
                            <p class="form-hint mb-0">{{ $paymentReceipt->getClientOriginalName() }}</p>
                        @endif
                    @endif
                </div>
                <div class="form-group">
                    <label for="downpayment-notes">Notes</label>
                    <textarea id="downpayment-notes" wire:model="notes" rows="2" maxlength="1000"></textarea>
                    @error('notes')<p class="form-hint text-danger">{{ $message }}</p>@enderror
                </div>
                <button type="submit" class="btn btn-primary btn-block" wire:loading.attr="disabled" wire:target="submit">
                    <span wire:loading.remove wire:target="submit">Submit Downpayment</span>
                    <span wire:loading wire:target="submit">Submitting…</span>
                </button>
            </div>
        </section>
    </form>
</div>
