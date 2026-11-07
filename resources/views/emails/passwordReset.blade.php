<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Cairo', sans-serif;
            direction: ltr;
            text-align: left;
            background-color: #f9f9f9;
            padding: 20px;
        }
        .container {
            max-width: 500px;
            margin: auto;
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        }
        h1 {
            color: #333;
            text-align: center;
        }
        p {
            font-size: 18px;
            color: #555;
        }
        .code {
            font-size: 22px;
            font-weight: bold;
            color: #d9534f;
            text-align: center;
            padding: 10px;
            border: 2px dashed #d9534f;
            display: block;
            width: fit-content;
            margin: 10px auto;
        }
        .footer {
            font-size: 14px;
            color: #888;
            text-align: center;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Reset Password</h1>
        <p>Hello, <strong>{{ $name }}</strong>,</p>
        <p>Your password reset code is:</p>
        <span class="code">{{ $code }}</span>
        <p>Please note this code is valid for <strong>10 minutes only</strong>.</p>
        <p>If you did not request a password reset, please ignore this email.</p>
        <p class="footer">Thank you for using our service.</p>
    </div>
</body>
</html>