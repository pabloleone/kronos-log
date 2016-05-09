<?php

namespace Kronos\Tests\Log\Writer;

use Kronos\Log\Adaptor\FileFactory;
use Kronos\Log\Adaptor\TTY;
use Kronos\Log\Enumeration\AnsiBackgroundColor;
use Kronos\Log\Enumeration\AnsiTextColor;
use Kronos\Log\Writer\Console;
use Psr\Log\LogLevel;

class ConsoleTest extends \PHPUnit_Framework_TestCase {

	const LOGLEVEL_BELOW_ERROR = LogLevel::INFO;
	const LOGLEVEL_ABOVE_WARNING = LogLevel::ERROR;
	const A_MESSAGE = 'a message {key}';
	const CONTEXT_KEY = 'key';
	const CONTEXT_VALUE = 'value';
	const INTERPOLATED_MESSAGE = 'a message value';
	const INTERPOLATED_MESSAGE_WITH_LOG_LEVEL = 'INFO : a message value';
	const DATETIME_REGEX = '\[\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\]';

	/**
	 * @var Console
	 */
	private $writer;

	private $factory;
	private $stdout;
	private $stderr;

	public function setUp() {
		$this->factory = $this->getMockBuilder(FileFactory::class)->disableOriginalConstructor()->getMock();
	}

	public function test_NewConsole_Constructor_ShouldCreateAdaptorForStdoutAndStderr() {
		$this->factory
			->expects($this->exactly(2))
			->method('createTTYAdaptor')
			->withConsecutive(
				[Console::STDOUT],
				[Console::STDERR]
			);

		$this->writer = new Console($this->factory);
	}

	public function test_Console_LogWithLevelBelowError_ShouldWriteInterpolatedMessageToStdout() {
		$this->givenFactoryReturnFileAdaptors();
		$this->expectsWriteToBeCalled($this->stdout, self::INTERPOLATED_MESSAGE);
		$this->writer = new Console($this->factory);

		$this->writer->log(self::LOGLEVEL_BELOW_ERROR, self::A_MESSAGE, [self::CONTEXT_KEY => self::CONTEXT_VALUE]);
	}

	public function test_Console_LogWarning_ShouldWriteInterpolatedMessageToStdoutInYellow() {
		$this->givenFactoryReturnFileAdaptors();
		$this->expectsWriteToBeCalled($this->stdout, self::INTERPOLATED_MESSAGE, AnsiTextColor::YELLOW);
		$this->writer = new Console($this->factory);

		$this->writer->log(LogLevel::WARNING, self::A_MESSAGE, [self::CONTEXT_KEY => self::CONTEXT_VALUE]);
	}

	public function test_Console_LogWithLevelAboveWarning_ShouldWriteInterpolatedMessageToStderrInWhiteOnRed() {
		$this->givenFactoryReturnFileAdaptors();
		$this->expectsWriteToBeCalled($this->stderr, self::INTERPOLATED_MESSAGE, AnsiTextColor::WHITE, AnsiBackgroundColor::RED);
		$this->writer = new Console($this->factory);

		$this->writer->log(self::LOGLEVEL_ABOVE_WARNING, self::A_MESSAGE, [self::CONTEXT_KEY => self::CONTEXT_VALUE]);
	}

	public function test_ConsolePrependingLogLevelAndDateTime_LogWithLevelBelowError_ShouldCallWriteWithMessagePrependedByDateTimeThenLogLevel() {
		$this->givenFactoryReturnFileAdaptors();
		$this->expectsWriteToBeCalled($this->stdout, $this->matchesRegularExpression('/'.self::DATETIME_REGEX.' '.self::INTERPOLATED_MESSAGE_WITH_LOG_LEVEL.'/'));
		$this->writer = new Console($this->factory);
		$this->writer->setPrependLogLevel();
		$this->writer->setPrependDateTime();

		$this->writer->log(self::LOGLEVEL_BELOW_ERROR, self::A_MESSAGE, [self::CONTEXT_KEY => self::CONTEXT_VALUE]);
	}

	private function givenFactoryReturnFileAdaptors() {
		$this->stdout = $this->getMockBuilder(TTY::class)->disableOriginalConstructor()->getMock();
		$this->stderr = $this->getMockBuilder(TTY::class)->disableOriginalConstructor()->getMock();

		$this->factory
			->method('createTTYAdaptor')
			->will($this->returnValueMap([
				[Console::STDOUT, $this->stdout],
				[Console::STDERR, $this->stderr],
			]));
	}

	private function expectsWriteToBeCalled($file, $message, $text_color = null, $background_color = null) {
		$file->expects($this->once())->method('write')->with($message, $text_color, $background_color);
	}
}