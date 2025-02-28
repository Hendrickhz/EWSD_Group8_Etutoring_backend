<!DOCTYPE html>
<html>
<head>
    <title>Inactivity Warning</title>
</head>
<body>
    <p>Dear {{ $studentName }},</p>
    <p>We noticed that you have not been active for the last 28 days. Please engage in some activities to stay on track.</p>

    @if($tutorName)
        <p>Your tutor, {{ $tutorName }}, has also been notified.</p>
    @endif

    <p>Best regards,<br>Student Management System</p>
</body>
</html>
