<?php

	function contact_entry($dbh, $name, $email, $comments){

		try{
			$contact = $dbh->prepare("INSERT INTO contact(name, email, comments, date) VALUES (:name, :email, :comments, datetime('now'))");
			$contact->bindParam(':name', $name);
			$contact->bindParam(':email', $email);
			$contact->bindParam(':comments', $comments);

			$contact->execute();
			return $dbh->lastInsertId();
		}

		catch(PDOException $e){ echo $e->getMessage(); }
	}

	function contact_entry_meta($dbh, $entry_id, $metas){

		foreach($metas as $key => $value){

			$contact = $dbh->prepare("INSERT INTO contact_meta(id, meta, value) VALUES (:id, :meta, :value)");
			$contact->bindParam(':id', $entry_id);
			$contact->bindParam(':meta', $key);
			$contact->bindParam(':value', $value);

			$contact->execute();
		}
	}

	$name = 		$_POST['contact-form-name'];
	$email = 		$_POST['contact-form-email'];
	$comments = 	$_POST['contact-form-comments'];
	$company = 		$_POST['contact-form-company'];

	if(!$name || !$email){

		die('Please fill name and email to proceed');
	}

	//Define the file from the database
	$database_file = 'contact.sqlite';

	//connect to SQLite database
	try{
		$dbh = new PDO("sqlite:{$database_file}");
		$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	}
	catch(PDOException $e){ echo $e->getMessage(); }

	$entry_id = contact_entry($dbh, $name, $email, $comments);
	contact_entry_meta($dbh, $entry_id, array('company' => $company));

	exit('Your form was sent!, go to the <a href="index.php">dashboard</a> or <a href="form.html">try the form one more time</a>');
?>