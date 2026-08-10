@extends('layouts.sec')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/patrons/directory.css') }}">
@endsection

@section('content')
<div class="patron-dir">
    <header class="patron-dir__hero">
        <div>
            <p class="patron-dir__eyebrow">Patron data</p>
            <h1 class="patron-dir__title">Students</h1>
            <p class="patron-dir__subtitle">Search, register, and manage student patron records.</p>
        </div>
        <div class="patron-dir__hero-actions">
            <a href="{{ route('students.create') }}" class="patron-dir__btn patron-dir__btn--primary">+ Register student</a>
            <a href="{{ route('book.index') }}" class="patron-dir__btn patron-dir__btn--outline">← Catalog</a>
        </div>
    </header>

    @include('patrons.partials.type_tabs', ['active' => 'students'])

    @include('patrons.partials.quick_actions_student')

    @if(session('success'))
        <div class="alert alert-success patron-dir__alert">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger patron-dir__alert">{{ session('error') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger patron-dir__alert">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="patron-dir__toolbar">
        <form action="{{ route('students.index') }}" method="GET" class="patron-dir__filters">
            <div class="patron-dir__field">
                <label for="student_search">Search</label>
                <input type="text" name="search" id="student_search" class="form-control"
                       placeholder="Name, ID, course, QR…" value="{{ request('search') }}">
            </div>
            <div class="patron-dir__field">
                <label for="student_program">Program</label>
                <select name="program_id" id="student_program" class="form-select">
                    <option value="">All programs</option>
                    @foreach ($programs as $program)
                        <option value="{{ $program->program_code }}" @selected(request('program_id') == $program->program_code)>
                            {{ $program->program_name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="patron-dir__field">
                <label for="student_year">Year level</label>
                <select name="year" id="student_year" class="form-select">
                    <option value="">All years</option>
                    @foreach (['1st Year', '2nd Year', '3rd Year', '4th Year', '5th Year', '6th Year'] as $yr)
                        <option value="{{ $yr }}" @selected(request('year') == $yr)>{{ $yr }}</option>
                    @endforeach
                </select>
            </div>
            <div class="patron-dir__filter-btn">
                <button type="submit" class="patron-dir__btn patron-dir__btn--outline">Apply</button>
            </div>
        </form>
    </div>

    <details class="patron-dir__import" @if($errors->has('file') || session('error')) open @endif>
        <summary>Import students from spreadsheet</summary>
        <div class="patron-dir__import-body">
            <p class="text-muted small mb-2">
                Upload <strong>HSC-STUDENTS</strong> (.xlsx) with columns:
                <code>Name</code>, <code>Program</code>, <code>ID</code>, <code>Value Of QR Code</code>,
                <code>Contact #</code>, <code>Address</code>, <code>Guardian</code>.
                Matching students are updated by <strong>ID</strong>; names are split into first / middle / last.
            </p>
            <form action="{{ route('students.import') }}" method="POST" enctype="multipart/form-data" class="d-flex flex-wrap align-items-center gap-2 mb-0">
                @csrf
                <input type="file" name="file" class="form-control form-control-sm" accept=".xlsx,.xls,.csv" required>
                <button type="submit" class="patron-dir__btn patron-dir__btn--outline">Upload</button>
            </form>
        </div>
    </details>

    <div class="patron-dir__meta">
        <span class="patron-dir__meta-item"><strong>{{ number_format($students->total()) }}</strong> registered</span>
        @if(request()->hasAny(['search', 'program_id', 'year', 'per_page']))
            <span class="patron-dir__meta-item">
                <a href="{{ route('students.index') }}" class="text-decoration-none">Clear filters</a>
            </span>
        @endif
    </div>

    <div class="patron-dir__card">
        @if($students->total() > 0)
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Program</th>
                            <th>Year</th>
                            <th class="text-end" style="width: 3rem;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($students as $student)
                            @php
                                $programLabel = $programs->firstWhere('program_code', $student->course)?->program_name
                                    ?? $student->course;
                            @endphp
                            <tr>
                                <td>
                                    <div class="patron-dir__person">
                                        @if($student->profile_picture)
                                            <img src="{{ asset($student->profile_picture) }}" alt="" class="patron-dir__avatar">
                                        @else
                                            <span class="patron-dir__avatar patron-dir__avatar--empty">N/A</span>
                                        @endif
                                        <div>
                                            <div class="patron-dir__person-name">
                                                {{ $student->lastname }}, {{ $student->firstname }}
                                            </div>
                                            <div class="patron-dir__person-meta">
                                                @if($student->id_number)
                                                    ID {{ $student->id_number }}
                                                @endif
                                                @if($student->qrcode)
                                                    · <span class="patron-dir__code">{{ $student->qrcode }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="patron-dir__chip">{{ $programLabel ?: '—' }}</span></td>
                                <td><span class="patron-dir__chip patron-dir__chip--muted">{{ $student->year ?: '—' }}</span></td>
                                <td class="text-end">
                                    @include('patrons.partials.row_menu_student', ['student' => $student])
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @include('layouts.partials.pagination_bar', ['paginator' => $students])
        @else
            <div class="patron-dir__empty">
                <div class="patron-dir__empty-icon">🎓</div>
                <p class="mb-2">No students match your filters.</p>
                <a href="{{ route('students.create') }}" class="patron-dir__btn patron-dir__btn--primary">Register first student</a>
            </div>
        @endif
    </div>
</div>
@endsection
