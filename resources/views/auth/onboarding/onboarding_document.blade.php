@extends('layouts.auth')

@section('content')
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2>{{ $document->title }}</h2>
                <p class="text-muted">Review document details and apply your e-signature to complete onboarding.</p>
            </div>
            <div>
                <a href="{{ route('portal.onboarding.show', ['token' => $token]) }}" class="btn btn-outline-secondary">Back to onboarding</a>
            </div>
        </div>

        <div class="card p-4 mb-4">
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

                        <dt class="col-sm-3">Current version</dt>
                        <dd class="col-sm-9">{{ optional($document->latestVersion)->version_number ?? 1 }}</dd>
                    </dl>
                </div>
                <div class="text-end">
                    <a href="{{ route('portal.onboarding.document.preview', ['token' => $token, 'document' => $document]) }}" class="btn btn-outline-primary me-2">Preview</a>
                    @if(Storage::disk($document->storage_disk)->exists($document->path))
                        <a href="{{ route('portal.onboarding.document.download', ['token' => $token, 'document' => $document]) }}" class="btn btn-outline-secondary me-2">Download</a>
                        <a href="{{ route('portal.onboarding.document.preview', ['token' => $token, 'document' => $document]) }}" class="btn btn-primary">Open document</a>
                    @endif
                </div>
            </div>
        </div>

        @php
            $supportingDocs = $document->metadata['supporting_documents'] ?? [];
        @endphp

        @if(!empty($supportingDocs))
            <div class="card p-4 mb-4 border-info">
                <h5 class="mb-3 text-info">
                    <i class="bi bi-file-earmark-check"></i>
                    Supporting documents to review
                </h5>
                <p class="text-muted mb-3">Before signing, you must review the following supporting documents:</p>
                
                <div class="list-group" id="supporting-documents-list">
                    @foreach($supportingDocs as $doc)
                        <div class="list-group-item d-flex justify-content-between align-items-center supporting-doc-item" data-doc-id="{{ $doc['id'] ?? md5($doc['name']) }}">
                            <div>
                                <h6 class="mb-1">
                                    <i class="bi bi-{{ in_array(strtolower(pathinfo($doc['name'], PATHINFO_EXTENSION)), ['pdf']) ? 'file-pdf' : 'file-earmark' }}"></i>
                                    {{ $doc['name'] }}
                                </h6>
                                <small class="text-muted">{{ number_format($doc['size'] ?? 0, 0) }} bytes</small>
                            </div>
                            <div class="d-flex gap-2 align-items-center">
                                <span class="badge bg-secondary doc-status" style="display: none;">Viewed</span>
                                <a href="#" class="btn btn-sm btn-outline-primary view-doc-btn" data-doc-url="{{ route('portal.onboarding.supporting.download', ['token' => $token, 'id' => $doc['id']]) }}" data-doc-name="{{ $doc['name'] }}">
                                    View document
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="alert alert-warning mb-4" id="view-warning">
                <i class="bi bi-exclamation-triangle"></i>
                <strong>Please review all documents</strong> before signing. You must view each supporting document to continue.
            </div>
        @endif

        <div class="card p-4">
            <h5 class="mb-3">Sign this document</h5>
            <p class="text-muted">Confirm your agreement with the document contents and apply your electronic signature.</p>

            <form method="POST" action="{{ route('portal.onboarding.document.sign', ['token' => $token, 'document' => $document]) }}">
                @csrf
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" name="confirm_signature" id="confirm_signature" value="1" required>
                    <label class="form-check-label" for="confirm_signature">
                        I confirm I have reviewed this document and agree to sign it electronically.
                    </label>
                </div>

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

    <script>
        (function(){
            const canvas = document.getElementById('signature-pad');
            if (!canvas) return;
            const ctx = canvas.getContext('2d');
            let drawing = false;
            let lastX = 0, lastY = 0;

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

            const form = canvas.closest('form');
            form.addEventListener('submit', function(e){
                const data = canvas.toDataURL('image/png');
                document.getElementById('signature_image').value = data;
            });
        })();

        // Handle supporting documents viewing requirement
        (function(){
            const supportingDocsSection = document.getElementById('supporting-documents-list');
            if (!supportingDocsSection) {
                return;
            }

            const confirmCheckbox = document.getElementById('confirm_signature');
            const viewButtons = document.querySelectorAll('.view-doc-btn');
            const viewWarning = document.getElementById('view-warning');
            const docItems = document.querySelectorAll('.supporting-doc-item');

            const totalDocs = docItems.length;
            let viewedDocs = new Set();

            function updateSigningAbility() {
                const allViewed = viewedDocs.size === totalDocs;
                
                if (allViewed) {
                    confirmCheckbox.disabled = false;
                    viewWarning.style.display = 'none';
                    docItems.forEach(item => {
                        item.classList.add('list-group-item-success');
                    });
                } else {
                    confirmCheckbox.disabled = true;
                    confirmCheckbox.checked = false;
                    viewWarning.style.display = 'block';
                }
            }

            // Initialize viewed state from server
            (function initFromServer(){
                fetch("{{ route('portal.onboarding.supporting.status', ['token' => $token]) }}")
                    .then(r => r.ok ? r.json() : Promise.reject())
                    .then(data => {
                        if (data && data.viewed) {
                            data.viewed.forEach(id => viewedDocs.add(id));
                            // mark UI
                            docItems.forEach(item => {
                                const id = item.dataset.docId;
                                if (viewedDocs.has(id)) {
                                    const badge = item.querySelector('.doc-status');
                                    badge.style.display = 'inline-block';
                                    const btn = item.querySelector('.view-doc-btn');
                                    if (btn) {
                                        btn.textContent = 'Viewed ✓';
                                        btn.classList.remove('btn-outline-primary');
                                        btn.classList.add('btn-success');
                                        btn.disabled = true;
                                    }
                                }
                            });
                            updateSigningAbility();
                        }
                    }).catch(()=>{});
            })();

            viewButtons.forEach(btn => {
                btn.addEventListener('click', function(e){
                    e.preventDefault();
                    const docItem = this.closest('.supporting-doc-item');
                    const docId = docItem.dataset.docId;
                    const docName = this.dataset.docName;
                    const docUrl = this.dataset.docUrl;

                    viewedDocs.add(docId);

                    const badge = docItem.querySelector('.doc-status');
                    badge.style.display = 'inline-block';
                    
                    this.textContent = 'Viewed ✓';
                    this.classList.remove('btn-outline-primary');
                    this.classList.add('btn-success');
                    this.disabled = true;

                    updateSigningAbility();

                    // Open the document in a new window and notify the server that it was viewed
                    if (docUrl && docUrl !== '#') {
                        window.open(docUrl, '_blank');
                    }

                    // POST to mark viewed (best-effort)
                    try {
                        fetch("{{ route('portal.onboarding.supporting.view', ['token' => $token, 'id' => 'REPLACE_ID']) }}".replace('REPLACE_ID', encodeURIComponent(docId)), {
                            method: 'POST',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.getAttribute('content') || ''
                            },
                            body: JSON.stringify({ viewed: true })
                        }).catch(() => {});
                    } catch (err) {
                        // ignore network errors
                    }
                });
            });

            // Initialize state
            updateSigningAbility();
        })();
    </script>
@endsection
