<!DOCTYPE html>
<html>
<head>
    <title>Document Upload Notification</title>
</head>
<body>
    <p>{{ $uploader->name }} has uploaded a new document.</p>
    <p><strong>Title:</strong> {{ $document->title }}</p>
    <p><strong>Description:</strong> {{ Str::limit($document->description, 50, '...') }}</p>
    <p>You can download the document here:</p>
    <a href="{{$document->full_url }}" target="_blank">
        View Document
    </a>

    <p>Thank you,</p>
    <p><strong>EduSpark eTutoring System</strong></p>
</body>
</html>
