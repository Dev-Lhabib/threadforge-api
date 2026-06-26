<?php

namespace App\Http\Requests\RawContent;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class RawContentStoreRequest extends FormRequest
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
            "title" => ['nullable', 'string','max:255'],
            'content' => ['required', 'string','min:20'],
            'source_type' => ['required', 'string', 'in:raw,markdown'],
            'blueprint_id' => ['required', 'integer', 'exists:blueprints,id'],
        ];
    }
}
