import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../providers/meetings_provider.dart';
import 'meeting_detail_screen.dart';

class MyMeetingsScreen extends ConsumerWidget {
  const MyMeetingsScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final meetingsAsync = ref.watch(meetingsProvider);

    return Scaffold(
      appBar: AppBar(title: const Text('My Meetings')),
      body: RefreshIndicator(
        onRefresh: () => ref.read(meetingsProvider.notifier).refresh(),
        child: meetingsAsync.when(
          data: (meetings) {
            if (meetings.isEmpty) {
              return ListView(
                children: const [
                  Padding(
                    padding: EdgeInsets.all(32),
                    child: Text("You haven't been added to any meetings yet.", textAlign: TextAlign.center),
                  ),
                ],
              );
            }

            return ListView.separated(
              itemCount: meetings.length,
              separatorBuilder: (_, _) => const Divider(height: 1),
              itemBuilder: (context, index) {
                final meeting = meetings[index];

                return ListTile(
                  leading: meeting.isRead
                      ? const Icon(Icons.mail_outline, color: Colors.grey)
                      : const Icon(Icons.mark_email_unread, color: Colors.indigo),
                  title: Text(
                    meeting.displayTitle,
                    style: TextStyle(fontWeight: meeting.isRead ? FontWeight.normal : FontWeight.bold),
                  ),
                  subtitle: Text('Added by ${meeting.creatorName}'),
                  onTap: () {
                    Navigator.of(context).push(
                      MaterialPageRoute(builder: (_) => MeetingDetailScreen(meetingId: meeting.id)),
                    );
                  },
                );
              },
            );
          },
          loading: () => const Center(child: CircularProgressIndicator()),
          error: (error, _) => Center(child: Text('Failed to load meetings: $error')),
        ),
      ),
    );
  }
}
