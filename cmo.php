#!/usr/bin/env php
<?php
function logIt($msg)
{
    fwrite(STDERR, $msg . "\n");
}

$handlerConfigFile=__DIR__ . '/handlers.json';
$handlers = json_decode(file_get_contents($handlerConfigFile), true);

if(json_last_error()){
    logIt("Can't open handler configuration ($handlerConfigFile)");
    logIt(json_last_error_msg());
}

$tmpFile = tempnam('/tmp', 'show-or-open');
$tmpFileFp = fopen($tmpFile, 'w');
if (!$tmpFileFp) {
    logIt("Can't open $tmpFile for writing\n");
    die(1);
}

while (!feof(STDIN)) {
    $chunk = fread(STDIN, 32);
    fwrite($tmpFileFp, $chunk);
    fwrite(STDOUT, $chunk);
}

fclose($tmpFileFp);
$mimeType = mime_content_type($tmpFile);

if (!isset($handlers[$mimeType])) {
    logit("No handler set for $mimeType");
    exit;
}
$handler = $handlers[$mimeType];
if (isset($handler['extension'])) {
    rename($tmpFile, $tmpFile . '.' . $handler['extension']);
    $tmpFile .= '.' . $handler['extension'];
}

$command = str_replace("{}", escapeshellarg($tmpFile), $handler['command']);
logit("Running $command for mineType $mimeType");
system($command);


