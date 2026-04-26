<!DOCTYPE html>
<html>
<body>
    <p>Hello {{ $admin->name }},</p>
    <p><strong>{{ $invitedBy->name }}</strong> has invited you to manage the klixbd admin panel.</p>
    <p>Click the link below to set your password and activate your account:</p>
    <p><a href="{{ $url }}">{{ $url }}</a></p>
    <p>This link is valid until you use it. If you did not expect this invitation, you can ignore this email.</p>
</body>
</html>
