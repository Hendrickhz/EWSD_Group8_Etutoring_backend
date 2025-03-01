<!DOCTYPE html>
<html>

<head>
    <title>Inactive Students Report</title>
</head>

<body>
    <h2>Hello {{ $tutor->name }},</h2>

    <p>The following students assigned to you have been inactive for over 28 days:</p>

    <ul>
        @foreach ($students as $student)
            <li>{{ $student->name }} (Last Active: {{ $student->last_active_at ?? 'Never' }})</li>
        @endforeach
    </ul>

    <p>Please follow up with them to ensure they are staying engaged.</p>

    <br>
    <br>
    <p>Thank you,</p>
    <p><strong>EduSpark eTutoring System</strong></p>
</body>

</html>
