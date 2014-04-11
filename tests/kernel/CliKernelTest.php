<?php
namespace Aura\Cli_Kernel;

use Aura\Project_Kernel\Factory;
use Aura\Cli\Status;

class CliKernelTest extends \PHPUnit_Framework_TestCase
{
    protected $cli_kernel;
    
    protected $status;
    
    protected function console(array $argv = array())
    {
        array_unshift($argv, 'cli/console.php');
        $_SERVER['argv'] = $argv;
        
        $path = __DIR__;
        $di = (new Factory)->newContainer(
            $path,
            'kernel',
            "$path/composer.json",
            "$path/vendor/composer/installed.json"
        );

        $this->cli_kernel = $di->newInstance('Aura\Cli_Kernel\CliKernel');
        $this->status = $this->cli_kernel->__invoke();
    }
    
    public function testHello()
    {
        $this->console(array('aura-integration-hello'));
        $expect = 'Hello World!';
        $this->assertStderr('');
        $this->assertStdout('Hello World!' . PHP_EOL);
        $this->assertStatus(Status::SUCCESS);
    }
    
    public function testNoCommandSpecified()
    {
        $this->console();
        $expect = <<<EOT
aura-integration-exception
    Throws an exception.
aura-integration-hello
    Integration test command for hello world.
aura-integration-no-help-available
    No summary available.
help
    No summary available.

EOT;
        $this->assertStderr('');
        $this->assertStdout($expect);
        $this->assertStatus(Status::SUCCESS);
    }
    
    public function testCommandNotAvailable()
    {
        $this->console(array('aura-integration-no-such-command'));
        $this->assertStderr("Command 'aura-integration-no-such-command' not available." . PHP_EOL);
        $this->assertStdout('');
        $this->assertStatus(Status::UNAVAILABLE);
    }
    
    public function testException()
    {
        $this->console(array('aura-integration-exception'));
        $this->assertStderr('Exception: mock exception' . PHP_EOL);
        $this->assertStdout('');
        $this->assertStatus(Status::FAILURE);
    }

    public function testHelp()
    {
        $this->console(array('help'));
        $expect = <<<EOT
aura-integration-exception
    Throws an exception.
aura-integration-hello
    Integration test command for hello world.
aura-integration-no-help-available
    No summary available.
help
    No summary available.

EOT;
        $this->assertStderr('');
        $this->assertStdout($expect);
        $this->assertStatus(Status::SUCCESS);
    }
    
    public function testHelpCommand()
    {
        $this->console(array('help', 'aura-integration-hello'));
        $expect = <<<EOT
SUMMARY
    aura-integration-hello -- Integration test command for hello world.

DESCRIPTION
    The quick brown fox jumps over the lazy dog.

EOT;
        $this->assertStderr('');
        $this->assertStdout($expect);
        $this->assertStatus(Status::SUCCESS);
    }

    public function testHelpCommandUnvailable()
    {
        $this->console(array('help', 'aura-integration-no-such-command'));
        $expect = <<<EOT
Command 'aura-integration-no-such-command' not available.

EOT;
        $this->assertStderr($expect);
        $this->assertStdout('');
        $this->assertStatus(Status::UNAVAILABLE);
    }

    public function testHelpCommandHelpUnvailable()
    {
        $this->console(array('help', 'aura-integration-no-help-available'));
        $expect = <<<EOT
Help for command 'aura-integration-no-help-available' not available.

EOT;
        $this->assertStderr($expect);
        $this->assertStdout('');
        $this->assertStatus(Status::UNAVAILABLE);
    }

    protected function assertStdout($expect)
    {
        $stdout = $this->cli_kernel->stdio->getStdout();
        $stdout->rewind();
        $actual = $stdout->fread(strlen($expect));
        $this->assertEquals($expect, $actual);
    }
    
    protected function assertStderr($expect)
    {
        $stderr = $this->cli_kernel->stdio->getStderr();
        $stderr->rewind();
        $actual = $stderr->fread(strlen($expect));
        $this->assertEquals($expect, $actual);
    }

    protected function assertStatus($expect)
    {
        $this->assertEquals($expect, $this->status);
    }
}
