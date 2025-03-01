<!DOCTYPE html>
<html>

<head>
    <title>Inactivity Warning</title>
</head>

<body>
    <p>Dear {{ $studentName }},</p>
    <p>We noticed that you have not been active for the last 28 days. Please engage in some activities to stay on track.
    </p>

    @if ($tutorName)
        <p>To ensure you are keeping up with your studies, please log in and engage with your tutor,
            {{ $tutorName }}.</p>
    @endif

    <p>If you need assistance, feel free to reach out.</p>

    <br>
    <br>
    <p>Thank you,</p>
    <p><strong>EduSpark eTutoring System</strong></p>

</body>

</html>
