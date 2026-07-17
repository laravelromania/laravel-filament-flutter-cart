<?php

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| Feature tests for the application boot the full Laravel test kernel via the
| base TestCase. Pure unit tests (the app's tests/Unit and each module's
| tests/Unit) stay framework-free so value objects can be tested in isolation.
|
*/

uses(Tests\TestCase::class)->in('Feature');
