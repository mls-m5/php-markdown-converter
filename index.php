<?php

class MarkdownConverter
{
    private $lines;
    private $filename;
    private $hasH1Header;

    public function __construct($markdownName)
    {
        if (!file_exists($markdownName)) {
            throw new Exception("File not found: " . $markdownName);
        }

        $this->lines = file($markdownName, FILE_IGNORE_NEW_LINES);
        $this->filename = pathinfo($markdownName, PATHINFO_FILENAME);
        $this->hasH1Header = false;
    }

    private function convertHeaders($line)
    {
        $headerPattern = '/^(#{1,6})\s+(.*)$/';

        return preg_replace_callback($headerPattern, function ($matches) {
            $headerLevel = strlen($matches[1]);
            if ($headerLevel === 1) {
                $this->hasH1Header = true;
            }
            return "<h{$headerLevel}>{$matches[2]}</h{$headerLevel}>";
        }, $line);
    }

    private function convertImages($line)
    {
        $imagePattern = '/!\[(.*?)\]\((.*?)\)/';

        return preg_replace_callback($imagePattern, function ($matches) {
            $altText = $matches[1];
            $imageUrl = $matches[2];
            return "<img src=\"{$imageUrl}\" alt=\"{$altText}\">";
        }, $line);
    }

    private function convertLinks($line)
    {
        $linkPattern = '/\[(.*?)\]\((.*?)\)/';

        return preg_replace_callback($linkPattern, function ($matches) {
            $linkText = $matches[1];
            $linkUrl = $matches[2];

            // Add "http://" if the URL starts with "www."
            if (preg_match('/^www\./', $linkUrl)) {
                $linkUrl = "http://{$linkUrl}";
            }

            return "<a href=\"{$linkUrl}\">{$linkText}</a>";
        }, $line);
    }



    public function convert()
    {
        $convertedLines = [];

        foreach ($this->lines as $line) {
            $convertedLine = $this->convertHeaders($line);
            $convertedLine = $this->convertImages($convertedLine);
            $convertedLine = $this->convertLinks($convertedLine);
            $convertedLines[] = $convertedLine;
        }

        if (!$this->hasH1Header) {
            array_unshift($convertedLines, "<h1>{$this->filename}</h1>");
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