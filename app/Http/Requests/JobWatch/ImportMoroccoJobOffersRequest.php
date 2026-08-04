<?php

namespace App\Http\Requests\JobWatch;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class ImportMoroccoJobOffersRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'offers_csv' => [
                'required',
                'file',
                'max:10240',
            ],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $file = $this->file('offers_csv');

            if ($file === null) {
                return;
            }

            $extension = strtolower(
                $file->getClientOriginalExtension()
            );

            if (! in_array($extension, ['csv', 'txt'], true)) {
                $validator->errors()->add(
                    'offers_csv',
                    'Le fichier doit être au format CSV.'
                );
            }
        });
    }

    public function messages(): array
    {
        return [
            'offers_csv.required' => (
                'Sélectionnez un fichier CSV contenant les offres.'
            ),
            'offers_csv.file' => 'Le fichier envoyé est invalide.',
            'offers_csv.max' => (
                'Le fichier ne doit pas dépasser 10 Mo.'
            ),
        ];
    }
}
