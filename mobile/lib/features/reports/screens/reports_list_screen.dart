import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../providers/reports_provider.dart';
import 'create_report_screen.dart';
import 'edit_report_screen.dart';

class ReportsListScreen extends ConsumerWidget {
  const ReportsListScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final reportsAsync = ref.watch(reportsListProvider);
    final today = DateTime.now().toIso8601String().substring(0, 10);

    return Scaffold(
      appBar: AppBar(title: const Text('My Reports')),
      floatingActionButton: FloatingActionButton.extended(
        onPressed: () async {
          final created = await Navigator.of(context).push<bool>(
            MaterialPageRoute(builder: (_) => const CreateReportScreen()),
          );
          if (created == true) ref.read(reportsListProvider.notifier).refresh();
        },
        icon: const Icon(Icons.add),
        label: const Text('New Report'),
      ),
      body: RefreshIndicator(
        onRefresh: () => ref.read(reportsListProvider.notifier).refresh(),
        child: reportsAsync.when(
          data: (reports) {
            if (reports.isEmpty) {
              return ListView(
                children: const [
                  Padding(
                    padding: EdgeInsets.all(32),
                    child: Text('No reports yet. Tap "New Report" to submit your first daily report.', textAlign: TextAlign.center),
                  ),
                ],
              );
            }

            return ListView.builder(
              itemCount: reports.length,
              itemBuilder: (context, index) {
                final report = reports[index];
                final isToday = report.reportDate == today;

                return ListTile(
                  title: Text(report.reportDate),
                  subtitle: Text('${report.totalHours} hrs · ${report.meetings.length} meetings'),
                  trailing: Chip(
                    label: Text(report.status),
                    backgroundColor: report.status == 'draft' ? Colors.grey.shade200 : Colors.green.shade100,
                  ),
                  onTap: isToday
                      ? () async {
                          final updated = await Navigator.of(context).push<bool>(
                            MaterialPageRoute(builder: (_) => EditReportScreen(report: report)),
                          );
                          if (updated == true) ref.read(reportsListProvider.notifier).refresh();
                        }
                      : null,
                );
              },
            );
          },
          loading: () => const Center(child: CircularProgressIndicator()),
          error: (error, _) => Center(child: Text('Failed to load reports: $error')),
        ),
      ),
    );
  }
}
