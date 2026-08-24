import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/providers.dart';
import '../models/meeting.dart';

class MeetingsNotifier extends AsyncNotifier<List<Meeting>> {
  @override
  Future<List<Meeting>> build() => _fetch();

  Future<List<Meeting>> _fetch() async {
    final dio = ref.read(apiClientProvider).dio;
    final response = await dio.get('/my-meetings');
    final data = response.data['data'] as List<dynamic>;
    return data.map((m) => Meeting.fromJson(m as Map<String, dynamic>)).toList();
  }

  Future<void> refresh() async {
    state = const AsyncLoading();
    state = await AsyncValue.guard(_fetch);
  }

  Future<void> markRead(int meetingId) async {
    final dio = ref.read(apiClientProvider).dio;
    await dio.post('/my-meetings/$meetingId/read');
    await refresh();
  }
}

final meetingsProvider = AsyncNotifierProvider<MeetingsNotifier, List<Meeting>>(MeetingsNotifier.new);

final unreadMeetingsCountProvider = Provider<int>((ref) {
  final meetings = ref.watch(meetingsProvider).value ?? [];
  return meetings.where((m) => !m.isRead).length;
});

final meetingDetailProvider = FutureProvider.autoDispose.family<Meeting, int>((ref, meetingId) async {
  final dio = ref.watch(apiClientProvider).dio;
  final response = await dio.get('/my-meetings/$meetingId');
  return Meeting.fromJson(response.data['data'] as Map<String, dynamic>);
});
