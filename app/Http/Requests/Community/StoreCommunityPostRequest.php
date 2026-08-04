<?php

namespace App\Http\Requests\Community;

use Illuminate\Foundation\Http\FormRequest;

class StoreCommunityPostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'body' => [
                'required',
                'string',
                'min:2',
                'max:5000',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'body.required' => 'Le contenu de la publication est obligatoire.',
            'body.min' => 'La publication doit contenir au moins 2 caractères.',
            'body.max' => 'La publication ne doit pas dépasser 5 000 caractères.',
        ];
    }
}
