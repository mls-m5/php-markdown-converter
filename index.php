<?php

class MarkdownConverter
{
    private $lines;

    public function __construct($markdownName)
    {
        if (!file_exists($markdownName)) {
            throw new Exception("File not found: " . $markdownName);
        }

        $this->lines = file($markdownName, FILE_IGNORE_NEW_LINES);
    }

    private function convertHeaders($line)
    {
        $headerPattern = '/^(#{1,6})\s+(.*)$/';

        return preg_replace_callback($headerPattern, function ($matches) {
            $headerLevel = strlen($matches[1]);
            return sprintf("<h%d>%s</h%d>", $headerLevel, $matches[2], $headerLevel);
        }, $line);
    }

    public function convert()
    {
        $convertedLines = [];

        foreach ($this->lines as $line) {
            $convertedLine = $this->convertHeaders($line);
            $convertedLines[] = $convertedLine;
        }

        return implode("\n", $convertedLines);
    }
}

$markdownName = 'md/test.md';

try {
    $converter = new MarkdownConverter($markdownName);
    $convertedContent = $converter->convert();
    echo $convertedContent;
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}