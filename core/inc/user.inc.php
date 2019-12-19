<?php
//convert username and password to not include scripts

function validate_credentials($user_name, $user_password){
  $dbcon = mysqli_connect('127.0.0.1', 'root', '', 'private_message_system');

  $user_name = mysqli_real_escape_string($dbcon, $user_name);
  $user_password = sha1($user_password);

//Check if username and password exists and returns user_id
  $sqlcheck = ("SELECT `user_id` FROM `users` WHERE `user_name` = '{$user_name}' AND `user_password` = '{$user_password}'");
  $result = mysqli_query($dbcon, $sqlcheck);

  if (mysqli_num_rows($result) != 1){
   return false;
  }

  return mysqli_result($result, 0);
  //if ($result=mysqli_query($dbcon,$sqlcheck))
  //{
  //$rowcount=mysqli_num_rows($result);
  //if ($rowcount == 1){
  //  return true;
  //}
//  else {
  //  return false;
//  }
  //}
}

function fetch_user_ids($user_names){

  $dbcon2 = mysqli_connect('127.0.0.1', 'root', '', 'private_message_system');
  if (mysqli_connect_errno())
    {
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
    }

    foreach ($user_names as &$name){
    $name = mysqli_real_escape_string($dbcon2, $name); //Safe to use inside a query
  }

  $sqlcheck2 = ("SELECT `user_id`, `user_name` FROM `users` WHERE `user_name` IN ('" . implode("','", $user_names) . "')");
  $result = mysqli_query($dbcon2, $sqlcheck2);
  
  $names = array();

    while (($row = mysqli_fetch_assoc($result)) !== null){
      $names[$row['user_name']] = $row['user_id'];
    }
  return $names;
  }

?>
