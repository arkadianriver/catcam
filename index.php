<?php

include 'config/catcfg.inc';

// the dwnld link is a styled button to pass post data
// https://stackoverflow.com/a/33880971
function print_row($app, $file, $size, $path) {
  $fmtfile = _filefmt($file);
  $fmtsize = _sizefmt($size);
  echo '<a target="mediawin" href="https://www.dropbox.com/home/Apps/'. $app
     . '?preview='. $file .'">'.$fmtfile .' - '. $fmtsize .'</a> <form method='
     . '"post" action="catget.php" class="inline"><input type="hidden" name='
     . '"size" value="'. $size .'"/><button type="submit" name="file" class='
     . '"link-button" value="'. $path .'">⭳</button></form>'."\n";

EOL;
}

// make size human-readable. ugly impl, but fastest
function _sizefmt($bytes) {
  if ($bytes < 1024.0) {
    return sprintf("%5.1f", $bytes).' b';
  } elseif (($bytes >= 1024.0) && ($bytes < 1048576.0)) {
    return sprintf("%5.1f", $bytes / 1024.0).' k';
  } elseif (($bytes >= 1048576.0) && ($bytes < 1073741824.0)) {
    return sprintf("%5.1f", $bytes / 1048576.0).' MB';
  } else {
    return sprintf("%5.1f", $bytes / 1073741824.0).' Giga-crap!';
  }
}

// reformat motion's default file name, which is n++-YYYYmmddHHMMSS.avi
function _filefmt($f) {
  return sprintf('%5.0f - %s-%s-%s %s:%s:%s',
                 substr($f,0,-19),substr($f,-18,-14),substr($f,-14,-12),
                 substr($f,-12,-10),substr($f,-10,-8),substr($f,-8,-6),
                 substr($f,-6,-4) );
}

// API call fn()
function drop_get($request, $tok, $data) {
  return file_get_contents(
    "https://api.dropboxapi.com/2/files$request",
    false,
    stream_context_create(
      array(
        'http' => array(
          'method'  => 'POST',
          'header'  => "Authorization: Bearer $tok\r\n"
                     . "Content-Type: application/json\r\n",
          'content' => $data
        )
      )
    )
  );
}

// main
$results = !empty($_POST['cursor'])
         ? drop_get('/list_folder/continue',
                   $c['tok'], '{"cursor": "'. $_POST['cursor'] .'"}')
         : drop_get('/list_folder',
                  $c['tok'], '{"path": ""}');

// results to loop through below, PHP-objectified
$r = json_decode($results);

?>
<!doctype html>
<html>
  <head>
    <title>CatCam</title>
<style>
body {
  font-family: Tahoma, Helvetica;
}
h1 {
  font-size: 18pt;
}
pre {
  font-family: Consolas, Monaco, Fixed-Width;
}
a {
  color: blue;
  text-decoration: none;
}
a:hover, a:active {
  font-weight: bold;
}
a:visited {
  color: gray;
}
.inline {
  display: inline;
}
.link-button {
  background: none;
  border: none;
  color: blue;
  text-decoration: none;
  cursor: pointer;
  font-family: Consolas, Monaco, Fixed-Width;
  font-size: 16px;
}
.link-button:focus, .link-button:hover {
  outline: none;
  font-weight: bold;
}
.link-button:active {
  color:red;
}
</style>
  </head>
  <body>
    <h1>CatCam Files</h1>
    <pre><?php
      $ttl = 0;
      foreach ($r->entries as $entry) {
        $ttl += $entry->size;
        print_row($c['app'], $entry->name, $entry->size, $entry->path_lower);
      }
      echo "</pre>";
      if ($r->has_more) { // if set, there's another page of data
        echo <<<EOM

        <form method="post" action="index.php" class="inline">
          <button type="submit" name="cursor" value="{$r->cursor}"
          >...and there's more.</button>
        </form>
EOM;
      }
      ?>
    <p>Total size on page: <?php echo _sizefmt($ttl); ?></p>
    <p><a href="https://www.dropbox.com/home/Apps/<?php echo $c['app']; ?>"
        target="_blank"><button id="godropdir" type="button"
        >Go to Dropbox folder</button></a></p>
  </body>
</html>
