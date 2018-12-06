<?php
	session_start();
	if (!empty($_POST['editTask'])) {
		$update = $_POST['editTask'];
		$userId = $_GET['edit'];
		
		include ('connect.php');
		
		$query = "UPDATE taskBook SET task='$update' WHERE id='$userId'";
		mysqli_query($connect, $query) or die (mysqli_error($connect));
		header ('Location: admin.php');
	}
?>
<head>
	<meta charset="utf-8">
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
	<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js" integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy" crossorigin="anonymous"></script>
	<style>
		body {
			padding: 30px;
		}
	</style>
</head>
<body>
	<div class="container">
		<form action="" method="POST">
			<div >
				<label for="task" class="control-label col-md-2"> Задача: </label>
				<div class="col-md-4">
					<textarea name="editTask" class="form-control" placeholder="Введите новую задачу"></textarea><br>
				</div>
			</div>
			<input type="submit" class="btn-success" value="Отредактировать">
		</form>
	</div>
</body>