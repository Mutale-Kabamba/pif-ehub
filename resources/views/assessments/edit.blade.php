@extends('layouts.app')

@section('title', 'Edit Assessment - ' . $assessment->title)

@section('content')
<div class="page-header" style="display: flex; justify-content: space-between; align-items: center;">
    <div>
        <h1>Edit Assessment</h1>
        <h4>Modify assessment configuration, questions, scoring rules, and assigned users</h4>
    </div>
    <div>
        <a href="{{ route('assessments.show', $assessment->id) }}" class="btn" style="background: #e0e0e0; color: #333;">
            &larr; Back to Details
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

<form action="{{ route('assessments.update', $assessment->id) }}" method="POST" id="assessment-edit-form">
    @csrf
    @method('PUT')

    <!-- General Info Card -->
    <div style="background: #fff; border: 1px solid #e0e0e0; border-radius: 8px; padding: 24px; margin-bottom: 24px;">
        <h3 style="color: #59B33F; margin-bottom: 16px; font-size: 1.2rem; border-bottom: 1px solid #eee; padding-bottom: 8px;">
            1. Assessment Information
        </h3>

        <div style="display: grid; grid-template-columns: 2fr 1fr 1fr; gap: 16px;">
            <div class="form-group">
                <label for="title">Assessment Title *</label>
                <input type="text" name="title" id="title" class="form-control" value="{{ old('title', $assessment->title) }}" required>
            </div>

            <div class="form-group">
                <label for="type">Assessment Type *</label>
                <select name="type" id="type" class="form-control" required>
                    <option value="interview" {{ old('type', $assessment->type) == 'interview' ? 'selected' : '' }}>Interview</option>
                    <option value="assessment" {{ old('type', $assessment->type) == 'assessment' ? 'selected' : '' }}>Assessment</option>
                    <option value="survey" {{ old('type', $assessment->type) == 'survey' ? 'selected' : '' }}>Survey</option>
                </select>
            </div>

            <div class="form-group">
                <label for="status">Status *</label>
                <select name="status" id="status" class="form-control" required>
                    <option value="draft" {{ old('status', $assessment->status) == 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="active" {{ old('status', $assessment->status) == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="completed" {{ old('status', $assessment->status) == 'completed' ? 'selected' : '' }}>Completed</option>
                </select>
            </div>
        </div>

        <div class="form-group" style="margin-bottom: 0;">
            <label for="description">Description / Instructions</label>
            <textarea name="description" id="description" class="form-control" rows="2">{{ old('description', $assessment->description) }}</textarea>
        </div>
    </div>

    <!-- Scoring Rules Card -->
    <div style="background: #fff; border: 1px solid #e0e0e0; border-radius: 8px; padding: 24px; margin-bottom: 24px;">
        <h3 style="color: #59B33F; margin-bottom: 16px; font-size: 1.2rem; border-bottom: 1px solid #eee; padding-bottom: 8px;">
            2. Scoring Rules & Thresholds
        </h3>

        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px;">
            <div class="form-group">
                <label for="max_panelists">Max Panelists / Evaluators</label>
                <input type="number" name="max_panelists" id="max_panelists" class="form-control" value="{{ old('max_panelists', $assessment->rule?->max_panelists) }}" min="1">
            </div>

            <div class="form-group">
                <label for="score_cap">Score Cap</label>
                <input type="number" step="0.01" name="score_cap" id="score_cap" class="form-control" value="{{ old('score_cap', $assessment->rule?->score_cap) }}">
            </div>

            <div class="form-group">
                <label for="passing_threshold">Passing Threshold</label>
                <input type="number" step="0.01" name="passing_threshold" id="passing_threshold" class="form-control" value="{{ old('passing_threshold', $assessment->rule?->passing_threshold) }}">
            </div>
        </div>
    </div>

    <!-- Assignments Card -->
    <div style="background: #fff; border: 1px solid #e0e0e0; border-radius: 8px; padding: 24px; margin-bottom: 24px;">
        <h3 style="color: #59B33F; margin-bottom: 16px; font-size: 1.2rem; border-bottom: 1px solid #eee; padding-bottom: 8px;">
            3. Participant Assignments
        </h3>

        @php
            $assignedPanelistIds = $assessment->panelists->pluck('id')->toArray();
            $assignedCandidateIds = $assessment->candidates->pluck('id')->toArray();
        @endphp

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px;">
            <div>
                <label style="font-weight: bold; margin-bottom: 8px; display: block;">Assign Evaluators / Panelists:</label>
                <div style="max-height: 200px; overflow-y: auto; border: 1px solid #ddd; padding: 12px; border-radius: 6px; background: #fafafa;">
                    @foreach($panelists as $panelist)
                        <label style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px; font-weight: normal;">
                            <input type="checkbox" name="panelists[]" value="{{ $panelist->id }}" {{ in_array($panelist->id, old('panelists', $assignedPanelistIds)) ? 'checked' : '' }} style="accent-color: #59B33F;">
                            <span>{{ $panelist->panelist_name ?: $panelist->name }} ({{ $panelist->email }})</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <div>
                <label style="font-weight: bold; margin-bottom: 8px; display: block;">Assign Candidates:</label>
                <div style="max-height: 200px; overflow-y: auto; border: 1px solid #ddd; padding: 12px; border-radius: 6px; background: #fafafa;">
                    @foreach($candidates as $candidate)
                        <label style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px; font-weight: normal;">
                            <input type="checkbox" name="candidates[]" value="{{ $candidate->id }}" {{ in_array($candidate->id, old('candidates', $assignedCandidateIds)) ? 'checked' : '' }} style="accent-color: #59B33F;">
                            <span>{{ $candidate->name }} (Panel {{ $candidate->panel }})</span>
                        </label>
                    @endforeach
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
            <!-- Questions rendered via JS script -->
        </div>
    </div>

    <div style="text-align: right; margin-bottom: 40px;">
        <button type="submit" class="btn btn-primary" style="padding: 12px 28px; font-size: 1rem;">
            Update Assessment & Rules
        </button>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let questionIndex = 0;
    const questionsContainer = document.getElementById('questions-container');
    const addQuestionBtn = document.getElementById('add-question-btn');

    // Existing questions data passed from server
    const existingQuestions = @json($assessment->questions);

    function createQuestionBlock(index, data = null) {
        const div = document.createElement('div');
        div.className = 'evaluation-block question-item';
        div.dataset.index = index;

        const qText = data ? data.question_text : '';
        const qType = data ? data.type : 'scale';
        const qWeight = data ? data.weight : '1.0';

        div.innerHTML = `
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                <h4 style="margin: 0; color: #1a1a1a;">Question #${index + 1}</h4>
                <button type="button" class="btn btn-danger remove-question-btn" style="padding: 4px 8px; font-size: 0.75rem;">
                    Remove
                </button>
            </div>

            <div style="display: grid; grid-template-columns: 3fr 1fr 1fr; gap: 16px; margin-bottom: 12px;">
                <div class="form-group" style="margin: 0;">
                    <label>Question Text *</label>
                    <input type="text" name="questions[${index}][question_text]" class="form-control" value="${qText}" required>
                </div>
                <div class="form-group" style="margin: 0;">
                    <label>Type *</label>
                    <select name="questions[${index}][type]" class="form-control question-type-select" required>
                        <option value="scale" ${qType === 'scale' ? 'selected' : ''}>Scale (1-5 / Numeric)</option>
                        <option value="text" ${qType === 'text' ? 'selected' : ''}>Text Response</option>
                        <option value="multiple_choice" ${qType === 'multiple_choice' ? 'selected' : ''}>Multiple Choice</option>
                        <option value="boolean" ${qType === 'boolean' ? 'selected' : ''}>Yes / No (Boolean)</option>
                    </select>
                </div>
                <div class="form-group" style="margin: 0;">
                    <label>Weight (Multiplier) *</label>
                    <input type="number" step="0.1" name="questions[${index}][weight]" class="form-control" value="${qWeight}" required>
                </div>
            </div>

            <input type="hidden" name="questions[${index}][order]" value="${index + 1}">

            <div class="options-wrapper" style="display: ${['multiple_choice', 'boolean'].includes(qType) ? 'block' : 'none'}; background: #fafafa; border: 1px dashed #ccc; padding: 12px; border-radius: 6px; margin-top: 12px;">
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

        if (data && data.options && data.options.length > 0) {
            data.options.forEach(opt => {
                addOptionRow(optionsContainer, index, opt.option_label, opt.option_value);
            });
        }

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
            renumberQuestions();
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
            <input type="number" step="0.1" name="questions[${qIdx}][options][${optIdx}][option_value]" class="form-control" style="height: 36px; font-size: 0.85rem;" value="${defaultValue}" placeholder="Score value">
            <button type="button" class="btn btn-danger remove-option-btn" style="padding: 4px 8px; font-size: 0.75rem; height: 36px;">&times;</button>
        `;

        row.querySelector('.remove-option-btn').addEventListener('click', function() {
            row.remove();
        });

        container.appendChild(row);
    }

    function renumberQuestions() {
        const items = questionsContainer.querySelectorAll('.question-item');
        items.forEach((item, idx) => {
            item.querySelector('h4').textContent = `Question #${idx + 1}`;
        });
    }

    addQuestionBtn.addEventListener('click', function() {
        const qBlock = createQuestionBlock(questionIndex);
        questionsContainer.appendChild(qBlock);
        questionIndex++;
    });

    if (existingQuestions && existingQuestions.length > 0) {
        existingQuestions.forEach(q => {
            const qBlock = createQuestionBlock(questionIndex, q);
            questionsContainer.appendChild(qBlock);
            questionIndex++;
        });
    } else {
        addQuestionBtn.click();
    }
});
</script>
@endsection
