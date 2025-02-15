<!DOCTYPE html>
<html>
<head>
    <title>Blog Post Notification</title>
</head>
<body>
    <h2>{{ $blog->title }}</h2>
    <p><strong>Author:</strong> {{ $blog->author->name }}</p>

    <p><strong>Excerpt:</strong> {{ Str::limit($blog->content, 50, '...') }}</p>
    <p><a href="{{ url('/blogs/' . $blog->id) }}">Read more</a></p>

    <p>Thank you,</p>
    <p><strong>eTutoring System</strong></p>
</body>
</html>
