<?php 

use Closure;
use RuntimeException;
use InvalidArgumentException;
use Monolog\Handler\SyslogHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger as MonologLogger;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\ErrorLogHandler;
use Monolog\Handler\RotatingFileHandler;
use Psr\Log\LoggerInterface as PsrLoggerInterface;
use base_support_contracts_interface_jsonable as Jsonable;
use base_support_contracts_interface_arrayable as Arrayable;
 

class base_log_writer implements PsrLoggerInterface
{

	/**
	 * The Monolog logger instance.
	 *
	 * @var \Monolog\Logger
	 */
	protected $monolog;

	/**
	 * The Log levels.
	 *
	 * @var array
	 */
	protected $levels = [
		'debug'     => MonologLogger::DEBUG,
		'info'      => MonologLogger::INFO,
		'notice'    => MonologLogger::NOTICE,
		'warning'   => MonologLogger::WARNING,
		'error'     => MonologLogger::ERROR,
		'critical'  => MonologLogger::CRITICAL,
		'alert'     => MonologLogger::ALERT,
		'emergency' => MonologLogger::EMERGENCY,
	];

	/**
	 * Create a new log writer instance.
	 *
	 * @param  \Monolog\Logger  $monolog
	 * @return void
	 */
	public function __construct(MonologLogger $monolog)
	{
		$this->monolog = $monolog;
	}

	/**
	 * Log an emergency message to the logs.
	 *
	 * @param  string  $message
	 * @param  array  $context
	 * @return void
	 */
	public function emergency($message, array $context = array())
	{
		return $this->writeLog(__FUNCTION__, $message, $context);
	}

	/**
	 * Log an alert message to the logs.
	 *
	 * @param  string  $message
	 * @param  array  $context
	 * @return void
	 */
	public function alert($message, array $context = array())
	{
		return $this->writeLog(__FUNCTION__, $message, $context);
	}

	/**
	 * Log a critical message to the logs.
	 *
	 * @param  string  $message
	 * @param  array  $context
	 * @return void
	 */
	public function critical($message, array $context = array())
	{
		return $this->writeLog(__FUNCTION__, $message, $context);
	}

	/**
	 * Log an error message to the logs.
	 *
	 * @param  string  $message
	 * @param  array  $context
	 * @return void
	 */
	public function error($message, array $context = array())
	{
		return $this->writeLog(__FUNCTION__, $message, $context);
	}

	/**
	 * Log a warning message to the logs.
	 *
	 * @param  string  $message
	 * @param  array  $context
	 * @return void
	 */
	public function warning($message, array $context = array())
	{
		return $this->writeLog(__FUNCTION__, $message, $context);
	}

	/**
	 * Log a notice to the logs.
	 *
	 * @param  string  $message
	 * @param  array  $context
	 * @return void
	 */
	public function notice($message, array $context = array())
	{
		return $this->writeLog(__FUNCTION__, $message, $context);
	}

	/**
	 * Log an informational message to the logs.
	 *
	 * @param  string  $message
	 * @param  array  $context
	 * @return void
	 */
	public function info($message, array $context = array())
	{
		return $this->writeLog(__FUNCTION__, $message, $context);
	}

	/**
	 * Log a debug message to the logs.
	 *
	 * @param  string  $message
	 * @param  array  $context
	 * @return void
	 */
	public function debug($message, array $context = array())
	{
		return $this->writeLog(__FUNCTION__, $message, $context);
	}

	/**
	 * Log a message to the logs.
	 *
	 * @param  string  $level
	 * @param  string  $message
	 * @param  array  $context
	 * @return void
	 */
	public function log($level, $message, array $context = array())
	{
		return $this->writeLog($level, $message, $context);
	}

	/**
	 * Dynamically pass log calls into the writer.
	 *
	 * @param  string  $level
	 * @param  string  $message
	 * @param  array  $context
	 * @return void
	 */
	public function write($level, $message, array $context = array())
	{
		return $this->writeLog($level, $message, $context);
	}

	/**
	 * Write a message to Monolog.
	 *
	 * @param  string  $level
	 * @param  string  $message
	 * @param  array  $context
	 * @return void
	 */
	protected function writeLog($level, $message, $context)
	{
        // 临时逻辑, 弥补console ouput 的缺失
        if (kernel::runningInConsole())
        {
            if ($level !== 'debug') echo $message.PHP_EOL;
        }
		$this->monolog->{$level}($message, $context);
	}

	/**
	 * Register a file log handler.
	 *
	 * @param  string  $path
	 * @param  string  $level
	 * @return void
	 */
	public function useFiles($path, $level = 'debug')
	{
		$this->monolog->pushHandler($handler = new StreamHandler($path, $this->parseLevel($level)));

		$handler->setFormatter($this->getDefaultFormatter());
	}

	/**
	 * Register a daily file log handler.
	 *
	 * @param  string  $path
	 * @param  int     $days
	 * @param  string  $level
	 * @return void
	 */
	public function useDailyFiles($path, $days = 0, $level = 'debug')
	{
		$this->monolog->pushHandler(
			$handler = new RotatingFileHandler($path, $days, $this->parseLevel($level))
		);

		$handler->setFormatter($this->getDefaultFormatter());
	}

	/**
	 * Register a Syslog handler.
	 *
	 * @param  string  $name
	 * @param  string  $level
	 * @return void
	 */
	public function useSyslog($name = 'luckymall', $level = 'debug')
	{
		return $this->monolog->pushHandler(new SyslogHandler($name, LOG_USER, $level));
	}

	/**
	 * Register an error_log handler.
	 *
	 * @param  string  $level
	 * @param  integer $messageType
	 * @return void
	 */
	public function useErrorLog($level = 'debug', $messageType = ErrorLogHandler::OPERATING_SYSTEM)
	{
		$this->monolog->pushHandler(
			$handler = new ErrorLogHandler($messageType, $this->parseLevel($level))
		);

		$handler->setFormatter($this->getDefaultFormatter());
	}

	/**
	 * Format the parameters for the logger.
	 *
	 * @param  mixed  $message
	 * @return void
	 */
	protected function formatMessage($message)
	{
		if (is_array($message))
		{
			return var_export($message, true);
		}
		elseif ($message instanceof Jsonable)
		{
			return $message->toJson();
		}
		elseif ($message instanceof Arrayable)
		{
			return var_export($message->toArray(), true);
		}

		return $message;
	}

	/**
	 * Parse the string level into a Monolog constant.
	 *
	 * @param  string  $level
	 * @return int
	 *
	 * @throws \InvalidArgumentException
	 */
	protected function parseLevel($level)
	{
		if (isset($this->levels[$level]))
		{
			return $this->levels[$level];
		}

		throw new InvalidArgumentException("Invalid log level.");
	}

	/**
	 * Get the underlying Monolog instance.
	 *
	 * @return \Monolog\Logger
	 */
	public function getMonolog()
	{
		return $this->monolog;
	}

	/**
	 * Get a defaut Monolog formatter instance.
	 *
	 * @return \Monolog\Formatter\LineFormatter
	 */
	protected function getDefaultFormatter()
	{
        //return new WildfireFormatter();
		return new LineFormatter("[%datetime%] %channel%.%level_name%: %message% %context% %extra%\n", null, true, true);
	}
}
