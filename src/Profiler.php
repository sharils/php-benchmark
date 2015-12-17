<?php
namespace Sharils;

class Profiler
{
    const FILENAME_PREFIX = 'php-profiler-';

    const TEMPLATE = <<<'PHP'
<?php
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

    private $count = 1000000;

    private $setUp = null;

    private $tearDown = null;

    public function count($count = null)
    {
        if (func_num_args() === 0) {
            return $this->count;
        } else {
            assert('is_int($count) && 0 < $count && $count <= 1000000');

            $this->count = $count;
        }
    }

    public function profile(array $snippets)
    {
        $phps = array_map([$this, 'toPhp'], $snippets);

        $filenames = array_map([$this, 'toFilename'], $phps);

        $times = array_map([$this, 'toTime'], $filenames);

        $data = array_map([$this, 'toObject'], $snippets, $filenames, $times);

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

    private function toFilename($content)
    {
        assert('is_string($content)');

        $filename = sys_get_temp_dir() .
            DIRECTORY_SEPARATOR .
            self::FILENAME_PREFIX .
            md5($content);

        assert('$filename !== false');

        $success = file_put_contents($filename, $content);
        assert('$success !== false');

        return $filename;
    }

    private function toObject($snippet, $filename, $time)
    {
        return (object) get_defined_vars();
    }

    private function toPhp($snippet)
    {
        assert('is_string($snippet)');

        return sprintf(
            self::TEMPLATE,
            $this->setUp(),
            $this->count(),
            $snippet,
            $this->tearDown()
        );
    }

    private function toTime($filename)
    {
        assert('is_readable($filename)');

        return (double) `php $filename`;
    }
}
