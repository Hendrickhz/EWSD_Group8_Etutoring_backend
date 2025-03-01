<!DOCTYPE html>
<html>

<head>
    <title>Meeting Notification</title>
</head>

<body>
    <h2>{{ $subject }}</h2>
    @if ($meeting->status === 'pending')
        <p>A meeting has been requested by {{ $meeting->student->name }}.</p>
    @elseif($meeting->status === 'confirmed')
        <p>A meeting has been created by {{ $meeting->tutor->name }}.</p>
    @else
        <p>The meeting has been cancelled.</p>
    @endif

    <p><strong>Title:</strong> {{ $meeting->title }}</p>
    <p><strong>Type:</strong> {{ $meeting->type }}</p>
    <p><strong>Date:</strong> {{ $meeting->date }}</p>
    <p><strong>Time:</strong> {{ $meeting->time }}</p>
    @if ($meeting->type === 'in-person')
        <p><strong>Location:</strong> {{ $meeting->location }}</p>
    @else
        <p><strong>Platform:</strong> {{ $meeting->platform }}</p>
        <p><strong>Meeting Link:</strong> <a href="{{ $meeting->meeting_link }}">{{ $meeting->meeting_link }}</a></p>
    @endif
    @if (!empty($meeting->notes))
        <p><strong>Notes:</strong> <?php echo(nl2br($meeting->notes)); ?></p>
    @endif

    <br>
    <br>
    <p>Thank you,</p>
    <p><strong>EduSpark eTutoring System</strong></p>
</body>

</html>
