import 'package:dio/dio.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/providers.dart';
import '../models/app_user.dart';

class AuthState {
  const AuthState({this.user, this.isLoading = false, this.error});

  final AppUser? user;
  final bool isLoading;
  final String? error;

  bool get isAuthenticated => user != null;

  AuthState copyWith({AppUser? user, bool? isLoading, String? error}) {
    return AuthState(
      user: user ?? this.user,
      isLoading: isLoading ?? this.isLoading,
      error: error,
    );
  }
}

class AuthNotifier extends Notifier<AuthState> {
  @override
  AuthState build() {
    Future.microtask(_tryAutoLogin);
    return const AuthState(isLoading: true);
  }

  Future<void> _tryAutoLogin() async {
    final tokenStorage = ref.read(tokenStorageProvider);
    final token = await tokenStorage.read();

    if (token == null) {
      state = const AuthState();
      return;
    }

    try {
      final dio = ref.read(apiClientProvider).dio;
      final response = await dio.get('/auth/me');
      state = AuthState(user: AppUser.fromJson(response.data['data']));
    } catch (_) {
      await tokenStorage.clear();
      state = const AuthState();
    }
  }

  Future<bool> login(String email, String password) async {
    state = state.copyWith(isLoading: true, error: null);

    try {
      final dio = ref.read(apiClientProvider).dio;
      final response = await dio.post('/auth/login', data: {
        'email': email,
        'password': password,
        'device_name': 'flutter-app',
      });

      await ref.read(tokenStorageProvider).save(response.data['token'] as String);
      state = AuthState(user: AppUser.fromJson(response.data['user']));
      return true;
    } on DioException catch (e) {
      final message = e.response?.data is Map
          ? (e.response?.data['message'] as String? ?? 'Login failed.')
          : 'Unable to reach the server.';
      state = state.copyWith(isLoading: false, error: message);
      return false;
    }
  }

  Future<void> logout() async {
    try {
      await ref.read(apiClientProvider).dio.post('/auth/logout');
    } catch (_) {
      // Ignore network errors on logout — we clear the local token regardless.
    }
    await ref.read(tokenStorageProvider).clear();
    state = const AuthState();
  }
}

final authProvider = NotifierProvider<AuthNotifier, AuthState>(AuthNotifier.new);
