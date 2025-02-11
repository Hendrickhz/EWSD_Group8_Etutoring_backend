<!DOCTYPE html>
<html>
<head>
    <title>Tutor Assignment Notification</title>
</head>
<body>
    <p>Hello {{ $recipientName }},</p>

    @if ($role == 'student')
        <p>You have been assigned a new personal tutor: <strong>{{ $otherUserName }}</strong>.</p>
        <p>Please log in to the eTutoring system to start communicating with your tutor.</p>
    @else
        <p>You have been assigned a new student: <strong>{{ $otherUserName }}</strong>.</p>
        <p>Please log in to the eTutoring system to manage your student list.</p>
    @endif

    <p>Thank you,</p>
    <p><strong>eTutoring System</strong></p>
</body>
</html>
