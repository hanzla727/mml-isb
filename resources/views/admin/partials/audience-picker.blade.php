@php
    $nas = $nas ?? [];
    $ucs = $ucs ?? [];
    $departments = $departments ?? [];
    $teams = $teams ?? [];
    $users = $users ?? [];
@endphp

<hr>
<div class="mb-3">
    <label class="form-label">{{ __('Assign To') }}</label>
    <select name="scope" class="form-select audience-scope-select" onchange="toggleAudienceScope(this)">
        <option value="individual">{{ __('Specific User(s)') }}</option>
        <option value="teams">{{ __('Team(s)') }}</option>
        <option value="departments">{{ __('Department(s)') }}</option>
        <option value="uc">{{ __('Entire UC') }}</option>
        <option value="na">{{ __('Entire NA') }}</option>
        <option value="nas">{{ __('Multiple NAs') }}</option>
        <option value="all">{{ __('All Volunteers') }}</option>
    </select>
</div>

<div class="mb-3 audience-scope-target" data-scope="individual">
    <label class="form-label small">{{ __('Search & select users') }}</label>
    <input type="text" class="form-control form-control-sm mb-2 audience-search" placeholder="{{ __('Search users...') }}">
    <div class="border rounded p-2 audience-user-list" style="max-height: 180px; overflow-y: auto;">
        @foreach ($users as $user)
            <div class="form-check audience-user-option">
                <input type="checkbox" name="user_ids[]" value="{{ $user->id }}" class="form-check-input">
                <label class="form-check-label small">{{ $user->name }}</label>
            </div>
        @endforeach
    </div>
</div>

<div class="mb-3 audience-scope-target" data-scope="departments" style="display:none;">
    <label class="form-label small">{{ __('Department(s)') }} <span class="text-muted">— {{ __('tick one, a few, or all') }}</span></label>
    <div class="border rounded p-2" style="max-height: 180px; overflow-y: auto;">
        @foreach ($departments as $department)
            <div class="form-check">
                <input type="checkbox" name="department_ids[]" value="{{ $department->id }}" class="form-check-input" disabled>
                <label class="form-check-label small">{{ $department->name }}</label>
            </div>
        @endforeach
    </div>
</div>

<div class="mb-3 audience-scope-target" data-scope="teams" style="display:none;">
    <label class="form-label small">{{ __('Team(s)') }} <span class="text-muted">— {{ __('tick one, a few, or all') }}</span></label>
    <div class="border rounded p-2" style="max-height: 180px; overflow-y: auto;">
        @foreach ($teams as $team)
            <div class="form-check">
                <input type="checkbox" name="team_ids[]" value="{{ $team->id }}" class="form-check-input" disabled>
                <label class="form-check-label small">{{ $team->name }}</label>
            </div>
        @endforeach
    </div>
</div>

<div class="mb-3 audience-scope-target" data-scope="uc" style="display:none;">
    <label class="form-label small">{{ __('UC') }}</label>
    <select name="uc_id" class="form-select" disabled>
        @foreach ($ucs as $uc)
            <option value="{{ $uc->id }}">{{ $uc->name }}</option>
        @endforeach
    </select>
</div>

<div class="mb-3 audience-scope-target" data-scope="na" style="display:none;">
    <label class="form-label small">{{ __('NA') }}</label>
    <select name="na_id" class="form-select" disabled>
        @foreach ($nas as $na)
            <option value="{{ $na->id }}">{{ $na->name }}</option>
        @endforeach
    </select>
</div>

<div class="mb-3 audience-scope-target" data-scope="nas" style="display:none;">
    <label class="form-label small">{{ __('NAs') }} <span class="text-muted">— {{ __('tick one, a few, or all') }}</span></label>
    <div class="border rounded p-2" style="max-height: 180px; overflow-y: auto;">
        @foreach ($nas as $na)
            <div class="form-check">
                <input type="checkbox" name="na_ids[]" value="{{ $na->id }}" class="form-check-input" disabled>
                <label class="form-check-label small">{{ $na->name }}</label>
            </div>
        @endforeach
    </div>
</div>

<script>
    function toggleAudienceScope(select) {
        const container = select.closest('form');
        container.querySelectorAll('.audience-scope-target').forEach((el) => {
            const active = el.dataset.scope === select.value;
            el.style.display = active ? '' : 'none';
            el.querySelectorAll('select, input[type="checkbox"]').forEach((field) => {
                field.disabled = !active;
            });
        });
    }

    document.addEventListener('input', function (e) {
        if (e.target.classList.contains('audience-search')) {
            const search = e.target.value.toLowerCase();
            e.target.closest('.audience-scope-target').querySelectorAll('.audience-user-option').forEach((opt) => {
                opt.style.display = opt.textContent.toLowerCase().includes(search) ? '' : 'none';
            });
        }
    });
</script>
