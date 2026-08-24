import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../providers/meetings_provider.dart';

class MeetingDetailScreen extends ConsumerStatefulWidget {
  const MeetingDetailScreen({super.key, required this.meetingId});

  final int meetingId;

  @override
  ConsumerState<MeetingDetailScreen> createState() => _MeetingDetailScreenState();
}

class _MeetingDetailScreenState extends ConsumerState<MeetingDetailScreen> {
  bool _markedRead = false;

  @override
  Widget build(BuildContext context) {
    final meetingAsync = ref.watch(meetingDetailProvider(widget.meetingId));

    // Mark as read once the detail has loaded, mirroring the web behavior.
    meetingAsync.whenData((meeting) {
      if (!_markedRead && !meeting.isRead) {
        _markedRead = true;
        Future.microtask(() => ref.read(meetingsProvider.notifier).markRead(widget.meetingId));
      }
    });

    return Scaffold(
      appBar: AppBar(title: const Text('Meeting Detail')),
      body: meetingAsync.when(
        data: (meeting) => ListView(
          padding: const EdgeInsets.all(16),
          children: [
            Text(meeting.displayTitle, style: Theme.of(context).textTheme.titleLarge),
            const SizedBox(height: 4),
            Text('Added by ${meeting.creatorName}', style: const TextStyle(color: Colors.grey)),
            const SizedBox(height: 16),
            Card(
              child: Padding(
                padding: const EdgeInsets.all(16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text('Person Met: ${meeting.contactName}'),
                    Text(meeting.contactPhone, style: const TextStyle(color: Colors.grey)),
                    if (meeting.followUpRequired) ...[
                      const SizedBox(height: 8),
                      Chip(label: const Text('Follow-up required'), backgroundColor: Colors.amber.shade100),
                    ],
                  ],
                ),
              ),
            ),
            const SizedBox(height: 12),
            Card(
              child: Padding(
                padding: const EdgeInsets.all(16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text('Discussion', style: TextStyle(fontWeight: FontWeight.bold)),
                    Text(meeting.discussion?.isNotEmpty == true ? meeting.discussion! : '—'),
                    const SizedBox(height: 12),
                    const Text('Notes', style: TextStyle(fontWeight: FontWeight.bold)),
                    Text(meeting.notes?.isNotEmpty == true ? meeting.notes! : '—'),
                  ],
                ),
              ),
            ),
            const SizedBox(height: 12),
            Card(
              child: Padding(
                padding: const EdgeInsets.all(16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text('Participants', style: TextStyle(fontWeight: FontWeight.bold)),
                    const SizedBox(height: 8),
                    ...meeting.participants.map(
                      (p) => ListTile(
                        contentPadding: EdgeInsets.zero,
                        title: Text(p.name),
                        trailing: Text(
                          p.isRead ? 'Read' : 'Unread',
                          style: TextStyle(color: p.isRead ? Colors.green : Colors.grey),
                        ),
                      ),
                    ),
                  ],
                ),
              ),
            ),
          ],
        ),
        loading: () => const Center(child: CircularProgressIndicator()),
        error: (error, _) => Center(child: Text('Failed to load meeting: $error')),
      ),
    );
  }
}
