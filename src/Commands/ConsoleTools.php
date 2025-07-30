<?php

namespace JobMetric\PackageCore\Commands;

trait ConsoleTools
{
    /**
     * get stub file
     *
     * @param string $path
     * @param array $items
     * @param string $fileType
     *
     * @return string
     */
    protected function getStub(string $path, array $items = [], string $fileType = '.php.stub'): string
    {
        $content = file_get_contents($path . $fileType);

        foreach ($items as $key => $item) {
            $content = str_replace('{{' . $key . '}}', $item, $content);
        }

        return $content;
    }

    /**
     * save file
     *
     * @param string $path
     * @param string $content
     *
     * @return void
     */
    protected function putFile(string $path, string $content): void
    {
        file_put_contents($path, $content);
    }

    /**
     * check if directory exists
     *
     * @param string $path
     *
     * @return bool
     */
    protected function isDir(string $path): bool
    {
        return is_dir($path);
    }

    /**
     * make directory
     *
     * @param string $path
     *
     * @return void
     */
    protected function makeDir(string $path): void
    {
        mkdir($path, 0775, true);
    }

    /**
     * check if file exists
     *
     * @param string $path
     *
     * @return bool
     */
    protected function isFile(string $path): bool
    {
        return file_exists($path);
    }

    /**
     * custom alert for console
     *
     * @param string $message
     * @param string $type
     *
     * @return void
     */
    public function message(string $message, string $type = 'info'): void
    {
        $this->newLine();
        switch ($type) {
            case 'info':
                $this->line("  <bg=blue;options=bold> INFO </> " . $message);
                break;
            case 'error':
                $this->line("  <bg=red;options=bold> ERROR </> " . $message);
                break;
            case 'warning':
                $this->line("  <bg=yellow;options=bold> WARNING </> " . $message);
                break;
            case'success':
                $this->line("  <bg=green;options=bold> SUCCESS </> " . $message);
        }
        $this->newLine();
    }

    /**
     * Outputs stylized text to the console using ANSI color and formatting.
     * Returns the formatted string using Symfony Console color and style tags.
     *
     * Note:
     * - You can combine multiple styles using a comma: e.g., `bold,underscore`.
     *
     * @param string $text The message text to be displayed in the console.
     * @param string $color Foreground color (defaults to 'white').
     * Supported colors:
     *  - black
     *  - red
     *  - green
     *  - yellow
     *  - blue
     *  - magenta
     *  - cyan
     *  - white
     *  - default
     * @param string $style Text style or styles separated by comma (defaults to 'bold').
     * Supported styles (options):
     *  - bold
     *  - underscore
     *  - reverse
     *  - blink
     *  - concealed
     *
     * @return string
     */
    public function writeText(string $text, string $color = 'default', string $style = 'bold'): string
    {
        return "<fg={$color};options={$style}>$text</>";
    }

}
