<h3>&#x1F4BB; Practical Computer Literacy Grading Terminal</h3>

@if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="alert alert-error">
        {{ session('error') }}
    </div>
@endif

<form action="{{ route('admin.literacy.store') }}" method="POST">
    @csrf

    <div class="form-group">
        <label for="candidate_id">Select Candidate</label>
        <select name="candidate_id" id="candidate_id" class="form-control" required>
            <option value="">-- Choose a Candidate --</option>
            @foreach($candidates ?? [] as $candidate)
                <option value="{{ $candidate->id }}">{{ $candidate->name }}</option>
            @endforeach
        </select>
    </div>

    <div class="form-group">
        <label for="assessment_date">Assessment Date</label>
        <input type="date" name="assessment_date" id="assessment_date" class="form-control"
               value="{{ date('Y-m-d') }}" required>
    </div>

    <hr class="section-divider">

    @foreach($literacyTasks ?? [] as $key => $task)
        <div class="question-block">
            <p>{{ $loop->iteration }}. {{ $task }}</p>
            <div class="radio-group" style="gap: 16px;">
                <label>
                    <input type="radio" name="{{ $key }}" value="0" checked> 0 (Not Done)
                </label>
                <label>
                    <input type="radio" name="{{ $key }}" value="1"> 1 (Partial)
                </label>
                <label>
                    <input type="radio" name="{{ $key }}" value="2"> 2 (Complete)
                </label>
            </div>
        </div>
    @endforeach

    <div class="form-group" style="margin-top: 32px;">
        <button type="submit" class="btn btn-primary btn-full" style="font-size: 1.05rem; padding: 14px 24px;">
            Save Practical Test Scores
        </button>
    </div>
</form>
