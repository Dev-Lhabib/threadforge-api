<?php

namespace App\Http\Requests\Blueprint;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class BlueprintStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'tone' => ['required', 'string', 'max:255'],
            'max_hashtags' => ['required', 'integer', 'min:0', 'max:10'],
            'max_characters' => ['required', 'integer', 'min:0', 'max:280'],
            'additional_rules' => ['nullable', 'string'],
        ];
    }
}
