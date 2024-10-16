<?php
// If this file is called directly, abort.
if (!defined('ABSPATH') || !defined('WPINC') || !defined('BOTBLOCKER')) {
    exit;
}
global $BBCS;
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title><!--error--></title>
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            color: #333;
        }
        .container {
            text-align: center;
            background: #fff;
            padding: 2em;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }
        .container h1 {
            color: #e74c3c;
        }
        .container p {
            margin: 1em 0;
        }
        .container a {
            color: #3498db;
            text-decoration: none;
        }
        .container a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>Sorry, your request has been denied.</h1>
    <p>Error Code: <?php echo htmlspecialchars($BBCS['error_headers'][$BBCS['header_error_code']], ENT_QUOTES, 'UTF-8'); ?></p>
    <p><!--ip_ban_msg--></p>
    <p><a href="https://cybersecure.top/" title="Block Bad Bot Traffic" target="_blank">Protected by CyberSecure BotBlocker</a></p>
</div>
</body>
</html>
