<?php

include 'config/catcfg.inc';

function drop_curlget($tok, $path, $size) {
  $ch=curl_init();
  curl_setopt($ch, CURLOPT_URL,"https://content.dropboxapi.com/2/files/download");
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
  curl_setopt($ch, CURLOPT_HTTPHEADER,array("Authorization: Bearer $tok",
                                            "Dropbox-API-Arg: {\"path\": \"$path\"}"));
  // http://php.net/manual/en/function.curl-exec.php#87015
  $output = curl_exec($ch);
  $info = curl_getinfo($ch);
  curl_close($ch);
  if ($output === false || $info['http_code'] != 200) {
    $output = "No data returned. HTTP code ". $info['http_code'];
    if (curl_error($ch)) $output .= "\n". curl_error($ch);
    echo $output;
  } else {
    $p = explode('/',$path);
    $file = end($p);
    // 'OK' status; format $output data if necessary here:
    header('Content-Type: application/octet-stream');
    header('Content-Length: '.$size);
    header('Content-Disposition: attachment; filename=cat-'.$file);
    header('Content-Transfer-Encoding: binary');
    echo $output;
  }
}

// main
if (!empty($_POST['file'])) {
  drop_curlget($c['tok'], $_POST['file'], $_POST['size']);
} else {
  echo 'no data provided.';
}

?>
