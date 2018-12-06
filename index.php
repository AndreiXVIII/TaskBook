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
		<input type="submit" class="btn btn-primary" name="allTasks" value="Все задачи">
		<input type="submit" class="btn btn-default" name="createTask" value="Создание задачи">	
		<input type="submit" class="btn btn-default" name="enterToAdmin" value="Вход для админа">
	</form>
	<?php
		//обнуление авторизации админа
		$_SESSION['auth'] = null;
		
		include ('connect.php');
		
		//сортировка по имени пользователя, email и статусу
		$order_by = $_GET["order_by"];	
		if ($order_by == 'name') {
			$sort = 'ORDER BY name';
		}
		elseif ($order_by == 'email') {
			$sort = 'ORDER BY email';
		}
		elseif ($order_by == 'status') {
			$sort = 'ORDER BY status';
		}
		elseif ($order_by == 'task') {
			$sort = 'ORDER BY task';
		}
		else {
				$sort = '';
		}
			
		//Пагинация
		if (isset($_GET['page'])) {
			$page = $_GET['page'];
		}
		else {
			$page = 1;
		}
		
		$quantity = 3;
		$pageNumber = ($page - 1) * $quantity; 
		
	
		$query = "SELECT * FROM taskBook $sort LIMIT $pageNumber, $quantity";
		$result = mysqli_query($connect, $query) or die (mysqli_error($connect));
		for ($arr = []; $row = mysqli_fetch_assoc($result); $arr[] = $row);
		
		//Получаем колличество записей в задачнике
		$query = "SELECT COUNT(*) as count FROM taskBook";
		$result = mysqli_query($connect, $query) or die (mysqli_error($connect));
		$count = mysqli_fetch_assoc($result)['count'];

		//Узнаем какое количество страниц должно быть, для вывода записей 	
		$pagesCount = ceil($count / $quantity);
	?>
	<div class="table-responsive">
	<table class="table table-striped">
		<thead>
			<tr class="active">
				<th><a href="index.php?order_by=name"> Имя пользователя </a></th>
				<th><a href="index.php?order_by=email"> Почта </a></th>
				<th><a href="index.php?order_by=status"> Статус </a></th>
				<th><a href="index.php?order_by=task"> Текст задачи </a></th>
			</tr>
		</thead>
		<tbody>
			<?php		
				foreach ($arr as $elem) {
					if ($elem['status'] == '0') {
						$statusShow = 'не выполнена';
					}
					else {
						$statusShow = 'выполнена';
					}
					echo "<tr class=\"forModal\">
								<td> {$elem['name']} </td>
								<td> {$elem['email']} </td>
								<td> $statusShow </td>
								<td> ".substr($elem['task'], 0, 100)." </td>
								<td class=\"task\" style=\"display:none\"> {$elem['task']} </td>
								<td style=\"display:none\"> 
									<img class=\"image\" src='data:image/*;base64,", base64_encode($elem['image']), "' alt='на этом месте должна быть картинка :)' />
								</td>
						  </tr>";
				}
			?>
		</tbody>
	</table>
	<nav aria-label="Задачи">
		<ul class="pagination">
		<?php 
			//Полученое количество страниц, через цикл выводим ссылками. Реализация пагинации
			if ($page != 1) {
				$prev = $page - 1;
				echo "<li class=\"page-item\">
						<a href=\"?order_by=$order_by&page=$prev\" aria-label=\"Предыдущая\" class=\"page-link\"> 
							<span aria-hidden=\"true\"> &laquo; </span> 
						</a>
					</li>";
			}
			else {
				echo " <li class=\"page-item disabled\">
						<a href=\"#\" aria-label=\"Предыдущая\" class=\"page-link disabled\"> &laquo; </a>
					</li>";
			}
			for ($i = 1; $i <= $pagesCount; $i++) {
				if ($page == $i) {
					$active = 'active';
				}
				else {
					$active = '';
				}
				echo "<li class=\"page-item $active\"><a href=\"?order_by=$order_by&page=$i\" class=\"page-link\"> $i </a></li>";
			}
			if ($page != $pagesCount) {
				$next = $page + 1;
				echo "<li class=\"page-item\">
						<a href=\"?order_by=$order_by&page=$next\" aria-label=\"Следущая\" class=\"page-link\"> 
							<span aria-hidden=\"true\"> &raquo; </span> 
						</a>
					</li>";
			}
			else {
				echo " <li class=\"page-item disabled\">
						<a href=\"#\" aria-label=\"Следущая\" class=\"page-link disabled\"> &raquo; </a>
					</li>";
			}
		?>
		</ul>
	</nav>
	</div>
	<!-- Модульное окно с полным текстом задания и с изображением -->
	<div id="test" class="modal fade" tabindex="-1">
		<div class="modal-dialog modal-lg">
			<div class="modal-content">
				<div class="modal-header">
					<h2> Подробное описание задачи </h2>
					<button class="close" data-dismiss="modal"> x </button>
				</div>
				<div class="modal-body">
					<p id="modalTaskValue">  </p>
					<div id="modalImage"> </div>
				</div>
			</div>
		</div>
	</div>
</div>
	<script>
		let cellElements = document.getElementsByClassName('forModal');
		let showModalTask = document.getElementById('modalTaskValue');
		let showModalImage = document.getElementById('modalImage');
		for (let i = 0;  i < cellElements.length; i++) {
			cellElements[i].onclick = function() {
				let classTask = document.getElementsByClassName('task');
				let classImage = document.getElementsByClassName('image');
				for (let k = 0; k < classTask.length; k++) {
					var task = classTask[i].innerHTML;
				}
				for (let j = 0; j < classImage.length; j++) {
					var image = classImage[i];
				}

				showModalTask.innerHTML = task;
				showModalImage.appendChild(image);
				
				$('#test').modal();
				$('#test').on('hidden.bs.modal', function(e) {
					location.reload();
				});
			}
		}
	</script>
</body>
	
	
	
	
	
	

	