<?php
session_start();

if(isset($_POST['user'])) {
	$_SESSION['user'] = $_POST['user'];
}