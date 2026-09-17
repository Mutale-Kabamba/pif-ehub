{{-- ═══════════════════════════════════════════════════════════ --}}
{{-- PANEL INTERVIEW EVALUATION SHEET                           --}}
{{-- ═══════════════════════════════════════════════════════════ --}}

@php
    $panelLabel = $user->panelLabel();
    $panelColor = ($user->panel === 'A') ? '#6C63FF' : (($user->panel === 'B') ? '#1A7F4F' : '#555');
@endphp

<div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px; margin-bottom:8px;">
    <h3 style="margin:0;">&#x1F4DD; Live Interview Evaluation &mdash;
        <span style="color:{{ $panelColor }};">{{ $panelLabel }}</span>
    </h3>
    <span style="background:{{ $panelColor }}; color:#fff; font-size:0.8rem; padding:4px 12px; border-radius:20px; font-weight:600;">
        Panelist: {{ $user->panelist_name ?? $user->name }}
    </span>
</div>

<div class="info-box" style="margin-bottom:24px;">
    Interviews are <strong>20 minutes</strong> each. Select a candidate to begin. You will be prompted to confirm
    before the timer starts. A windup alert appears at <strong>5&nbsp;minutes remaining</strong>.
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert-error">{{ session('error') }}</div>
@endif

{{-- ─── CANDIDATE SELECTOR ──────────────────────────────────── --}}
<div class="form-group" style="max-width:500px;">
    <label for="candidate_picker" style="font-weight:600;">Select Candidate from {{ $panelLabel }} Roster</label>
    <select id="candidate_picker" class="form-control" style="font-size:1rem;">
        <option value="">-- Choose a Candidate --</option>
        @foreach($panelCandidates ?? [] as $candidate)
            <option value="{{ $candidate->id }}" data-name="{{ $candidate->name }}">
                {{ $candidate->name }}
                @if($candidate->gender) ({{ $candidate->gender }}) @endif
            </option>
        @endforeach
    </select>
</div>

{{-- ─── CONFIRMATION MODAL ──────────────────────────────────── --}}
<div id="confirm-modal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.55);
     z-index:1000; align-items:center; justify-content:center;">
    <div style="background:#fff; border-radius:12px; padding:36px 40px; max-width:420px; width:90%;
                box-shadow:0 8px 32px rgba(0,0,0,0.25); text-align:center;">
        <div style="font-size:2.5rem; margin-bottom:12px;">&#x23F1;&#xFE0F;</div>
        <h3 style="margin:0 0 8px; font-size:1.25rem;">Start Interview?</h3>
        <p style="color:#555; margin-bottom:24px; line-height:1.5;">
            You are about to start a <strong>20-minute interview</strong> with:<br>
            <strong id="modal-candidate-name" style="color:#1A7F4F; font-size:1.1rem;"></strong>
        </p>
        <div style="display:flex; gap:12px; justify-content:center;">
            <button id="modal-cancel" class="btn btn-secondary" style="min-width:110px;">
                Cancel
            </button>
            <button id="modal-confirm" class="btn btn-primary" style="min-width:130px; background:#1A7F4F;">
                &#x25B6;&#xFE0F; Start Interview
            </button>
        </div>
    </div>
</div>

{{-- ─── INTERVIEW TIMER BLOCK ───────────────────────────────── --}}
<div id="timer-block" style="display:none; background:#f8f9fa; border:2px solid #1A7F4F;
     border-radius:12px; padding:20px 28px; margin:24px 0; position:relative;">

    {{-- Windup banner (hidden until 5-min mark) --}}
    <div id="windup-banner" style="display:none; background:#FF6B35; color:#fff; border-radius:8px;
         padding:12px 20px; margin-bottom:16px; font-weight:600; font-size:1rem;
         animation: pulseBanner 1s ease-in-out infinite;">
        &#x26A0;&#xFE0F;&nbsp; 5 MINUTES REMAINING &mdash; Please start wrapping up the interview!
    </div>

    <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px;">
        <div>
            <div style="font-size:0.85rem; color:#777; text-transform:uppercase; letter-spacing:0.05em;">
                Now Interviewing
            </div>
            <div id="timer-candidate-name" style="font-size:1.2rem; font-weight:700; color:#1a1a1a; margin-top:2px;"></div>
        </div>

        <div style="text-align:center;">
            <div style="font-size:0.75rem; color:#777; text-transform:uppercase; letter-spacing:0.08em; margin-bottom:4px;">
                Time Remaining
            </div>
            <div id="countdown-display" style="font-size:3rem; font-weight:800; font-variant-numeric:tabular-nums;
                 color:#1A7F4F; letter-spacing:0.05em; line-height:1;">
                20:00
            </div>
            {{-- Progress bar --}}
            <div style="background:#ddd; border-radius:4px; height:6px; width:200px; margin:8px auto 0;">
                <div id="progress-bar" style="background:#1A7F4F; border-radius:4px; height:6px;
                     width:100%; transition:width 1s linear;"></div>
            </div>
        </div>

        <div>
            <button id="stop-timer-btn" class="btn btn-secondary" style="font-size:0.85rem; padding:8px 16px;"
                    onclick="stopInterview()">
                &#x23F9;&#xFE0F; End Interview
            </button>
        </div>
    </div>
</div>

{{-- ─── TIME-EXPIRED BANNER ─────────────────────────────────── --}}
<div id="expired-banner" style="display:none; background:#c0392b; color:#fff; border-radius:8px;
     padding:14px 20px; margin:12px 0; font-weight:600; font-size:1rem; text-align:center;">
    &#x23F0; TIME IS UP &mdash; Interview time has elapsed. Please finalise your evaluation and submit.
</div>

{{-- ─── EVALUATION FORM ─────────────────────────────────────── --}}
<div id="eval-form-wrap" style="display:none;">

    <form action="{{ route('admin.panel.store') }}" method="POST" id="panel-eval-form">
        @csrf
        <input type="hidden" name="candidate_id" id="form-candidate-id">

        {{-- Evaluation Block 1: Motivation --}}
        <div class="evaluation-block">
            <h4>&#x1F5E3;&#xFE0F; Motivation</h4>
            <p class="prompt">"Why do you specifically want to learn digital product development at PIF?"</p>
            <div class="radio-group">
                @for($i = 1; $i <= 5; $i++)
                    <label>
                        <input type="radio" name="crit1_motivation" value="{{ $i }}" {{ $i == 3 ? 'checked' : '' }}>
                        {{ $i }}
                    </label>
                @endfor
            </div>
        </div>

        {{-- Evaluation Block 2: Availability --}}
        <div class="evaluation-block">
            <h4>&#x1F5E3;&#xFE0F; Availability</h4>
            <p class="prompt">"Are you able to commit to the full 6-month programme schedule including daily sessions and project assignments?"</p>
            <div class="radio-group">
                @for($i = 1; $i <= 5; $i++)
                    <label>
                        <input type="radio" name="crit2_availability" value="{{ $i }}" {{ $i == 3 ? 'checked' : '' }}>
                        {{ $i }}
                    </label>
                @endfor
            </div>
        </div>

        {{-- Evaluation Block 3: Resilience --}}
        <div class="evaluation-block">
            <h4>&#x1F5E3;&#xFE0F; Resilience / Problem Solving</h4>
            <p class="prompt">"Describe a time you faced a major challenge or setback. How did you respond and what did you learn?"</p>
            <div class="radio-group">
                @for($i = 1; $i <= 5; $i++)
                    <label>
                        <input type="radio" name="crit3_resilience" value="{{ $i }}" {{ $i == 3 ? 'checked' : '' }}>
                        {{ $i }}
                    </label>
                @endfor
            </div>
        </div>

        {{-- Evaluation Block 4: Communication --}}
        <div class="evaluation-block">
            <h4>&#x1F5E3;&#xFE0F; Communication Skills</h4>
            <p class="prompt">Evaluate the candidate's ability to articulate thoughts clearly, listen actively, and respond coherently.</p>
            <div class="radio-group">
                @for($i = 1; $i <= 5; $i++)
                    <label>
                        <input type="radio" name="crit4_communication" value="{{ $i }}" {{ $i == 3 ? 'checked' : '' }}>
                        {{ $i }}
                    </label>
                @endfor
            </div>
        </div>

        {{-- Comments --}}
        <div class="form-group">
            <label for="comments">Panelist Notes &amp; Observations</label>
            <textarea name="comments" id="comments" class="form-control" rows="5"
                      placeholder="Record any additional observations, context, or notes..."></textarea>
        </div>

        <div class="form-group" style="margin-top:28px;">
            <button type="submit" class="btn btn-primary btn-full" style="font-size:1.05rem; padding:14px 24px;">
                &#x2705; Submit Evaluation
            </button>
        </div>
    </form>

</div>{{-- /eval-form-wrap --}}

{{-- ─── TIMER STYLES ────────────────────────────────────────── --}}
<style>
@keyframes pulseBanner {
    0%, 100% { opacity: 1; }
    50%       { opacity: 0.75; }
}
#countdown-display.warning { color: #e67e22; }
#countdown-display.critical { color: #c0392b; animation: pulseBanner 0.7s ease-in-out infinite; }
</style>

{{-- ─── TIMER JAVASCRIPT ────────────────────────────────────── --}}
<script>
(function () {
    const TOTAL_SECONDS  = 20 * 60;  // 20 minutes
    const WINDUP_AT      = 5 * 60;   // alert when 5 min remain
    const STORAGE_KEY    = 'pif_interview_timer';

    let timerInterval    = null;
    let selectedId       = null;
    let selectedName     = '';

    // ── Element refs ─────────────────────────────────────────
    const picker         = document.getElementById('candidate_picker');
    const modal          = document.getElementById('confirm-modal');
    const modalName      = document.getElementById('modal-candidate-name');
    const modalCancel    = document.getElementById('modal-cancel');
    const modalConfirm   = document.getElementById('modal-confirm');
    const timerBlock     = document.getElementById('timer-block');
    const timerNameEl    = document.getElementById('timer-candidate-name');
    const countdownEl    = document.getElementById('countdown-display');
    const progressBar    = document.getElementById('progress-bar');
    const windupBanner   = document.getElementById('windup-banner');
    const expiredBanner  = document.getElementById('expired-banner');
    const evalWrap       = document.getElementById('eval-form-wrap');
    const formCandidateId = document.getElementById('form-candidate-id');

    // ── Restore any active session from localStorage ──────────
    const saved = loadTimerState();
    if (saved && saved.endTime > Date.now()) {
        restoreTimer(saved);
    }

    // ── Candidate picker → show confirmation modal ────────────
    picker.addEventListener('change', function () {
        const opt = this.options[this.selectedIndex];
        if (!opt.value) return;

        selectedId   = opt.value;
        selectedName = opt.dataset.name || opt.text.replace(/\s*\(.*\)/, '').trim();

        // If a different interview is running, don't interrupt
        if (timerInterval !== null) {
            alert('An interview is already in progress. End the current interview before starting a new one.');
            this.value = '';
            return;
        }

        modalName.textContent = selectedName;
        modal.style.display   = 'flex';
    });

    // ── Modal: Cancel ─────────────────────────────────────────
    modalCancel.addEventListener('click', function () {
        modal.style.display = 'none';
        picker.value        = '';
        selectedId          = null;
        selectedName        = '';
    });

    // ── Modal: Confirm — start timer ──────────────────────────
    modalConfirm.addEventListener('click', function () {
        modal.style.display = 'none';
        const endTime       = Date.now() + TOTAL_SECONDS * 1000;
        saveTimerState({ endTime, candidateId: selectedId, candidateName: selectedName });
        startCountdown(endTime, selectedId, selectedName);
    });

    // ── Close modal on backdrop click ─────────────────────────
    modal.addEventListener('click', function (e) {
        if (e.target === modal) modalCancel.click();
    });

    // ── Start countdown ───────────────────────────────────────
    function startCountdown(endTime, candidateId, candidateName) {
        timerNameEl.textContent    = candidateName;
        formCandidateId.value      = candidateId;
        timerBlock.style.display   = 'block';
        evalWrap.style.display     = 'block';
        expiredBanner.style.display = 'none';
        windupBanner.style.display  = 'none';
        countdownEl.className       = '';

        windupFired = false;

        tick(endTime);
        timerInterval = setInterval(() => tick(endTime), 1000);
    }

    let windupFired = false;

    function tick(endTime) {
        const remaining = Math.max(0, Math.round((endTime - Date.now()) / 1000));
        const pct       = (remaining / TOTAL_SECONDS) * 100;

        // Display
        const m = Math.floor(remaining / 60).toString().padStart(2, '0');
        const s = (remaining % 60).toString().padStart(2, '0');
        countdownEl.textContent = `${m}:${s}`;
        progressBar.style.width = `${pct}%`;

        // Colour stages
        if (remaining <= 60) {
            countdownEl.className   = 'critical';
            progressBar.style.background = '#c0392b';
        } else if (remaining <= WINDUP_AT) {
            countdownEl.className   = 'warning';
            progressBar.style.background = '#e67e22';
        }

        // Windup alert at 5 minutes remaining
        if (!windupFired && remaining <= WINDUP_AT) {
            windupFired = true;
            windupBanner.style.display = 'block';
            // Browser notification (if permitted)
            if (Notification.permission === 'granted') {
                new Notification('⏱ PIF Interview Windup', {
                    body: `5 minutes remaining for ${timerNameEl.textContent}. Please start wrapping up.`,
                    icon: '/favicon.ico',
                });
            }
        }

        // Time expired
        if (remaining === 0) {
            clearInterval(timerInterval);
            timerInterval = null;
            countdownEl.textContent = '00:00';
            expiredBanner.style.display = 'block';
            windupBanner.style.display  = 'none';
            clearTimerState();
        }
    }

    // ── Stop interview manually ───────────────────────────────
    window.stopInterview = function () {
        if (!confirm('End this interview session?')) return;
        clearInterval(timerInterval);
        timerInterval = null;
        timerBlock.style.display   = 'none';
        windupBanner.style.display = 'none';
        expiredBanner.style.display = 'none';
        picker.value = '';
        clearTimerState();
    };

    // ── Restore a saved timer ─────────────────────────────────
    function restoreTimer(saved) {
        selectedId   = String(saved.candidateId);
        selectedName = saved.candidateName;
        // Sync picker
        for (let i = 0; i < picker.options.length; i++) {
            if (picker.options[i].value === selectedId) {
                picker.selectedIndex = i;
                break;
            }
        }
        startCountdown(saved.endTime, saved.candidateId, saved.candidateName);
    }

    // ── localStorage helpers ──────────────────────────────────
    function saveTimerState(state) {
        try { localStorage.setItem(STORAGE_KEY, JSON.stringify(state)); } catch (e) {}
    }
    function loadTimerState() {
        try { return JSON.parse(localStorage.getItem(STORAGE_KEY)); } catch (e) { return null; }
    }
    function clearTimerState() {
        try { localStorage.removeItem(STORAGE_KEY); } catch (e) {}
    }

    // ── Request notification permission on load ───────────────
    if (typeof Notification !== 'undefined' && Notification.permission === 'default') {
        Notification.requestPermission();
    }
})();
</script>

