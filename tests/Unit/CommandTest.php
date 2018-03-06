<?php
/**
 * @covers \Phroses\Command
 */

namespace Phroses\Testing;

use \Phroses\Command;
use \Phroses\Exceptions\ExitException;

class CommandTest extends TestCase {

    /**
     * Makes sure the error method prints a line and exits
     * @covers \Phroses\Command::error
     */
    public function testError() {
        $this->expectException(ExitException::class);
        $this->expectOutputString("test\n");

        (new class extends Command {

            public function execute(array $args, array $flags) {
                $this->error("test");
            }

        })->execute([], []);
    }

    /**
     * Tests read without setting valid options
     * @covers \Phroses\Command::read
     */
    public function testReadWithoutValid() {
        $this->expectOutputString("yes/no");
        
        $input = (new class extends Command {

            public function execute(array $args, array $flags) {
                $this->stream = fopen("data:text/plain,".urlencode("test"), "r");

                return $this->read("yes/no", null);
            }

        })->execute([], []);

        $this->assertEquals("test", $input);
    }

    /**
     * Tests read while setting valid options
     * @covers \Phroses\Command::read
     */
    public function testReadWithValid() {
        $this->expectOutputString("Y/nInvalid option 'a'\nY/n");

        $input = (new class extends Command {

            public function execute(array $args, array $flags) {
                $this->stream = fopen("data:text/plain,".urlencode("a\ny"), "r");
                return $this->read('Y/n');
            }

        })->execute([], []);

        $this->assertEquals('y', $input);
    }
}