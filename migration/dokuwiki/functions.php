<?php
require_once(__DIR__ . '/DocuwikiToMarkdownExtra.php');
require_once(__DIR__ . '/Parsedown.php');

function println($message) {
    echo $message . "\n";
}

function getDirContents($dir, &$results = array()) {
    $files = scandir($dir);

    foreach ($files as $key => $value) {
        $path = realpath($dir . DIRECTORY_SEPARATOR . $value);
        if (!is_dir($path)) {
            $results[] = $path;
        } else if ($value != "." && $value != "..") {
            getDirContents($path, $results);
            $results[] = $path;
        }
    }

    return $results;
}

$markdownConverter = new DocuwikiToMarkdownExtra();
$parseDownConverter = new Parsedown();

function convertDokuWikiToHtml($content) {
    global $markdownConverter, $parseDownConverter;

    // To markdown
    $markdown = $markdownConverter->convert($content);

    // To html
    $html = $parseDownConverter->text($markdown);

    return $html;
}
