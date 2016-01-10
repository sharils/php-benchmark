phrofiler
=========

PHP snippet profiler.

Install
-------

```sh
composer require sharils/phrofiler
```

Example
-------

```sh
./vendor/bin/phrofiler -s'$a = 4;' '$a === null' '!isset($a)' 'is_null($a)'
```

```
      snippet: $a === null
 timeFilename: /tmp/php-phrofiler-time-dc5a5e959c87e239e305ba72a7a461fd
wholeFilename: /tmp/php-phrofiler-whole-dc5a5e959c87e239e305ba72a7a461fd
         time: 0.0029055833816528 (average seconds)
        ratio: 100%

      snippet: !isset($a)
 timeFilename: /tmp/php-phrofiler-time-7466ec99408428ca9ad95b453d3c55fb
wholeFilename: /tmp/php-phrofiler-whole-7466ec99408428ca9ad95b453d3c55fb
         time: 0.0034789085388184 (average seconds)
        ratio: 84%

      snippet: is_null($a)
 timeFilename: /tmp/php-phrofiler-time-c848003afeaf7dd06e8d2c830fe63a4a
wholeFilename: /tmp/php-phrofiler-whole-c848003afeaf7dd06e8d2c830fe63a4a
         time: 0.0079437255859374 (average seconds)
        ratio: 37%
```
