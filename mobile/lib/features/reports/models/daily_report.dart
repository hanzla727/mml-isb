class MeetingContact {
  MeetingContact({required this.id, required this.name, required this.phone});

  factory MeetingContact.fromJson(Map<String, dynamic> json) {
    return MeetingContact(
      id: json['id'] as int,
      name: json['name'] as String? ?? '',
      phone: json['phone'] as String? ?? '',
    );
  }

  final int id;
  final String name;
  final String phone;
}

class ReportMeeting {
  ReportMeeting({
    this.contactId,
    this.contact,
    this.title,
    this.meetingDatetime,
    required this.category,
    this.discussion,
    this.followUpRequired = false,
    this.notes,
    this.name,
    this.phone,
    this.selectAllVolunteers = false,
    this.participantIds = const [],
  });

  factory ReportMeeting.fromJson(Map<String, dynamic> json) {
    return ReportMeeting(
      contactId: json['contact']?['id'] as int?,
      contact: json['contact'] != null ? MeetingContact.fromJson(json['contact']) : null,
      title: json['title'] as String?,
      meetingDatetime: json['meeting_datetime'] as String?,
      category: json['category'] as String? ?? 'general',
      discussion: json['discussion'] as String?,
      followUpRequired: json['follow_up_required'] as bool? ?? false,
      notes: json['notes'] as String?,
    );
  }

  final int? contactId;
  final MeetingContact? contact;
  final String? title;
  final String? meetingDatetime;
  final String category;
  final String? discussion;
  final bool followUpRequired;
  final String? notes;

  // Only used when creating a brand-new contact (no contactId yet).
  final String? name;
  final String? phone;

  final bool selectAllVolunteers;
  final List<int> participantIds;

  Map<String, dynamic> toJson() {
    return {
      if (contactId != null) 'contact_id': contactId,
      if (contactId == null) 'name': name,
      if (contactId == null) 'phone': phone,
      if (title != null && title!.isNotEmpty) 'title': title,
      if (meetingDatetime != null) 'meeting_datetime': meetingDatetime,
      'category': category,
      'discussion': discussion,
      'follow_up_required': followUpRequired,
      'notes': notes,
      'select_all_volunteers': selectAllVolunteers,
      'participant_ids': participantIds,
    };
  }
}

class TaskProgress {
  TaskProgress({
    required this.targetId,
    required this.title,
    required this.metric,
    required this.type,
    required this.targetValue,
    required this.currentValue,
    this.isCompleted = false,
    this.notes,
  });

  factory TaskProgress.fromJson(Map<String, dynamic> json) {
    final progress = json['progress'] as Map<String, dynamic>? ?? {};

    return TaskProgress(
      targetId: json['id'] as int,
      title: json['title'] as String? ?? '',
      metric: json['metric'] as String? ?? 'custom',
      type: json['type'] as String? ?? 'daily',
      targetValue: double.tryParse('${json['target_value']}') ?? 0,
      currentValue: double.tryParse('${progress['current_value'] ?? json['current_value'] ?? 0}') ?? 0,
      isCompleted: json['is_completed'] as bool? ?? false,
      notes: json['notes'] as String?,
    );
  }

  final int targetId;
  final String title;
  final String metric;
  final String type;
  final double targetValue;
  double currentValue;
  bool isCompleted;
  String? notes;

  bool get isEditable => metric == 'custom';

  Map<String, dynamic> toJson() {
    return {
      'target_id': targetId,
      if (isEditable) 'current_value': currentValue,
      'is_completed': isCompleted,
      'notes': notes,
    };
  }
}

class DailyReport {
  DailyReport({
    this.id,
    required this.reportDate,
    required this.fieldStartTime,
    required this.fieldEndTime,
    this.totalHours = 0,
    this.status = 'submitted',
    this.summary,
    this.challenges,
    this.tomorrowPlan,
    this.meetings = const [],
  });

  factory DailyReport.fromJson(Map<String, dynamic> json) {
    return DailyReport(
      id: json['id'] as int?,
      reportDate: json['report_date'] as String,
      fieldStartTime: (json['field_start_time'] as String? ?? '').substring(0, 5),
      fieldEndTime: (json['field_end_time'] as String? ?? '').substring(0, 5),
      totalHours: double.tryParse('${json['total_hours']}') ?? 0,
      status: json['status'] as String? ?? 'submitted',
      summary: json['summary'] as String?,
      challenges: json['challenges'] as String?,
      tomorrowPlan: json['tomorrow_plan'] as String?,
      meetings: (json['meetings'] as List<dynamic>? ?? [])
          .map((m) => ReportMeeting.fromJson(m as Map<String, dynamic>))
          .toList(),
    );
  }

  final int? id;
  final String reportDate;
  final String fieldStartTime;
  final String fieldEndTime;
  final double totalHours;
  final String status;
  final String? summary;
  final String? challenges;
  final String? tomorrowPlan;
  final List<ReportMeeting> meetings;

  Map<String, dynamic> toJson({required String status, required List<TaskProgress> taskProgress}) {
    return {
      'report_date': reportDate,
      'field_start_time': fieldStartTime,
      'field_end_time': fieldEndTime,
      'status': status,
      'summary': summary,
      'challenges': challenges,
      'tomorrow_plan': tomorrowPlan,
      'meetings': meetings.map((m) => m.toJson()).toList(),
      'task_progress': taskProgress.map((t) => t.toJson()).toList(),
    };
  }
}
