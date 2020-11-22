#!/usr/bin/php
<?php

fputs(STDERR, <<<BANNER
    \e[95m          __    _         
     _ _ ___ / _|__| |___  __ 
    | '_/ -_)  _/ _` / _ \/ _|
    |_| \___|_| \__,_\___/\__|
    \e[0m
    BANNER
);

$opt = getopt('vh', [ // verbose and help are the only short options
    'classes:',
    'exclude:',
    'help',
    'layout:',
    'layout-theme:',
    'out:',
    'sources:',
    'title:',
    'verbose',
    'xml:'
]);

if (isset($opt['h']) or isset($opt['help'])) {
    fputs(STDERR, <<<USAGE
    
    Reflects PHP entities into single-page documentation.
    Progress is output to STDERR.
    
    $ php {$argv[0]} [options]
    
    \e[97m--help, -h \e[0m

      Prints this message to STDERR and calls exit(1)

    \e[97m--classes="My/Namespace , Another\Namespace" \e[0m
    
      Comma-separated entity name patterns to document.
      
      Forward slashes are converted to namespace separators,
      to make shell scripting easier.
      
      If this is empty or omitted, absolutely everything will be documented.
      
      Default: empty (document all)
    
    \e[97m--exclude="/my/side/effects.php , /ignore/me.php" \e[0m

      Comma-separated file name patters to exclude.
      
      This may be because they cause side effects or have other
      purposes other than purely housing entities.
      
      Default: empty (exclude none)
    
    \e[97m--layout="vendor/hfw/refdoc/layout/vanilla/vanilla.xslt" \e[0m
    
      XSLT file which transforms the intermediate reflection XML to HTML.
      
      Default: "vendor/hfw/refdoc/layout/vanilla/vanilla.xslt"
    
    \e[97m--layout-theme="darkly" \e[0m
    
      Sets a parameter, named "theme", on the layout XSLT.

      The interpretation of this is up to the layout.

      For the vanilla layout, this selects a theme from Bootswatch.
      
      Default: "darkly"
      
      https://bootswatch.com/darkly
    
    \e[97m--out="refdoc.html" \e[0m
    
      The HTML file to write.
      
      If this is "-", or STDOUT is being redirected, then STDOUT is used.
      
      Default: "refdoc.html"
    
    \e[97m--sources="src/" \e[0m
    
      Comma-separated relative paths to recursively include.
      
      When recursing directories, only PHP files are included.
      
      "vendor/autoload.php" is always included.
      
      Default: "src/"
      
    \e[97m--title="RefDoc" \e[0m
    
      Sets the resulting HTML document title.
      
      Default: "RefDoc"
     
    \e[97m--verbose, -v \e[0m
    
      Lots and lots of information.
      
      Default: no

    \e[97m--xml="refdoc.xml" \e[0m
    
      Optionally writes the intermediate reflection XML to a file.
      
      If this is "-", the XML is written to STDOUT, and the script exits.
      
      This is helpful for debugging, or if you're making a layout.
      
      Default: no


    USAGE
    );
    exit(1);
}

include_once 'vendor/autoload.php';

use Helix\RefDoc\Log;
use Helix\RefDoc\RefXML;

// define friendly error handlers
set_exception_handler(function(Throwable $e) use ($opt) {
    Log::error("{$e->getMessage()} in {$e->getFile()}:{$e->getLine()}");
    Log::line($e->getTraceAsString() . "\n");
    if (empty($opt)) {
        Log::line("Try --help\n");
    }
    exit(1);
});
set_error_handler(function($severity, $message, $file, $line) {
    throw new ErrorException($message, $severity, $severity, $file, $line);
});

// set log options
Log::$verbose = isset($opt['v']) || isset($opt['verbose']);
if (!stream_isatty(STDERR)) {
    Log::disableColors();
}

// reflect to xml
($xml = new RefXML)
    ->setClasses($opt['classes'] ?? null)
    ->setExclude($opt['exclude'] ?? null)
    ->setSources($opt['sources'] ?? null)
    ->generate();

// output the intermediate xml?
if ($xmlOut = $opt['xml'] ?? null) {
    Log::entity("Writing intermediate XML to {$xmlOut}\n");
    if ($xmlOut === '-') {
        echo $xml;
        exit;
    }
    file_put_contents($xmlOut, $xml);
}

// convert the intermediate xml to dom
// todo post-reflection, inject dot svg
$xml = $xml->toDOMDocument();

// load the layout
Log::entity("Loading XSLT layout...\n");
($layout = new DOMDocument)->load($opt['layout'] ?? (__DIR__ . '/layout/vanilla/vanilla.xslt'));

// configure the xslt processor
$processor = new XSLTProcessor;
$processor->importStylesheet($layout);
$processor->setParameter('', 'title', $opt['title'] ?? 'RefDoc');
$processor->setParameter('', 'theme', $opt['layout-theme'] ?? 'darkly');

// write the final document
$out = $opt['out'] ?? 'refdoc.html';
Log::entity("Writing to {$out}\n");
if ($out === '-' or !stream_isatty(STDOUT)) {
    echo $processor->transformToXml($xml);
}
else {
    file_put_contents($out, $processor->transformToXml($xml));
}