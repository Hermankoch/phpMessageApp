<<?php

// Fetches a summary of the conversations
function fetch_conversation_summery(){
  $dbcon = mysqli_connect('127.0.0.1', 'root', '', 'private_message_system');
  // conversation id is needed to delete the conversations
  // MAX used to select the last date of message reply
  $sqlquery = "SELECT
                    `conversations`.`conversation_id`,
                    `conversations`.`conversation_subject`,
                    MAX(`conversations_messages`.`message_date`) AS `conversation_last_reply`,
                    MAX(`conversations_messages`.`message_date`) > `conversations_members`.`conversation_last_view` AS `conversation_unread`
               FROM `conversations`
               LEFT JOIN `conversations_messages` ON `conversations`.`conversation_id` = `conversations_messages`.`conversation_id`
               INNER JOIN `conversations_members` ON `conversations`.`conversation_id` = `conversations_members`.`conversation_id`
               WHERE `conversations_members`.`user_id` = {$_SESSION['user_id']}
               AND `conversations_members`.`conversation_deleted` = 0
               GROUP BY `conversations`.`conversation_id`
               ORDER BY `conversation_last_reply` DESC";

$result = mysqli_query($dbcon, $sqlquery);

//Code to check what is wrong in the query
//die(mysqli_error($dbcon));

$conversations = array();

while (($row = mysqli_fetch_assoc($result)) !== null){
  $conversations[] = array(
    'id' => $row['conversation_id'],
    'subject' => $row['conversation_subject'],
    'last_reply' => $row['conversation_last_reply'],
    'unread_messages' => ($row['conversation_unread'] == 1),
  );
}
return $conversations;
}

function fetch_conversation_messages($conversation_id){
  $conversation_id = (int)$conversation_id;

 $sqlquery = "SELECT
              `conversations_messages`.`message_date`,
              `conversations_messages`.`message_date` > `conversations_members`.`conversation_last_view` AS `message_unread`,
              `conversations_messages`.`message_text`,
              `users`.`user_name`
              FROM `conversations_messages`
              INNER JOIN `users` ON `conversations_messages`.`user_id` = `users`.`user_id`
              INNER JOIN `conversations_members` ON `conversations_messages`.`conversation_id` = `conversations_members`.`conversation_id`
              WHERE `conversations_messages`.`conversation_id` = {$conversation_id}
              AND `conversations_members`.`user_id` = {$_SESSION['user_id']}
              ORDER BY `conversations_messages`.`message_date` DESC";

  $dbcon = mysqli_connect('127.0.0.1', 'root', '', 'private_message_system');
  $result = mysqli_query($dbcon, $sqlquery);
  //die(mysqli_error($dbcon));
  $messages = array();

  while (($row = mysqli_fetch_assoc($result)) !== null){
    $messages[] = array (
      'date' => $row['message_date'],
      'unread' => $row['message_unread'],
      'text' => $row['message_text'],
      'user_name' => $row['user_name'],
    );
  }
  // You can set the 'user' => array ('name' $row['user_name'], emailaddress, displayname etc)
  return $messages;
}

function update_conversation_last_view($conversation_id){
  $conversation_id = (int)$conversation_id;

  $sqlquery = " UPDATE `conversations_members`
                SET `conversation_last_view` = UNIX_TIMESTAMP()
                WHERE `conversation_id` = {$conversation_id}
                AND `user_id` = {$_SESSION['user_id']}";


  $dbcon = mysqli_connect('127.0.0.1', 'root', '', 'private_message_system');
  mysqli_query($dbcon, $sqlquery);

}

function add_conversation_message($conversation_id, $text){
  $dbcon = mysqli_connect('127.0.0.1', 'root', '', 'private_message_system');
  $conversation_id = (int)$conversation_id;
  $text = mysqli_real_escape_string($dbcon, htmlentities($text));

  $sqlquery = "INSERT INTO `conversations_messages` (`conversation_id`, `user_id`, `message_date`, `message_text`)
               VALUES ('{$conversation_id}', '{$_SESSION['user_id']}', UNIX_TIMESTAMP(), '{$text}')";
  //message will not go to user who deleted the conversations "UPDATE CONVERSATION MEMBERS SET CONVERSATION DELETED = 0 WHERE CONID = CONID"
  mysqli_query($dbcon, $sqlquery);
}


// Create a new conversation, making the users included in the conversation.
function create_conversation($user_ids, $subject, $body)
{
  $dbcon = mysqli_connect('127.0.0.1', 'root', '', 'private_message_system');
  //Change any malicious code to text only
  $subject = mysqli_real_escape_string($dbcon, htmlentities($subject));
  $body = mysqli_real_escape_string($dbcon, htmlentities($body));
  //malicious code end
  $sqlquery = "INSERT INTO `conversations` (`conversation_subject`) VALUES ('{$subject}')";
  mysqli_query($dbcon, $sqlquery);

 //Get Subject ID to link to users part of the conversation
  $conversation_id = mysqli_insert_id($dbcon);
  //Into database con_msg 4 columns values
  $sqlquery = "INSERT INTO `conversations_messages` (`conversation_id`, `user_id`, `message_date`, `message_text`)
               VALUES ('{$conversation_id}', '{$_SESSION['user_id']}', UNIX_TIMESTAMP(), '{$body}')";
  //FROM_UNIXTIME(unix_timestamp)
  mysqli_query($dbcon, $sqlquery);
 // die(mysqli_error($dbcon));
  //for each user array

  $values = array("({$conversation_id}, {$_SESSION['user_id']}, UNIX_TIMESTAMP(), 0)");

  //$user_ids[] = $_SESSION['user_id']; //Append the user sending the message to the array

  //loop through each user that is added to the conversation and add to a ,cslist
  foreach ($user_ids as $user_id){
  $user_id = (int) $user_id; // Extra security to ensure data comes from the database
  $values[] = "({$conversation_id}, {$user_id}, 0, 0)";
  }

  $sqlquery = "INSERT INTO `conversations_members` (`conversation_id`, `user_id`, `conversation_last_view`, `conversation_deleted`)
               VALUES " . implode(", ", $values); //include reciepients

  mysqli_query($dbcon, $sqlquery);

}

// Check if user is a member of conversation
function validate_conversation_id($conversation_id){
  $dbcon = mysqli_connect('127.0.0.1', 'root', '', 'private_message_system');
  $conversation_id = (int)$conversation_id;

  $sqlquery = "SELECT COUNT(1)
              FROM `conversations_members`
              WHERE `conversation_id` = {$conversation_id}
              AND `user_id` = {$_SESSION['user_id']}
              AND `conversation_deleted` = 0";

    if ($result=mysqli_query($dbcon, $sqlquery))
    {
    $rowcount=mysqli_num_rows($result);
    if ($rowcount == 1){
      return true;
    }
    else {
      return false;
    }
    }
  }
  //return (mysqli_result($result, 0) == 1);

function delete_conversation($conversation_id){
  $dbcon = mysqli_connect('127.0.0.1', 'root', '', 'private_message_system');
  $conversation_id = (int)$conversation_id;

  $sqlquery = "SELECT DISTINCT `conversation_deleted`
               FROM `conversations_members`
               WHERE `user_id` != {$_SESSION['user_id']}
               AND `conversation_id` = {$conversation_id}";

  $result = mysqli_query($dbcon, $sqlquery);
  //die(mysqli_error($dbcon));
  if (mysqli_num_rows($result) === 1 && mysqli_result($result, 0) == 1){
  mysqli_query($dbcon, "DELETE FROM `conversations` WHERE `conversation_id` = {$conversation_id}");
  mysqli_query($dbcon, "DELETE FROM `conversations_members` WHERE `conversation_id` = {$conversation_id}");
  mysqli_query($dbcon, "DELETE FROM `conversations_messages` WHERE `conversation_id` = {$conversation_id}");
}else {
  $sql =  "UPDATE `conversations_members`
           SET `conversation_deleted` = 1
           WHERE `conversation_id` = {$conversation_id}
           AND `user_id` = {$_SESSION['user_id']}";

mysqli_query($dbcon, $sql);

  }
}

function mysqli_result($result, $iRow, $field = 0)
{
    if(!mysqli_data_seek($result, $iRow))
        return false;
    if(!($row = mysqli_fetch_array($result)))
        return false;
    if(!array_key_exists($field, $row))
        return false;
    return $row[$field];
}
 ?>
