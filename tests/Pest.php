<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific
| PHPUnit test case class. By default, that class is "PHPUnit\Framework\TestCase".
| Here we bind our extended TestCase (with tenant helpers) to all Feature tests,
| and also apply RefreshDatabase so each feature test starts with a clean DB.
|
*/

uses(TestCase::class)->in('Feature');
uses(TestCase::class, RefreshDatabase::class)->in('Feature');

/*
|--------------------------------------------------------------------------
| Unit test directories that need the TestCase (e.g. for policies, invariants)
|--------------------------------------------------------------------------
*/

uses(TestCase::class)->in('Unit/Policies');
uses(TestCase::class)->in('Unit/Invariants');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
*/

expect()->extend('toBeValidStatus', function () {
    $validStatuses = ['open', 'in_progress', 'pending', 'resolved', 'closed', 'pending_approval', 'approved', 'rejected', 'scheduled'];

    return $this->toBeIn($validStatuses);
});

expect()->extend('toBeValidPriority', function () {
    $validPriorities = ['low', 'medium', 'high', 'critical', 'urgent'];

    return $this->toBeIn($validPriorities);
});
