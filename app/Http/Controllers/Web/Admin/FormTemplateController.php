<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\FormTemplate;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FormTemplateController extends Controller
{
    public function index()
    {
        return view('admin.forms.index', [
            'formTemplates' => FormTemplate::withCount(['fields', 'submissions'])->orderBy('name')->get(),
        ]);
    }

    public function create()
    {
        return view('admin.forms.form', ['formTemplate' => new FormTemplate(), 'fields' => []]);
    }

    public function edit(FormTemplate $formTemplate)
    {
        return view('admin.forms.form', [
            'formTemplate' => $formTemplate,
            'fields' => $formTemplate->fields,
        ]);
    }

    public function show(FormTemplate $formTemplate)
    {
        return view('admin.forms.show', [
            'formTemplate' => $formTemplate->load('fields'),
            'submissions' => $formTemplate->submissions()->with(['user', 'submittable', 'values.field'])->orderByDesc('created_at')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validated($request);

        $formTemplate = FormTemplate::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'created_by' => $request->user()->id,
        ]);

        $this->syncFields($formTemplate, $validated['fields']);

        return redirect()->route('admin.forms.index')->with('status', 'Form template created.');
    }

    public function update(Request $request, FormTemplate $formTemplate)
    {
        $validated = $this->validated($request);

        $formTemplate->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
        ]);

        $formTemplate->fields()->delete();
        $this->syncFields($formTemplate, $validated['fields']);

        return redirect()->route('admin.forms.index')->with('status', 'Form template updated.');
    }

    public function destroy(FormTemplate $formTemplate)
    {
        $formTemplate->delete();

        return redirect()->route('admin.forms.index')->with('status', 'Form template deleted.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'fields' => ['required', 'array', 'min:1'],
            'fields.*.label' => ['required', 'string', 'max:255'],
            'fields.*.field_type' => ['required', Rule::in(['text', 'number', 'date', 'time', 'dropdown', 'checkbox', 'radio', 'textarea', 'file', 'image'])],
            'fields.*.options' => ['nullable', 'string'],
            'fields.*.is_required' => ['nullable', 'boolean'],
        ]);
    }

    private function syncFields(FormTemplate $formTemplate, array $fields): void
    {
        foreach ($fields as $index => $field) {
            $choices = collect(explode(',', $field['options'] ?? ''))
                ->map(fn ($choice) => trim($choice))
                ->filter()
                ->values();

            $formTemplate->fields()->create([
                'label' => $field['label'],
                'field_type' => $field['field_type'],
                'options' => $choices->isNotEmpty() ? ['choices' => $choices->all()] : null,
                'is_required' => ! empty($field['is_required']),
                'sort_order' => $index,
            ]);
        }
    }
}
