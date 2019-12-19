<?php

$valid_conversation = (isset($_GET['conversation_id']) && validate_conversation_id($_GET['conversation_id']));

if ($valid_conversation === false){
  $errors[] = 'Invalid conversation ID.';
}

if (isset($_POST['message'])){
  if (empty($_POST['message']))
  {
    $errors[] = 'You must enter a message.';
  }
  if (empty($errors)){
  add_conversation_message($_GET['conversation_id'], $_POST['message']);
  }
}

if(empty($errors) === false){
  foreach ($errors as $error){
    echo '<div class="msg error">', $error, '</div>';
  }
}

if ($valid_conversation){
$messages = fetch_conversation_messages($_GET['conversation_id']);
update_conversation_last_view($_GET['conversation_id']);
//print_r($messages);
//test if the array returns correctly

 ?>

<div class="actions">
  <a href="index.php?page=inbox">Inbox</a>
  <a href="index.php?page=logout">Logout</a>
</div>

<form action="" method="post">
  <div>
    <textarea name="message" rows="10" cols="90"></textarea>
  </div>
  <div>
    <input type="submit" value="Add Message" />
  </div>
</form>


<div class="messages">
<?php
foreach ($messages as $message){
  ?>
  <div class="message <?php if ($message['unread']) echo 'unread'; ?>">
    <p class = "name"><?php echo $message['user_name']; ?> (<?php echo date('d/m/Y H:i:s', $message['date']); ?>)</p>
    <p class = "text"><?php echo $message['text']; ?></p>
    </div>
  <?php
}

?>
</div>
<?php } ?>
