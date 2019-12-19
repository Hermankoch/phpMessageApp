<?php

if (isset($_POST['to'], $_POST['subject'], $_POST['body'])){
    $errors = array();

    if (empty($_POST['to'])){
      $errors[] = "You must enter at least one name.";

    } else if (preg_match('#^[a-z, ]+$#i', $_POST['to']) === 0) {
      $errors[] = "The list of names is not valid";
    } else {
      $user_names = explode(',', $_POST['to']);

      foreach ($user_names as &$name){
        $name = trim($name);
      }
      $user_ids = fetch_user_ids($user_names);

      if (count($user_ids) !== count($user_names)){
        $errors[] = 'The following users could not be found: ' . implode(', ', array_diff($user_names, array_keys($user_ids)));
        // Use array different function to compare 2 arrays
      }
    }

  if (empty($_POST['subject'])){
    $errors[] = "You must enter a subject";
  }
  if (empty($_POST['body'])){
    $errors[] = "You did not enter a message";
  }
  if (empty($errors)){
  create_conversation(array_unique($user_ids), $_POST['subject'], $_POST['body']);
  // Array Unique if a username has been entered more than once
  }
}

if(isset($errors)){
  // The form is submitted with no errors
  if (empty($errors)){
    echo '<div class="success">Your message has been sent ! <a href="index.php?page=inbox">Return to your Inbox</a></div>';
  }
  // The form is submitted with errors
  else {
    foreach ($errors as $error){
      echo '<div class="error">', $error, '</div>';
    }
  }
}

 ?>
 <div class="actions">
   <a href="index.php?page=inbox">Inbox</a>
   <a href="index.php?page=logout">Logout</a>
 </div>

<form action="" method="post">
<div>
  <label for="to">To</label>
<!-- -->
  <input type="text" name="to" id="to" value="<?php if (isset($_POST['to'])) echo htmlentities($_POST['to']); ?>"/>
</div>

<div>
  <label for="subject">Subject</label>
  <input type="text" name="subject" id="subject" value="<?php if (isset($_POST['subject'])) echo htmlentities($_POST['subject']); ?>"/>
</div>

<div>
  <textarea name="body" rows="20" cols="90"><?php if (isset($_POST['body'])) echo htmlentities($_POST['body']); ?></textarea>
</div>

<div>
  <input type="submit" value="Send" />
</form>
