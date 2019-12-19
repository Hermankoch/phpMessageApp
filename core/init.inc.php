<?php
$core_path = dirname(__FILE__);

if (empty($_GET['page']) || in_array("{$_GET['page']}.page.inc.php", scandir("{$core_path}/pages")) == false)
{
  echo "You landed on an invalid page" ."<br>"."";
  echo "Please use the following format:" ."<br>"."";
  echo "http://localhost/privateMsgApp/index.php?page=inbox" ."<br>"."";
  echo "http://localhost/privateMsgApp/index.php?page=login" ."<br>"."";
  // If page is invalid redirect below
  //header('Location: inbox.php')
  die ();
}

//Useful Code for Testing Purposes.
//To print the array
//print_r(scandir("{$core_path}/pages"));
//Useful code
//if (empty($_GET['page']))
//include($_GET[])
//file_exists();

session_start();
//mysqli_connect('127.0.0.1', 'root', '');
$dbcon = mysqli_connect('127.0.0.1', 'root', '');
mysqli_select_db($dbcon,'private_message_system');

//include script function created to validate password
include("{$core_path}/inc/user.inc.php");
//include private message functions
include("{$core_path}/inc/private_message.inc.php");
//If user has submitted the login form
if (isset($_POST['user_name'], $_POST['user_password']))
{
$user_id = validate_credentials($_POST['user_name'],$_POST['user_password']);
//Check if user and password combination is correct and assign their id to the session(cookie)
    if ($user_id !== false){
        $_SESSION['user_id'] = $user_id;
        header('Location: index.php?page=inbox');
        die();
      }
} // check if user submitted the login form

//check if user is logged in
if (empty($_SESSION['user_id']) && $_GET['page'] !== 'login'){
  header('HTTP/1.1 403 Forbidden');
  header('Location: index.php?page=login');
  die();
}

$include_file = "{$core_path}/pages/{$_GET['page']}.page.inc.php";
echo $include_file;
?>
