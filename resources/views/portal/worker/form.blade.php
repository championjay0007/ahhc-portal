@extends('layouts.portal')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2>{{ $document->title }}</h2>
            <p class="text-muted">Review the assigned form and add your electronic signature.</p>
        </div>
        <div>
            <a href="{{ route('portal.worker.forms') }}" class="btn btn-outline-secondary">Back to forms</a>
        </div>
    </div>

    <div class="card portal-card p-4 mb-4">
        <div class="d-flex justify-content-between align-items-start gap-3">
            <div>
                <h5 class="mb-2">Document details</h5>
                <dl class="row mb-0">
                    <dt class="col-sm-3">Type</dt>
                    <dd class="col-sm-9">{{ ucfirst(str_replace('_', ' ', $document->document_type)) }}</dd>

                    <dt class="col-sm-3">Status</dt>
                    <dd class="col-sm-9">
                        <span class="badge bg-{{ $document->status === 'signed' ? 'success' : 'secondary' }}">
                            {{ ucfirst($document->status) }}
                        </span>
                    </dd>

                    <dt class="col-sm-3">Uploaded</dt>
                    <dd class="col-sm-9">{{ $document->created_at->format('Y-m-d H:i') }}</dd>

                    <dt class="col-sm-3">Uploaded by</dt>
                    <dd class="col-sm-9">{{ optional($document->uploader)->name ?? 'Unknown' }}</dd>
                </dl>
            </div>
            <div class="text-end">
                <a href="{{ route('portal.worker.forms.download', $document) }}" class="btn btn-primary">Download document</a>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card portal-card p-4 mb-4">
                <h5 class="mb-3">Version history</h5>
                @if($document->versions->isEmpty())
                    <p class="text-muted">No version history is available for this document.</p>
                @else
                    <div class="list-group">
                        @foreach($document->versions->sortByDesc('version_number') as $version)
                            <div class="list-group-item d-flex justify-content-between align-items-start gap-3">
                                <div>
                                    <strong>Version {{ $version->version_number }}</strong>
                                    <div class="small text-muted">Uploaded {{ $version->created_at->format('Y-m-d H:i') }} by {{ optional($version->uploadedBy)->name ?? 'Unknown' }}</div>
                                    @if($version->notes)
                                        <div class="small text-muted">Notes: {{ $version->notes }}</div>
                                    @endif
                                </div>
                                <span class="badge bg-secondary">{{ strtoupper($version->mime_type) }}</span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="card portal-card p-4">
                <h5 class="mb-3">Signature history</h5>
                @if($document->signatures->isEmpty())
                    <p class="text-muted">This document has not been signed yet.</p>
                @else
                    <div class="list-group">
                        @foreach($document->signatures as $signature)
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong>{{ optional($signature->signedBy)->name ?? 'Unknown signer' }}</strong>
                                        <div class="small text-muted">{{ ucfirst(str_replace('_', ' ', $signature->signature_method)) }} • {{ $signature->signed_at->format('Y-m-d H:i') }}</div>
                                    </div>
                                    <span class="badge bg-secondary">Signed</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        @if($document->status !== 'signed')
            <div class="col-lg-5">
                <div class="card portal-card p-4 border border-primary">
                    <h5 class="mb-3">Sign this document</h5>
                    <p class="text-muted">Confirm your agreement and apply your electronic signature.</p>

                    <form method="POST" action="{{ route('portal.worker.forms.sign', $document) }}">
                        @csrf
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" name="confirm_signature" id="confirm_signature" value="1" required>
                            <label class="form-check-label" for="confirm_signature">
                                I confirm I have reviewed this document and agree to sign it electronically.
                            </label>
                        </div>
                        @error('confirm_signature')
                            <div class="text-danger small mb-3">{{ $message }}</div>
                        @enderror

                        <div class="mb-3">
                            <label class="form-label">Draw your signature</label>
                            <canvas id="signature-pad" width="500" height="150" style="border:1px solid #ccc; display:block; touch-action: none;"></canvas>
                            <div class="mt-2">
                                <button type="button" id="clear-signature" class="btn btn-sm btn-outline-secondary">Clear</button>
                            </div>
                            <input type="hidden" name="signature_image" id="signature_image">
                        </div>

                        <button type="submit" class="btn btn-success">Sign document</button>
                    </form>
                </div>
            </div>
        @endif
    </div>

    <script>
        (function(){
            const canvas = document.getElementById('signature-pad');
            if (!canvas) return;
            const ctx = canvas.getContext('2d');
            let drawing = false;
            let lastX = 0, lastY = 0;

            function getPointer(e) {
                const rect = canvas.getBoundingClientRect();
                if (e.touches && e.touches.length) {
                    return { x: e.touches[0].clientX - rect.left, y: e.touches[0].clientY - rect.top };
                }
                return { x: e.clientX - rect.left, y: e.clientY - rect.top };
            }

            function drawLine(x1, y1, x2, y2) {
                ctx.strokeStyle = '#111';
                ctx.lineWidth = 2;
                ctx.lineCap = 'round';
                ctx.beginPath();
                ctx.moveTo(x1, y1);
                ctx.lineTo(x2, y2);
                ctx.stroke();
            }

            canvas.addEventListener('pointerdown', function(e){ drawing = true; const p = getPointer(e); lastX = p.x; lastY = p.y; e.preventDefault(); });
            canvas.addEventListener('pointermove', function(e){ if (!drawing) return; const p = getPointer(e); drawLine(lastX, lastY, p.x, p.y); lastX = p.x; lastY = p.y; e.preventDefault(); });
            canvas.addEventListener('pointerup', function(e){ drawing = false; e.preventDefault(); });
            canvas.addEventListener('pointercancel', function(e){ drawing = false; e.preventDefault(); });

            document.getElementById('clear-signature').addEventListener('click', function(){ ctx.clearRect(0,0,canvas.width,canvas.height); document.getElementById('signature_image').value = ''; });

            const form = canvas.closest('form');
            form.addEventListener('submit', function(){
                const data = canvas.toDataURL('image/png');
                document.getElementById('signature_image').value = data;
            });
        })();
    </script>
@endsection
