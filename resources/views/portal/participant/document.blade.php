@extends('layouts.portal')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2>{{ $document->title }}</h2>
            <p class="text-muted">Review document details, download the file, and apply your e-signature.</p>
        </div>
        <div>
            <a href="{{ route('portal.participant.documents.index') }}" class="btn btn-outline-secondary">Back to documents</a>
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

                    <dt class="col-sm-3">Expires</dt>
                    <dd class="col-sm-9">
                        @if($document->expires_at)
                            <span>{{ $document->expires_at->format('Y-m-d') }}</span>
                            @if($document->expires_at->isPast())
                                <span class="badge bg-danger ms-1">Expired</span>
                            @endif
                        @else
                            <span class="text-muted">Never</span>
                        @endif
                    </dd>

                    @if(isset($signatureRequest))
                        <dt class="col-sm-3">Signature request</dt>
                        <dd class="col-sm-9">
                            <span class="badge bg-{{ $signatureRequest->status === 'signed' ? 'success' : ($signatureRequest->status === 'expired' ? 'danger' : 'warning') }}">
                                {{ ucfirst($signatureRequest->status) }}
                            </span>
                            @if($signatureRequest->expires_at)
                                <div class="small text-muted">Expires {{ $signatureRequest->expires_at->format('Y-m-d') }}</div>
                            @endif
                        </dd>
                    @endif

                    <dt class="col-sm-3">Uploaded</dt>
                    <dd class="col-sm-9">{{ $document->created_at->format('Y-m-d H:i') }}</dd>

                    <dt class="col-sm-3">Uploaded by</dt>
                    <dd class="col-sm-9">{{ optional($document->uploader)->name ?? 'Unknown' }}</dd>

                    <dt class="col-sm-3">Current version</dt>
                    <dd class="col-sm-9">{{ optional($document->latestVersion)->version_number ?? 1 }}</dd>
                </dl>
            </div>
            <div class="text-end">
                @if($document->hasStoredFilePath())
                    <a href="{{ route('portal.participant.documents.preview', $document) }}" class="btn btn-outline-primary me-2">Preview</a>
                    <a href="{{ route('portal.participant.documents.download', $document) }}" class="btn btn-primary">Download document</a>
                @endif
            </div>
        </div>
    </div>

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
                        <div class="text-end">
                            <span class="badge bg-secondary d-block mb-2">{{ strtoupper($version->mime_type) }}</span>
                            <a href="{{ route('portal.participant.documents.versions.download', [$document, $version]) }}" class="btn btn-sm btn-outline-secondary">Download v{{ $version->version_number }}</a>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <div class="row g-4">
        <div class="col-lg-7">
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
                                    <div class="d-flex align-items-center gap-2">
                                        @if($signature->signature_path)
                                            <a href="{{ route('portal.participant.documents.signature.download', $signature) }}" class="btn btn-sm btn-outline-primary">View signature</a>
                                        @endif
                                        <span class="badge bg-secondary">Signed</span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        @if($document->status !== 'signed')
            <div class="col-lg-5">
                <div class="card portal-card p-4 border border-primary mb-4">
                    <h5 class="mb-3">Sign this document</h5>
                    <p class="text-muted">Confirm your agreement with the document contents and apply your electronic signature.</p>

                    <form method="POST" action="{{ route('portal.participant.documents.sign', $document) }}">
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

                <div class="card portal-card p-4 border border-secondary">
                    <h5 class="mb-3">Upload a new version</h5>
                    <form method="POST" action="{{ route('portal.participant.documents.versions.store', $document) }}" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label for="file" class="form-label">New version file</label>
                            <input id="file" name="file" type="file" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="notes" class="form-label">Notes (optional)</label>
                            <textarea id="notes" name="notes" class="form-control" rows="3">{{ old('notes') }}</textarea>
                        </div>
                        <button type="submit" class="btn btn-outline-primary">Upload new version</button>
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

            function resizeCanvas() {
                // keep size as-is for simplicity
            }

            function drawLine(x1, y1, x2, y2) {
                ctx.strokeStyle = '#111';
                ctx.lineWidth = 2;
                ctx.lineCap = 'round';
                ctx.beginPath();
                ctx.moveTo(x1, y1);
                ctx.lineTo(x2, y2);
                ctx.stroke();
                ctx.closePath();
            }

            function getPointer(e) {
                const rect = canvas.getBoundingClientRect();
                if (e.touches && e.touches.length) {
                    return { x: e.touches[0].clientX - rect.left, y: e.touches[0].clientY - rect.top };
                }
                return { x: e.clientX - rect.left, y: e.clientY - rect.top };
            }

            canvas.addEventListener('pointerdown', function(e){ drawing = true; const p = getPointer(e); lastX = p.x; lastY = p.y; e.preventDefault(); });
            canvas.addEventListener('pointermove', function(e){ if (!drawing) return; const p = getPointer(e); drawLine(lastX, lastY, p.x, p.y); lastX = p.x; lastY = p.y; e.preventDefault(); });
            canvas.addEventListener('pointerup', function(e){ drawing = false; e.preventDefault(); });
            canvas.addEventListener('pointercancel', function(e){ drawing = false; e.preventDefault(); });

            document.getElementById('clear-signature').addEventListener('click', function(){ ctx.clearRect(0,0,canvas.width,canvas.height); document.getElementById('signature_image').value = ''; });

            // On form submit, capture the signature image data
            const form = canvas.closest('form');
            form.addEventListener('submit', function(e){
                // encode canvas even if blank - server will still accept
                const data = canvas.toDataURL('image/png');
                document.getElementById('signature_image').value = data;
            });
        })();
    </script>
@endsection
