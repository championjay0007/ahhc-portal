# Messaging System Documentation

## Overview
A complete message management system allowing admins to send messages to users with pre-designed templates.

## Features

### 1. Message Templates
- Create, edit, and delete reusable message templates
- Template types: general, alert, notification, compliance, care_review
- Support for dynamic variables ({{user_name}}, {{date}}, etc.)
- Template categories for organization

### 2. Send Messages
- Send targeted messages to selected users
- Send broadcast messages to all users of specific roles
- Use templates or compose custom messages
- Email notifications sent alongside in-app messages

### 3. Message Inbox
- Users can view received messages
- Mark messages as read/unread
- Delete messages
- Email notification when new messages arrive

## Admin Routes

### Template Management
- `GET /portal/admin/messages/templates` - View all templates
- `GET /portal/admin/messages/templates/create` - Create new template
- `POST /portal/admin/messages/templates` - Store template
- `GET /portal/admin/messages/templates/{template}/edit` - Edit template
- `PUT /portal/admin/messages/templates/{template}` - Update template
- `DELETE /portal/admin/messages/templates/{template}` - Delete template

### Send Messages
- `GET /portal/admin/messages/send` - Send to specific users
- `POST /portal/admin/messages/send` - Store message
- `GET /portal/admin/messages/broadcast` - Broadcast to role groups
- `POST /portal/admin/messages/broadcast` - Store broadcast
- `GET /portal/admin/messages/sent` - View sent messages

## User Routes

- `GET /portal/messages/inbox` - View inbox
- `GET /portal/messages/{message}` - View message
- `POST /portal/messages/{message}/mark-read` - Mark as read
- `POST /portal/messages/{message}/mark-unread` - Mark as unread
- `POST /portal/messages/{message}/delete` - Delete message

## Models

### Message
```php
- id (primary key)
- sender_id (foreign key to users)
- recipient_id (foreign key to users)
- subject (string)
- body (longText)
- template_id (foreign key, nullable)
- type (string: general, alert, etc.)
- read_at (timestamp, nullable)
- deleted_at (soft delete)
- timestamps
```

### MessageTemplate
```php
- id (primary key)
- name (string, unique)
- subject (string)
- body (longText)
- type (string)
- category (string, nullable)
- is_active (boolean)
- timestamps
```

## Service Usage

### Send Single Message
```php
use App\Services\MessageService;

MessageService::sendMessage(
    senderId: 1,
    recipientId: 5,
    subject: 'Hello',
    body: 'This is a test message'
);
```

### Send Using Template
```php
$template = MessageTemplate::find(1);
MessageService::sendMessageUsingTemplate(
    senderId: 1,
    recipientId: 5,
    template: $template,
    replacements: ['user_name' => 'John', 'date' => '2026-06-06']
);
```

### Send to Multiple Users
```php
MessageService::sendMessageToMultipleUsers(
    senderId: 1,
    recipientIds: [2, 3, 4, 5],
    subject: 'Group Message',
    body: 'Message content here'
);
```

### Broadcast with Template
```php
$template = MessageTemplate::find(1);
$recipients = User::where('role', 'worker')->get();
MessageService::sendTemplateToMultipleUsers(
    senderId: 1,
    recipientIds: $recipients->pluck('id')->toArray(),
    template: $template
);
```

### Get User Inbox
```php
$messages = MessageService::getUserInbox(userId: 5, perPage: 20);
```

### Get Unread Count
```php
$unreadCount = MessageService::getUnreadCount(userId: 5);
```

## Default Templates

Six default templates are included:
1. Welcome Message
2. Important Update
3. Compliance Document Due
4. Care Review Reminder
5. Account Security Notice
6. General Announcement

## Template Variables

Available variables for use in templates:
- `{{user_name}}` - Full name
- `{{first_name}}` - First name
- `{{last_name}}` - Last name
- `{{email}}` - Email address
- `{{date}}` - Current date
- `{{participant_name}}` - Participant name (if applicable)
- `{{worker_name}}` - Worker name (if applicable)
- `{{organization}}` - Organization name

## Email Notifications

When a message is sent:
1. Message is saved to database
2. Email is automatically sent if:
   - Recipient has an email address
   - User has not disabled email notifications
   - Email service is configured

Email configuration in `.env`:
- `MAIL_MAILER` - Mail driver (log, smtp, postmark, etc.)
- `MAIL_FROM_ADDRESS` - Sender email
- `MAIL_FROM_NAME` - Sender name

## Integration Points

### Sending Messages from Controllers
```php
// In any controller
NotificationService::notify([
    'user_id' => $user->id,
    'type' => 'info',
    'title' => 'Message Title',
    'data' => [
        'message' => 'Message content',
        'url' => route('some.route')
    ]
]);

// Or use MessageService directly
MessageService::sendMessage($senderId, $recipientId, $subject, $body);
```

### Accessing User Messages
```php
// Get received messages
$user->receivedMessages()->get();

// Get sent messages
$user->sentMessages()->get();

// Check notification preferences
$user->notificationPreferences()->channel_email;
```

## Permissions

- Admins: Full access to send messages, manage templates
- Users: Can view received messages, mark as read/unread
- All authenticated users: Can view their inbox

## Notes

- Messages support soft deletes (can be recovered if needed)
- Template body supports HTML formatting
- Database indexes optimize recipient and sender lookups
- Service automatically respects user notification preferences
