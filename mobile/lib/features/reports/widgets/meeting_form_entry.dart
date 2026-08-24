import 'package:flutter/material.dart';

/// Mutable form-state for a single meeting row inside the report form.
class MeetingFormEntry {
  MeetingFormEntry({this.contactId, String? contactName, String? contactPhone})
      : nameController = TextEditingController(text: contactName ?? ''),
        phoneController = TextEditingController(text: contactPhone ?? '');

  final int? contactId;
  final TextEditingController nameController;
  final TextEditingController phoneController;
  final TextEditingController cnicController = TextEditingController();
  final TextEditingController addressController = TextEditingController();
  final TextEditingController titleController = TextEditingController();
  final TextEditingController discussionController = TextEditingController();
  final TextEditingController notesController = TextEditingController();

  DateTime meetingDateTime = DateTime.now();
  String category = 'general';
  bool followUpRequired = false;
  bool selectAllVolunteers = false;
  Set<int> participantIds = {};

  void dispose() {
    nameController.dispose();
    phoneController.dispose();
    cnicController.dispose();
    addressController.dispose();
    titleController.dispose();
    discussionController.dispose();
    notesController.dispose();
  }

  Map<String, dynamic> toJson() {
    return {
      if (contactId != null) 'contact_id': contactId,
      if (contactId == null) 'name': nameController.text,
      if (contactId == null) 'phone': phoneController.text,
      if (contactId == null && cnicController.text.isNotEmpty) 'cnic': cnicController.text,
      if (contactId == null && addressController.text.isNotEmpty) 'address': addressController.text,
      if (titleController.text.isNotEmpty) 'title': titleController.text,
      'meeting_datetime': meetingDateTime.toIso8601String(),
      'category': category,
      'discussion': discussionController.text,
      'follow_up_required': followUpRequired,
      'notes': notesController.text,
      'select_all_volunteers': selectAllVolunteers,
      'participant_ids': participantIds.toList(),
    };
  }
}
