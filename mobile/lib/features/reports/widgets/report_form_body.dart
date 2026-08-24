import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:intl/intl.dart';

import '../models/daily_report.dart';
import '../providers/reports_provider.dart';
import 'meeting_form_entry.dart';

const _categories = {
  'general': 'General Meeting',
  'fund_discussion': 'Fund Discussion',
  'family_visit': 'Family Visit',
  'follow_up': 'Follow-up',
  'project_discussion': 'Project Discussion',
  'other': 'Other',
};

class ReportFormBody extends ConsumerStatefulWidget {
  const ReportFormBody({super.key, this.existing});

  final DailyReport? existing;

  @override
  ConsumerState<ReportFormBody> createState() => _ReportFormBodyState();
}

class _ReportFormBodyState extends ConsumerState<ReportFormBody> {
  late TimeOfDay _startTime;
  late TimeOfDay _endTime;
  late final TextEditingController _summaryController;
  late final TextEditingController _challengesController;
  late final TextEditingController _tomorrowPlanController;
  final List<MeetingFormEntry> _meetings = [];
  List<TaskProgress> _taskProgress = [];
  bool _submitting = false;

  @override
  void initState() {
    super.initState();
    final existing = widget.existing;
    _startTime = existing != null ? _parseTime(existing.fieldStartTime) : const TimeOfDay(hour: 9, minute: 0);
    _endTime = existing != null ? _parseTime(existing.fieldEndTime) : const TimeOfDay(hour: 17, minute: 0);
    _summaryController = TextEditingController(text: existing?.summary ?? '');
    _challengesController = TextEditingController(text: existing?.challenges ?? '');
    _tomorrowPlanController = TextEditingController(text: existing?.tomorrowPlan ?? '');

    for (final meeting in existing?.meetings ?? <ReportMeeting>[]) {
      final entry = MeetingFormEntry(
        contactId: meeting.contactId,
        contactName: meeting.contact?.name,
        contactPhone: meeting.contact?.phone,
      );
      entry.titleController.text = meeting.title ?? '';
      entry.discussionController.text = meeting.discussion ?? '';
      entry.notesController.text = meeting.notes ?? '';
      entry.category = meeting.category;
      entry.followUpRequired = meeting.followUpRequired;
      _meetings.add(entry);
    }
  }

  TimeOfDay _parseTime(String value) {
    final parts = value.split(':');
    return TimeOfDay(hour: int.parse(parts[0]), minute: int.parse(parts[1]));
  }

  String _formatTime(TimeOfDay time) => '${time.hour.toString().padLeft(2, '0')}:${time.minute.toString().padLeft(2, '0')}';

  double get _totalHours {
    final minutes = (_endTime.hour * 60 + _endTime.minute) - (_startTime.hour * 60 + _startTime.minute);
    return minutes > 0 ? minutes / 60 : 0;
  }

  @override
  void dispose() {
    _summaryController.dispose();
    _challengesController.dispose();
    _tomorrowPlanController.dispose();
    for (final m in _meetings) {
      m.dispose();
    }
    super.dispose();
  }

  Future<void> _pickTime(bool isStart) async {
    final picked = await showTimePicker(context: context, initialTime: isStart ? _startTime : _endTime);
    if (picked == null) return;
    setState(() => isStart ? _startTime = picked : _endTime = picked);
  }

  void _addMeeting() {
    setState(() => _meetings.add(MeetingFormEntry()));
  }

  void _removeMeeting(int index) {
    setState(() {
      _meetings[index].dispose();
      _meetings.removeAt(index);
    });
  }

  Future<void> _submit(String status) async {
    setState(() => _submitting = true);

    final payload = {
      'report_date': widget.existing?.reportDate ?? DateFormat('yyyy-MM-dd').format(DateTime.now()),
      'field_start_time': _formatTime(_startTime),
      'field_end_time': _formatTime(_endTime),
      'status': status,
      'summary': _summaryController.text,
      'challenges': _challengesController.text,
      'tomorrow_plan': _tomorrowPlanController.text,
      'meetings': _meetings.map((m) => m.toJson()).toList(),
      'task_progress': _taskProgress.map((t) => t.toJson()).toList(),
    };

    final result = await ref.read(reportsListProvider.notifier).submit(
          reportId: widget.existing?.id,
          payload: payload,
        );

    if (!mounted) return;
    setState(() => _submitting = false);

    if (result.success) {
      Navigator.of(context).pop(true);
    } else if (result.savedOffline) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('No connection — report saved on this device and will sync automatically.')),
      );
      Navigator.of(context).pop(false);
    } else {
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(result.error ?? 'Something went wrong.')));
    }
  }

  @override
  Widget build(BuildContext context) {
    final targetsAsync = ref.watch(assignedTargetsProvider);
    final volunteersAsync = ref.watch(volunteerDirectoryProvider);

    return Scaffold(
      appBar: AppBar(title: Text(widget.existing != null ? 'Edit Report' : 'New Daily Report')),
      body: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          _sectionCard(
            title: 'Working Hours',
            children: [
              Row(
                children: [
                  Expanded(child: _timeField('Start', _startTime, () => _pickTime(true))),
                  const SizedBox(width: 12),
                  Expanded(child: _timeField('End', _endTime, () => _pickTime(false))),
                ],
              ),
              const SizedBox(height: 12),
              Text('Total: ${_totalHours.toStringAsFixed(2)} hours', style: Theme.of(context).textTheme.titleMedium),
            ],
          ),
          targetsAsync.when(
            data: (targets) {
              if (_taskProgress.isEmpty && targets.isNotEmpty) {
                _taskProgress = targets;
              }
              if (targets.isEmpty) return const SizedBox.shrink();
              return _sectionCard(
                title: 'Assigned Tasks',
                children: _taskProgress.map(_taskTile).toList(),
              );
            },
            loading: () => const Padding(padding: EdgeInsets.all(16), child: Center(child: CircularProgressIndicator())),
            error: (e, _) => const SizedBox.shrink(),
          ),
          _sectionCard(
            title: 'Meetings',
            trailing: IconButton(icon: const Icon(Icons.add_circle), onPressed: _addMeeting),
            children: _meetings.isEmpty
                ? [const Text('No meetings added yet.', style: TextStyle(color: Colors.grey))]
                : List.generate(_meetings.length, (i) => _meetingCard(i, volunteersAsync)),
          ),
          _sectionCard(
            title: 'Daily Summary',
            children: [
              _textArea('What did you achieve today?', _summaryController),
              const SizedBox(height: 12),
              _textArea('What problems did you face?', _challengesController),
              const SizedBox(height: 12),
              _textArea('What will you do tomorrow?', _tomorrowPlanController),
            ],
          ),
          const SizedBox(height: 90),
        ],
      ),
      bottomNavigationBar: BottomAppBar(
        child: Row(
          mainAxisAlignment: MainAxisAlignment.end,
          children: [
            OutlinedButton(
              onPressed: _submitting ? null : () => _submit('draft'),
              child: const Text('Save Draft'),
            ),
            const SizedBox(width: 12),
            FilledButton(
              onPressed: _submitting ? null : () => _submit('submitted'),
              child: _submitting
                  ? const SizedBox(height: 18, width: 18, child: CircularProgressIndicator(strokeWidth: 2))
                  : const Text('Submit Report'),
            ),
          ],
        ),
      ),
    );
  }

  Widget _sectionCard({required String title, required List<Widget> children, Widget? trailing}) {
    return Card(
      margin: const EdgeInsets.only(bottom: 16),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Text(title, style: Theme.of(context).textTheme.titleMedium),
                ?trailing,
              ],
            ),
            const SizedBox(height: 12),
            ...children,
          ],
        ),
      ),
    );
  }

  Widget _timeField(String label, TimeOfDay time, VoidCallback onTap) {
    return InkWell(
      onTap: onTap,
      child: InputDecorator(
        decoration: InputDecoration(labelText: label, border: const OutlineInputBorder()),
        child: Text(_formatTime(time)),
      ),
    );
  }

  Widget _textArea(String label, TextEditingController controller) {
    return TextField(
      controller: controller,
      maxLines: 3,
      decoration: InputDecoration(labelText: label, border: const OutlineInputBorder()),
    );
  }

  Widget _taskTile(TaskProgress task) {
    final percentage = task.targetValue > 0 ? (task.currentValue / task.targetValue * 100).clamp(0, 100) : 0.0;

    return Padding(
      padding: const EdgeInsets.only(bottom: 12),
      child: Container(
        padding: const EdgeInsets.all(12),
        decoration: BoxDecoration(border: Border.all(color: Colors.grey.shade300), borderRadius: BorderRadius.circular(8)),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Expanded(child: Text(task.title, style: const TextStyle(fontWeight: FontWeight.w600))),
                Chip(label: Text(task.metric), visualDensity: VisualDensity.compact),
              ],
            ),
            const SizedBox(height: 8),
            LinearProgressIndicator(value: percentage / 100),
            const SizedBox(height: 8),
            if (task.isEditable)
              TextFormField(
                initialValue: task.currentValue.toString(),
                keyboardType: TextInputType.number,
                decoration: const InputDecoration(labelText: 'Quantity achieved', isDense: true),
                onChanged: (value) => task.currentValue = double.tryParse(value) ?? task.currentValue,
              )
            else
              Text('${task.currentValue} / ${task.targetValue} ${task.metric} — auto-tracked', style: const TextStyle(color: Colors.grey)),
            CheckboxListTile(
              value: task.isCompleted,
              onChanged: (value) => setState(() => task.isCompleted = value ?? false),
              title: const Text('Mark as completed'),
              controlAffinity: ListTileControlAffinity.leading,
              contentPadding: EdgeInsets.zero,
              dense: true,
            ),
            TextFormField(
              initialValue: task.notes,
              decoration: const InputDecoration(labelText: 'Notes', isDense: true),
              onChanged: (value) => task.notes = value,
            ),
          ],
        ),
      ),
    );
  }

  Widget _meetingCard(int index, AsyncValue volunteersAsync) {
    final entry = _meetings[index];

    return Container(
      margin: const EdgeInsets.only(bottom: 16),
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(border: Border.all(color: Colors.grey.shade300), borderRadius: BorderRadius.circular(8)),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text('Meeting ${index + 1}', style: const TextStyle(fontWeight: FontWeight.w600)),
              IconButton(icon: const Icon(Icons.delete_outline, color: Colors.red), onPressed: () => _removeMeeting(index)),
            ],
          ),
          if (entry.contactId != null)
            Padding(
              padding: const EdgeInsets.only(bottom: 8),
              child: Text('Contact: ${entry.nameController.text} (${entry.phoneController.text})'),
            )
          else ...[
            TextField(controller: entry.nameController, decoration: const InputDecoration(labelText: 'Person Name', isDense: true)),
            const SizedBox(height: 8),
            TextField(controller: entry.phoneController, decoration: const InputDecoration(labelText: 'Phone Number', isDense: true)),
            const SizedBox(height: 8),
          ],
          TextField(controller: entry.titleController, decoration: const InputDecoration(labelText: 'Meeting Title (optional)', isDense: true)),
          const SizedBox(height: 8),
          DropdownButtonFormField<String>(
            initialValue: entry.category,
            decoration: const InputDecoration(labelText: 'Category', isDense: true),
            items: _categories.entries.map((e) => DropdownMenuItem(value: e.key, child: Text(e.value))).toList(),
            onChanged: (value) => setState(() => entry.category = value ?? 'general'),
          ),
          const SizedBox(height: 8),
          TextField(controller: entry.discussionController, decoration: const InputDecoration(labelText: 'Brief Discussion', isDense: true), maxLines: 2),
          const SizedBox(height: 8),
          CheckboxListTile(
            value: entry.followUpRequired,
            onChanged: (value) => setState(() => entry.followUpRequired = value ?? false),
            title: const Text('Follow-up required'),
            controlAffinity: ListTileControlAffinity.leading,
            contentPadding: EdgeInsets.zero,
            dense: true,
          ),
          TextField(controller: entry.notesController, decoration: const InputDecoration(labelText: 'Notes', isDense: true), maxLines: 2),
          const Divider(height: 24),
          CheckboxListTile(
            value: entry.selectAllVolunteers,
            onChanged: (value) => setState(() => entry.selectAllVolunteers = value ?? false),
            title: const Text('Select all volunteers'),
            controlAffinity: ListTileControlAffinity.leading,
            contentPadding: EdgeInsets.zero,
            dense: true,
          ),
          if (!entry.selectAllVolunteers)
            volunteersAsync.when(
              data: (volunteers) => Wrap(
                spacing: 8,
                children: volunteers.map<Widget>((v) {
                  final selected = entry.participantIds.contains(v.id);
                  return FilterChip(
                    label: Text(v.name),
                    selected: selected,
                    onSelected: (value) => setState(() {
                      if (value) {
                        entry.participantIds.add(v.id);
                      } else {
                        entry.participantIds.remove(v.id);
                      }
                    }),
                  );
                }).toList(),
              ),
              loading: () => const Center(child: CircularProgressIndicator()),
              error: (e, _) => const Text('Could not load volunteers.', style: TextStyle(color: Colors.red)),
            ),
        ],
      ),
    );
  }
}
