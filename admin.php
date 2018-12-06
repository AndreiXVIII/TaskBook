<?php
	session_start();
	if (isset($_POST['allTasks'])) {
		header ('Location: index.php');
	}
	if (isset($_POST['createTask'])) {
		header ('Location: createTask.php');
	}
	if (isset($_POST['enterToAdmin'])) {
		header ('Location: admin.php');
	}

	include ('connect.php');				
				
	//меняем статус о выполнении задачи 
	if (!empty($_GET['changeStatus'])) {
		$statusId = $_GET['changeStatus'];
		$queryUpdate = "SELECT * FROM taskBook WHERE id='$statusId'";
		$resultUpdate = mysqli_query($connect, $queryUpdate) or die (mysqli_error($connect));
		$rowUpdate = mysqli_fetch_assoc($resultUpdate);
					
		if ($rowUpdate['status'] == '0') {
			$status = '1';
		}
		else {
			$status = '0';
		}
			
		$queryStatus = "UPDATE taskBook SET status='$status' WHERE id='$statusId'";
		$resultStatus = mysqli_query($connect, $queryStatus) or die (mysqli_error($connect));
		header ('Location: admin.php');
	}
?>
<head>
	<meta charset="utf-8">
	<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
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
			<input type="submit" class="btn btn-default" name="allTasks" value="Все задачи">
			<input type="submit" class="btn btn-default" name="createTask" value="Создание задачи">	
			<input type="submit" class="btn btn-primary" name="enterToAdmin" value="Вход для админа">
		</form>
	<?php
		//авторизация администратора
		if (!empty($_POST['name']) AND !empty($_POST['password'])) {
			if ($_POST['name'] == 'admin') {
				if ($_POST['password'] == '123') {
					//сохраняем авторизацию в сессию;
					$_SESSION['auth'] = true;	
				}
				else {
					$passwordMessage = "<p style=\"color:red\"> Введите правильный пароль </p>";
					$error_borderPassword = 'style="border-color:red"';
				}
			}
			else {
				$loginMessage = "<p style=\"color:red\"> Введите правильный логин </p>";
				$error_borderLogin = 'style="border-color:red"';
			}
		}
		//проверяем на то, что авторизация прошла успешно, и даем администратору все права
		if (isset($_SESSION['auth'])) {
			//скрываем форму авторизации так как она нам больше не нужна
			$hideShowForm = 'style="visibility:hidden"';
			//сортировка по имени пользователя, email и статусу
			$order_by = $_GET["order_by"];	
			if ($order_by == 'name') {
				$sort = 'ORDER BY name';
			}
			elseif ($order_by == 'email') {
				$sort = 'ORDER BY email';
			}
			elseif ($order_by == 'task') {
				$sort = 'ORDER BY task';
			}
			else {
					$sort = '';
			}
			
			$query = "SELECT * FROM taskBook $sort ";
			$result = mysqli_query($connect, $query) or die (mysqli_error($connect));
			for ($arr = []; $row = mysqli_fetch_assoc($result); $arr[] = $row);
		?>	
		<div class="table-responsive">
			<table class="table table-striped">
				<thead>
					<tr>
						<th><a href="admin.php?order_by=name"> Имя пользователя </a></th>
						<th><a href="admin.php?order_by=email"> Почта </a></th>
						<th> Статус </th>
						<th><a href="admin.php?order_by=task"> Текст задачи </a></th>
						<th> Редактировать статус </th>
						<th> Редактировать задачу </th>
					</tr>
				</thead>
				<tbody>
		<?php		
			foreach ($arr as $elem) {
				if ($elem['status'] == 0) {
					$statusСhange = 'выполнена';
					$statusShow = 'не выполнена';
				}
				if ($elem['status'] == 1) {
					$statusСhange = 'не выполнена';
					$statusShow = 'выполнена';
				}
				echo "<tr>
							<td> {$elem['name']} </td>
							<td> {$elem['email']} </td>
							<td> $statusShow </td>
							<td> {$elem['task']} </td>
							<td><a href=\"?changeStatus={$elem['id']}\"> $statusСhange </a></td>
							<td><a href=\"edit.php?edit={$elem['id']}\"> редактировать </a></td>
					  </tr>";
			}
	?>
				</tbody>
			</table>
		</div>
	<?php		
		}
			//если нет авторизации показываем форму для авторизации
			else {
				$hideShowForm = '';
		}
	?>	
	<form action="" method="POST" role="form" <?= $hideShowForm; ?> >
			<div class="form-group">
				<?php if(isset($loginMessage)) echo $loginMessage; ?>
				<label for="login" class="control-label col-md-2"> Login </label>
				<div class="col-md-4">
					<input type="text" name="name" class="form-control" id="login" <?php if(isset($error_borderLogin)) echo $error_borderLogin; ?> placeholder="Введите логин">
				</div>
			</div>
			<div class="form-group">
				<?= $passwordMessage; ?>
				<label for="login" class="control-label col-md-2"> Password </label>
				<div class="col-md-4">
					<input type="password" name="password" class="form-control" id="password" <?php if(isset($error_borderPassword)) echo $error_borderPassword; ?> placeholder="Введите пароль">
				</div>
			</div>
			<input type="submit" class="btn btn-default">
			<br>
			<br>
			<button class="btn btn-warning btn-sm" id="info"> Получить логин и пароль (для тестовой проверки) </button>
	</form>
</div>
<script>
	let info = document.getElementById('info');
	info.onclick = function() {
		alert("login: admin");
		alert("password: 123");
	}
</script>
</body>