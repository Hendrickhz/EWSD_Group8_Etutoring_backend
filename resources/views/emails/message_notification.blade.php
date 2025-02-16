<!DOCTYPE html>
<html>

<head>
    <title>New Message Notification</title>
</head>

<body>
    <p>Hello {{ $recipientName }},</p>

    <p>You have received a new message from <strong>{{ $senderName }}</strong>:</p>

    <blockquote>
        "{{ $messageContent }}"
    </blockquote>

    <p>Log in to your account to reply.</p>


    <p>Thank you,</p>
    <p><strong>eTutoring System</strong></p>
</body>

</html>
