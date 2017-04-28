<?php
/**
 * Created by PhpStorm.
 * User: chase
 * Date: 20/03/17
 * Time: 16:00
 */

namespace App\Traits;


use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Output\ConsoleOutput;

trait CustomConsoleOutput
{
    /**
     * @var ConsoleOutput
     */
    protected $output;


    /**
     * Function called to initialize $this->output
     */
    protected function initConsoleOutput()
    {
        if (is_null($this->output)) {
            $this->output = new ConsoleOutput();
        }
    }

    /**
     * Write a string as standard output.
     * @param string $string
     * @param string $style
     */
    public function line($string, $style = null)
    {
        $this->initConsoleOutput();

        $styled = $style ? "<$style>$string</$style>" : $string;

        $this->output->write($styled . PHP_EOL);
    }

    /**
     * Write a string as information output.
     * @param string $string
     */
    public function info($string)
    {
        $this->line($string, 'info');
    }

    /**
     * Write a string as comment output.
     * @param string $string
     */
    public function comment($string)
    {
        $this->line($string, 'comment');
    }

    /**
     * Write a string as error output.
     * @param string $string
     */
    public function error($string)
    {
        $this->line($string, 'error');
    }

    /**
     * Write a string as warning output.
     * @param string $string
     */
    public function warn($string)
    {
        if (!$this->output->getFormatter()->hasStyle('warning')) {
            $style = new OutputFormatterStyle('yellow');

            $this->output->getFormatter()->setStyle('warning', $style);
        }

        $this->line($string, 'warning');
    }
}