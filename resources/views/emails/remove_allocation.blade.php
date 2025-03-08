<!DOCTYPE html>
<html>

<head>
    <title>Update on Tutor-Student Assignment</title>
</head>

<body>
    <p>Hello {{ $recipientName }},</p>

    @if ($role == 'student')
        <p>
            We would like to inform you that you are no longer assigned to <strong>{{ $otherUserName }}</strong> for
            tutoring. This change has been made by the administration.
        </p>

        <p>If you require any assistance or have questions about your new tutor assignment, please contact the
            administration for further guidance.</p>
    @else
        <p>
            We would like to inform you that <strong>{{ $otherUserName }}</strong> has been removed from your assigned
            students by the administration. This means you will no longer be responsible for their tutoring sessions and
            academic guidance.
        </p>
        <p>If you have any concerns regarding this change, please feel free to reach out to the administration.</p>
    @endif

    <br>
    <br>
    <p>Thank you,</p>
    <p><strong>EduSpark eTutoring System</strong></p>
</body>

</html>
