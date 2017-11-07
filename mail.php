<?php
$to = "gjd1982@gmail.com";
$subject = "Test email";
$message = "This is a test email.";
$from = "contactus@choicestationery.com";
$headers = "From:" . $from;
if (mail($to, $subject, $message, $headers)) {
	echo("Your message has been sent successfully");
	} else {
	echo("Sorry, your message could not be sent");
}
?>