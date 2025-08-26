<?php

use App\Http\Requests\InterventionStatusRequest;

it('accepts a valid status', function () {
    $rules = (new InterventionStatusRequest())->rules();
    $validator = validator(['status' => 'planifiée'], $rules);
    expect($validator->passes())->toBeTrue();
});

it('rejects an invalid status', function () {
    $rules = (new InterventionStatusRequest())->rules();
    $validator = validator(['status' => 'unknown'], $rules);
    expect($validator->fails())->toBeTrue();
});
