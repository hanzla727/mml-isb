import 'dart:async';

import 'package:connectivity_plus/connectivity_plus.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import 'features/auth/providers/auth_provider.dart';
import 'features/meetings/providers/meetings_provider.dart';
import 'features/meetings/screens/my_meetings_screen.dart';
import 'features/reports/providers/reports_provider.dart';
import 'features/reports/screens/reports_list_screen.dart';

class HomeShell extends ConsumerStatefulWidget {
  const HomeShell({super.key});

  @override
  ConsumerState<HomeShell> createState() => _HomeShellState();
}

class _HomeShellState extends ConsumerState<HomeShell> {
  int _index = 0;
  StreamSubscription<List<ConnectivityResult>>? _connectivitySubscription;

  static const _screens = [ReportsListScreen(), MyMeetingsScreen()];

  @override
  void initState() {
    super.initState();

    // Flush any offline-saved report draft on start, and again whenever
    // connectivity is restored.
    ref.read(reportsListProvider.notifier).attemptFlushPendingDraft();
    _connectivitySubscription = Connectivity().onConnectivityChanged.listen((results) {
      if (!results.contains(ConnectivityResult.none)) {
        ref.read(reportsListProvider.notifier).attemptFlushPendingDraft();
      }
    });
  }

  @override
  void dispose() {
    _connectivitySubscription?.cancel();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final unreadCount = ref.watch(unreadMeetingsCountProvider);

    return Scaffold(
      body: IndexedStack(index: _index, children: _screens),
      bottomNavigationBar: NavigationBar(
        selectedIndex: _index,
        onDestinationSelected: (value) => setState(() => _index = value),
        destinations: [
          const NavigationDestination(icon: Icon(Icons.assignment_outlined), label: 'Reports'),
          NavigationDestination(
            icon: Badge(
              label: Text('$unreadCount'),
              isLabelVisible: unreadCount > 0,
              child: const Icon(Icons.groups_outlined),
            ),
            label: 'Meetings',
          ),
        ],
      ),
      drawer: Drawer(
        child: SafeArea(
          child: Column(
            children: [
              ListTile(
                title: Text(ref.watch(authProvider).user?.name ?? ''),
                subtitle: Text(ref.watch(authProvider).user?.email ?? ''),
              ),
              const Spacer(),
              ListTile(
                leading: const Icon(Icons.logout),
                title: const Text('Log Out'),
                onTap: () => ref.read(authProvider.notifier).logout(),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
