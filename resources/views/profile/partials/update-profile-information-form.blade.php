<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            Informations du profil
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            Modifiez les informations personnelles de votre compte.
        </p>
    </header>

    <form
        method="post"
        action="{{ route('profile.update') }}"
        class="mt-6 space-y-6"
    >
        @csrf
        @method('patch')

        {{-- Nom du compte --}}
        <div>
            <x-input-label for="name" value="Nom du compte" />

            <x-text-input
                id="name"
                name="name"
                type="text"
                class="mt-1 block w-full"
                :value="old('name', $user->name)"
                required
                autofocus
                autocomplete="name"
            />

            <x-input-error
                class="mt-2"
                :messages="$errors->get('name')"
            />
        </div>

        {{-- Adresse électronique --}}
        <div>
            <x-input-label
                for="email"
                value="Adresse électronique"
            />

            <x-text-input
                id="email"
                name="email"
                type="email"
                class="mt-1 block w-full"
                :value="old('email', $user->email)"
                required
                autocomplete="username"
            />

            <x-input-error
                class="mt-2"
                :messages="$errors->get('email')"
            />

            @if (
                $user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail
                && ! $user->hasVerifiedEmail()
            )
                <div class="mt-2">
                    <p class="text-sm text-gray-800">
                        Votre adresse électronique n’est pas encore vérifiée.

                        <button
                            form="send-verification"
                            class="rounded-md text-sm text-gray-600 underline hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                        >
                            Cliquez ici pour renvoyer le courriel de vérification.
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 text-sm font-medium text-green-600">
                            Un nouveau lien de vérification a été envoyé à votre
                            adresse électronique.
                        </p>
                    @endif
                </div>
            @endif
        </div>

        {{-- Prénom --}}
        <div>
            <x-input-label for="first_name" value="Prénom" />

            <x-text-input
                id="first_name"
                name="first_name"
                type="text"
                class="mt-1 block w-full"
                :value="old('first_name', $user->profile?->first_name)"
                autocomplete="given-name"
            />

            <x-input-error
                class="mt-2"
                :messages="$errors->get('first_name')"
            />
        </div>

        {{-- Nom de famille --}}
        <div>
            <x-input-label for="last_name" value="Nom de famille" />

            <x-text-input
                id="last_name"
                name="last_name"
                type="text"
                class="mt-1 block w-full"
                :value="old('last_name', $user->profile?->last_name)"
                autocomplete="family-name"
            />

            <x-input-error
                class="mt-2"
                :messages="$errors->get('last_name')"
            />
        </div>

        {{-- Téléphone --}}
        <div>
            <x-input-label for="phone" value="Téléphone" />

            <x-text-input
                id="phone"
                name="phone"
                type="text"
                class="mt-1 block w-full"
                :value="old('phone', $user->profile?->phone)"
                autocomplete="tel"
            />

            <x-input-error
                class="mt-2"
                :messages="$errors->get('phone')"
            />
        </div>

        {{-- Date de naissance --}}
        <div>
            <x-input-label
                for="birth_date"
                value="Date de naissance"
            />

            <x-text-input
                id="birth_date"
                name="birth_date"
                type="date"
                class="mt-1 block w-full"
                :value="old(
                    'birth_date',
                    $user->profile?->birth_date?->format('Y-m-d')
                )"
            />

            <x-input-error
                class="mt-2"
                :messages="$errors->get('birth_date')"
            />
        </div>

        {{-- Genre --}}
        <div>
            <x-input-label for="gender" value="Genre" />

            <select
                id="gender"
                name="gender"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
            >
                <option value="">Sélectionner</option>

                <option
                    value="homme"
                    @selected(
                        old('gender', $user->profile?->gender) === 'homme'
                    )
                >
                    Homme
                </option>

                <option
                    value="femme"
                    @selected(
                        old('gender', $user->profile?->gender) === 'femme'
                    )
                >
                    Femme
                </option>

                <option
                    value="autre"
                    @selected(
                        old('gender', $user->profile?->gender) === 'autre'
                    )
                >
                    Autre
                </option>
            </select>

            <x-input-error
                class="mt-2"
                :messages="$errors->get('gender')"
            />
        </div>

        {{-- Adresse --}}
        <div>
            <x-input-label for="address" value="Adresse" />

            <x-text-input
                id="address"
                name="address"
                type="text"
                class="mt-1 block w-full"
                :value="old('address', $user->profile?->address)"
                autocomplete="street-address"
            />

            <x-input-error
                class="mt-2"
                :messages="$errors->get('address')"
            />
        </div>

        {{-- Ville --}}
        <div>
            <x-input-label for="city" value="Ville" />

            <x-text-input
                id="city"
                name="city"
                type="text"
                class="mt-1 block w-full"
                :value="old('city', $user->profile?->city)"
                autocomplete="address-level2"
            />

            <x-input-error
                class="mt-2"
                :messages="$errors->get('city')"
            />
        </div>

        {{-- Pays --}}
        <div>
            <x-input-label for="country" value="Pays" />

            <x-text-input
                id="country"
                name="country"
                type="text"
                class="mt-1 block w-full"
                :value="old('country', $user->profile?->country)"
                autocomplete="country-name"
            />

            <x-input-error
                class="mt-2"
                :messages="$errors->get('country')"
            />
        </div>

        {{-- Biographie --}}
        <div>
            <x-input-label for="bio" value="Biographie" />

            <textarea
                id="bio"
                name="bio"
                rows="5"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
            >{{ old('bio', $user->profile?->bio) }}</textarea>

            <x-input-error
                class="mt-2"
                :messages="$errors->get('bio')"
            />
        </div>

        {{-- Bouton d’enregistrement --}}
        <div class="flex items-center gap-4">
            <x-primary-button>
                Enregistrer
            </x-primary-button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 3000)"
                    class="text-sm text-gray-600"
                >
                    Profil mis à jour.
                </p>
            @endif
        </div>
    </form>
</section>