# Jobs helps to organize jobs easily.

[![Build Status](https://travis-ci.org/limen/php-jobs.svg?branch=master)](https://travis-ci.org/limen/php-jobs)
[![Packagist](https://img.shields.io/packagist/l/limen/php-jobs.svg?maxAge=2592000)](https://packagist.org/packages/limen/php-jobs)

## Features

+ jobs are packed into jobset
+ jobs can be executed orderly or not orderly or combined
+ job's execution time is settable
+ job would be retried after failed until reached the max retry count
+ the max retry count is adjustable
+ job's next execution time is settable
+ job can be asynchronous (execute and wait feedback)

## Installation

Recommend to install via [composer](https://getcomposer.org/ "").

```bash
composer require "limen/php-jobs"
```

## Usage

see [examples](https://github.com/limen/php-jobs/tree/master/src/Examples) 
 [tests](https://github.com/limen/php-jobs/tree/master/tests)

## Objects

### Jobset

A jobset is consist of one or more jobs. 

The jobs can be executed orderly or not orderly or combined. 

### Job

Job has execution time, so you can decide when the job should be executed.

If a job failed, its tried count would increase by 1 and its next execution time is up to you.

The max retry count is also up to you. 

If the tried count reached the max retry count, the job would be marked as "failed".

## Want to use in Laravel?

see [laravel-jobs](https://github.com/limen/laravel-jobs)

## Development

### Test

```bash
$ phpunit --bootstrap tests/bootstrap.php tests/
```
