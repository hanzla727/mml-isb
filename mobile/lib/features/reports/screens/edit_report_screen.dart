import 'package:flutter/material.dart';

import '../models/daily_report.dart';
import '../widgets/report_form_body.dart';

class EditReportScreen extends StatelessWidget {
  const EditReportScreen({super.key, required this.report});

  final DailyReport report;

  @override
  Widget build(BuildContext context) {
    return ReportFormBody(existing: report);
  }
}
