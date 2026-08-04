<?php

namespace App\Http\Requests\Community;

use Illuminate\Foundation\Http\FormRequest;

class StoreCommunityCommentRequest extends FormRequest
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
                'min:1',
                'max:2000',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'body.required' => 'Le commentaire est obligatoire.',
            'body.max' => (
                'Le commentaire ne doit pas dépasser 2 000 caractères.'
            ),
        ];
    }
}
