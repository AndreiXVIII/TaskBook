<?php
	
	$local = 'localhost';
	$user = 'root';
	$password = '';
	$db_base = 'test';

	$connect = mysqli_connect($local, $user, $password, $db_base) or die (mysqli_error($connect));
	mysqli_query($connect, "SET NAMES 'utf8'");