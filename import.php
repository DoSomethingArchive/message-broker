$signups = array();

$file = fopen('emails.csv', 'r');
while (($line = fgetcsv($file)) !== FALSE) {

  //$line is an array of the csv elements
  $signup[] = 
  

}
fclose($file);