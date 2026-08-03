<?php

namespace App\Http\Requests\JobWatch;

use App\Models\JobWatch;

class UpdateJobWatchRequest extends StoreJobWatchRequest
{
    public function authorize(): bool
    {
        $jobWatch = $this->route('jobWatch');

        return $jobWatch instanceof JobWatch
            && $this->user() !== null
            && $this->user()->can('update', $jobWatch);
    }

    public function rules(): array
    {
        return parent::rules();
    }
}
