<section id="step-7" class="wizard-card-step">
    <div class="mb-4">
        <div class="d-flex align-items-center gap-3">
            <div class="badge bg-primary text-white wizard-step-indicator">7</div>
            <div>
                <h2 class="h5 mb-1">Agreements & Electronic Signature</h2>
                <p class="text-muted mb-0">Please review all required agreements and sign electronically to continue.</p>
            </div>
        </div>
    </div>

    @php
        // Prefer admin-uploaded documents (marked onboarding_required); fall back to Agreement records
        $docMap = collect($adminAssignedDocs ?? [])->keyBy('title');
        $agreementMap = collect($agreements ?? [])->keyBy('title');
        $getAgreementLink = function ($title) use ($docMap, $agreementMap, $token) {
            $doc = $docMap->get($title);
            if ($doc) {
                return [
                    'show' => route('portal.onboarding.document.show', ['token' => $token, 'document' => $doc]),
                    'download' => route('portal.onboarding.document.download', ['token' => $token, 'document' => $doc]),
                ];
            }
            $agreement = $agreementMap->get($title);
            if ($agreement) {
                return [
                    'show' => route('portal.onboarding.agreement.show', ['token' => $token, 'agreement' => $agreement]),
                    'download' => route('portal.onboarding.agreement.download', ['token' => $token, 'agreement' => $agreement]),
                ];
            }
            return null;
        };
    @endphp

    @if(($adminAssignedDocs ?? collect())->isNotEmpty())
        <div class="alert alert-info mb-4">
            <div class="d-flex justify-content-between align-items-start gap-3">
                <div>
                    <h6 class="alert-heading mb-1">Required onboarding forms</h6>
                    <p class="mb-0 small">Allegiance Heart &amp; Home Care has assigned the following forms for you to review and, where required, sign before continuing.</p>
                </div>
            </div>
            <ul class="list-group list-group-flush mt-3">
                @foreach(($adminAssignedDocs ?? collect()) as $document)
                    <li class="list-group-item px-0 d-flex justify-content-between align-items-center gap-3">
                        <div>
                            <strong>{{ $document->title }}</strong>
                            <div class="small text-muted">{{ $document->document_type }}</div>
                        </div>
                        <div class="btn-group" role="group">
                            <a href="{{ route('portal.onboarding.document.show', ['token' => $token, 'document' => $document]) }}" class="btn btn-sm btn-outline-primary">Open</a>
                            <a href="{{ route('portal.onboarding.document.preview', ['token' => $token, 'document' => $document]) }}" class="btn btn-sm btn-outline-secondary">Preview</a>
                            <a href="{{ route('portal.onboarding.document.download', ['token' => $token, 'document' => $document]) }}" class="btn btn-sm btn-outline-secondary">Download</a>
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="mb-3">
        <div class="form-check mb-2">
            <input class="form-check-input" type="checkbox" id="agreement_self_management" name="agreement_self_management" value="1" {{ old('agreement_self_management', $draftData['agreement_self_management'] ?? false) ? 'checked' : '' }} data-required="true" required>
            <label class="form-check-label" for="agreement_self_management">
                I agree to the
                @php $link = $getAgreementLink('Self-Management Agreement'); @endphp
                @if($link)
                    <a href="{{ $link['show'] }}" target="_blank" rel="noopener" class="fw-semibold text-primary" style="text-decoration: underline;">Self-Management Agreement</a>
                @else
                    <span class="fw-semibold text-secondary">Self-Management Agreement</span>
                @endif
                .
            </label>
            <div class="invalid-feedback">You must accept the self-management agreement.</div>
        </div>

        <div class="form-check mb-2">
            <input class="form-check-input" type="checkbox" id="agreement_privacy" name="agreement_privacy" value="1" {{ old('agreement_privacy', $draftData['agreement_privacy'] ?? false) ? 'checked' : '' }} data-required="true" required>
            <label class="form-check-label" for="agreement_privacy">
                I have read and accept the
                @php $link = $getAgreementLink('Privacy Consent'); @endphp
                @if($link)
                    <a href="{{ $link['show'] }}" target="_blank" rel="noopener" class="fw-semibold text-primary" style="text-decoration: underline;">Privacy Consent</a>
                @else
                    <span class="fw-semibold text-secondary">Privacy Consent</span>
                @endif
                .
            </label>
            <div class="invalid-feedback">You must accept the privacy consent.</div>
        </div>

        <div class="form-check mb-2">
            <input class="form-check-input" type="checkbox" id="agreement_responsibilities" name="agreement_responsibilities" value="1" {{ old('agreement_responsibilities', $draftData['agreement_responsibilities'] ?? false) ? 'checked' : '' }} data-required="true" required>
            <label class="form-check-label" for="agreement_responsibilities">
                I agree to the
                @php $link = $getAgreementLink('Responsibilities Agreement'); @endphp
                @if($link)
                    <a href="{{ $link['show'] }}" target="_blank" rel="noopener" class="fw-semibold text-primary" style="text-decoration: underline;">Responsibilities Agreement</a>
                @else
                    <span class="fw-semibold text-secondary">Responsibilities Agreement</span>
                @endif
                .
            </label>
            <div class="invalid-feedback">You must accept the responsibilities agreement.</div>
        </div>

        <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" id="agreement_terms" name="agreement_terms" value="1" {{ old('agreement_terms', $draftData['agreement_terms'] ?? false) ? 'checked' : '' }} data-required="true" required>
            <label class="form-check-label" for="agreement_terms">
                I agree to the
                @php $link = $getAgreementLink('Terms & Conditions'); @endphp
                @if($link)
                    <a href="{{ $link['show'] }}" target="_blank" rel="noopener" class="fw-semibold text-primary" style="text-decoration: underline;">Terms & Conditions</a>
                @else
                    <span class="fw-semibold text-secondary">Terms & Conditions</span>
                @endif
                .
            </label>
            <div class="invalid-feedback">You must accept the terms and conditions.</div>
        </div>
    </div>

    <div class="mb-3">
        <label class="form-label" for="agreement_full_name">Full name for agreement signature</label>
        <input type="text" class="form-control" id="agreement_full_name" name="agreement_full_name" value="{{ old('agreement_full_name', $draftData['agreement_full_name'] ?? '') }}" data-required="true" required>
        @error('agreement_full_name')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-4">
        <label class="form-label">Draw your signature</label>
        <canvas id="signature-pad" width="500" height="150" class="border rounded" style="touch-action:none;"></canvas>
        <div class="mt-2 d-flex gap-2">
            <button type="button" id="clear-signature" class="btn btn-sm btn-outline-secondary">Clear</button>
            <span class="text-muted small">A handwritten signature image will be included in each agreement.</span>
        </div>
        <input type="hidden" id="signature_image" name="signature_image" value="{{ old('signature_image', $draftData['signature_image'] ?? '') }}">
        @error('signature_image')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>
</section>

@push('scripts')
<script>
(function () {
    const canvas = document.getElementById('signature-pad');
    const ctx = canvas.getContext('2d');
    let drawing = false;

    function getPointerPosition(event) {
        const rect = canvas.getBoundingClientRect();
        if (event.touches && event.touches.length) {
            return {
                x: event.touches[0].clientX - rect.left,
                y: event.touches[0].clientY - rect.top,
            };
        }
        return {
            x: event.clientX - rect.left,
            y: event.clientY - rect.top,
        };
    }

    function startDrawing(event) {
        drawing = true;
        const pos = getPointerPosition(event);
        ctx.beginPath();
        ctx.moveTo(pos.x, pos.y);
        event.preventDefault();
    }

    function draw(event) {
        if (!drawing) return;
        const pos = getPointerPosition(event);
        ctx.lineTo(pos.x, pos.y);
        ctx.strokeStyle = '#000';
        ctx.lineWidth = 2;
        ctx.lineCap = 'round';
        ctx.stroke();
        event.preventDefault();
    }

    function stopDrawing(event) {
        if (!drawing) return;
        drawing = false;
        const data = canvas.toDataURL('image/png');
        document.getElementById('signature_image').value = data;
        event.preventDefault();
    }

    canvas.addEventListener('mousedown', startDrawing);
    canvas.addEventListener('mousemove', draw);
    canvas.addEventListener('mouseup', stopDrawing);
    canvas.addEventListener('mouseout', stopDrawing);
    canvas.addEventListener('touchstart', startDrawing);
    canvas.addEventListener('touchmove', draw);
    canvas.addEventListener('touchend', stopDrawing);

    document.getElementById('clear-signature').addEventListener('click', function () {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        document.getElementById('signature_image').value = '';
    });
})();
</script>
@endpush
