<?php

if(isset($_GET['delete_conversation'])){
  if (validate_conversation_id($_GET['delete_conversation']) === false)
  {
    $errors[] = 'Invalid conversation ID.';
  }
  if (empty($errors))
  {
    delete_conversation($_GET['delete_conversation']);
  }
}

$conversations = fetch_conversation_summery();

if (empty($conversations)){
$errors[] = 'You have no messages.';
}

if(empty($errors) === false){
  foreach ($errors as $error){
    echo '<div class="msg error">', $error, '</div>';
  }
}


//errror test the array below
//print_r($conversations);

 ?>

<div class="actions">
  <a href="index.php?page=new_conversation">Start a conversation</a>
  <a href="index.php?page=logout">Logout</a>
</div>

<div class="conversations">

<?php
 foreach ($conversations as $conversation) {
  ?>
<div class="conversation">
<h2>
  <a style="color:red;" href="index.php?page=inbox&amp;delete_conversation=<?php echo $conversation['id']; ?>">[x]</a>
  <a href="index.php?page=view_conversation&amp;conversation_id=<?php echo $conversation['id']; ?>"><?php echo $conversation['subject']; ?></a>
</h2>
<p>Last Reply: <?php echo date('d/m/Y H:i:s', $conversation['last_reply']); ?></p>
</div>
  <?php
 }
?>

</div>
