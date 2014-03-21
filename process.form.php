<?php

	require('contact.inc.php');

	$name = 		$_POST['contact-form-name'];
	$email = 		$_POST['contact-form-email'];
	$comments = 	$_POST['contact-form-comments'];
	$company = 		$_POST['contact-form-company'];

	if(!$name || !$email){

		die('Please fill name and email to proceed');
	}

	$chimpregistry = new ChimpRegistry();

	$chimpregistry->registry_name = $name;
	$chimpregistry->registry_email = $email;
	$chimpregistry->registry_comments = $comments;

	$chimpregistry->addMeta('company', $company, 'Company');

	$chimpregistry->insert();

	exit('Your form was sent! Go to the <a href="index.php">dashboard</a> or <a href="form.html">try the form one more time</a>');
?>