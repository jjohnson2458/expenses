{{-- Voice Input Modal --}}
<div class="modal fade" id="voiceModal" tabindex="-1" aria-labelledby="voiceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title" id="voiceModalLabel">
                    <i class="bi bi-mic me-2"></i> Voice Input
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                {{-- Browser Check --}}
                <div id="voiceUnsupported" class="d-none">
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle me-1"></i>
                        Voice input is not supported in your browser. Please use Chrome, Edge, or Safari.
                    </div>
                </div>

                <div id="voiceSupported">
                    {{-- Mic Button --}}
                    <div class="mb-4">
                        <button type="button" id="voiceMicBtn" class="btn rounded-circle p-0 border-0" style="width: 100px; height: 100px; background: linear-gradient(135deg, #4e73df, #224abe); transition: all 0.3s;">
                            <i class="bi bi-mic-fill text-white" style="font-size: 2.5rem;"></i>
                        </button>
                        <div id="voiceStatus" class="mt-2 text-muted small">Click to start speaking</div>
                    </div>

                    {{-- Transcript --}}
                    <div id="voiceTranscriptSection" class="d-none mb-3">
                        <label class="form-label fw-semibold small text-start d-block">What you said:</label>
                        <div id="voiceTranscript" class="bg-light rounded p-3 text-start" style="min-height: 50px;"></div>
                    </div>

                    {{-- Parsed Result --}}
                    <div id="voiceParsedSection" class="d-none">
                        <h6 class="fw-semibold text-start mb-3">Parsed Result</h6>
                        <div class="row g-2 text-start mb-3">
                            <div class="col-12">
                                <label class="form-label small fw-semibold">Description</label>
                                <input type="text" class="form-control form-control-sm" id="voiceDescription" readonly>
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-semibold">Amount</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text">$</span>
                                    <input type="text" class="form-control" id="voiceAmount" readonly>
                                </div>
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-semibold">Type</label>
                                <input type="text" class="form-control form-control-sm" id="voiceType" readonly>
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-semibold">Category</label>
                                <input type="text" class="form-control form-control-sm" id="voiceCategory" readonly>
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-semibold">Date</label>
                                <input type="text" class="form-control form-control-sm" id="voiceDate" readonly>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 justify-content-center" id="voiceActions" style="display: none !important;">
                <button type="button" id="voiceTryAgain" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-clockwise me-1"></i> Try Again
                </button>
                <button type="button" id="voiceAddToLedger" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-1"></i> Add to Ledger
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(function() {
    const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;

    if (!SpeechRecognition) {
        $('#voiceUnsupported').removeClass('d-none');
        $('#voiceSupported').addClass('d-none');
        return;
    }

    const recognition = new SpeechRecognition();
    recognition.continuous = false;
    recognition.interimResults = false;
    recognition.lang = '{{ session("lang", "en") === "es" ? "es-US" : "en-US" }}';

    let isListening = false;

    const $micBtn = $('#voiceMicBtn');
    const $status = $('#voiceStatus');
    const $transcriptSection = $('#voiceTranscriptSection');
    const $transcript = $('#voiceTranscript');
    const $parsedSection = $('#voiceParsedSection');
    const $actions = $('#voiceActions');

    function resetVoice() {
        $transcriptSection.addClass('d-none');
        $parsedSection.addClass('d-none');
        $actions.css('display', 'none').addClass('d-none');
        $transcript.text('');
        $status.text('Click to start speaking');
        $micBtn.css('background', 'linear-gradient(135deg, #4e73df, #224abe)');
        $('#voiceDescription, #voiceAmount, #voiceType, #voiceCategory, #voiceDate').val('');
    }

    $micBtn.on('click', function() {
        if (isListening) {
            recognition.stop();
            return;
        }
        resetVoice();
        recognition.start();
    });

    recognition.onstart = function() {
        isListening = true;
        $status.text('Listening... Speak now');
        $micBtn.css('background', 'linear-gradient(135deg, #e74a3b, #c0392b)');
        $micBtn.find('i').addClass('pulse-animation');
    };

    recognition.onresult = function(event) {
        const text = event.results[0][0].transcript;
        $transcriptSection.removeClass('d-none');
        $transcript.text(text);
        $status.text('Processing...');

        // Parse the voice input via AJAX
        $.ajax({
            url: '{{ url("/expenses/voice") }}',
            method: 'POST',
            data: { transcript: text },
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function(response) {
                if (response.success) {
                    const data = response.data || response;
                    $('#voiceDescription').val(data.description || '');
                    $('#voiceAmount').val(data.amount || '');
                    $('#voiceType').val(data.type || 'debit');
                    $('#voiceCategory').val(data.category || '');
                    $('#voiceDate').val(data.date || new Date().toISOString().split('T')[0]);

                    $parsedSection.removeClass('d-none');
                    $actions.css('display', '').removeClass('d-none');
                    $status.text('Review and confirm');
                } else {
                    $status.text('Could not parse. Try again.');
                    $actions.css('display', '').removeClass('d-none');
                }
            },
            error: function() {
                $status.text('Error processing voice input. Try again.');
                $actions.css('display', '').removeClass('d-none');
            }
        });
    };

    recognition.onerror = function(event) {
        isListening = false;
        $micBtn.css('background', 'linear-gradient(135deg, #4e73df, #224abe)');
        if (event.error === 'no-speech') {
            $status.text('No speech detected. Try again.');
        } else {
            $status.text('Error: ' + event.error + '. Try again.');
        }
    };

    recognition.onend = function() {
        isListening = false;
        $micBtn.css('background', 'linear-gradient(135deg, #4e73df, #224abe)');
    };

    // Try Again
    $('#voiceTryAgain').on('click', function() {
        resetVoice();
    });

    // Add to Ledger
    $('#voiceAddToLedger').on('click', function() {
        const data = {
            description: $('#voiceDescription').val(),
            amount: $('#voiceAmount').val(),
            type: $('#voiceType').val() || 'debit',
            category: $('#voiceCategory').val(),
            date: $('#voiceDate').val() || new Date().toISOString().split('T')[0]
        };

        $.ajax({
            url: '{{ url("/expenses") }}',
            method: 'POST',
            data: data,
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function(response) {
                $('#voiceModal').modal('hide');
                resetVoice();
                window.location.reload();
            },
            error: function(xhr) {
                alert('Error saving expense. Please try again or add manually.');
            }
        });
    });

    // Reset on modal close
    $('#voiceModal').on('hidden.bs.modal', function() {
        if (isListening) recognition.stop();
        resetVoice();
    });
});
</script>
<style>
    @keyframes pulse-animation {
        0% { opacity: 1; }
        50% { opacity: 0.5; }
        100% { opacity: 1; }
    }
    .pulse-animation {
        animation: pulse-animation 1s infinite;
    }
</style>
@endpush
