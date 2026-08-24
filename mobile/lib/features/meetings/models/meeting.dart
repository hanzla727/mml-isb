class MeetingParticipant {
  MeetingParticipant({required this.id, required this.name, this.readAt});

  factory MeetingParticipant.fromJson(Map<String, dynamic> json) {
    return MeetingParticipant(
      id: json['id'] as int,
      name: json['name'] as String,
      readAt: json['read_at'] as String?,
    );
  }

  final int id;
  final String name;
  final String? readAt;

  bool get isRead => readAt != null;
}

class Meeting {
  Meeting({
    required this.id,
    this.title,
    this.meetingDatetime,
    required this.category,
    this.discussion,
    this.followUpRequired = false,
    this.notes,
    required this.contactName,
    required this.contactPhone,
    required this.creatorName,
    this.isRead = false,
    this.participants = const [],
  });

  factory Meeting.fromJson(Map<String, dynamic> json) {
    return Meeting(
      id: json['id'] as int,
      title: json['title'] as String?,
      meetingDatetime: json['meeting_datetime'] as String?,
      category: json['category'] as String? ?? 'general',
      discussion: json['discussion'] as String?,
      followUpRequired: json['follow_up_required'] as bool? ?? false,
      notes: json['notes'] as String?,
      contactName: json['contact']?['name'] as String? ?? '',
      contactPhone: json['contact']?['phone'] as String? ?? '',
      creatorName: json['creator']?['name'] as String? ?? '',
      isRead: json['is_read'] as bool? ?? false,
      participants: (json['participants'] as List<dynamic>? ?? [])
          .map((p) => MeetingParticipant.fromJson(p as Map<String, dynamic>))
          .toList(),
    );
  }

  final int id;
  final String? title;
  final String? meetingDatetime;
  final String category;
  final String? discussion;
  final bool followUpRequired;
  final String? notes;
  final String contactName;
  final String contactPhone;
  final String creatorName;
  final bool isRead;
  final List<MeetingParticipant> participants;

  String get displayTitle => (title != null && title!.isNotEmpty) ? title! : category;
}
