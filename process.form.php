<?php

	$name = 		$_POST['contact-form-name'];
	$email = 		$_POST['contact-form-email'];
	$comments = 	$_POST['contact-form-comments'];

	if(!$name || !$email){

		die('Please fill name and email to proceed');
	}

	//Define the file from the database
	$database_file = 'contact.sqlite';

	//connect to SQLite database
	try{ $dbh = new PDO("sqlite:{$database_file}"); }
	catch(PDOException $e){ echo $e->getMessage(); }

	$contact = $dbh->prepare("INSERT INTO contact(name, email, comments, date) VALUES (:name, :email, :comments, datetime('now'))");
	$contact->bindParam(':name', $name);
	$contact->bindParam(':email', $email);
	$contact->bindParam(':comments', $comments);

	$contact->execute();

	exit('Your form was sent!, go to the <a href="contact.php">dashboard</a>');
?>