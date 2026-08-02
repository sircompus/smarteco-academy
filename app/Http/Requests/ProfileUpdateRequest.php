<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Autoriser l'utilisateur connecté à modifier son profil.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Règles de validation du profil.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Informations du compte utilisateur
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)
                    ->ignore($this->user()->id),
            ],

            // Informations du profil
            'first_name' => [
                'nullable',
                'string',
                'max:100',
            ],

            'last_name' => [
                'nullable',
                'string',
                'max:100',
            ],

            'phone' => [
                'nullable',
                'string',
                'max:30',
            ],

            'birth_date' => [
                'nullable',
                'date',
                'before_or_equal:today',
            ],

            'gender' => [
                'nullable',
                'string',
                'max:20',
            ],

            'address' => [
                'nullable',
                'string',
                'max:255',
            ],

            'city' => [
                'nullable',
                'string',
                'max:100',
            ],

            'country' => [
                'nullable',
                'string',
                'max:100',
            ],

            'bio' => [
                'nullable',
                'string',
                'max:2000',
            ],
        ];
    }

    /**
     * Messages de validation personnalisés.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Le nom du compte est obligatoire.',
            'name.max' => 'Le nom ne doit pas dépasser 255 caractères.',

            'email.required' => 'L’adresse électronique est obligatoire.',
            'email.email' => 'L’adresse électronique n’est pas valide.',
            'email.unique' => 'Cette adresse électronique est déjà utilisée.',

            'first_name.max' => 'Le prénom ne doit pas dépasser 100 caractères.',
            'last_name.max' => 'Le nom ne doit pas dépasser 100 caractères.',

            'phone.max' => 'Le numéro de téléphone ne doit pas dépasser 30 caractères.',

            'birth_date.date' => 'La date de naissance n’est pas valide.',
            'birth_date.before_or_equal' => 'La date de naissance ne peut pas être postérieure à aujourd’hui.',

            'gender.max' => 'Le genre ne doit pas dépasser 20 caractères.',
            'address.max' => 'L’adresse ne doit pas dépasser 255 caractères.',
            'city.max' => 'La ville ne doit pas dépasser 100 caractères.',
            'country.max' => 'Le pays ne doit pas dépasser 100 caractères.',
            'bio.max' => 'La biographie ne doit pas dépasser 2 000 caractères.',
        ];
    }
}