<!DOCTYPE html>
<html>
<head>
    <title>Meeting Notification</title>
</head>
<body>
    <h2>{{ $subject }}</h2>
    <p>A meeting has been {{ $meeting->status }}.</p>

    <p><strong>Title:</strong> {{ $meeting->title }}</p>
    <p><strong>Type:</strong> {{ $meeting->type }}</p>
    <p><strong>Date:</strong> {{ $meeting->date }}</p>
    <p><strong>Time:</strong> {{ $meeting->time }}</p>
    <p><strong>Location:</strong> {{ $meeting->location }}</p>
    <p><strong>Meeting Link:</strong> <a href="{{ $meeting->meeting_link }}">{{ $meeting->meeting_link }}</a></p>
    <p><strong>Notes:</strong> <?php nl2br($meeting->notes) ?></p>

    <p>Thank you,</p>
    <p><strong>eTutoring System</strong></p>
</body>
</html>
