<!DOCTYPE html>
<html>
<head>
    <title>Blog Post Notification</title>
</head>
<body>
    <p>{{ $blog->author->name }} has posted a new blog post.</p>
    <br>
    <h2>{{ $blog->title }}</h2>
    <p>{{ Str::limit($blog->content, 50, '...') }}</p>
    <p>To read the full blog, please open the EduSpark eTutoring System.</p>

    <br>
    <br>
    <p>Thank you,</p>
    <p><strong>EduSpark eTutoring System</strong></p>
</body>
</html>
