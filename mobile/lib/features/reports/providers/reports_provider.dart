import 'package:connectivity_plus/connectivity_plus.dart';
import 'package:dio/dio.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/providers.dart';
import '../../auth/models/app_user.dart';
import '../models/daily_report.dart';
import 'draft_storage.dart';

final draftStorageProvider = Provider<DraftStorage>((ref) => DraftStorage());

class SubmitResult {
  SubmitResult({required this.success, this.savedOffline = false, this.error});

  final bool success;
  final bool savedOffline;
  final String? error;
}

class ReportsListNotifier extends AsyncNotifier<List<DailyReport>> {
  @override
  Future<List<DailyReport>> build() => _fetch();

  Future<List<DailyReport>> _fetch() async {
    final dio = ref.read(apiClientProvider).dio;
    final response = await dio.get('/my-reports');
    final data = (response.data['data'] as List<dynamic>);
    return data.map((r) => DailyReport.fromJson(r as Map<String, dynamic>)).toList();
  }

  Future<void> refresh() async {
    state = const AsyncLoading();
    state = await AsyncValue.guard(_fetch);
  }

  /// Submits a report. If the device is offline, the payload is stashed
  /// locally via [DraftStorage] and flushed automatically once connectivity
  /// returns (see [attemptFlushPendingDraft]).
  Future<SubmitResult> submit({
    int? reportId,
    required Map<String, dynamic> payload,
  }) async {
    final dio = ref.read(apiClientProvider).dio;

    try {
      if (reportId != null) {
        await dio.put('/reports/$reportId', data: payload);
      } else {
        await dio.post('/reports', data: payload);
      }
      await refresh();
      return SubmitResult(success: true);
    } on DioException catch (e) {
      final isNetworkError = e.type == DioExceptionType.connectionError ||
          e.type == DioExceptionType.connectionTimeout ||
          e.type == DioExceptionType.receiveTimeout;

      if (isNetworkError && reportId == null) {
        await ref.read(draftStorageProvider).save(payload);
        return SubmitResult(success: false, savedOffline: true);
      }

      final message = e.response?.data is Map
          ? (e.response?.data['message'] as String? ?? 'Failed to submit report.')
          : 'Unable to reach the server.';
      return SubmitResult(success: false, error: message);
    }
  }

  /// Called on app start / connectivity restore to retry a locally-saved draft.
  Future<void> attemptFlushPendingDraft() async {
    final draftStorage = ref.read(draftStorageProvider);
    final pending = await draftStorage.load();
    if (pending == null) return;

    final connectivity = await Connectivity().checkConnectivity();
    if (connectivity.contains(ConnectivityResult.none)) return;

    final dio = ref.read(apiClientProvider).dio;
    try {
      await dio.post('/reports', data: pending);
      await draftStorage.clear();
      await refresh();
    } catch (_) {
      // Still offline or server rejected it — leave the draft in place for the next attempt.
    }
  }
}

final reportsListProvider = AsyncNotifierProvider<ReportsListNotifier, List<DailyReport>>(
  ReportsListNotifier.new,
);

final assignedTargetsProvider = FutureProvider.autoDispose<List<TaskProgress>>((ref) async {
  final dio = ref.watch(apiClientProvider).dio;
  final response = await dio.get('/targets');
  final data = response.data['data'] as List<dynamic>;
  return data.map((t) => TaskProgress.fromJson(t as Map<String, dynamic>)).toList();
});

final volunteerDirectoryProvider = FutureProvider.autoDispose<List<AppUser>>((ref) async {
  final dio = ref.watch(apiClientProvider).dio;
  final response = await dio.get('/volunteers');
  final data = response.data['data'] as List<dynamic>;
  return data
      .map((u) => AppUser(id: u['id'] as int, name: u['name'] as String, email: '', role: 'user'))
      .toList();
});
