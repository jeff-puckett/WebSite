<?php

include_once('../_resources/credentials.php');
//$page_title = "Home Page";
require_once('../_resources/header.php');

echo "
  <h1>$section_title</h1>
  <div class='well'>
";

$path = realpath($path_real_relative_root);

/*
$objects = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path));
$Regex = new RegexIterator($objects, '/^.+\.(php|html)$/i', RecursiveRegexIterator::GET_MATCH);
foreach($Regex as $name => $object){
    echo substr($name,strlen($path_real_relative_root)) . "<br/>\n";
}
*/

// thanks to salathe
// http://stackoverflow.com/questions/3321547/how-to-use-regexiterator-in-php

abstract class FilesystemRegexFilter extends RecursiveRegexIterator {
    protected $regex;
    public function __construct(RecursiveIterator $it, $regex) {
        $this->regex = $regex;
        parent::__construct($it, $regex);
    }
}

class FilenameFilter extends FilesystemRegexFilter {
    // Filter files against the regex
    public function accept() {
        return ( ! $this->isFile() || preg_match($this->regex, $this->getFilename()));
    }
}

class DirnameFilter extends FilesystemRegexFilter {
    // Filter directories against the regex
    public function accept() {
        return ( ! $this->isDir() || preg_match($this->regex, $this->getFilename()));
    }
}

$directory = new RecursiveDirectoryIterator($path);
// Filter out _resources and hidden folders
$filter = new DirnameFilter($directory, '/^(?!(\.|_resources))/');
// Filter PHP/HTML files 
$filter = new FilenameFilter($filter, '/\.(?:php|html)$/');
// Filter out credentials files
$filter = new FilenameFilter($filter, '/^(?!credentials)/');
// Filter out navigation-menu files
$filter = new FilenameFilter($filter, '/^(?!navigation-menu)/');
// Filter out header.php files
$filter = new FilenameFilter($filter, '/^(?!header.php)/');
// Filter out footer.php files
$filter = new FilenameFilter($filter, '/^(?!footer.php)/');

$sitemap = '
<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
';
foreach(new RecursiveIteratorIterator($filter) as $pathfile) {
  $sitemap .= "\t<url>\n";
  $basefile = substr($pathfile,strlen($path_real_relative_root));
  $webfile = "$path_web_relative_root$basefile";
  // FIX: using invalid protocol relative url,
  // need dynamic sitemap to serve up http or https
  $sitemap .= "\t\t<loc>//$_SERVER[SERVER_NAME]$webfile</loc>\n";
  $modified = date("Y-m-d",filemtime($pathfile));
  $sitemap .= "\t\t<lastmod>$modified</lastmod>\n";
  $sitemap .= "\t</url>\n";
  
  echo "<a target='_blank' href='$path_web_relative_root$basefile'>$basefile</a><br/>\n";
}
$sitemap .= "</urlset>\n";

echo "<pre>" . htmlentities($sitemap) . "</pre>";

echo "
  </div><!-- /.well-->
";

require_once('../_resources/footer.php');

?>
