<?php
require_once(__DIR__ . '/functions.php');
require_once(__DIR__ . '/settings.php');

$localSettingFile = __DIR__ . '/settings.local.php';
if (file_exists($localSettingFile)) {
    require_once($localSettingFile);
}

println('Start migrating');

// Get migrated
$migratedLogFile = __DIR__ . '/.tmp/migrated.json';
if (file_exists($migratedLogFile)) {
    $migratedContent = file_get_contents($migratedLogFile);
    $migrated = json_decode($migratedContent, true);
} else {
    $migrated = [];
}

// Get files list from input
$files = getDirContents($settings['dokuwiki_pages_path']);

// Traverse all files
foreach ($files as $file) {
    println('- Processing for file: ' . $file);

    // Skip if already migrated
    if (in_array($file, $migrated)) {
        println('Migrated already ~> skip');
        continue;
    }

    if (substr($file, -4) === '.txt') {
        // Get name
        $name = str_replace('.txt', '', $file);
        $name = str_replace($settings['dokuwiki_pages_path'], '', $name);

        $name = trim($name, '/ ');
        $name = str_replace('/', ':', $name);

        // Get html content
        $content = file_get_contents($file);
        $html = convertDokuWikiToHtml($content);

        // Create pages with API
        $url = $settings['bookstack_url'] . '/api/books/' . $settings['bookstack_book_id'] . '/pages';

        // Setup request to send json via POST
        $data = array(
            'name' => $name,
            'html' => $html,
        );
        $token = $settings['bookstack_api_key'] . ':' . $settings['bookstack_api_secret'];
        
        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => $url,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'POST',
          CURLOPT_POSTFIELDS => json_encode($data),
          CURLOPT_HTTPHEADER => array(
            'Authorization: Token ' . $token,
            'Content-Type: application/json',
          ),
        ));
        
        $response = curl_exec($curl);
        curl_close($curl);

        $resData = json_decode($response, true);

        if ($resData['id']) {
            $migrated[] = $file;

            println('created succesfully with slug ' . $resData['slug']);
        } else {
            println('failed to create the page');
        }

    } else {
        println('not a txt file ~> skip.');
    }

    file_put_contents($migratedLogFile, json_encode($migrated));
    println('Save migrated log file');
}
