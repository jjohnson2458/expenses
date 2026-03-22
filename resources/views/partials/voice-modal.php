<?php
/**
 * Voice Input Modal Partial
 *
 * Uses Web Speech API to capture spoken input, sends it to /expenses/voice
 * for natural-language parsing, and displays the result for confirmation.
 */
?>
<div class="modal fade" id="voiceModal" tabindex="-1" aria-labelledby="voiceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="voiceModalLabel">
                    <i class="bi bi-mic me-2"></i>Voice Expense Input
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">

                <!-- Mic button -->
                <button type="button" id="voiceMicBtn" class="btn btn-outline-primary rounded-circle p-4 mb-3"
                        style="width: 100px; height: 100px;" aria-label="Start voice recording">
                    <i class="bi bi-mic-fill fs-1" id="voiceMicIcon"></i>
                </button>

                <!-- Status -->
                <p id="voiceStatus" class="text-muted mb-3">Click the microphone to start speaking</p>

                <!-- Raw transcript -->
                <div id="voiceTranscript" class="d-none mb-3">
                    <label class="form-label small text-muted">You said:</label>
                    <div class="alert alert-light border text-start mb-0" id="voiceTranscriptText"></div>
                </div>

                <!-- Parsed result preview -->
                <div id="voiceResult" class="d-none">
                    <hr>
                    <h6 class="mb-3">Parsed Result</h6>
                    <div class="text-start">
                        <table class="table table-sm table-borderless mb-0">
                            <tr>
                                <td class="text-muted fw-semibold" style="width: 110px;">Description</td>
                                <td id="voiceResDescription">-</td>
                            </tr>
                            <tr>
                                <td class="text-muted fw-semibold">Amount</td>
                                <td id="voiceResAmount">-</td>
                            </tr>
                            <tr>
                                <td class="text-muted fw-semibold">Category</td>
                                <td id="voiceResCategory">-</td>
                            </tr>
                            <tr>
                                <td class="text-muted fw-semibold">Date</td>
                                <td id="voiceResDate">-</td>
                            </tr>
                            <tr>
                                <td class="text-muted fw-semibold">Type</td>
                                <td id="voiceResType">-</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer justify-content-between">
                <button type="button" id="voiceTryAgainBtn" class="btn btn-outline-secondary d-none">
                    <i class="bi bi-arrow-counterclockwise me-1"></i>Try Again
                </button>
                <button type="button" id="voiceAddBtn" class="btn btn-primary d-none">
                    <i class="bi bi-plus-lg me-1"></i>Add to Ledger
                </button>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    'use strict';

    const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
    if (!SpeechRecognition) {
        const btn = document.getElementById('voiceMicBtn');
        if (btn) {
            btn.disabled = true;
            document.getElementById('voiceStatus').textContent = 'Speech recognition is not supported in this browser.';
        }
        return;
    }

    const recognition   = new SpeechRecognition();
    recognition.lang    = 'en-US';
    recognition.continuous       = false;
    recognition.interimResults   = false;
    recognition.maxAlternatives  = 1;

    const micBtn        = document.getElementById('voiceMicBtn');
    const micIcon       = document.getElementById('voiceMicIcon');
    const statusEl      = document.getElementById('voiceStatus');
    const transcriptBox = document.getElementById('voiceTranscript');
    const transcriptTxt = document.getElementById('voiceTranscriptText');
    const resultBox     = document.getElementById('voiceResult');
    const tryAgainBtn   = document.getElementById('voiceTryAgainBtn');
    const addBtn        = document.getElementById('voiceAddBtn');

    const resDesc     = document.getElementById('voiceResDescription');
    const resAmount   = document.getElementById('voiceResAmount');
    const resCategory = document.getElementById('voiceResCategory');
    const resDate     = document.getElementById('voiceResDate');
    const resType     = document.getElementById('voiceResType');

    let isListening = false;
    let parsedData  = null;

    function resetUI() {
        isListening = false;
        micBtn.classList.remove('btn-danger');
        micBtn.classList.add('btn-outline-primary');
        micIcon.className = 'bi bi-mic-fill fs-1';
        statusEl.textContent = 'Click the microphone to start speaking';
        transcriptBox.classList.add('d-none');
        resultBox.classList.add('d-none');
        tryAgainBtn.classList.add('d-none');
        addBtn.classList.add('d-none');
        parsedData = null;
    }

    micBtn.addEventListener('click', function () {
        if (isListening) {
            recognition.stop();
            return;
        }
        resetUI();
        isListening = true;
        micBtn.classList.remove('btn-outline-primary');
        micBtn.classList.add('btn-danger');
        micIcon.className = 'bi bi-mic-fill fs-1 voice-pulse';
        statusEl.textContent = 'Listening...';
        recognition.start();
    });

    recognition.addEventListener('result', function (event) {
        const transcript = event.results[0][0].transcript;
        transcriptTxt.textContent = transcript;
        transcriptBox.classList.remove('d-none');
        statusEl.textContent = 'Processing...';

        // Send to server for parsing
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
        fetch('/expenses/voice', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({ text: transcript })
        })
        .then(function (res) { return res.json(); })
        .then(function (data) {
            parsedData = data;
            resDesc.textContent     = data.description || '-';
            resAmount.textContent   = data.amount !== null ? '$' + parseFloat(data.amount).toFixed(2) : '-';
            resCategory.textContent = data.category || 'Uncategorized';
            resDate.textContent     = data.date || '-';
            resType.textContent     = (data.type || 'debit').charAt(0).toUpperCase() + (data.type || 'debit').slice(1);

            resultBox.classList.remove('d-none');
            tryAgainBtn.classList.remove('d-none');
            addBtn.classList.remove('d-none');
            statusEl.textContent = 'Review the parsed result below.';
        })
        .catch(function () {
            statusEl.textContent = 'Error processing voice input. Please try again.';
            tryAgainBtn.classList.remove('d-none');
        });
    });

    recognition.addEventListener('end', function () {
        isListening = false;
        micBtn.classList.remove('btn-danger');
        micBtn.classList.add('btn-outline-primary');
        micIcon.className = 'bi bi-mic-fill fs-1';
    });

    recognition.addEventListener('error', function (event) {
        isListening = false;
        micBtn.classList.remove('btn-danger');
        micBtn.classList.add('btn-outline-primary');
        micIcon.className = 'bi bi-mic-fill fs-1';
        statusEl.textContent = 'Error: ' + event.error + '. Please try again.';
        tryAgainBtn.classList.remove('d-none');
    });

    tryAgainBtn.addEventListener('click', function () {
        resetUI();
    });

    addBtn.addEventListener('click', function () {
        if (!parsedData) return;

        // Build query string to pre-fill the expense form
        const params = new URLSearchParams();
        if (parsedData.description) params.set('description', parsedData.description);
        if (parsedData.amount)      params.set('amount', parsedData.amount);
        if (parsedData.date)        params.set('date', parsedData.date);
        if (parsedData.type)        params.set('type', parsedData.type);
        if (parsedData.category_id) params.set('category_id', parsedData.category_id);

        window.location.href = '/expenses/create?' + params.toString();
    });

    // Reset when modal is closed
    var modalEl = document.getElementById('voiceModal');
    if (modalEl) {
        modalEl.addEventListener('hidden.bs.modal', function () {
            if (isListening) recognition.stop();
            resetUI();
        });
    }
})();
</script>

<style>
.voice-pulse {
    animation: voicePulse 1s ease-in-out infinite;
}
@keyframes voicePulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.4; }
}
</style>
