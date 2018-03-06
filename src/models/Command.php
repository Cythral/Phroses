<?php
/**
 * A command class to be extended and used in /src/commands.php
 * Provides an interface for working inside a cli environment
 */
namespace Phroses;

use \Phroses\Exceptions\ExitException;

abstract class Command {

    /** @var string $name the name of the command */
    public $name;

    /** @var resource $stream the stream resource to use for i/o */
    protected $stream = STDIN;

    /**
     * Executes the command
     * 
     * @param array $args an array of arguments
     * @param array $flags an array of --flags
     * @return mixed can be anything, up to the definer
     */
    abstract public function execute(array $args, array $flags);

    /**
     * Reads command line input.  Also provides a way to accept only certain answers
     * 
     * @param string|null $output a string to output before reading input
     * @param array|null $valid an array of valid responses
     * @return mixed user input
     */
    protected function read(?string $output = null, ?array $valid = ['y','n','']) {
        if($output) echo $output;
        $answer = strtolower(trim(fgets($this->stream)));
        
        if($valid) {
            if(!in_array($answer, $valid)) {
                println("Invalid option '$answer'");
                return $this->read($output, $valid);
            }
        }

        return $answer;
    }

    /**
     * Prints an error and then exits
     * 
     * @param string $output the line to print
     * @return void
     */
    protected function error(string $output): void {
        println($output);
        throw new ExitException(1);
    }

    /**
     * Requires that the configuration file be loaded.  If it is not, an error is displayed
     * and the program exits
     * 
     * @return void
     */
    protected function requireConfigFile(): void {
        if(!Phroses::$configFileLoaded) {
			$this->error("Config file not present, please complete the installation of Phroses before proceeding.");
		}
    }
}