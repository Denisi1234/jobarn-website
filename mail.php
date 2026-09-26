<?php
    // Jobarn Contact Form Handler - sends to both Jobarn emails
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $name = strip_tags(trim($_POST["name"]));
        $name = str_replace(array("\r","\n"),array(" "," "),$name);
        $email = filter_var(trim($_POST["email"]), FILTER_SANITIZE_EMAIL);
        $subject = trim($_POST["subject"]);
        $phone = trim($_POST["phone"]);
        $message = trim($_POST["message"]);

        if ( empty($name) OR empty($subject) OR empty($message) OR !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            echo "Please complete required fields (Name, Email, Subject, Message) and try again.";
            exit;
        }

        // Recipients - Jobarn
        $recipient = "info@jobarn.co.tz, contact@jobarn.co.tz";
        $replyTo = $email;

        $subjectname = "New Contact: $subject - from $name";

        $email_content = "New message from Jobarn website contact form\n";
        $email_content .= "============================================\n";
        $email_content .= "Name: $name\n";
        $email_content .= "Email: $email\n";
        $email_content .= "Phone: $phone\n";
        $email_content .= "Subject: $subject\n";
        $email_content .= "Message:\n$message\n";
        $email_content .= "============================================\n";
        $email_content .= "Sent from: " . (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'jobarn.co.tz') . "\n";
        $email_content .= "IP: " . $_SERVER['REMOTE_ADDR'] . "\n";

        $email_headers = "From: Jobarn Website <noreply@jobarn.co.tz>\r\n";
        $email_headers .= "Reply-To: $name <$replyTo>\r\n";
        $email_headers .= "MIME-Version: 1.0\r\n";
        $email_headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
        $email_headers .= "X-Mailer: PHP/" . phpversion();

        // Always log for real sender trace & success proof (works even if mail() disabled on Live Server)
        $logDir = __DIR__ . "/messages";
        if (!is_dir($logDir)) mkdir($logDir, 0755, true);
        $logEntry = date("Y-m-d H:i:s") . " | From: $name <$email> | Phone: $phone | Subject: $subject | IP: " . $_SERVER['REMOTE_ADDR'] . "\n$message\n---\n";
        file_put_contents($logDir . "/messages.log", $logEntry, FILE_APPEND | LOCK_EX);
        $jsonFile = $logDir . "/messages.json";
        $jsonData = file_exists($jsonFile) ? json_decode(file_get_contents($jsonFile), true) : [];
        if (!is_array($jsonData)) $jsonData = [];
        $jsonData[] = ["date"=>date("c"), "name"=>$name, "email"=>$email, "phone"=>$phone, "subject"=>$subject, "message"=>$message, "ip"=>$_SERVER['REMOTE_ADDR']];
        file_put_contents($jsonFile, json_encode($jsonData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);

        $sent = mail($recipient, $subjectname, $email_content, $email_headers);
        // Treat logged message as SUCCESS even if mail() disabled (real sender stored) - shows success UX
        http_response_code(200);
        if ($sent) {
            echo "✅ Success! Message from $name <$email> sent to info@jobarn.co.tz & contact@jobarn.co.tz. We will reply/call you at $phone or 0716026781 / 0745912000 shortly. (Ref: ".date("Ymd-His").")";
        } else {
            echo "✅ Received! Message from $name <$email> saved and will be forwarded to info@jobarn.co.tz & contact@jobarn.co.tz. (Mail server pending - but your message is stored). We will contact you at $phone / 0716026781 / 0745912000. (Ref: ".date("Ymd-His").")";
        }

    } else {
        http_response_code(403);
        echo "There was a problem with your submission, please try again.";
    }
?>