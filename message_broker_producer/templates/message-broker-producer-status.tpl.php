<?php
  $bla = TRUE;
  if ($bla) {
    $bla = FALSE;
  }
?>
<h1>RabbitMQ Status</h1>
<ul>
<?php foreach ($output as $status_type => $status): ?>
  <li><strong><?php print ucwords($status_type) ?></strong>: <?php print $status ?></li>
<?php endforeach; ?>  
</ul>