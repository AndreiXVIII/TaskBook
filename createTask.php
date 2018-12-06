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
		<input type="submit" class="btn btn-default" name="allTasks" value="Все задачи">
		<input type="submit" class="btn btn-primary" name="createTask" value="Создание задачи">	
		<input type="submit" class="btn btn-default" name="enterToAdmin" value="Вход для админа">
	</form>
	<?php
		$_SESSION['auth'] = null;

		if (isset($_POST['sent'])) {
			if (!empty($_POST['name']) AND !empty($_POST['email']) AND !empty($_POST['task'])) {
				if (!empty(trim($_POST['name'])) AND !empty(trim($_POST['email'])) AND !empty(trim($_POST{'task'}))) {
					$regEmail = "#^[A-Za-z\d]+@[a-z]+\.[a-z]{2,}$#";
					if (preg_match($regEmail, $_POST['email'])) {
						$name = $_POST['name'];
						$email = $_POST['email'];
						$task = $_POST['task'];
						//проверка загруженного файла на изображение
						if (isset($_FILES)) {
							$file = $_FILES['image']['tmp_name'];
							if (!empty($file)) {
								$imageType = $_FILES['image']['type'];
								$typeValid = ['image/png', 'image/gif', 'image/jpeg'];
								$sizeValid = 20971520;
								if (in_array($imageType, $typeValid)) {							
									if ($_FILES['image']['size'] > $sizeValid) {
										die('Слишком большой размер файла. Допустимое значение 20Мб');
									}
									$size = getimagesize($_FILES['image']['tmp_name']);
									list($widthOrigin, $heightOrigin) = $size;	
									if ($widthOrigin > 320 OR $heightOrigin > 240) {
										$width = 320;
										$height = 240;
										//создаем изображение для дальнейших преобразований, в зависимости от типа переданного файла
										if ($imageType == 'image/jpeg') {
											$createdImage = imagecreatefromjpeg($file);	
										}
										elseif ($imageType == 'image/gif') {
											$createdImage = imagecreatefromgif($file);
										}
										elseif ($imageType == 'image/png') {
											$createdImage = imagecreatefrompng($file);
										} 								
										//вычисляем пропорции изображения и размеры преобразованного изображения
										if ($widthOrigin > $heightOrigin) {
											$ratio = $widthOrigin / $width;
										}
										else {
											$ratio = $heightOrigin / $height;
										}
										$proportionWidth = floor($widthOrigin / $ratio);
										$proportionHeight = floor($heightOrigin / $ratio);
										//создаем пустую картинку с шириной и высотой, полученой через пропорции
										$emptyPicture = imagecreatetruecolor($proportionWidth, $proportionHeight);
										//копируем исходное изображение ($createdImage) в только что созданное ($emptyPicture), изменяя его размеры
										imagecopyresampled($emptyPicture, $createdImage, 0, 0, 0, 0, $proportionWidth, $proportionHeight, $widthOrigin, $heightOrigin);
										//сохраняем полученное изображение и очищаем память
										imagejpeg($emptyPicture, $_FILES['image']['tmp_name'], 100);
										imagedestroy($emptyPicture);
										imagedestroy($createdImage);
										//добавляем в базу данных все записи
										$image = addslashes(file_get_contents($_FILES['image']['tmp_name']));
										$image_name = addslashes( $_FILES['image']['name']);
										
										//вывожу сообщение об успешной сохранности файлов, и очищаю от повторного добавления в базу данных при обновлении страницы
										$status = "<p class=\"text-success\"> Запись успешно сохранена </p>";
										//$_POST['name'] = '';
										//$_POST['email'] = '';
										//$_POST['task'] = '';
										
										include ('connect.php');
										
										$query = "INSERT INTO taskBook SET name='$name', email='$email', status='0', task='$task', image='$image', image_name='$image_name'";
										$result = mysqli_query($connect, $query) or die (mysqli_error($connect));
									}
								}	
								else {
									$imageError = "<p style=\"color:red\"> Вы загрузили не верный формат картинки (JPG/GIF/PNG) </p>";
								}								
							}
							else {
								$imageError = "<p style=\"color:red\"> Загрузите изображение </p>";
							}
						}
					}
					else {	
							$errorEmail = 'style="color:red"';
							$error_borderEmail = 'style="border-color:red"';		
							$status = "<p class=\"text-danger\"> Введите корректный e-mail </p>";
					}
				}
				else {
						$error = 'style="color:red"';
						$error_border = 'style="border-color:red"';
						$status = "<p style=\"color:red\"> В введенных данных не должно быть одних пробелов </p>";
				}
			}
			else {
				$status = "<p style=\"color:red\"> заполните все поля </p>";
				empty($_POST['name']) ? $errorName = 'style="color:red"' AND $error_borderName = 'style="border-color:red"': null;		
				empty($_POST['email']) ? $errorEmail = 'style="color:red"' AND $error_borderEmail = 'style="border-color:red"': null;
				empty($_POST['task']) ? $errorTask = 'style="color:red"' AND $error_borderTask = 'style="border-color:red"': null;
				empty($file) ? $errorImage = 'style="color:red"' : null;
			}
		}	
	?>	
	<p><?= $status; ?> </p>
	<form action="" method="POST" role="form"  enctype="multipart/form-data" id="form">
		<div class="form-group">
			<label for="name" class="control-label col-md-2" <?=$errorName; ?>> Имя: </label>
			<div class="col-md-4">
				<input type="text" name="name" class="form-control" id="name" <?=$error_borderName; ?> placeholder="Введите имя пользователя" value="<?php if(!empty($_POST['name'])) echo $_POST['name']; ?>">
			</div>
		</div>
		<div class="form-group">
			<label for="email" class="control-label col-md-2" <?=$errorEmail; ?>> E-mail: </label>
			<div class="col-md-4">
				<input type="text" name="email" class="form-control" id="email" <?=$error_borderEmail; ?>  placeholder="Введите почту" value="<?php if(!empty($_POST['name'])) echo $_POST['email']; ?>">
			</div>
		</div>
		<div >
			<label for="task" class="control-label col-md-2" <?=$errorTask; ?>> Задача: </label>
			<div class="col-md-4">
				<textarea name="task" class="form-control" id="task" <?=$error_borderTask; ?> placeholder="Текст задачи"><?php if(!empty($_POST['name'])) echo $_POST['task']; ?></textarea><br>
			</div>
		</div>
		<div class="form-group">
			<?= $imageError; ?>
			<label for="image" class="control-label col-md-2" <?= $errorImage; ?>> Image: </label><br>
			<input type="file" name="image" class="btn btn-sm" >
			<img src="" id="image">
		</div>
		<div>
			<input type="submit" class="btn btn-default" name="sent" value="Создать задачу">
		</div>
	</form>
	<br>
	<button class="btn btn-info" data-toggle="modal" data-target="#preview" id="btn"> Предварительный просмотр </button>
	<!-- Модальное окно предварительного просмотра информации перед отправкой данных -->
	<div id="preview" class="modal fade" tabindex="-1">
		<div class="modal-dialog modal-lg">
			<div class="modal-content">
				<div class="modal-header"> 
					<h2> Предварительный просмотр </h2>
					<button class="close" data-dismiss="modal"> x </button>
				</div>
				<div class="modal-body">
					<p><b>Имя:</b> <span id="showModalName"></span></p>
					<p><b>E-mail:</b> <span id="showModalEmail"></span></p>
					<p><b>Задача:</b> <span id="showModalTask"></span></p>
					<div id="showModalImage"> </div>
				</div>
			</div>
		</div>
	</div>
	<script>
		let enteredName = document.getElementById('name');
		let enteredEmail = document.getElementById('email');
		let enteredTask = document.getElementById('task');
		let uploadedImage = document.getElementById('image');
		let userName = document.getElementById('showModalName');
		let userEmail = document.getElementById('showModalEmail');
		let userTask = document.getElementById('showModalTask');
		let userImage = document.getElementById('showModalImage');
		let button = document.getElementById("btn");
		button.onclick = function() {
			userName.innerHTML = enteredName.value;
			userEmail.innerHTML = enteredEmail.value;
			userTask.innerHTML = enteredTask.value;
		}	
	</script>
</div>
</body>
