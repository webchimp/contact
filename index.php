<?php

	/*	   ______            __             __
		  / ____/___  ____  / /_____ ______/ /_
		 / /   / __ \/ __ \/ __/ __ `/ ___/ __/
		/ /___/ /_/ / / / / /_/ /_/ / /__/ /_
		\____/\____/_/ /_/\__/\__,_/\___/\__/
		                                       */

	/* *********************************************************************************************
	Configuration **********************************************************************************
	********************************************************************************************* */

	//Define the file from the database
	$database_file = 'contact.sqlite';

	//Number of rows per page
	$rows_per_page = '20';

	/* *********************************************************************************************
	********************************************************************************************* */

	//connect to SQLite database
	try{ $dbh = new PDO("sqlite:{$database_file}"); }
	catch(PDOException $e){ echo $e->getMessage(); }

	//Fetch Users ----------------------------------------------------------------------------------

	$page = isset($_GET['page'])? $_GET['page']-1 : 0;
	$pagination_offset = $rows_per_page*$page;

	$contacts = $dbh->prepare("SELECT * FROM contact");
	$contacts->execute();
	$contacts = $contacts->fetchAll(PDO::FETCH_ASSOC);
	$row_count = count($contacts);

	$contacts = $dbh->prepare("SELECT * FROM contact ORDER BY date DESC LIMIT {$pagination_offset}, {$rows_per_page}");
	$contacts->execute();
	$contacts = $contacts->fetchAll(PDO::FETCH_ASSOC);

	$pagination = true;

	//Search of a user by email

	$filter_by = isset($_POST['search-by'])? $_POST['search-by'] : '';

	if(isset($_POST['search-by'])){

		$contacts = $dbh->prepare("SELECT * FROM contact WHERE " . $_POST['search-by'] . " LIKE '%" . $_POST['search-email'] . "%'");

		$contacts->execute();
		$contacts = $contacts->fetchAll(PDO::FETCH_ASSOC);

		$row_count = count($contacts);

		$pagination = false;
	}

	//Filter by date

	$filter_date_from = isset($_POST['filter-date-from'])? $_POST['filter-date-from'] : '';
	$filter_date_to = isset($_POST['filter-date-to'])? $_POST['filter-date-to'] : '';

	if($filter_date_from && $filter_date_to){

		if($filter_date_from != '' && $filter_date_to != ''){

			$contacts = $dbh->prepare("SELECT * FROM contact WHERE date BETWEEN '" . $_POST['filter-date-from'] . "' AND '" . $_POST['filter-date-to'] . "'");
			$contacts->execute();
			$contacts = $contacts->fetchAll(PDO::FETCH_ASSOC);

			$row_count = count($contacts);

			$pagination = false;
		}
	}

	$num_pages = ceil($row_count/$rows_per_page);

	//Delete process -------------------------------------------------------------------------------

	if(isset($_GET['delete'])){

		$id = $_GET['delete'];

		$delete = $dbh->prepare("DELETE FROM contact WHERE id = :id");
		$delete->bindParam('id', $id, PDO::PARAM_INT);
		$delete->execute();

		header('Location: index.php?success=The row has been deleted successfully');
	}

	//CSV Process ----------------------------------------------------------------------------------

	function download_send_headers($filename){

		// disable caching
		$now = gmdate("D, d M Y H:i:s");
		header("Expires: Tue, 03 Jul 2001 06:00:00 GMT");
		header("Cache-Control: max-age=0, no-cache, must-revalidate, proxy-revalidate");
		header("Last-Modified: {$now} GMT");

		// force download
		header("Content-Type: application/force-download");
		header("Content-Type: application/octet-stream");
		header("Content-Type: application/download");

		// disposition / encoding on response body
		header("Content-Disposition: attachment;filename={$filename}");
		header("Content-Transfer-Encoding: binary");
	}

	function array2csv(array &$array){

		if(count($array) == 0){
			return null;
		}

		ob_start();

		$keys = array_keys(reset($array));

		foreach($keys as &$value){ $value = ucwords($value); }

		unset($value);

		$df = fopen("php://output", 'w');
		fputcsv($df, $keys);

		foreach ($array as $row) {
			fputcsv($df, $row);
		}

		fclose($df);
		return ob_get_clean();
	}

	if(isset($_GET['csv'])){

		$contacts = $dbh->query("SELECT * FROM contact");
		$contacts->execute();
		$contacts = $contacts->fetchAll(PDO::FETCH_ASSOC);

		download_send_headers("data_export_" . date("Y-m-d") . ".csv");
		echo array2csv($contacts);
		die();
	}

	//----------------------------------------------------------------------------------------------
?>

<!doctype html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Contact | by WebChimp</title>
	<link href="//netdna.bootstrapcdn.com/bootstrap/3.1.0/css/bootstrap.min.css" rel="stylesheet">
	<link href="//cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.3.0/css/datepicker.min.css" rel="stylesheet">

	<style>

		header{

			background: #428BCA;
			box-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
		}

		header a{ color: white; }

		header .container{

			height: 50px;
			padding-left: 90px;

			background: url(data:image/png;base64,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) left top no-repeat;
		}

		.align-center{ text-align: center; }
		.table-id{ width: 50px; }
		.table-table-action{ width: 100px; }

		.buttons{ padding-bottom: 20px; }

		.search-by{ margin-right: 20px; }

		.no-found{

			clear: both;
			padding: 70px 0;
			text-align: center;
		}

		.no-found .icon{

			font-size: 100px;
			margin-bottom: -20px;
		}

		.intro{

			padding-bottom: 0;
		}

	</style>
</head>
<body>

	<div class="modal fade" id="contact-view">
		<div class="modal-dialog">
			<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h4 class="modal-title">Modal title</h4>
			</div>
			<div class="modal-body">
				<p>One fine body&hellip;</p>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
			</div>
			</div><!-- /.modal-content -->
		</div><!-- /.modal-dialog -->
	</div><!-- /.modal -->

	<div class="modal fade" id="contact-delete">
		<div class="modal-dialog">
			<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h4 class="modal-title">Delete Record</h4>
			</div>
			<div class="modal-body">
				<p>Are you sure you want to delete this record?</p>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">No, sorry...</button>
				<a href="#" class="btn btn-primary" id="contact-delete-confirm">Yes! Delete</a>
			</div>
			</div><!-- /.modal-content -->
		</div><!-- /.modal-dialog -->
	</div><!-- /.modal -->


	<header class="navbar navbar-static-top bs-docs-nav" role="banner">
		<div class="container">
			<div class="navbar-header">
				<a class="navbar-brand" href="index.php">Contact <small>by WebChimp</small></a>
			</div>
		</div>
	</header>

	<div id="content" class="bs-header">
		<div class="container">
			<div class="well well-sm intro">
				<p>Contact will show you all the information from your contact form.</p>
			</div>
		</div>
	</div>

	<div class="container">
		<?php if(isset($_GET['success'])): ?>
			<div class="alert alert-success fade in">
				<button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
				<?php echo $_GET['success']; ?>
			</div>
		<?php endif; ?>

		<div class="controls clearfix">
			<form class="form-inline pull-left search-by" role="form" method="post">
				<div class="form-group">
					<label for="search-by">Search by</label>
					<select name="search-by" id="search-by" class="form-control">
						<option value="email" <?php echo $filter_by == 'email'? 'selected="selected"' : ''; ?>>Email</option>
						<option value="name" <?php echo $filter_by == 'name'? 'selected="selected"' : ''; ?>>Name</option>
					</select>
					<input type="text" name="search-email" class="form-control" id="search-email" placeholder="Search" value="<?php echo isset($_POST['search-email'])? $_POST['search-email'] : ''; ?>">
				</div>
				<button type="submit" class="btn btn-default">Search</button>
			</form>

			<form class="form-inline pull-left filter-date" role="form" method="post">
				<div class="form-group">
					<label for="filter-date-from">Filter by date</label>
					<input type="text" name="filter-date-from" class="date-input form-control" id="filter-date-from" placeholder="Date From" value="<?php echo $filter_date_from? $filter_date_from : ''; ?>">
				</div>
				<div class="form-group">
					<label class="sr-only" for="filter-date-to">Date To</label>
					<input type="text" name="filter-date-to" class="date-input form-control" id="filter-date-to" placeholder="Date To" value="<?php echo $filter_date_to? $filter_date_to : ''; ?>">
				</div>
				<button type="submit" class="btn btn-default">Filter</button>
			</form>

			<div class="buttons pull-right">
				<a href="index.php?csv=1" class="btn btn-primary"><span class="glyphicon glyphicon-file"></span> Download CSV</a>
			</div>
		</div>

		<?php if($row_count): ?>

			<table class="table table-striped table-bordered table-hover">
				<thead>
					<tr>
						<th class=" table-id align-center">#</th>
						<th>Name</th>
						<th>Email</th>
						<th>Date</th>
						<th class="table-action align-center">Details</th>
						<th class="table-action align-center">Delete</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach($contacts as $contact): ?>

					<tr>
						<td class="contact-id align-center"><?php echo $contact['id']; ?></td>
						<td class="contact-name"><?php echo $contact['name']; ?></td>
						<td class="contact-email"><a href="mailto:<?php echo $contact['email']; ?>"><?php echo $contact['email']; ?></a></td>
						<td class="contact-date"><?php echo date('l jS \of F Y h:i:s A', strtotime($contact['date'])); ?></td>
						<td class="align-center">
							<button type="button" class="btn btn-primary btn-xs contact-view">View <span class="glyphicon glyphicon-eye-open"></span></button>
							<div class="contact-comments hidden"><p><?php echo $contact['comments']; ?></p></div>
						</td>
						<td class="align-center"><button type="button" class="btn btn-danger btn-xs contact-delete">Delete <span class="glyphicon glyphicon-trash"></span></button></td>
					</tr>

					<?php endforeach; ?>
				</tbody>
				<tfoot>
					<tr>
						<th class="align-center">#</th>
						<th>Name</th>
						<th>Email</th>
						<th>Date</th>
						<th class="align-center">Details</th>
						<th class="align-center">Delete</th>
					</tr>
				</tfoot>
			</table>

			<?php if($pagination && $num_pages > 1): ?>
				<ul class="pagination pull-right">
					<?php if($page > 0): ?>
						<li><a href="index.php?page=<?php echo $page; ?>">&laquo;</a></li>
					<?php endif; ?>

					<?php for($i = 0; $i < $num_pages; $i++): ?>
						<li <?php echo $page == $i? 'class="active"' : ''; ?>><a href="index.php?page=<?php echo $i+1; ?>"><?php echo $i+1; ?></a></li>
					<?php endfor; ?>

					<?php if($page+1 < $num_pages): ?>
						<li><a href="index.php?page=<?php echo $page+2; ?>">&raquo;</a></li>
					<?php endif; ?>
				</ul>
			<?php endif; ?>

		<?php /* No results */ else: ?>

			<div class="no-found well">
				<span class="glyphicon glyphicon-remove icon"></span>
				<h2>No information found in database</h2>
			</div>

		<?php endif; ?>

	</div>

	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
	<script type="text/javascript" src="//netdna.bootstrapcdn.com/bootstrap/3.1.0/js/bootstrap.min.js"></script>
	<script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.3.0/js/bootstrap-datepicker.min.js"></script>

	<script>
	jQuery(document).ready(function($){

		$('.date-input').datepicker({format: 'yyyy-mm-dd'});

		$('.contact-view').on('click', function(){

			var fila = $(this).parents('tr');
			var title = fila.find('.contact-name').text() + ' (' + fila.find('.contact-email a').text() + ')';
			var body = fila.find('.contact-comments').html();

			$('#contact-view .modal-title').text(title);
			$('#contact-view .modal-body').html(body);
			$('#contact-view').modal('show');
		});

		$('.contact-delete').on('click', function(){

			var fila = $(this).parents('tr');
			var id = fila.find('.contact-id').text();

			$('#contact-delete-confirm').attr('href', 'index.php?delete=' + id);
			$('#contact-delete').modal('show');
		});
	});
	</script>
</body>
</html>