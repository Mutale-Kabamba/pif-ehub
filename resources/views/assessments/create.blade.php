@extends('layouts.app')

@section('title', 'Create Assessment Builder')

@section('content')
<div class="page-header" style="display: flex; justify-content: space-between; align-items: center;">
    <div>
        <h1>Assessment Builder</h1>
        <h4>Design custom questions, scoring rules, weights, panel configurations, and access keys</h4>
    </div>
    <div>
        <a href="{{ route('assessments.index') }}" class="btn" style="background: #e0e0e0; color: #333;">
            &larr; Back to Index
        </a>
    </div>
</div>

@if($errors->any())
    <div class="alert alert-error">
        <strong>Please correct the following errors:</strong>
        <ul style="margin-top: 8px; padding-left: 20px;">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form action="{{ route('assessments.store') }}" method="POST" id="assessment-builder-form">
    @csrf

    <!-- General Info Card -->
    <div style="background: #fff; border: 1px solid #e0e0e0; border-radius: 8px; padding: 24px; margin-bottom: 24px;">
        <h3 style="color: #59B33F; margin-bottom: 16px; font-size: 1.2rem; border-bottom: 1px solid #eee; padding-bottom: 8px;">
            1. Assessment Information & Survey Access Key
        </h3>

        <div style="display: grid; grid-template-columns: 2fr 1fr 1fr 1.2fr; gap: 16px;">
            <div class="form-group">
                <label for="title">Assessment Title *</label>
                <input type="text" name="title" id="title" class="form-control" value="{{ old('title') }}" placeholder="e.g. Q3 Panel Interview & Technical Assessment" required>
            </div>

            <div class="form-group">
                <label for="type">Assessment Type *</label>
                <select name="type" id="type" class="form-control" required>
                    <option value="interview" {{ old('type') == 'interview' ? 'selected' : '' }}>Interview</option>
                    <option value="assessment" {{ old('type') == 'assessment' ? 'selected' : '' }}>Assessment</option>
                    <option value="survey" {{ old('type') == 'survey' ? 'selected' : '' }}>Survey</option>
                </select>
            </div>

            <div class="form-group">
                <label for="status">Status *</label>
                <select name="status" id="status" class="form-control" required>
                    <option value="draft" {{ old('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="active" {{ old('status') == 'active' ? 'selected' : 'selected' }}>Active</option>
                    <option value="completed" {{ old('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                </select>
            </div>

            <div class="form-group">
                <label for="access_key">&#128273; Survey Access Key</label>
                <div style="display: flex; gap: 6px;">
                    <input type="text" name="access_key" id="access_key" class="form-control" value="{{ old('access_key', $suggestedKey) }}" style="text-transform: uppercase; font-weight: bold;">
                    <button type="button" onclick="generateKey()" class="btn" style="background: #eee; padding: 6px 10px; font-size: 0.8rem;" title="Generate New Key">&#8635;</button>
                </div>
            </div>
        </div>

        <div class="form-group" style="margin-bottom: 0;">
            <label for="description">Description / Instructions</label>
            <textarea name="description" id="description" class="form-control" rows="2" placeholder="Brief instructions for evaluators or candidates...">{{ old('description') }}</textarea>
        </div>
    </div>

    <!-- Scoring Rules & Multi-Panel Config Card -->
    <div style="background: #fff; border: 1px solid #e0e0e0; border-radius: 8px; padding: 24px; margin-bottom: 24px;">
        <h3 style="color: #59B33F; margin-bottom: 16px; font-size: 1.2rem; border-bottom: 1px solid #eee; padding-bottom: 8px;">
            2. Dynamic Scoring Rules & Multi-Panel Configuration
        </h3>

        <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px;">
            <div class="form-group">
                <label for="number_of_panels">Number of Panels</label>
                <input type="number" name="number_of_panels" id="number_of_panels" class="form-control" value="{{ old('number_of_panels', 2) }}" min="1">
                <small style="color: #777;">Total panels (e.g. 2 = Panel A, Panel B).</small>
            </div>

            <div class="form-group">
                <label for="max_panelists">Max Panelists per Candidate</label>
                <input type="number" name="max_panelists" id="max_panelists" class="form-control" value="{{ old('max_panelists', 3) }}" min="1">
                <small style="color: #777;">Limit judge scores averaged per candidate.</small>
            </div>

            <div class="form-group">
                <label for="score_cap">Score Cap (Max Score)</label>
                <input type="number" step="0.01" name="score_cap" id="score_cap" class="form-control" value="{{ old('score_cap') }}" placeholder="e.g. 100.00">
            </div>

            <div class="form-group">
                <label for="passing_threshold">Passing Threshold</label>
                <input type="number" step="0.01" name="passing_threshold" id="passing_threshold" class="form-control" value="{{ old('passing_threshold', 50.00) }}">
            </div>
        </div>
    </div>

    <!-- Participant Assignments & Multi-Panel Rosters Card -->
    <div style="background: #fff; border: 1px solid #e0e0e0; border-radius: 8px; padding: 24px; margin-bottom: 24px;">
        <h3 style="color: #59B33F; margin-bottom: 16px; font-size: 1.2rem; border-bottom: 1px solid #eee; padding-bottom: 8px;">
            3. Participant Assignments & Panel Registration
        </h3>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px;">

            <!-- Evaluator Assignment & Registration -->
            <div>
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                    <label style="font-weight: bold; color: #1a1a1a; margin: 0;">
                        Assign Evaluators / Panelists:
                    </label>
                    <button type="button" onclick="addNewEvaluatorRow()" class="btn" style="background: #e8f5e9; color: #2e7d32; padding: 3px 8px; font-size: 0.75rem;">
                        + Register New Evaluator
                    </button>
                </div>

                <div style="max-height: 260px; overflow-y: auto; border: 1px solid #ddd; padding: 12px; border-radius: 6px; background: #fafafa;">
                    @forelse($panelists as $pIdx => $panelist)
                        <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px; margin-bottom: 8px; padding-bottom: 6px; border-bottom: 1px solid #eee;">
                            <label style="display: flex; align-items: center; gap: 8px; margin: 0; font-weight: normal; cursor: pointer; flex: 1;">
                                <input type="checkbox" name="panelists[{{ $pIdx }}][user_id]" value="{{ $panelist->id }}" style="accent-color: #59B33F;">
                                <span>{{ $panelist->panelist_name ?: $panelist->name }}</span>
                            </label>
                            <select name="panelists[{{ $pIdx }}][panel_name]" class="form-control" style="width: 100px; height: 32px; font-size: 0.8rem;">
                                <option value="A" {{ $panelist->panel == 'A' ? 'selected' : '' }}>Panel A</option>
                                <option value="B" {{ $panelist->panel == 'B' ? 'selected' : '' }}>Panel B</option>
                                <option value="cover" {{ $panelist->panel == 'cover' ? 'selected' : '' }}>Cover</option>
                            </select>
                        </div>
                    @empty
                        <div style="color: #888; font-size: 0.85rem;">No existing evaluators found. Click "+ Register New Evaluator" above.</div>
                    @endforelse

                    <div id="new-evaluators-container"></div>
                </div>
            </div>

            <!-- Candidate Assignment & Registration -->
            <div>
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                    <label style="font-weight: bold; color: #1a1a1a; margin: 0;">
                        Assign Candidates:
                    </label>
                    <button type="button" onclick="addNewCandidateRow()" class="btn" style="background: #e3f2fd; color: #1976d2; padding: 3px 8px; font-size: 0.75rem;">
                        + Register New Candidate
                    </button>
                </div>

                <div style="max-height: 260px; overflow-y: auto; border: 1px solid #ddd; padding: 12px; border-radius: 6px; background: #fafafa;">
                    @forelse($candidates as $cIdx => $candidate)
                        <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px; margin-bottom: 8px; padding-bottom: 6px; border-bottom: 1px solid #eee;">
                            <label style="display: flex; align-items: center; gap: 8px; margin: 0; font-weight: normal; cursor: pointer; flex: 1;">
                                <input type="checkbox" name="candidates[{{ $cIdx }}][candidate_id]" value="{{ $candidate->id }}" style="accent-color: #59B33F;">
                                <span>{{ $candidate->name }} ({{ $candidate->gender ?: 'N/A' }})</span>
                            </label>
                            <select name="candidates[{{ $cIdx }}][panel_name]" class="form-control" style="width: 100px; height: 32px; font-size: 0.8rem;">
                                <option value="A" {{ $candidate->panel == 'A' ? 'selected' : '' }}>Panel A</option>
                                <option value="B" {{ $candidate->panel == 'B' ? 'selected' : '' }}>Panel B</option>
                            </select>
                        </div>
                    @empty
                        <div style="color: #888; font-size: 0.85rem;">No existing candidates found. Click "+ Register New Candidate" above.</div>
                    @endforelse

                    <div id="new-candidates-container"></div>
                </div>
            </div>

        </div>
    </div>

    <!-- Questions Builder Card -->
    <div style="background: #fff; border: 1px solid #e0e0e0; border-radius: 8px; padding: 24px; margin-bottom: 24px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; border-bottom: 1px solid #eee; padding-bottom: 8px;">
            <h3 style="color: #59B33F; font-size: 1.2rem; margin: 0;">
                4. Custom Questions & Scoring Weights
            </h3>
            <button type="button" id="add-question-btn" class="btn btn-primary" style="padding: 6px 14px; font-size: 0.85rem;">
                + Add Question
            </button>
        </div>

        <div id="questions-container">
            <!-- Questions rendered via JS -->
        </div>
    </div>

    <div style="text-align: right; margin-bottom: 40px;">
        <button type="submit" class="btn btn-primary" style="padding: 12px 28px; font-size: 1rem;">
            Save Assessment & Rules
        </button>
    </div>
</form>

<script>
function generateKey() {
    const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    let rand = '';
    for(let i=0; i<6; i++) {
        rand += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    document.getElementById('access_key').value = 'KEY-' + rand;
}

let newEvalCount = 0;
function addNewEvaluatorRow() {
    const container = document.getElementById('new-evaluators-container');
    const div = document.createElement('div');
    div.style.background = '#e8f5e9';
    div.style.border = '1px solid #c8e6c9';
    div.style.padding = '8px';
    div.style.borderRadius = '6px';
    div.style.marginTop = '8px';
    div.style.display = 'grid';
    div.style.gridTemplateColumns = '1fr 1fr 1fr 80px auto';
    div.style.gap = '6px';

    div.innerHTML = `
        <input type="text" name="new_panelists[${newEvalCount}][name]" class="form-control" style="height: 32px; font-size: 0.8rem;" placeholder="Full Name" required>
        <input type="email" name="new_panelists[${newEvalCount}][email]" class="form-control" style="height: 32px; font-size: 0.8rem;" placeholder="Email" required>
        <input type="password" name="new_panelists[${newEvalCount}][password]" class="form-control" style="height: 32px; font-size: 0.8rem;" placeholder="Password" required>
        <select name="new_panelists[${newEvalCount}][panel_name]" class="form-control" style="height: 32px; font-size: 0.8rem;">
            <option value="A">Panel A</option>
            <option value="B">Panel B</option>
            <option value="cover">Cover</option>
        </select>
        <button type="button" onclick="this.parentElement.remove()" class="btn btn-danger" style="padding: 2px 6px; height: 32px; font-size: 0.75rem;">&times;</button>
    `;

    container.appendChild(div);
    newEvalCount++;
}

let newCandCount = 0;
function addNewCandidateRow() {
    const container = document.getElementById('new-candidates-container');
    const div = document.createElement('div');
    div.style.background = '#e3f2fd';
    div.style.border = '1px solid #bbdefb';
    div.style.padding = '8px';
    div.style.borderRadius = '6px';
    div.style.marginTop = '8px';
    div.style.display = 'grid';
    div.style.gridTemplateColumns = '2fr 1fr 1fr auto';
    div.style.gap = '6px';

    div.innerHTML = `
        <input type="text" name="new_candidates[${newCandCount}][name]" class="form-control" style="height: 32px; font-size: 0.8rem;" placeholder="Candidate Name" required>
        <select name="new_candidates[${newCandCount}][gender]" class="form-control" style="height: 32px; font-size: 0.8rem;">
            <option value="Male">Male</option>
            <option value="Female">Female</option>
        </select>
        <select name="new_candidates[${newCandCount}][panel_name]" class="form-control" style="height: 32px; font-size: 0.8rem;">
            <option value="A">Panel A</option>
            <option value="B">Panel B</option>
        </select>
        <button type="button" onclick="this.parentElement.remove()" class="btn btn-danger" style="padding: 2px 6px; height: 32px; font-size: 0.75rem;">&times;</button>
    `;

    container.appendChild(div);
    newCandCount++;
}

document.addEventListener('DOMContentLoaded', function() {
    let questionIndex = 0;
    const questionsContainer = document.getElementById('questions-container');
    const addQuestionBtn = document.getElementById('add-question-btn');

    function createQuestionBlock(index) {
        const div = document.createElement('div');
        div.className = 'evaluation-block question-item';
        div.dataset.index = index;

        div.innerHTML = `
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                <h4 style="margin: 0; color: #1a1a1a;">Question #${index + 1}</h4>
                <button type="button" class="btn btn-danger remove-question-btn" style="padding: 4px 8px; font-size: 0.75rem;">Remove</button>
            </div>
            <div style="display: grid; grid-template-columns: 3fr 1fr 1fr; gap: 16px; margin-bottom: 12px;">
                <div class="form-group" style="margin: 0;">
                    <label>Question Text *</label>
                    <input type="text" name="questions[${index}][question_text]" class="form-control" placeholder="Enter question prompt..." required>
                </div>
                <div class="form-group" style="margin: 0;">
                    <label>Type *</label>
                    <select name="questions[${index}][type]" class="form-control question-type-select" required>
                        <option value="scale">Scale (1-5 / Numeric)</option>
                        <option value="text">Text Response</option>
                        <option value="multiple_choice">Multiple Choice</option>
                        <option value="boolean">Yes / No (Boolean)</option>
                    </select>
                </div>
                <div class="form-group" style="margin: 0;">
                    <label>Weight (Multiplier) *</label>
                    <input type="number" step="0.1" name="questions[${index}][weight]" class="form-control" value="1.0" required>
                </div>
            </div>
            <input type="hidden" name="questions[${index}][order]" value="${index + 1}">
            <div class="options-wrapper" style="display: none; background: #fafafa; border: 1px dashed #ccc; padding: 12px; border-radius: 6px; margin-top: 12px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                    <label style="font-weight: bold; margin: 0; font-size: 0.85rem; color: #555;">Selectable Options & Score Values:</label>
                    <button type="button" class="btn add-option-btn" style="background: #e0e0e0; color: #333; padding: 3px 8px; font-size: 0.75rem;">+ Add Choice</button>
                </div>
                <div class="options-container"></div>
            </div>
        `;

        const typeSelect = div.querySelector('.question-type-select');
        const optionsWrapper = div.querySelector('.options-wrapper');
        const optionsContainer = div.querySelector('.options-container');

        typeSelect.addEventListener('change', function() {
            if (this.value === 'multiple_choice' || this.value === 'boolean') {
                optionsWrapper.style.display = 'block';
                if (optionsContainer.children.length === 0) {
                    if (this.value === 'boolean') {
                        addOptionRow(optionsContainer, index, 'Yes', 1.0);
                        addOptionRow(optionsContainer, index, 'No', 0.0);
                    } else {
                        addOptionRow(optionsContainer, index, 'Option A', 5.0);
                        addOptionRow(optionsContainer, index, 'Option B', 0.0);
                    }
                }
            } else {
                optionsWrapper.style.display = 'none';
            }
        });

        div.querySelector('.add-option-btn').addEventListener('click', function() {
            addOptionRow(optionsContainer, index, '', 1.0);
        });

        div.querySelector('.remove-question-btn').addEventListener('click', function() {
            div.remove();
        });

        return div;
    }

    function addOptionRow(container, qIdx, defaultLabel = '', defaultValue = 1.0) {
        const optIdx = container.children.length;
        const row = document.createElement('div');
        row.style.display = 'grid';
        row.style.gridTemplateColumns = '2fr 1fr auto';
        row.style.gap = '8px';
        row.style.marginBottom = '6px';
        row.style.alignItems = 'center';

        row.innerHTML = `
            <input type="text" name="questions[${qIdx}][options][${optIdx}][option_label]" class="form-control" style="height: 36px; font-size: 0.85rem;" value="${defaultLabel}" placeholder="Choice label" required>
            <input type="number" step="0.1" name="questions[${qIdx}][options][${optIdx}][option_value]" class="form-control" style="height: 36px; font-size: 0.85rem;" value="${defaultValue}">
            <button type="button" class="btn btn-danger remove-option-btn" style="padding: 4px 8px; font-size: 0.75rem; height: 36px;">&times;</button>
        `;

        row.querySelector('.remove-option-btn').addEventListener('click', function() {
            row.remove();
        });

        container.appendChild(row);
    }

    addQuestionBtn.addEventListener('click', function() {
        const qBlock = createQuestionBlock(questionIndex);
        questionsContainer.appendChild(qBlock);
        questionIndex++;
    });

    addQuestionBtn.click();
    addQuestionBtn.click();
});
</script>
@endsection
