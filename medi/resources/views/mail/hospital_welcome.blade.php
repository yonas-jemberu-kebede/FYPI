<!DOCTYPE html>
<html>
<head>
    <title>Welcome to Our Platform</title>
</head>
<body>
    <h1>Welcome, {{ $hospital_name }}!</h1>
    <p>Your hospital account has been successfully created on our platform.</p>
    <p>Below are your login credentials:</p>
    <ul>
        <li><strong>Email:</strong> {{ $hospital_email }}</li>
        <li><strong>Password:</strong> {{ $password }}</li>
    </ul>
    <p>Please log in to your account and change your password for security.</p>
    {{-- <p><a href="{{ url('/login') }}">Log In Here</a></p> --}}
    <p>Thank you for joining our platform!</p>
</body>
</html>
