<?php

declare(strict_types=1);

namespace App\Http\Requests\Subscribers;

use App\Enums\SubscriberStatus;
use App\Models\Subscriber;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UpdateSubscriberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    // Same normalization as StoreSubscriberRequest - see its comment and
    // PROJECT_NOTES.md Day 9.
    protected function prepareForValidation(): void
    {
        if ($this->has('email')) {
            $this->merge(['email' => Str::lower((string) $this->input('email'))]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var Subscriber $subscriber */
        $subscriber = $this->route('subscriber');

        return [
            'email' => [
                'sometimes',
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('subscribers', 'email')->ignore($subscriber->id),
            ],
            'name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'status' => ['sometimes', Rule::enum(SubscriberStatus::class)],
        ];
    }
}
