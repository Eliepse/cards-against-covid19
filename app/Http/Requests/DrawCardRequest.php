<?php

namespace App\Http\Requests;

use App\Card;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DrawCardRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'type' => ['required', Rule::in([Card::WHITE, Card::BLACK])],
	        'amount' => ['required', 'integer', 'min:1']
        ];
    }
}
