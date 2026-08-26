@php
    $nas = $nas ?? [];
    $ucs = $ucs ?? [];
    $departments = $departments ?? [];
    $teams = $teams ?? [];
    $users = $users ?? [];
@endphp

<hr>
<div class="mb-3">
    <label class="form-label">Assign To</label>
    <select name="scope" class="form-select audience-scope-select" onchange="toggleAudienceScope(this)">
        <option value="individual">Specific User(s)</option>
        <option value="teams">Team(s)</option>
        <option value="departments">Department(s)</option>
        <option value="uc">Entire UC</option>
        <option value="na">Entire NA</option>
        <option value="nas">Multiple NAs</option>
        <option value="all">All Volunteers</option>
    </select>
</div>

<div class="mb-3 audience-scope-target" data-scope="individual">
    <label class="form-label small">Search &amp; select users</label>
    <input type="text" class="form-control form-control-sm mb-2 audience-search" placeholder="Search users...">
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
    <label class="form-label small">Department(s) <span class="text-muted">— pick one, a few, or all</span></label>
    <select name="department_ids[]" class="form-select" multiple size="4" disabled>
        @foreach ($departments as $department)
            <option value="{{ $department->id }}">{{ $department->name }}</option>
        @endforeach
    </select>
</div>

<div class="mb-3 audience-scope-target" data-scope="teams" style="display:none;">
    <label class="form-label small">Team(s) <span class="text-muted">— pick one, a few, or all</span></label>
    <select name="team_ids[]" class="form-select" multiple size="4" disabled>
        @foreach ($teams as $team)
            <option value="{{ $team->id }}">{{ $team->name }}</option>
        @endforeach
    </select>
</div>

<div class="mb-3 audience-scope-target" data-scope="uc" style="display:none;">
    <label class="form-label small">UC</label>
    <select name="uc_id" class="form-select" disabled>
        @foreach ($ucs as $uc)
            <option value="{{ $uc->id }}">{{ $uc->name }}</option>
        @endforeach
    </select>
</div>

<div class="mb-3 audience-scope-target" data-scope="na" style="display:none;">
    <label class="form-label small">NA</label>
    <select name="na_id" class="form-select" disabled>
        @foreach ($nas as $na)
            <option value="{{ $na->id }}">{{ $na->name }}</option>
        @endforeach
    </select>
</div>

<div class="mb-3 audience-scope-target" data-scope="nas" style="display:none;">
    <label class="form-label small">NAs</label>
    <select name="na_ids[]" class="form-select" multiple size="4" disabled>
        @foreach ($nas as $na)
            <option value="{{ $na->id }}">{{ $na->name }}</option>
        @endforeach
    </select>
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
