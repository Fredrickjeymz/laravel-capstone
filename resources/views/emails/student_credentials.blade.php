<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Your Account Credentials</title>
</head>
<body>
    <h2>Welcome to the Sepnas Formative Assessment Generator System</h2>

    <p>Hi,</p>

    <p>Your student account has been created. Please find your login details below:</p>

    <ul>
        <li><strong>Username (email):</strong> {{ $username }}</li>
        <li><strong>Temporary Password:</strong> {{ $password }}</li>
    </ul>

    <p>For security, please log in and change your password immediately. This temporary password will only be valid until you reset it.</p>

    <p>Thanks,<br/>Admin Team</p>
</body>
</html>
