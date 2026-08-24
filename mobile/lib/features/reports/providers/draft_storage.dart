import 'dart:convert';

import 'package:shared_preferences/shared_preferences.dart';

/// Persists a single pending report submission locally when the device is
/// offline, so it can be retried automatically once connectivity returns.
class DraftStorage {
  static const _key = 'pending_report_submission';

  Future<void> save(Map<String, dynamic> payload) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString(_key, jsonEncode(payload));
  }

  Future<Map<String, dynamic>?> load() async {
    final prefs = await SharedPreferences.getInstance();
    final raw = prefs.getString(_key);
    if (raw == null) return null;
    return jsonDecode(raw) as Map<String, dynamic>;
  }

  Future<void> clear() async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove(_key);
  }

  Future<bool> hasPending() async {
    final prefs = await SharedPreferences.getInstance();
    return prefs.containsKey(_key);
  }
}
