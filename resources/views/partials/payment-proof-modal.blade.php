<dialog class="payment-proof-dialog" id="payment-proof-dialog" aria-labelledby="payment-proof-title">
    <div class="payment-proof-dialog-header">
        <strong id="payment-proof-title">Payment receipt</strong>
        <button type="button" class="btn btn-outline btn-sm" data-proof-close aria-label="Close receipt preview">Close</button>
    </div>
    <div class="payment-proof-dialog-content">
        <div class="payment-proof-metadata">
            <div><span>Reference</span><strong data-proof-reference>—</strong></div>
            <div><span>Amount</span><strong data-proof-amount>—</strong></div>
        </div>
        <img src="" alt="Submitted payment receipt" data-proof-image hidden>
        <iframe src="" title="Submitted payment receipt PDF" data-proof-pdf hidden></iframe>
    </div>
    <div class="payment-proof-dialog-actions">
        <a class="btn btn-primary" href="#" download data-proof-download>Download</a>
    </div>
</dialog>

@once
    <script>
        (() => {
            const dialog = document.getElementById('payment-proof-dialog');
            if (!dialog) {
                return;
            }

            const image = dialog.querySelector('[data-proof-image]');
            const pdf = dialog.querySelector('[data-proof-pdf]');
            const download = dialog.querySelector('[data-proof-download]');
            const reference = dialog.querySelector('[data-proof-reference]');
            const amount = dialog.querySelector('[data-proof-amount]');

            document.addEventListener('click', (event) => {
                const trigger = event.target.closest('[data-proof-viewer]');
                if (trigger) {
                    const url = trigger.dataset.proofUrl;
                    const type = trigger.dataset.proofType;
                    image.hidden = type === 'pdf';
                    pdf.hidden = type !== 'pdf';
                    image.src = type === 'pdf' ? '' : url;
                    pdf.src = type === 'pdf' ? url : '';
                    download.href = trigger.dataset.proofDownloadUrl;
                    reference.textContent = trigger.dataset.proofReference || '—';
                    amount.textContent = trigger.dataset.proofAmount || '—';
                    if (!dialog.open) {
                        dialog.showModal();
                    }
                    return;
                }

                if (event.target.closest('[data-proof-close]')) {
                    dialog.close();
                }
            });

            dialog.addEventListener('click', (event) => {
                if (event.target === dialog) {
                    dialog.close();
                }
            });

            dialog.addEventListener('close', () => {
                image.src = '';
                pdf.src = '';
                download.href = '#';
                reference.textContent = '—';
                amount.textContent = '—';
            });
        })();
    </script>
@endonce
