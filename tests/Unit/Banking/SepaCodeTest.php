<?php

use SchenkeIo\Invoice\Banking\SepaCode;

it('can generate a sepa code', function () {
    $code = new SepaCode('name', '84493', 12.34, 'information');
    expect($code->dataUri())->toBeString();
});
