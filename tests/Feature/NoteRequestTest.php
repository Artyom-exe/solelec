<?php

use App\Http\Requests\NoteRequest;
use Illuminate\Validation\Validator;

it('validates note request success', function () {
    $request = new NoteRequest();
    $data = ['content' => 'Ceci est une note valide'];

    $rules = (new NoteRequest())->rules();

    $validator = validator($data, $rules);
    expect($validator->passes())->toBeTrue();
});

it('validates note request failure when content missing', function () {
    $rules = (new NoteRequest())->rules();
    $validator = validator([], $rules);
    expect($validator->fails())->toBeTrue();
});
