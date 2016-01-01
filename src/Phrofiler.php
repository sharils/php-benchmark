<?php
namespace Sharils;

use ErrorException;

class Phrofiler
{
    const TIME_FILENAME_PREFIX = 'php-phrofiler-time-';

    const TIME_TEMPLATE = <<<'PHP'
#!/usr/bin/env php
<?php
set_error_handler(function ($severity, $message, $file, $line) {
    if (error_reporting() & $severity) {
        $error = json_encode([
            $message,
            0,
            $severity,
            $file,
            $line
        ]);
        fwrite(STDERR, $error);
        exit($severity);
    }
});

$success = ob_start();
assert('$success !== false');

%s;

$_ = microtime(true);
for ($__ = %d; --$__; ) {
    %s;
}
$_ = microtime(true) - $_;

%s;

ob_clean();

echo $_;

PHP;

    const WHOLE_FILENAME_PREFIX = 'php-phrofiler-whole-';

    const WHOLE_TEMPLATE = <<<'PHP'
#!/usr/bin/env php
<?php
%s;

%s;

%s;

PHP;

    private $loopCount = 100000;

    private $setUp = null;

    private $tearDown = null;

    private $timeCount = 10;

    public function loopCount($loopCount = null)
    {
        if (func_num_args() === 0) {
            return $this->loopCount;
        } else {
            assert('is_int($loopCount) && 0 < $loopCount && $loopCount <= 1000000');

            $this->loopCount = $loopCount;
        }
    }

    public function profile(array $snippets)
    {
        $timeFilenames = array_map([$this, 'toTimeFilename'], $snippets);

        $wholeFilenames = array_map([$this, 'toWholeFilename'], $snippets);

        $times = array_map([$this, 'toTime'], $timeFilenames);

        $data = array_map([$this, 'toObject'], $snippets, $timeFilenames, $wholeFilenames, $times);

        usort($data, [$this, 'lowToHigh']);

        return $data;
    }

    public function setUp($setUp = null)
    {
        if (func_num_args() === 0) {
            return $this->setUp;
        } else {
            assert('is_string($setUp)');

            $this->setUp = $setUp;
        }
    }

    public function tearDown($tearDown = null)
    {
        if (func_num_args() === 0) {
            return $this->tearDown;
        } else {
            assert('is_string($tearDown)');

            $this->tearDown = $tearDown;
        }
    }

    private function lowToHigh($low, $high)
    {
        return $low->time < $high->time ? -1 : 1;
    }

    private function toObject($snippet, $timeFilename, $wholeFilename, $time)
    {
        return (object) get_defined_vars();
    }

    private function toFilename($prefix, $snippet)
    {
        $filename = sys_get_temp_dir() .
            DIRECTORY_SEPARATOR .
            $prefix .
            md5($this->setUp . $snippet . $this->tearDown);

        return $filename;
    }

    private function toTime($filename)
    {
        assert('is_readable($filename)');

        for ($timeCount = 0; $timeCount < $this->timeCount; $timeCount++) {
            $proc = proc_open($filename, [
                1 => ['pipe', 'w'],
                2 => ['pipe', 'w'],
            ], $pipes);
            assert('is_resource($proc)');

            $error = stream_get_contents($pipes[2]);
            if ($error) {
                $error = json_decode($error);
                assert('is_array($error)');
                throw new ErrorException(...$error);
            }

            $time = stream_get_contents($pipes[1]);

            $times[] = (double) `$filename`;
        }

        $time = array_sum($times) / $this->timeCount;

        return $time;
    }

    private function toTimeFilename($snippet)
    {
        assert('is_string($snippet)');

        $timeFilename = $this->toFilename(self::TIME_FILENAME_PREFIX, $snippet);
        assert('$timeFilename !== false');

        if (!is_readable($timeFilename)) {
            $content = $this->toTimePhp($snippet);

            $success = file_put_contents($timeFilename, $content);
            assert('$success !== false');

            $success = chmod($timeFilename, 0777);
            assert('$success !== false');
        }

        return $timeFilename;
    }

    private function toTimePhp($snippet)
    {
        assert('is_string($snippet)');

        return sprintf(
            self::TIME_TEMPLATE,
            $this->setUp(),
            $this->loopCount(),
            $snippet,
            $this->tearDown()
        );
    }

    private function toWholeFilename($snippet)
    {
        assert('is_string($snippet)');

        $wholeFilename = $this->toFilename(self::WHOLE_FILENAME_PREFIX, $snippet);
        assert('$wholeFilename !== false');

        if (!is_readable($wholeFilename)) {
            $content = $this->toWholePhp($snippet);

            $success = file_put_contents($wholeFilename, $content);
            assert('$success !== false');

            $success = chmod($wholeFilename, 0777);
            assert('$success !== false');
        }

        return $wholeFilename;
    }

    private function toWholePhp($snippet)
    {
        assert('is_string($snippet)');

        return sprintf(
            self::WHOLE_TEMPLATE,
            $this->setUp(),
            $snippet,
            $this->tearDown()
        );
    }
}
